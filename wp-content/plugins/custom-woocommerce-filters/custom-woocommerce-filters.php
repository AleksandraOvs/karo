<?php
/*
Plugin Name: Custom WooCommerce Filters
Description: Настраиваемый AJAX-фильтр WooCommerce с поддержкой числовых диапазонов.
Version: 3.0.0
Author: PurpleWeb
*/

if (!defined('ABSPATH')) {
    exit;
}


/* =========================================================
 * WOOCOMMERCE
 * ========================================================= */

add_action('admin_init', function () {

    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
?>
            <div class="notice notice-error">
                <p>
                    <strong>Custom WooCommerce Filters:</strong>
                    для работы плагина необходимо установить и активировать WooCommerce.
                </p>
            </div>
    <?php
        });
    }
});


/* =========================================================
 * ATTRIBUTES
 * ========================================================= */

/**
 * Все глобальные атрибуты WooCommerce.
 */
function cwc_get_all_attributes()
{
    if (!function_exists('wc_get_attribute_taxonomies')) {
        return [];
    }

    $attributes = wc_get_attribute_taxonomies();

    if (empty($attributes)) {
        return [];
    }

    $result = [];

    foreach ($attributes as $attribute) {

        $taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);

        $result[$taxonomy] = [
            'id'       => (int) $attribute->attribute_id,
            'name'     => $attribute->attribute_name,
            'label'    => $attribute->attribute_label,
            'taxonomy' => $taxonomy,
        ];
    }

    return $result;
}


/**
 * Настройки фильтров.
 */
function cwc_get_filter_settings()
{
    $settings = get_option('cwc_filter_settings', []);

    return is_array($settings) ? $settings : [];
}


/**
 * Порядок фильтров.
 */
function cwc_get_filter_order()
{
    $order = get_option('cwc_filter_order', []);

    return is_array($order) ? $order : [];
}


/**
 * Синхронизация настроек с существующими
 * WooCommerce атрибутами.
 */
function cwc_sync_filter_settings()
{
    $attributes = cwc_get_all_attributes();

    $settings = cwc_get_filter_settings();
    $order    = cwc_get_filter_order();

    /*
     * Новые атрибуты.
     */
    foreach ($attributes as $taxonomy => $attribute) {

        if (!isset($settings[$taxonomy])) {

            $settings[$taxonomy] = [
                'enabled' => false,
                'type'    => 'values',
                'ranges'  => [],
            ];
        }
    }


    /*
     * Удаляем отсутствующие атрибуты.
     */
    foreach ($settings as $taxonomy => $setting) {

        if (!isset($attributes[$taxonomy])) {
            unset($settings[$taxonomy]);
        }
    }


    /*
     * Чистим порядок.
     */
    $order = array_values(array_filter(
        $order,
        function ($taxonomy) use ($attributes) {
            return isset($attributes[$taxonomy]);
        }
    ));


    /*
     * Добавляем новые атрибуты в конец.
     */
    foreach ($attributes as $taxonomy => $attribute) {

        if (!in_array($taxonomy, $order, true)) {
            $order[] = $taxonomy;
        }
    }


    update_option('cwc_filter_settings', $settings);
    update_option('cwc_filter_order', $order);

    return [
        'settings' => $settings,
        'order'    => $order,
    ];
}


/* =========================================================
 * ADMIN MENU
 * ========================================================= */

add_action('admin_menu', function () {

    add_submenu_page(
        'woocommerce',
        'Фильтры товаров',
        'Фильтры товаров',
        'manage_woocommerce',
        'cwc-filters',
        'cwc_render_settings_page'
    );
});


/* =========================================================
 * ADMIN ASSETS
 * ========================================================= */

add_action('admin_enqueue_scripts', function ($hook) {

    if ($hook !== 'woocommerce_page_cwc-filters') {
        return;
    }

    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-sortable');

    wp_enqueue_style(
        'cwc-admin-style',
        plugin_dir_url(__FILE__) . 'css/admin.css',
        [],
        '3.0.0'
    );

    wp_enqueue_script(
        'cwc-admin-script',
        plugin_dir_url(__FILE__) . 'js/admin.js',
        ['jquery', 'jquery-ui-sortable'],
        '3.0.0',
        true
    );
});


/* =========================================================
 * FRONTEND ASSETS
 * ========================================================= */

