<?php
add_action(
    'wp_ajax_get_product_info_popup',
    'get_product_info_popup'
);

add_action(
    'wp_ajax_nopriv_get_product_info_popup',
    'get_product_info_popup'
);

function get_product_info_popup()
{
    if (empty($_POST['product_id'])) {
        wp_send_json_error([
            'message' => 'Не указан товар.',
        ]);
    }

    $product_id = absint($_POST['product_id']);

    if (!$product_id) {
        wp_send_json_error([
            'message' => 'Некорректный ID товара.',
        ]);
    }

    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error([
            'message' => 'Товар не найден.',
        ]);
    }

    /*
     * Передаём товар в global,
     * потому что content-product-data.php
     * и buttons.php используют global $product.
     */
    $GLOBALS['product'] = $product;

    ob_start();

    wc_get_template(
        'content-product-info-popup.php',
        [
            'product' => $product,
        ],
        '',
        get_stylesheet_directory() . '/woocommerce/'
    );

    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
    ]);
}
