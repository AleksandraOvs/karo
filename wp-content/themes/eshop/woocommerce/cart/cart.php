<?php

/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 11.0.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
    <?php do_action('woocommerce_before_cart_table'); ?>

    <div class="shop_table shop_table_responsive cart woocommerce-cart-form__contents">

        <div class="cart-header" aria-hidden="true">
            <div class="cart-header__product">
                Форма
                <?php //esc_html_e('Product', 'woocommerce'); 
                ?>
            </div>

            <div class="cart-header__price">
                <?php esc_html_e('Price', 'woocommerce'); ?>
            </div>

            <div class="cart-header__quantity">
                <?php esc_html_e('Quantity', 'woocommerce'); ?>
            </div>

            <div class="cart-header__subtotal">
                <?php esc_html_e('Subtotal', 'woocommerce'); ?>
            </div>
        </div>

        <div class="cart-items">

            <?php do_action('woocommerce_before_cart_contents'); ?>

            <?php
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {

                $_product = apply_filters(
                    'woocommerce_cart_item_product',
                    $cart_item['data'],
                    $cart_item,
                    $cart_item_key
                );

                $product_id = apply_filters(
                    'woocommerce_cart_item_product_id',
                    $cart_item['product_id'],
                    $cart_item,
                    $cart_item_key
                );

                $visible = apply_filters(
                    'woocommerce_cart_item_visible',
                    true,
                    $cart_item,
                    $cart_item_key
                );

                if (
                    $_product instanceof WC_Product &&
                    $_product->exists() &&
                    $cart_item['quantity'] > 0 &&
                    $visible
                ) {

                    $product_name = apply_filters(
                        'woocommerce_cart_item_name',
                        $_product->get_name(),
                        $cart_item,
                        $cart_item_key
                    );

                    $product_permalink = apply_filters(
                        'woocommerce_cart_item_permalink',
                        $_product->is_visible()
                            ? $_product->get_permalink($cart_item)
                            : '',
                        $cart_item,
                        $cart_item_key
                    );
            ?>

                    <div
                        class="woocommerce-cart-form__cart-item <?php echo esc_attr(
                                                                    apply_filters(
                                                                        'woocommerce_cart_item_class',
                                                                        'cart_item',
                                                                        $cart_item,
                                                                        $cart_item_key
                                                                    )
                                                                ); ?>">

                        <!-- Товар -->
                        <div class="cart-item__product">

                            <div class="cart-item__info">

                                <div
                                    class="product-name"
                                    data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>">

                                    <?php
                                    if (! $product_permalink) {
                                        echo wp_kses_post($product_name . '&nbsp;');
                                    } else {
                                        echo wp_kses_post(
                                            apply_filters(
                                                'woocommerce_cart_item_name',
                                                sprintf(
                                                    '<a href="%s">%s</a>',
                                                    esc_url($product_permalink),
                                                    $_product->get_name()
                                                ),
                                                $cart_item,
                                                $cart_item_key
                                            )
                                        );
                                    }

                                    if ($_product->get_sku()) : ?> <div class="product-sku"> ID: <?php echo esc_html($_product->get_sku()); ?> </div> <?php endif;

                                                                                                                                                    do_action(
                                                                                                                                                        'woocommerce_after_cart_item_name',
                                                                                                                                                        $cart_item,
                                                                                                                                                        $cart_item_key
                                                                                                                                                    );

                                                                                                                                                    // Характеристики товара.
                                                                                                                                                    echo wc_get_formatted_cart_item_data($cart_item);

                                                                                                                                                    // Backorder.
                                                                                                                                                    if (
                                                                                                                                                        $_product->backorders_require_notification() &&
                                                                                                                                                        $_product->is_on_backorder($cart_item['quantity'])
                                                                                                                                                    ) {
                                                                                                                                                        echo wp_kses_post(
                                                                                                                                                            apply_filters(
                                                                                                                                                                'woocommerce_cart_item_backorder_notification',
                                                                                                                                                                '<p class="backorder_notification">' .
                                                                                                                                                                    esc_html__('Available on backorder', 'woocommerce') .
                                                                                                                                                                    '</p>',
                                                                                                                                                                $product_id
                                                                                                                                                            )
                                                                                                                                                        );
                                                                                                                                                    }
                                                                                                                                                        ?>

                                </div>

                                <!-- Атрибуты товара -->
                                <div class="product-card__attributes-row product-card__attributes-row--specs">
                                    <div class="product-card__attribute"> <span class="product-card__attribute-label"> <?php esc_html_e('Форма', 'woocommerce'); ?> </span> <span class="product-card__attribute-value"> <?php $form = $_product->get_attribute('pa_form');
                                                                                                                                                                                                                            echo $form ? esc_html($form) : '—'; ?> </span> </div>
                                    <div class="product-card__attribute"> <span class="product-card__attribute-label"> <?php esc_html_e('Вес', 'woocommerce'); ?> </span> <span class="product-card__attribute-value"> <?php $weight = $_product->get_attribute('pa_weight-gr');
                                                                                                                                                                                                                        echo $weight ? esc_html($weight) : '—'; ?> </span> </div>
                                    <div class="product-card__attribute"> <span class="product-card__attribute-label"> <?php esc_html_e('Размер', 'woocommerce'); ?> </span> <span class="product-card__attribute-value"> <?php $size = $_product->get_attribute('pa_size-mm');
                                                                                                                                                                                                                            echo $size ? esc_html($size) : '—'; ?> </span> </div>
                                    <div class="product-card__attribute"> <span class="product-card__attribute-label"> <?php esc_html_e('Кургин', 'woocommerce'); ?> </span> <span class="product-card__attribute-value"> <?php $kurgin_score = $_product->get_attribute('pa_kurgin-score');
                                                                                                                                                                                                                            echo $kurgin_score ? esc_html($kurgin_score) : '—'; ?> </span> </div>
                                </div>

                            </div>

                        </div>


                        <!-- Цена -->
                        <div
                            class="cart-item__price product-price"
                            data-title="<?php esc_attr_e('Price', 'woocommerce'); ?>">

                            <?php
                            echo apply_filters(
                                'woocommerce_cart_item_price',
                                WC()->cart->get_product_price($_product),
                                $cart_item,
                                $cart_item_key
                            );
                            ?>

                        </div>


                        <!-- Количество -->
                        <div
                            class="cart-item__quantity product-quantity"
                            data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">

                            <?php
                            echo woocommerce_quantity_input(
                                [
                                    'input_name'   => "cart[{$cart_item_key}][qty]",
                                    'input_value'  => $cart_item['quantity'],
                                    'max_value'    => $_product->get_max_purchase_quantity(),
                                    'min_value'    => 0,
                                    'product_name' => $product_name,
                                ],
                                $_product,
                                false
                            );
                            ?>

                        </div>


                        <!-- Сумма -->
                        <div
                            class="cart-item__subtotal product-subtotal"
                            data-title="<?php esc_attr_e('Subtotal', 'woocommerce'); ?>">

                            <?php
                            echo apply_filters(
                                'woocommerce_cart_item_subtotal',
                                WC()->cart->get_product_subtotal(
                                    $_product,
                                    $cart_item['quantity']
                                ),
                                $cart_item,
                                $cart_item_key
                            );
                            ?>

                        </div>


                        <!-- Удалить -->
                        <div class="cart-item__remove product-remove">

                            <?php
                            echo apply_filters(
                                'woocommerce_cart_item_remove_link',
                                sprintf(
                                    '<a role="button" href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                                    esc_url(wc_get_cart_remove_url($cart_item_key)),
                                    esc_attr(
                                        sprintf(
                                            __('Remove %s from cart', 'woocommerce'),
                                            wp_strip_all_tags($product_name)
                                        )
                                    ),
                                    esc_attr($product_id),
                                    esc_attr($_product->get_sku())
                                ),
                                $cart_item_key
                            );
                            ?>

                        </div>

                    </div>

            <?php
                }
            }
            ?>

            <?php do_action('woocommerce_cart_contents'); ?>

        </div>


        <!-- Действия корзины -->
        <div class="cart-actions">

            <?php if (wc_coupons_enabled()) { ?>

                <div class="coupon">

                    <label
                        for="coupon_code"
                        class="screen-reader-text">
                        <?php esc_html_e('Coupon:', 'woocommerce'); ?>
                    </label>

                    <input
                        type="text"
                        name="coupon_code"
                        class="input-text"
                        id="coupon_code"
                        value=""
                        placeholder="<?php esc_attr_e('Coupon code', 'woocommerce'); ?>" />

                    <button
                        type="submit"
                        class="button<?php echo esc_attr(
                                            wc_wp_theme_get_element_class_name('button')
                                                ? ' ' . wc_wp_theme_get_element_class_name('button')
                                                : ''
                                        ); ?>"
                        name="apply_coupon"
                        value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>">
                        <?php esc_html_e('Apply coupon', 'woocommerce'); ?>
                    </button>

                    <?php do_action('woocommerce_cart_coupon'); ?>

                </div>

            <?php } ?>


            <button
                type="submit"
                class="button<?php echo esc_attr(
                                    wc_wp_theme_get_element_class_name('button')
                                        ? ' ' . wc_wp_theme_get_element_class_name('button')
                                        : ''
                                ); ?>"
                name="update_cart"
                value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>">
                <?php esc_html_e('Update cart', 'woocommerce'); ?>
            </button>


            <?php do_action('woocommerce_cart_actions'); ?>


            <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>

        </div>


        <?php do_action('woocommerce_after_cart_contents'); ?>

    </div>

    <?php do_action('woocommerce_after_cart_table'); ?>
</form>

<?php do_action('woocommerce_before_cart_collaterals'); ?>

<div class="cart-collaterals">
    <?php
    /**
     * Cart collaterals hook.
     *
     * @hooked woocommerce_cross_sell_display
     * @hooked woocommerce_cart_totals - 10
     */
    do_action('woocommerce_cart_collaterals');
    ?>
</div>

<?php do_action('woocommerce_after_cart'); ?>