add_action('wp_enqueue_scripts', function () {

    if (!function_exists('is_woocommerce')) {
        return;
    }

    /*
     * Подключаем только там, где потенциально
     * используется магазин / каталог.
     */
    if (
        !is_shop() &&
        !is_product_category() &&
        !is_product_tag()
    ) {
        return;
    }


    /*
     * jQuery UI Slider.
     */
    wp_enqueue_script('jquery-ui-slider');

    wp_enqueue_style(
        'jquery-ui-style',
        'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css',
        [],
        '1.13.2'
    );


    /*
     * CSS фильтра.
     */
    wp_enqueue_style(
        'cwc-style',
        plugin_dir_url(__FILE__) . 'css/style.css',
        [],
        '3.0.0'
    );


    /*
     * Основной JS интерфейса.
     */
    wp_enqueue_script(
        'cwc-scripts',
        plugin_dir_url(__FILE__) . 'js/scripts.js',
        [],
        '3.0.0',
        true
    );


    /*
     * AJAX фильтры.
     */
    wp_enqueue_script(
        'cwc-ajax-filters',
        plugin_dir_url(__FILE__) . 'js/ajax-filters.js',
        ['jquery', 'jquery-ui-slider'],
        '3.0.0',
        true
    );


    wp_localize_script(
        'cwc-ajax-filters',
        'cwc_ajax_object',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cwc_filter_nonce'),
        ]
    );
});


/* =========================================================
 * ADMIN SAVE
 * ========================================================= */

add_action('admin_post_cwc_save_filters', function () {

    if (!current_user_can('manage_woocommerce')) {
        wp_die('Недостаточно прав.');
    }

    check_admin_referer('cwc_save_filters');

    $attributes = cwc_get_all_attributes();

    $posted_settings = isset($_POST['filters'])
        ? wp_unslash($_POST['filters'])
        : [];

    if (!is_array($posted_settings)) {
        $posted_settings = [];
    }

    $settings = [];


    foreach ($attributes as $taxonomy => $attribute) {

        $data = isset($posted_settings[$taxonomy])
            ? $posted_settings[$taxonomy]
            : [];


        /*
         * Enabled.
         */
        $enabled = !empty($data['enabled']);


        /*
         * Type.
         */
        $type = isset($data['type'])
            ? sanitize_key($data['type'])
            : 'values';

        if (!in_array($type, ['values', 'range'], true)) {
            $type = 'values';
        }


        /*
         * Ranges.
         */
        $ranges = [];

        if (
            $type === 'range' &&
            !empty($data['ranges']) &&
            is_array($data['ranges'])
        ) {

            foreach ($data['ranges'] as $range) {

                if (!is_array($range)) {
                    continue;
                }

                $min = isset($range['min'])
                    ? trim((string) $range['min'])
                    : '';

                $max = isset($range['max'])
                    ? trim((string) $range['max'])
                    : '';


                /*
                 * 0,5 -> 0.5
                 */
                $min = str_replace(',', '.', $min);
                $max = str_replace(',', '.', $max);


                if ($min === '' && $max === '') {
                    continue;
                }


                if ($min !== '' && !is_numeric($min)) {
                    continue;
                }

                if ($max !== '' && !is_numeric($max)) {
                    continue;
                }


                /*
                 * Если обе границы есть,
                 * проверяем порядок.
                 */
                if (
                    $min !== '' &&
                    $max !== '' &&
                    (float) $min > (float) $max
                ) {
                    continue;
                }


                $ranges[] = [
                    'min' => $min,
                    'max' => $max,
                ];
            }
        }


        $settings[$taxonomy] = [
            'enabled' => $enabled,
            'type'    => $type,
            'ranges'  => $ranges,
        ];
    }


    /* -----------------------------------------------------
     * ORDER
     * ----------------------------------------------------- */

    $posted_order = isset($_POST['filter_order'])
        ? wp_unslash($_POST['filter_order'])
        : [];

    $order = [];

    if (is_array($posted_order)) {

        foreach ($posted_order as $taxonomy) {

            $taxonomy = sanitize_key($taxonomy);

            if (
                isset($attributes[$taxonomy]) &&
                !in_array($taxonomy, $order, true)
            ) {
                $order[] = $taxonomy;
            }
        }
    }


    foreach ($attributes as $taxonomy => $attribute) {

        if (!in_array($taxonomy, $order, true)) {
            $order[] = $taxonomy;
        }
    }


    update_option('cwc_filter_settings', $settings);
    update_option('cwc_filter_order', $order);


    wp_safe_redirect(
        add_query_arg(
            [
                'page'    => 'cwc-filters',
                'updated' => '1',
            ],
            admin_url('admin.php')
        )
    );

    exit;
});


