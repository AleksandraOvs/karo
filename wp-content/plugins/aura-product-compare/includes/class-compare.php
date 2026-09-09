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




    /**
     * Получить ID товаров в сравнении
     */
    // public function get_products()
    // {

    //     if (!function_exists('WC') || !WC()->session) {
    //         return [];
    //     }

    //     $products = WC()->session->get(self::SESSION_KEY, []);

    //     error_log(
    //         'AURA COMPARE GET: ' .
    //             print_r($products, true)
    //     );

    //     error_log(
    //         'AURA WC SESSION ID: ' .
    //             WC()->session->get_customer_id()
    //     );

    //     if (!is_array($products)) {
    //         return [];
    //     }

    //     return array_map('absint', $products);
    // }

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

        error_log('========== AURA SESSION DEBUG ==========');

        error_log(
            'COOKIE SESSION: ' .
                print_r(
                    $_COOKIE['wp_woocommerce_session_' . COOKIEHASH] ?? 'NO COOKIE',
                    true
                )
        );

        error_log(
            'WC CUSTOMER ID: ' .
                WC()->session->get_customer_id()
        );

        error_log(
            'WC HAS SESSION: ' .
                (WC()->session->has_session() ? 'YES' : 'NO')
        );

        $products = WC()->session->get(
            self::SESSION_KEY,
            []
        );

        error_log(
            'COMPARE PRODUCTS: ' .
                print_r($products, true)
        );

        error_log('========================================');

        if (!is_array($products)) {
            return [];
        }

        return array_map('absint', $products);
    }

    public function get_count()
    {
        return count($this->get_products());
    }

    /**
     * Добавить товар в сравнение
     *
     * @param int $product_id
     *
     * @return array
     */
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
    /***
     * временно
     */

    

    // public function add_product($product_id)
    // {
    //     $products = $this->get_products();

    //     error_log('AURA ADD BEFORE: ' . print_r($products, true));

    //     $product_id = absint($product_id);

    //     if (!$product_id) {
    //         return $products;
    //     }

    //     if (in_array($product_id, $products, true)) {
    //         return $products;
    //     }

    //     if (count($products) >= self::MAX_PRODUCTS) {
    //         return $products;
    //     }

    //     $products[] = $product_id;

    //     error_log('AURA ADD SET: ' . print_r($products, true));

    //     WC()->session->set(
    //         self::SESSION_KEY,
    //         $products
    //     );

    //     /*
    //  * Проверяем сразу после сохранения
    //  */
    //     $check = WC()->session->get(
    //         self::SESSION_KEY,
    //         []
    //     );

    //     error_log('AURA ADD AFTER SET: ' . print_r($check, true));

    //     return $products;
    // }


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
<path d="M11.001 14C11.7966 14 12.5597 14.3161 13.1223 14.8787C13.6849 15.4413 14.001 16.2044 14.001 17C14.001 17.7956 13.6849 18.5587 13.1223 19.1213C12.5597 19.6839 11.7966 20 11.001 20C10.2053 20 9.44226 19.6839 8.87966 19.1213C8.31705 18.5587 8.00098 17.7956 8.00098 17C8.00098 16.2044 8.31705 15.4413 8.87966 14.8787C9.44226 14.3161 10.2053 14 11.001 14ZM11.001 14V16.25" stroke="#202020" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M8.00109 7.565V11M14.0011 7.565V11M21.4331 21.05C21.5038 21.2748 21.5205 21.513 21.482 21.7454C21.4434 21.9779 21.3507 22.1979 21.2112 22.3879C21.0717 22.5778 20.8895 22.7321 20.6793 22.8385C20.469 22.9448 20.2367 23.0002 20.0011 23H2.00109C1.76548 23.0002 1.53315 22.9448 1.32291 22.8385C1.11267 22.7321 0.930441 22.5778 0.790984 22.3879C0.651528 22.1979 0.558766 21.9779 0.520216 21.7454C0.481665 21.513 0.498409 21.2748 0.569091 21.05L3.38209 12.05C3.47785 11.7455 3.66826 11.4795 3.92562 11.2906C4.18299 11.1018 4.49388 11 4.81309 11H17.1881C17.5075 10.9998 17.8186 11.1015 18.0762 11.2903C18.3337 11.4792 18.5243 11.7453 18.6201 12.05L21.4331 21.05ZM2.00109 0.5C1.87758 0.500014 1.75598 0.530531 1.6471 0.588842C1.53822 0.647153 1.44542 0.731452 1.37695 0.834252C1.30848 0.937052 1.26647 1.05517 1.25463 1.17811C1.24279 1.30106 1.2615 1.42502 1.30909 1.539C1.90542 2.96916 2.81089 4.2496 3.96053 5.28848C5.11018 6.32736 6.4755 7.09892 7.95856 7.5478C9.44162 7.99669 11.0056 8.11177 12.5384 7.8848C14.0712 7.65782 15.5348 7.09442 16.8241 6.235C18.548 5.08512 19.8931 3.45085 20.6901 1.538C20.7374 1.42431 20.7559 1.30072 20.7441 1.17815C20.7324 1.05559 20.6906 0.937808 20.6225 0.835218C20.5544 0.732628 20.4621 0.648382 20.3537 0.589919C20.2453 0.531456 20.1242 0.500574 20.0011 0.5H2.00109Z" stroke="#202020" stroke-linecap="round" stroke-linejoin="round"/>
</svg>';
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
