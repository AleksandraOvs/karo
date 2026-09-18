<?php

if (!defined('ABSPATH')) {
    exit;
}

class AURA_Product_Compare
{

    const SESSION_KEY = 'aura_compare_products';
    const MAX_PRODUCTS = 10;


    public function __construct()
    {

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_shortcode(
            'aura_compare_button',
            [$this, 'compare_button']
        );
    }

    /**
     * Таксономия брендов.
     */
    const BRAND_TAXONOMY = 'product_brand';

    /**
     * Атрибуты, участвующие в сравнении.
     * Порядок здесь = порядок вывода.
     */
    const COMPARE_ATTRIBUTES = [
        'pa_proizoditel',
        'pa_collection',
        'pa_obem-korobki-m3',
        'pa_forma',
        'pa_dlina-mm',
        'pa_vysota-mm',
    ];

    private function ensure_session()
    {
        if (!function_exists('WC')) {
            return false;
        }

        if (!WC()->session) {
            return false;
        }

        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }

        return true;
    }

    public function get_products()
    {
        if (!function_exists('WC') || !WC()->session) {
            error_log('AURA: NO WC SESSION');
            return [];
        }

        $this->ensure_session();



        $products = WC()->session->get(
            self::SESSION_KEY,
            []
        );



        if (!is_array($products)) {
            return [];
        }

        return array_map('absint', $products);
    }

    public function get_count()
    {
        return count($this->get_products());
    }

    public function get_compare_categories($products)
    {
        if (empty($products)) {
            return [];
        }

        $categories = [];

        foreach ($products as $product) {

            if (!$product instanceof WC_Product) {
                continue;
            }

            $terms = get_the_terms(
                $product->get_id(),
                'product_cat'
            );

            if (empty($terms) || is_wp_error($terms)) {
                continue;
            }

            foreach ($terms as $term) {

                /*
             * Ключом является ID категории.
             * Поэтому одинаковая категория никогда
             * не попадёт в список дважды.
             */
                $categories[$term->term_id] = $term;
            }
        }

        return $categories;
    }

    /**
     * Добавить товар в сравнение
     *
     * @param int $product_id
     * @return array
     */
    public function add_product($product_id)
    {
        if (!function_exists('WC') || !WC()->session) {
            error_log('AURA ADD: NO WC SESSION');
            return [];
        }

        $product_id = absint($product_id);

        error_log('========== AURA ADD START ==========');
        error_log('PRODUCT ID: ' . $product_id);

        error_log(
            'COOKIE: ' .
                ($_COOKIE['wp_woocommerce_session_' . COOKIEHASH] ?? 'NO COOKIE')
        );

        error_log(
            'CUSTOMER ID BEFORE: ' .
                WC()->session->get_customer_id()
        );

        error_log(
            'HAS SESSION BEFORE: ' .
                (WC()->session->has_session() ? 'YES' : 'NO')
        );

        $products = $this->get_products();

        error_log(
            'PRODUCTS BEFORE: ' .
                print_r($products, true)
        );

        if (!$product_id) {
            return $products;
        }

        if (in_array($product_id, $products, true)) {

            error_log('PRODUCT ALREADY EXISTS');

            return $products;
        }

        if (count($products) >= self::MAX_PRODUCTS) {

            error_log('MAX PRODUCTS REACHED');

            return $products;
        }

        $products[] = $product_id;

        error_log(
            'PRODUCTS TO SAVE: ' .
                print_r($products, true)
        );

        WC()->session->set(
            self::SESSION_KEY,
            $products
        );

        error_log(
            'PRODUCTS IMMEDIATELY AFTER SET: ' .
                print_r(
                    WC()->session->get(self::SESSION_KEY, []),
                    true
                )
        );

        error_log(
            'CUSTOMER ID AFTER: ' .
                WC()->session->get_customer_id()
        );

        error_log(
            'COOKIE AFTER: ' .
                ($_COOKIE['wp_woocommerce_session_' . COOKIEHASH] ?? 'NO COOKIE')
        );

        error_log('========== AURA ADD END ==========');

        return $this->get_products();
    }

    /**
     * Удалить товар
     */
    public function remove_product($product_id)
    {

        $products = $this->get_products();

        $product_id = absint($product_id);

        $products = array_values(
            array_diff($products, [$product_id])
        );

        WC()->session->set(self::SESSION_KEY, $products);

        error_log(
            'AURA COMPARE AFTER SET: ' .
                print_r(WC()->session->get(self::SESSION_KEY), true)
        );

        return $products;
    }


    /**
     * Очистить сравнение
     */
    public function clear_products()
    {

        WC()->session->set(self::SESSION_KEY, []);
    }


    /**
     * Проверить, добавлен ли товар
     */
    public function is_product_added($product_id)
    {

        return in_array(
            absint($product_id),
            $this->get_products(),
            true
        );
    }


    /**
     * Подключаем CSS и JS
     */
    public function enqueue_assets()
    {

        wp_enqueue_style(
            'aura-compare',
            AURA_COMPARE_URL . 'assets/css/compare.css',
            [],
            AURA_COMPARE_VERSION
        );

        wp_enqueue_script(
            'aura-compare',
            AURA_COMPARE_URL . 'assets/js/compare.js',
            ['jquery'],
            AURA_COMPARE_VERSION,
            true
        );

        wp_localize_script(
            'aura-compare',
            'auraCompare',
            [
                'ajax_url' => home_url('/wp-admin/admin-ajax.php'),
                'nonce'    => wp_create_nonce('aura_compare_nonce'),
                'count'    => $this->get_count(),
            ]
        );
    }

    /**
     * Получить характеристики одного товара
     *
     * @param WC_Product $product
     *
     * @return array
     */
    public function get_product_attributes($product)
    {

        if (!$product instanceof WC_Product) {
            return [];
        }

        $attributes = $product->get_attributes();

        if (empty($attributes)) {
            return [];
        }

        $result = [];

        foreach ($attributes as $attribute) {

            if (!$attribute instanceof WC_Product_Attribute) {
                continue;
            }

            if (!$attribute->get_visible()) {
                continue;
            }

            $name = $attribute->get_name();

            if (!$name) {
                continue;
            }


            /**
             * Глобальный атрибут WooCommerce
             * Например:
             * pa_color
             * pa_material
             */
            if ($attribute->is_taxonomy()) {

                $taxonomy = $name;

                $taxonomy_object = get_taxonomy($taxonomy);

                if (!$taxonomy_object) {
                    continue;
                }

                $attribute_name = wc_attribute_label($taxonomy);


                /**
                 * Получаем термины атрибута
                 */
                $terms = wc_get_product_terms(
                    $product->get_id(),
                    $taxonomy,
                    [
                        'fields' => 'all',
                    ]
                );

                if (is_wp_error($terms) || empty($terms)) {
                    continue;
                }


                $values = [];

                foreach ($terms as $term) {

                    if (!empty($term->name)) {
                        $values[] = $term->name;
                    }
                }

                if (empty($values)) {
                    continue;
                }


                /**
                 * Для глобального атрибута
                 * ключом будет его taxonomy
                 */
                $key = $taxonomy;
            } else {

                /**
                 * Локальный атрибут товара
                 */
                $attribute_name = $name;

                $options = $attribute->get_options();

                if (empty($options)) {
                    continue;
                }


                $values = [];

                foreach ($options as $option) {

                    $option = trim((string) $option);

                    if ($option !== '') {
                        $values[] = $option;
                    }
                }

                if (empty($values)) {
                    continue;
                }


                /**
                 * Для локального атрибута
                 * создаём стабильный ключ
                 */
                $key = 'custom_' . sanitize_title($name);
            }


            $result[] = [
                'key'       => $key,
                'name'      => $attribute_name,
                'value'     => implode(', ', $values),
                'values'    => $values,
                'taxonomy'  => $attribute->is_taxonomy() ? $name : '',
            ];
        }


        return $result;
    }

    /**
     * Получить уникальные категории товаров
     * из текущего списка сравнения.
     *
     * @param array $products Массив WC_Product
     * @return array
     */


    /**
     * Получить общие характеристики
     * для всех товаров в сравнении
     *
     * @param array $products
     *
     * @return array
     */
    public function get_compare_attributes($products)
    {

        if (empty($products)) {
            return [];
        }


        /*
     * Все характеристики товаров
     */
        $all_attributes = [];


        /*
     * ==========================================
     * БРЕНД
     * ==========================================
     */

        $brand_attribute = [
            'key'      => self::BRAND_TAXONOMY,
            'name'     => 'Бренд',
            'taxonomy' => self::BRAND_TAXONOMY,
            'values'   => [],
        ];


        foreach ($products as $product) {

            if (!$product instanceof WC_Product) {
                continue;
            }


            $product_id = $product->get_id();


            $terms = get_the_terms(
                $product_id,
                self::BRAND_TAXONOMY
            );


            if (is_wp_error($terms) || empty($terms)) {
                continue;
            }


            $values = [];


            foreach ($terms as $term) {

                if (!empty($term->name)) {
                    $values[] = $term->name;
                }
            }


            if (!empty($values)) {

                $brand_attribute['values'][$product_id]
                    = implode(', ', $values);
            }
        }


        /*
     * ==========================================
     * АТРИБУТЫ ТОВАРОВ
     * ==========================================
     */

        foreach ($products as $product) {

            if (!$product instanceof WC_Product) {
                continue;
            }


            $product_attributes = $this->get_product_attributes($product);


            foreach ($product_attributes as $attribute) {

                $key = $attribute['key'];


                /*
             * Если атрибут ещё не добавлен,
             * создаём его.
             */
                if (!isset($all_attributes[$key])) {

                    $all_attributes[$key] = [
                        'key'      => $key,
                        'name'     => $attribute['name'],
                        'taxonomy' => $attribute['taxonomy'],
                        'values'   => [],
                    ];
                }


                /*
             * Сохраняем значение
             * конкретного товара.
             */
                $product_id = $product->get_id();


                $all_attributes[$key]['values'][$product_id]
                    = $attribute['value'];
            }
        }


        /*
     * ==========================================
     * ФОРМИРУЕМ РЕЗУЛЬТАТ
     * ==========================================
     */

        $result = [];


        /*
     * Бренд всегда первым,
     * если хотя бы у одного товара
     * он заполнен.
     */
        if (!empty($brand_attribute['values'])) {

            $result[] = $brand_attribute;
        }


        /*
     * Затем идут выбранные атрибуты
     * строго в порядке COMPARE_ATTRIBUTES.
     */
        foreach (self::COMPARE_ATTRIBUTES as $attribute_key) {

            if (isset($all_attributes[$attribute_key])) {

                $result[] = $all_attributes[$attribute_key];
            }
        }


        return $result;
    }


    /**
     * Кнопка "Добавить в сравнение"
     */
    public function compare_button($atts = [])
    {

        global $product;

        if (!$product) {
            return '';
        }

        if (!$product instanceof WC_Product) {
            return '';
        }

        $product_id = $product->get_id();

        $added = $this->is_product_added($product_id);

        ob_start();

?>
        <?php
        $compare_img = '<svg width="22" height="24" viewBox="0 0 22 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M10.8408 13.84C11.6365 13.84 12.3995 14.156 12.9621 14.7186C13.5248 15.2813 13.8408 16.0443 13.8408 16.84C13.8408 17.6356 13.5248 18.3987 12.9621 18.9613C12.3995 19.5239 11.6365 19.84 10.8408 19.84C10.0452 19.84 9.28211 19.5239 8.7195 18.9613C8.15689 18.3987 7.84082 17.6356 7.84082 16.84C7.84082 16.0443 8.15689 15.2813 8.7195 14.7186C9.28211 14.156 10.0452 13.84 10.8408 13.84ZM10.8408 13.84V16.09" stroke="#202020" stroke-width="0.68" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M7.84094 7.40497V10.84M13.8409 7.40497V10.84M21.2729 20.89C21.3436 21.1147 21.3604 21.353 21.3218 21.5854C21.2833 21.8178 21.1905 22.0379 21.051 22.2278C20.9116 22.4177 20.7294 22.5721 20.5191 22.6785C20.3089 22.7848 20.0765 22.8401 19.8409 22.84H1.84094C1.60533 22.8401 1.37299 22.7848 1.16275 22.6785C0.952514 22.5721 0.770285 22.4177 0.630828 22.2278C0.491371 22.0379 0.39861 21.8178 0.360059 21.5854C0.321509 21.353 0.338253 21.1147 0.408935 20.89L3.22194 11.89C3.3177 11.5855 3.50811 11.3194 3.76547 11.1306C4.02283 10.9418 4.33372 10.8399 4.65294 10.84H17.0279C17.3473 10.8397 17.6584 10.9414 17.916 11.1303C18.1736 11.3192 18.3641 11.5853 18.4599 11.89L21.2729 20.89ZM1.84094 0.339966C1.71742 0.33998 1.59582 0.370497 1.48694 0.428808C1.37806 0.487119 1.28526 0.571418 1.2168 0.674218C1.14833 0.777018 1.10631 0.895136 1.09447 1.01808C1.08263 1.14103 1.10134 1.26499 1.14894 1.37897C1.74526 2.80912 2.65073 4.08957 3.80037 5.12845C4.95002 6.16733 6.31535 6.93888 7.7984 7.38777C9.28146 7.83665 10.8455 7.95174 12.3783 7.72476C13.9111 7.49779 15.3746 6.93438 16.6639 6.07497C18.3879 4.92509 19.733 3.29082 20.5299 1.37797C20.5772 1.26427 20.5958 1.14069 20.584 1.01812C20.5722 0.895552 20.5304 0.777774 20.4623 0.675184C20.3942 0.572594 20.3019 0.488348 20.1935 0.429885C20.0852 0.371422 19.9641 0.34054 19.8409 0.339966H1.84094Z" stroke="#202020" stroke-width="0.68" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

';
        ?>
        <button
            type="button"
            class="product-compare aura-compare-btn <?php echo $added ? 'added' : ''; ?>"
            data-product-id="<?php echo esc_attr($product_id); ?>">
            <?php echo $compare_img; ?>
            <span>
                <?php
                echo $added
                    ? 'В сравнении'
                    : 'Добавить в сравнение';
                ?>
            </span>
        </button>


<?php

        return ob_get_clean();
    }
}