/* =========================================================
 * ADMIN PAGE
 * ========================================================= */

function cwc_render_settings_page()
{
    if (!current_user_can('manage_woocommerce')) {
        return;
    }

    $data = cwc_sync_filter_settings();

    $attributes = cwc_get_all_attributes();

    $settings = $data['settings'];
    $order    = $data['order'];

    ?>

    <div class="wrap cwc-admin">

        <h1>Фильтры товаров</h1>

        <?php if (isset($_GET['updated'])) : ?>

            <div class="notice notice-success is-dismissible">
                <p>
                    Настройки фильтра сохранены.
                </p>
            </div>

        <?php endif; ?>


        <?php if (empty($attributes)) : ?>

            <div class="notice notice-warning">

                <p>
                    В WooCommerce пока нет глобальных атрибутов товаров.
                </p>

                <p>
                    Создайте атрибуты в
                    <strong>Товары → Атрибуты</strong>.
                </p>

            </div>

        <?php else : ?>

            <form
                method="post"
                action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

                <input
                    type="hidden"
                    name="action"
                    value="cwc_save_filters">

                <?php wp_nonce_field('cwc_save_filters'); ?>


                <div class="cwc-admin-description">

                    <p>
                        Здесь можно выбрать атрибуты, которые будут использоваться
                        в фильтре товаров.
                    </p>

                    <p>
                        Перетаскивайте атрибуты для изменения порядка их отображения
                        на странице магазина.
                    </p>

                </div>


                <div class="cwc-filter-list">

                    <?php foreach ($order as $taxonomy) : ?>

                        <?php

                        if (!isset($attributes[$taxonomy])) {
                            continue;
                        }

                        $attribute = $attributes[$taxonomy];

                        $setting = isset($settings[$taxonomy])
                            ? $settings[$taxonomy]
                            : [
                                'enabled' => false,
                                'type'    => 'values',
                                'ranges'  => [],
                            ];

                        $enabled = !empty($setting['enabled']);

                        $type = $setting['type'] ?? 'values';

                        $ranges = !empty($setting['ranges'])
                            ? $setting['ranges']
                            : [];

                        ?>

                        <div
                            class="cwc-filter-item"
                            data-taxonomy="<?php echo esc_attr($taxonomy); ?>">

                            <div class="cwc-filter-item__header">

                                <span class="cwc-drag-handle">
                                    ☰
                                </span>


                                <div class="cwc-filter-item__name">

                                    <strong>
                                        <?php echo esc_html($attribute['label']); ?>
                                    </strong>

                                    <code>
                                        <?php echo esc_html($taxonomy); ?>
                                    </code>

                                </div>


                                <label class="cwc-switch">

                                    <input
                                        type="checkbox"
                                        name="filters[<?php echo esc_attr($taxonomy); ?>][enabled]"
                                        value="1"
                                        <?php checked($enabled); ?>>

                                    <span>
                                        Показывать
                                    </span>

                                </label>


                                <select
                                    class="cwc-filter-type"
                                    name="filters[<?php echo esc_attr($taxonomy); ?>][type]">

                                    <option
                                        value="values"
                                        <?php selected($type, 'values'); ?>>
                                        Значения
                                    </option>

                                    <option
                                        value="range"
                                        <?php selected($type, 'range'); ?>>
                                        Диапазоны
                                    </option>

                                </select>


                                <button
                                    type="button"
                                    class="button cwc-expand">
                                    Настроить
                                </button>

                            </div>


                            <div class="cwc-filter-item__body">

                                <div class="cwc-values-info">

                                    <p>
                                        На сайте будут выведены существующие
                                        значения этого атрибута.
                                    </p>

                                </div>


                                <div class="cwc-ranges-settings">

                                    <h3>
                                        Диапазоны
                                    </h3>

                                    <p class="description">
                                        Укажите числовые границы.
                                        Например: 0–0,5, 0,5–2, 2–5.
                                    </p>


                                    <div class="cwc-ranges">

                                        <?php foreach ($ranges as $index => $range) : ?>

                                            <div class="cwc-range-row">

                                                <input
                                                    type="text"
                                                    inputmode="decimal"
                                                    name="filters[<?php echo esc_attr($taxonomy); ?>][ranges][<?php echo esc_attr($index); ?>][min]"
                                                    value="<?php echo esc_attr($range['min']); ?>"
                                                    placeholder="От">

                                                <span>—</span>

                                                <input
                                                    type="text"
                                                    inputmode="decimal"
                                                    name="filters[<?php echo esc_attr($taxonomy); ?>][ranges][<?php echo esc_attr($index); ?>][max]"
                                                    value="<?php echo esc_attr($range['max']); ?>"
                                                    placeholder="До">

                                                <button
                                                    type="button"
                                                    class="button cwc-remove-range">
                                                    Удалить
                                                </button>

                                            </div>

                                        <?php endforeach; ?>

                                    </div>


                                    <button
                                        type="button"
                                        class="button cwc-add-range">
                                        + Добавить диапазон
                                    </button>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>


                <p class="submit">

                    <button
                        type="submit"
                        class="button button-primary button-large">
                        Сохранить настройки
                    </button>

                </p>

            </form>

        <?php endif; ?>

    </div>

<?php
}


