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
    // const COMPARE_ATTRIBUTES = [
    //     'pa_proizoditel',
    //     'pa_collection',
    //     'pa_obem-korobki-m3',
    //     'pa_forma',
    //     'pa_dlina-mm',
    //     'pa_vysota-mm',
    // ];

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
        /*
 * Затем выводим все найденные атрибуты.
 */
        foreach ($all_attributes as $attribute) {

            $result[] = $attribute;
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
<path d="M10.501 16.0898V14.2031C9.92094 14.2779 9.37798 14.541 8.95996 14.959C8.46111 15.4578 8.18066 16.1344 8.18066 16.8398C8.18066 17.5453 8.46111 18.2219 8.95996 18.7207C9.45881 19.2196 10.1353 19.5 10.8408 19.5C11.5463 19.5 12.2228 19.2196 12.7217 18.7207C13.2205 18.2219 13.501 17.5453 13.501 16.8398C13.501 16.1344 13.2205 15.4578 12.7217 14.959C12.3037 14.541 11.7607 14.2779 11.1807 14.2031V16.0898C11.1807 16.2776 11.0286 16.4297 10.8408 16.4297C10.653 16.4297 10.501 16.2776 10.501 16.0898ZM14.1807 16.8398C14.1807 17.7257 13.8285 18.5748 13.2021 19.2012C12.5758 19.8275 11.7266 20.1797 10.8408 20.1797C9.955 20.1797 9.10586 19.8275 8.47949 19.2012C7.85312 18.5748 7.50098 17.7257 7.50098 16.8398C7.50098 15.954 7.85312 15.1049 8.47949 14.4785C9.10586 13.8521 9.955 13.5 10.8408 13.5C11.7266 13.5 12.5758 13.8521 13.2021 14.4785C13.8285 15.1049 14.1807 15.954 14.1807 16.8398Z" fill="#213B67"/>
<path d="M19.8429 0C20.0217 0.000875168 20.1973 0.0460129 20.3547 0.130859C20.5121 0.21582 20.6463 0.338217 20.7453 0.487305C20.8442 0.636392 20.9059 0.807234 20.923 0.985352C20.9402 1.16348 20.9126 1.34356 20.8439 1.50879C20.0726 3.36002 18.8037 4.95821 17.1818 6.12891L16.8527 6.35742C16.0232 6.91034 15.1238 7.34493 14.1808 7.65039V10.5H17.0285C17.42 10.4998 17.8016 10.6249 18.1174 10.8564C18.3937 11.0592 18.6068 11.3344 18.7345 11.6504L18.7843 11.7881L21.5978 20.7881L21.6496 20.9971C21.6898 21.209 21.6928 21.4268 21.6574 21.6406C21.6101 21.9257 21.4963 22.1958 21.3254 22.4287C21.1544 22.6616 20.9308 22.851 20.673 22.9814C20.4797 23.0792 20.2709 23.1418 20.0568 23.167L19.841 23.1797H1.84099C1.5522 23.1798 1.26765 23.1118 1.00994 22.9814C0.752042 22.851 0.527682 22.6617 0.356615 22.4287C0.185676 22.1958 0.0718519 21.9256 0.0245841 21.6406C-0.0226312 21.3556 -0.00151041 21.0637 0.085131 20.7881L2.89763 11.7881L2.94744 11.6504C3.07514 11.3345 3.28833 11.0592 3.56462 10.8564C3.88031 10.6248 4.26194 10.5 4.65349 10.5H7.50115V7.64941C6.04707 7.17854 4.70761 6.40665 3.57244 5.38086C2.45983 4.37545 1.56858 3.15076 0.954272 1.78516L0.835131 1.50977C0.766011 1.34417 0.73883 1.16397 0.756029 0.985352C0.773235 0.806673 0.834258 0.634753 0.933764 0.485352C1.03323 0.336043 1.16821 0.213632 1.32634 0.128906C1.48456 0.0441731 1.66151 3.25966e-05 1.84099 0H19.8429ZM4.65349 11.1797C4.40674 11.1797 4.16594 11.2584 3.96697 11.4043C3.76815 11.5502 3.62118 11.756 3.54704 11.9912L0.733568 20.9912V20.9922C0.679018 21.1658 0.665764 21.3498 0.695483 21.5293C0.725295 21.709 0.797597 21.8795 0.905443 22.0264C1.01328 22.1732 1.15404 22.2928 1.31658 22.375C1.47912 22.4572 1.65885 22.5001 1.84099 22.5H19.841C20.0231 22.5001 20.2029 22.4571 20.3654 22.375C20.528 22.2928 20.6697 22.1732 20.7775 22.0264C20.8853 21.8796 20.9567 21.709 20.9865 21.5293C21.0162 21.3497 21.003 21.1658 20.9484 20.9922V20.9912L18.1359 11.9922L18.0666 11.8213C17.9845 11.6572 17.8644 11.5138 17.715 11.4043C17.5159 11.2583 17.2754 11.1795 17.0285 11.1797H4.65349ZM13.5011 7.84473C13.148 7.93416 12.7898 8.00695 12.4279 8.06055C11.0102 8.27044 9.56697 8.19654 8.18083 7.8457V10.5H13.5011V7.84473ZM1.84099 0.679688C1.77352 0.67972 1.70711 0.696663 1.64763 0.728516C1.58823 0.760338 1.53757 0.80625 1.50017 0.862305C1.46274 0.918502 1.43926 0.983571 1.43279 1.05078C1.42637 1.11791 1.43708 1.18582 1.46306 1.24805C2.04007 2.63183 2.91612 3.87077 4.02849 4.87598C5.14083 5.88114 6.46174 6.62814 7.89665 7.0625C9.33156 7.49681 10.8453 7.60822 12.3283 7.38867C13.8114 7.16905 15.2282 6.62358 16.4758 5.79199L16.7843 5.57812C18.3045 4.48086 19.4931 2.98218 20.216 1.24707C20.2418 1.185 20.2517 1.11768 20.2453 1.05078C20.2388 0.983777 20.2161 0.919363 20.1789 0.863281C20.1417 0.807208 20.0916 0.760473 20.0324 0.728516C19.9732 0.696592 19.9063 0.680001 19.839 0.679688H1.84099Z" fill="#213B67"/>
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
