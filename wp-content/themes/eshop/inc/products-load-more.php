<?php
/*
 * PRODUCTS AJAX 
 */

/**
 * AJAX: загрузка товаров при скролле
 */
add_action('wp_ajax_load_more_products', 'aura_load_more_products');
add_action('wp_ajax_nopriv_load_more_products', 'aura_load_more_products');

function aura_load_more_products()
{

    check_ajax_referer('load_more_products', 'nonce');

    $page        = isset($_POST['paged']) ? absint($_POST['paged']) : 2;
    $page_type   = isset($_POST['page_type']) ? sanitize_key($_POST['page_type']) : 'shop';
    $category_id = isset($_POST['category_id']) ? absint($_POST['category_id']) : 0;

    /*
     * ВАЖНО:
     *
     * Первая страница выводит 18 товаров.
     * AJAX начинает со второй страницы.
     *
     * Поэтому:
     *
     * page 2 -> offset 18, 9 товаров
     * page 3 -> offset 27, 9 товаров
     * page 4 -> offset 36, 9 товаров
     *
     * Но offset лучше считать явно.
     */

    $offset = 18 + (($page - 2) * 9);

    $args = [
        'post_type'              => 'product',
        'post_status'            => 'publish',
        'posts_per_page'         => 9,
        'offset'                 => $offset,
        'orderby'                => 'menu_order',
        'order'                  => 'ASC',
        'ignore_sticky_posts'    => true,
        'no_found_rows'          => false,
    ];

    /*
     * Если это категория
     */
    if ($page_type === 'category' && $category_id) {

        $args['tax_query'] = [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $category_id,
            ],
        ];
    }

    $products = new WP_Query($args);

    ob_start();

    if ($products->have_posts()) {

        while ($products->have_posts()) {

            $products->the_post();

            wc_get_template_part('content', 'product');
        }
    }

    $html = ob_get_clean();

    /*
     * Есть ли ещё товары?
     */
    $has_more = ($offset + $products->post_count) < $products->found_posts;

    wp_reset_postdata();

    wp_send_json_success([
        'html'     => $html,
        'has_more' => $has_more,
        'count'    => $products->post_count,
        'found'    => $products->found_posts,
        'offset'   => $offset,
    ]);
}

add_action('wp_enqueue_scripts', function () {

    if (!is_woocommerce()) {
        return;
    }

    wp_enqueue_script(
        'products-load-more',
        get_stylesheet_directory_uri() . '/js/products-load-more.js',
        ['cwc-ajax-filters'],
        null,
        true
    );
});