/* =========================================================
 * FRONTEND HELPERS
 * ========================================================= */

/**
 * Убираем "Товар" из заголовка.
 */
function cwc_clean_title($title)
{
    return preg_replace(
        '/^Товар\s*[:\-–—]?\s*/ui',
        '',
        $title
    );
}


/**
 * Нормализуем числовое значение.
 */
function cwc_normalize_number($value)
{
    $value = trim((string) $value);
    $value = str_replace(',', '.', $value);

    return is_numeric($value)
        ? (float) $value
        : null;
}


/**
 * Проверяем, является ли term числовым.
 */
function cwc_term_numeric_value($term)
{
    if (!$term) {
        return null;
    }

    return cwc_normalize_number($term->name);
}


/**
 * Сортировка термов:
 * числовые -> по числу,
 * остальные -> по названию.
 */
function cwc_sort_terms($terms)
{
    usort($terms, function ($a, $b) {

        $a_num = cwc_term_numeric_value($a);
        $b_num = cwc_term_numeric_value($b);

        if ($a_num !== null && $b_num !== null) {
            return $a_num <=> $b_num;
        }

        if ($a_num !== null) {
            return -1;
        }

        if ($b_num !== null) {
            return 1;
        }

        return strnatcasecmp(
            $a->name,
            $b->name
        );
    });

    return $terms;
}


/**
 * Получаем диапазон цен магазина / категории.
 */
function cwc_get_category_price_range($category_id = 0)
{
    global $wpdb;

    $where = "
        p.post_type = 'product'
        AND p.post_status = 'publish'
    ";

    $params = [];

    if ($category_id) {

        $where .= "
            AND EXISTS (
                SELECT 1
                FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt
                    ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE tr.object_id = p.ID
                AND tt.taxonomy = 'product_cat'
                AND tt.term_id = %d
            )
        ";

        $params[] = (int) $category_id;
    }


    /*
     * Используем _price.
     * Для variable products WooCommerce обычно
     * хранит актуальную цену вариации / min price
     * через lookup table.
     *
     * Для диапазона самого фильтра дополнительно
     * учитываем lookup table.
     */

    $lookup = $wpdb->prefix . 'wc_product_meta_lookup';

    $sql = "
        SELECT
            MIN(l.min_price) AS min_price,
            MAX(l.max_price) AS max_price
        FROM {$wpdb->posts} p
        INNER JOIN {$lookup} l
            ON p.ID = l.product_id
        WHERE {$where}
    ";

    if (!empty($params)) {
        $sql = $wpdb->prepare($sql, $params);
    }

    $row = $wpdb->get_row($sql);


    if (
        !$row ||
        $row->min_price === null ||
        $row->max_price === null
    ) {
        return [0, 100000];
    }


    return [
        floor((float) $row->min_price),
        ceil((float) $row->max_price),
    ];
}


/**
 * Получаем диапазон цен.
 */
function cwc_get_store_price_range()
{
    return cwc_get_category_price_range(0);
}


/* =========================================================
 * FRONTEND ATTRIBUTE FILTER
 * ========================================================= */

/**
 * Получение термов атрибута, которые реально
 * используются товарами в текущей категории.
 */
