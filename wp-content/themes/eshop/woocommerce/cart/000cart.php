<?php

/**
 * Cart Page
 *
 * Custom flex layout.
 *
 * @package WooCommerce\Templates
 * @version 11.0.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');

?>


<div class="cart-contents__inner">
    <form
        class="woocommerce-cart-form"
        action="<?php echo esc_url(wc_get_cart_url()); ?>"
        method="post">

        <?php do_action('woocommerce_before_cart_table'); ?>

        <div class="cart-items">

            <?php do_action('woocommerce_before_cart_contents'); ?>

            <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :

                $product = $cart_item['data'];

                if (! $product instanceof WC_Product || ! $product->exists()) {
                    continue;
                }

                global $product;

                wc_get_template('content-cart-product.php');

            endforeach; ?>

            <?php do_action('woocommerce_cart_contents'); ?>


            <div class="cart-actions">

                <?php if (wc_coupons_enabled()) : ?>

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

                <?php endif; ?>


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


    <?php do_action('woocommerce_before_cart_collaterals');
    ?>


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

</div><!-- end of cart-contents__inner -->

<?php do_action('woocommerce_after_cart'); ?>