function cwc_get_attribute_terms_for_filter(
    $taxonomy,
    $current_cat_id = 0
) {

    if (!taxonomy_exists($taxonomy)) {
        return [];
    }


    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
    ]);


    if (
        is_wp_error($terms) ||
        empty($terms)
    ) {
        return [];
    }


    /*
     * Если категория не выбрана,
     * достаточно hide_empty.
     */
    if (!$current_cat_id) {
        return cwc_sort_terms($terms);
    }


    /*
     * Оставляем только термы,
     * которые используются в текущей категории.
     */
    $valid_term_ids = [];


    $query = new WP_Query([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',

        'tax_query' => [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $current_cat_id,
            ],
        ],
    ]);


    if (!empty($query->posts)) {

        foreach ($query->posts as $product_id) {

            $product_terms = wp_get_post_terms(
                $product_id,
                $taxonomy,
                [
                    'fields' => 'ids',
                ]
            );

            if (
                !is_wp_error($product_terms) &&
                !empty($product_terms)
            ) {
                $valid_term_ids = array_merge(
                    $valid_term_ids,
                    $product_terms
                );
            }
        }
    }


    wp_reset_postdata();


    $valid_term_ids = array_unique(
        array_map('intval', $valid_term_ids)
    );


    if (empty($valid_term_ids)) {
        return [];
    }


    $terms = array_filter(
        $terms,
        function ($term) use ($valid_term_ids) {
            return in_array(
                (int) $term->term_id,
                $valid_term_ids,
                true
            );
        }
    );


    return cwc_sort_terms(array_values($terms));
}


/**
 * Рендер обычного атрибута.
 */
function cwc_render_values_filter(
    $taxonomy,
    $title,
    $current_cat_id = 0,
    $number = 0
) {

    $terms = cwc_get_attribute_terms_for_filter(
        $taxonomy,
        $current_cat_id
    );


    if (empty($terms)) {
        return '';
    }


    ob_start();
?>

    <div class="filter">

        <div class="filter-item__title">


            <?php if ($number) : ?>
                <span class="filter-item__number">
                    <?php echo esc_html($number); ?>.
                </span>
            <?php endif; ?>

            <?php echo esc_html(cwc_clean_title($title)); ?>



        </div>


        <div class="filter-item__content">

            <ul
                class="sidebar-list"
                data-taxonomy="<?php echo esc_attr($taxonomy); ?>">

                <?php foreach ($terms as $term) : ?>

                    <li>

                        <a
                            href="#"
                            class="filter-item"
                            data-taxonomy="<?php echo esc_attr($taxonomy); ?>"
                            data-slug="<?php echo esc_attr($term->slug); ?>">


                            <?php echo esc_html($term->name); ?>

                        </a>

                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    </div>

<?php

    return ob_get_clean();
}


/**
 * Рендер числового диапазона.
 */
function cwc_render_range_filter(
    $taxonomy,
    $title,
    $ranges,
    $number = 0
) {

    if (
        !taxonomy_exists($taxonomy) ||
        empty($ranges)
    ) {
        return '';
    }


    /*
     * Получаем реальные числовые термы.
     *
     * Это важно:
     * диапазон является только интерфейсом,
     * а не WooCommerce term.
     */
    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
    ]);


    if (
        is_wp_error($terms) ||
        empty($terms)
    ) {
        return '';
    }


    $numeric_values = [];


    foreach ($terms as $term) {

        $value = cwc_term_numeric_value($term);

        if ($value !== null) {
            $numeric_values[] = $value;
        }
    }


    if (empty($numeric_values)) {
        return '';
    }


    ob_start();
?>

    <div class="filter">

        <div class="filter-item__title">


            <?php if ($number) : ?>
                <span class="filter-item__number">
                    <?php echo esc_html($number); ?>.
                </span>
            <?php endif; ?>

            <?php echo esc_html(cwc_clean_title($title)); ?>





        </div>


        <div class="filter-item__content">

            <ul
                class="sidebar-list numeric-filter-list"
                data-taxonomy="<?php echo esc_attr($taxonomy); ?>">

                <?php foreach ($ranges as $range) : ?>

                    <?php

                    $min = isset($range['min'])
                        ? cwc_normalize_number($range['min'])
                        : null;

                    $max = isset($range['max'])
                        ? cwc_normalize_number($range['max'])
                        : null;


                    /*
                     * Проверяем, что диапазон
                     * вообще пересекается с существующими
                     * значениями атрибута.
                     */
                    $has_value = false;

                    foreach ($numeric_values as $value) {

                        if (
                            ($min === null || $value >= $min) &&
                            ($max === null || $value <= $max)
                        ) {
                            $has_value = true;
                            break;
                        }
                    }


                    if (!$has_value) {
                        continue;
                    }


                    $label_min = $range['min'] ?? '';
                    $label_max = $range['max'] ?? '';

                    ?>

                    <li>

                        <a
                            href="#"
                            class="filter-item"
                            data-taxonomy="<?php echo esc_attr($taxonomy); ?>"
                            data-min="<?php echo esc_attr($min); ?>"
                            data-max="<?php echo esc_attr($max); ?>">

                            <?php
                            echo esc_html(
                                $label_min . '–' . $label_max
                            );
                            ?>

                        </a>

                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    </div>

<?php

    return ob_get_clean();
}


/* =========================================================
 * PRICE FILTER
 * ========================================================= */

function cwc_render_price_filter()
{
    $current_cat_id = is_product_category()
        ? get_queried_object_id()
        : 0;


    [$min, $max] = cwc_get_category_price_range(
        $current_cat_id
    );


    ob_start();
?>

    <div class="filter">

        <div class="filter-item__title">

            Цена



        </div>


        <div class="filter-item__content">

            <div class="price-range-wrap">

                <div
                    id="price-slider"
                    class="price-range"
                    data-min="<?php echo esc_attr($min); ?>"
                    data-max="<?php echo esc_attr($max); ?>">
                </div>


                <div class="range-inputs">

                    <div class="price-input">

                        <span class="price-prefix">
                            От
                        </span>

                        <input
                            type="number"
                            id="min_price"
                            value="<?php echo esc_attr($min); ?>">

                    </div>


                    <div class="price-input">

                        <span class="price-prefix">
                            До
                        </span>

                        <input
                            type="number"
                            id="max_price"
                            value="<?php echo esc_attr($max); ?>">

                    </div>

                </div>

            </div>

        </div>

    </div>

<?php

    return ob_get_clean();
}


/* =========================================================
 * MAIN SHORTCODE
 * ========================================================= */

function cwc_shop_filters_shortcode()
{
    $current_cat_id = is_product_category()
        ? get_queried_object_id()
        : 0;


    $data = cwc_sync_filter_settings();

    $settings = $data['settings'];
    $order    = $data['order'];

    ob_start();
?>

    <div class="filters-head">
        <?php
        /*
     * ==========================================
     * СОРТИРОВКА
     * ==========================================
     */

        echo '<div class="shop-header__item  shop-header__sorting">';

        woocommerce_catalog_ordering();

        echo '</div>';
        ?>

        <button class="button filter-toggle">

            Фильтры

        </button>

    </div>


    <div
        class="sidebar-area-wrapper _filters"
        data-current-cat="<?php echo esc_attr($current_cat_id); ?>">


        <div class="filters-wrapper">

            <button class="toggle-close">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.75 30.75L15.75 15.75L30.75 30.75M30.75 0.75L15.7471 15.75L0.75 0.75" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>

            </button>

            <h3>Параметры поиска</h3>
            <?php

            /*
             * Цена всегда первая.
             */
            //echo cwc_render_price_filter();


            /*
             * Атрибуты в порядке из админки.
             */
            $filter_number = 1;

            foreach ($order as $taxonomy) {

                if (empty($settings[$taxonomy]['enabled'])) {
                    continue;
                }

                if (!isset($settings[$taxonomy])) {
                    continue;
                }

                if (!taxonomy_exists($taxonomy)) {
                    continue;
                }

                $attribute = cwc_get_all_attributes()[$taxonomy] ?? null;

                if (!$attribute) {
                    continue;
                }

                $type = $settings[$taxonomy]['type'] ?? 'values';

                /*
     * Получаем HTML фильтра.
     */
                if ($type === 'range') {

                    $html = cwc_render_range_filter(
                        $taxonomy,
                        $attribute['label'],
                        $settings[$taxonomy]['ranges'] ?? [],
                        $filter_number
                    );
                } else {

                    $html = cwc_render_values_filter(
                        $taxonomy,
                        $attribute['label'],
                        $current_cat_id,
                        $filter_number
                    );
                }

                /*
     * Номер увеличиваем только если фильтр
     * действительно был выведен.
     */
                if ($html !== '') {

                    echo $html;

                    $filter_number++;
                }
            }
            ?>


            <div class="cwc-filter-actions">

                <!-- <button
                    id="cwc-apply-filters"
                    class="cwc-apply-button">

                    Показать результаты

                </button> -->


                <button
                    id="cwc-reset-filters"
                    class="cwc-reset-button">

                    Сбросить фильтры

                </button>

            </div>

        </div>

    </div>

<?php

    return ob_get_clean();
}


add_shortcode(
    'shop_filters',
    'cwc_shop_filters_shortcode'
);


/* =========================================================
 * AJAX HELPERS
 * ========================================================= */

/**
 * Получаем ID терминов, попадающих
 * в числовой диапазон.
 */
function cwc_get_term_ids_by_numeric_range(
    $taxonomy,
    $min,
    $max
) {

    if (!taxonomy_exists($taxonomy)) {
        return [];
    }


    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
    ]);


    if (
        is_wp_error($terms) ||
        empty($terms)
    ) {
        return [];
    }


    $term_ids = [];


    foreach ($terms as $term) {

        $value = cwc_term_numeric_value($term);

        if ($value === null) {
            continue;
        }


        if (
            ($min === null || $value >= $min) &&
            ($max === null || $value <= $max)
        ) {
            $term_ids[] = (int) $term->term_id;
        }
    }


    return $term_ids;
}


/**
 * Безопасно получаем массив значений.
 */
function cwc_request_array($value)
{
    if (!is_array($value)) {
        $value = [$value];
    }

    return array_values(
        array_filter(
            array_map(
                'sanitize_text_field',
                $value
            ),
            function ($value) {
                return $value !== '';
            }
        )
    );
}


/* =========================================================
 * AJAX FILTER
 * ========================================================= */

function cwc_filter_products_callback()
{
    /*
     * Nonce.
     */
    if (
        empty($_POST['nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['nonce'])),
            'cwc_filter_nonce'
        )
    ) {
        wp_send_json_error([
            'message' => 'Ошибка безопасности запроса.',
        ]);
    }


    $tax_query = [
        'relation' => 'AND',
    ];


    $meta_query = [
        'relation' => 'AND',
    ];


    /* =====================================================
     * ATTRIBUTE VALUES
     * ===================================================== */

    foreach ($_POST as $key => $value) {

        if (
            strpos($key, 'filter_') !== 0
        ) {
            continue;
        }


        $taxonomy = str_replace(
            'filter_',
            '',
            $key
        );


        if (!taxonomy_exists($taxonomy)) {
            continue;
        }


        $terms = cwc_request_array($value);


        if (empty($terms)) {
            continue;
        }


        /*
         * Один атрибут:
         *
         * material = steel OR aluminium
         *
         * Разные атрибуты:
         *
         * material AND country
         */
        $tax_query[] = [
            'taxonomy' => $taxonomy,
            'field'    => 'slug',
            'terms'    => $terms,
            'operator' => 'IN',
        ];
    }


    /* =====================================================
     * NUMERIC RANGES
     * ===================================================== */

    foreach ($_POST as $key => $value) {

        if (
            strpos($key, 'numeric_') !== 0
        ) {
            continue;
        }


        $taxonomy = str_replace(
            'numeric_',
            '',
            $key
        );


        if (!taxonomy_exists($taxonomy)) {
            continue;
        }


        if (!is_array($value)) {
            continue;
        }


        /*
         * Несколько выбранных диапазонов
         * одного атрибута объединяются через OR.
         *
         * Например:
         *
         * 0–0.5
         * 1–3
         *
         * означает:
         *
         * значение >= 0 AND <= 0.5
         * OR
         * значение >= 1 AND <= 3
         */
        $range_term_ids = [];


        foreach ($value as $range) {

            if (!is_array($range)) {
                continue;
            }


            $min = isset($range['min'])
                ? cwc_normalize_number($range['min'])
                : null;

            $max = isset($range['max'])
                ? cwc_normalize_number($range['max'])
                : null;


            if (
                $min === null &&
                $max === null
            ) {
                continue;
            }


            $ids = cwc_get_term_ids_by_numeric_range(
                $taxonomy,
                $min,
                $max
            );


            if (!empty($ids)) {
                $range_term_ids = array_merge(
                    $range_term_ids,
                    $ids
                );
            }
        }


        $range_term_ids = array_values(
            array_unique(
                array_map(
                    'intval',
                    $range_term_ids
                )
            )
        );


        /*
         * Если были выбраны диапазоны,
         * но ни один term в них не попал —
         * товаров быть не должно.
         */
        if (empty($range_term_ids)) {

            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => [-1],
                'operator' => 'IN',
            ];

            continue;
        }


        /*
         * Все подходящие термы объединяем через IN.
         *
         * Таким образом несколько диапазонов
         * одного атрибута работают как OR.
         */
        $tax_query[] = [
            'taxonomy' => $taxonomy,
            'field'    => 'term_id',
            'terms'    => $range_term_ids,
            'operator' => 'IN',
        ];
    }


    /* =====================================================
     * PRICE
     * ===================================================== */

    if (
        isset(
            $_POST['min_price'],
            $_POST['max_price']
        )
    ) {

        $min_price = cwc_normalize_number(
            $_POST['min_price']
        );

        $max_price = cwc_normalize_number(
            $_POST['max_price']
        );


        if (
            $min_price !== null &&
            $max_price !== null &&
            $min_price <= $max_price
        ) {

            /*
             * Простые товары.
             */
            $meta_query[] = [
                'relation' => 'OR',

                [
                    'key'     => '_price',
                    'value'   => [
                        $min_price,
                        $max_price,
                    ],
                    'compare' => 'BETWEEN',
                    'type'    => 'NUMERIC',
                ],

                /*
                 * Variable products.
                 *
                 * Если диапазон цены пересекается
                 * с диапазоном вариаций.
                 */
                [
                    'key'     => '_min_variation_price',
                    'value'   => $max_price,
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ],

                [
                    'key'     => '_max_variation_price',
                    'value'   => $min_price,
                    'compare' => '>=',
                    'type'    => 'NUMERIC',
                ],
            ];
        }
    }


    /* =====================================================
     * CATEGORY
     * ===================================================== */

    if (!empty($_POST['current_cat_id'])) {

        $category_id = absint(
            $_POST['current_cat_id']
        );


        if ($category_id) {

            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $category_id,
                'operator' => 'IN',
            ];
        }
    }


    /* =====================================================
     * SORT
     * ===================================================== */

    $allowed_orderby = [
        'menu_order',
        'popularity',
        'rating',
        'date',
        'price',
        'price-desc',
    ];


    $orderby = isset($_POST['orderby'])
        ? sanitize_key($_POST['orderby'])
        : 'menu_order';


    if (!in_array($orderby, $allowed_orderby, true)) {
        $orderby = 'menu_order';
    }


    $query_args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'paged'          => isset($_POST['page'])
            ? max(1, absint($_POST['page']))
            : 1,

        'tax_query' => count($tax_query) > 1
            ? $tax_query
            : [],

        'meta_query' => count($meta_query) > 1
            ? $meta_query
            : [],

        'orderby' => 'menu_order',
        'order'   => 'ASC',
    ];


    /*
     * WooCommerce sort.
     */
    switch ($orderby) {

        case 'popularity':

            $query_args['meta_key'] = 'total_sales';
            $query_args['orderby']  = 'meta_value_num';
            $query_args['order']    = 'DESC';

            break;


        case 'rating':

            $query_args['meta_key'] = '_wc_average_rating';
            $query_args['orderby']  = 'meta_value_num';
            $query_args['order']    = 'DESC';

            break;


        case 'date':

            $query_args['orderby'] = 'date';
            $query_args['order']   = 'DESC';

            break;


        case 'price':

            $query_args['meta_key'] = '_price';
            $query_args['orderby']  = 'meta_value_num';
            $query_args['order']    = 'ASC';

            break;


        case 'price-desc':

            $query_args['meta_key'] = '_price';
            $query_args['orderby']  = 'meta_value_num';
            $query_args['order']    = 'DESC';

            break;
    }


    /* =====================================================
     * QUERY
     * ===================================================== */

    $query = new WP_Query($query_args);


    ob_start();


    if ($query->have_posts()) {

        while ($query->have_posts()) {

            $query->the_post();

            wc_get_template_part(
                'content',
                'product'
            );
        }
    } else {

        echo '<li class="no-products">Товары не найдены</li>';
    }


    $html = ob_get_clean();


    /*
     * Пагинация.
     */
    $pagination = paginate_links([
        'base'      => '%_%',
        'format'    => '?paged=%#%',
        'current'   => max(
            1,
            absint($query->get('paged'))
        ),
        'total'     => $query->max_num_pages,
        'type'      => 'plain',
        'prev_text' => '←',
        'next_text' => '→',
    ]);


    wp_reset_postdata();


    wp_send_json_success([
        'html'       => $html,
        'pagination' => $pagination,
        'found'      => $query->found_posts,
        'pages'      => $query->max_num_pages,
    ]);
}


add_action(
    'wp_ajax_cwc_filter_products',
    'cwc_filter_products_callback'
);

add_action(
    'wp_ajax_nopriv_cwc_filter_products',
    'cwc_filter_products_callback'
);
