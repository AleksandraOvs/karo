<?php
defined('ABSPATH') || exit;

global $product;
$product_id = $product->get_id();

do_action('woocommerce_before_single_product');

if (post_password_required()) {
    echo get_the_password_form();
    return;
}
?>


<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>
    <!-- <h1 class="product-title"><?php //the_title(); 
                                    ?></h1> -->

    <div class="fixed-container">
        <div class="product-title-block">
            <h1 class="product-title">
                <?php the_title(); ?>
            </h1>
        </div>

        <div class="single-product__inner">

            <!-- 1. Галерея -->
            <div class="product-inner__images">
                <?php do_action('woocommerce_before_single_product_summary'); ?>
            </div>
            <div class="single-product__inner__content">
                <div class="product-inner__content__col _buy-col">

                    <div class="product-card__price">
                        <?php echo $product->get_price_html(); ?>
                    </div>


                    <div class="product-quantity">

                        <button
                            type="button"
                            class="quantity-minus"
                            aria-label="Уменьшить количество">
                            −
                        </button>

                        <?php
                        woocommerce_quantity_input(
                            [
                                'min_value'   => $product->get_min_purchase_quantity(),
                                'max_value'   => $product->get_max_purchase_quantity(),
                                'input_value' => $product->get_min_purchase_quantity(),
                            ],
                            $product
                        );
                        ?>

                        <button
                            type="button"
                            class="quantity-plus"
                            aria-label="Увеличить количество">
                            +
                        </button>

                    </div>


                    <?php if ($product->is_type('simple')) : ?>

                        <form
                            class="cart product-buy-form"
                            action="<?php echo esc_url($product->get_permalink()); ?>"
                            method="post"
                            enctype="multipart/form-data">

                            <input
                                type="hidden"
                                name="quantity"
                                value="<?php echo esc_attr($product->get_min_purchase_quantity()); ?>"
                                class="product-buy-quantity">

                            <button
                                type="submit"
                                name="add-to-cart"
                                value="<?php echo esc_attr($product->get_id()); ?>"
                                class="button single_add_to_cart_button button">
                                Купить
                            </button>

                        </form>

                    <?php endif; ?>

                    <button
                        type="button"
                        class="share-product"
                        data-product_id="<?php echo esc_attr($product_id); ?>"
                        data-product_url="<?php echo esc_url(get_permalink($product_id)); ?>">
                        <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.7 4.92L5.81 8.94C5.93 9.27 6 9.63 6 10C6 10.37 5.93 10.73 5.81 11.06L12.7 15.08C13.094 14.6059 13.6247 14.265 14.2197 14.1036C14.8146 13.9422 15.4449 13.9683 16.0245 14.1782C16.604 14.3882 17.1048 14.7718 17.4583 15.2768C17.8118 15.7818 18.001 16.3836 18 17C18 18.66 16.66 20 15 20C13.34 20 12 18.66 12 17C12 16.63 12.07 16.27 12.19 15.94L5.3 11.92C4.90596 12.3941 4.37528 12.735 3.78033 12.8964C3.18538 13.0578 2.55514 13.0317 1.97555 12.8218C1.39597 12.6118 0.895254 12.2282 0.541708 11.7232C0.188163 11.2182 -0.00100561 10.6164 4.02049e-06 10C-0.00100561 9.38356 0.188163 8.7818 0.541708 8.27682C0.895254 7.77183 1.39597 7.3882 1.97555 7.17823C2.55514 6.96827 3.18538 6.9422 3.78033 7.10358C4.37528 7.26496 4.90596 7.60594 5.3 8.08L12.19 4.06C12.07 3.73 12 3.37 12 3C12 1.34 13.34 0 15 0C16.66 0 18 1.34 18 3C18.001 3.61644 17.8118 4.2182 17.4583 4.72318C17.1048 5.22817 16.604 5.6118 16.0245 5.82177C15.4449 6.03173 14.8146 6.0578 14.2197 5.89642C13.6247 5.73504 13.094 5.39406 12.7 4.92ZM15 19C16.11 19 17 18.11 17 17C17 15.89 16.11 15 15 15C13.89 15 13 15.9 13 17C13 18.1 13.9 19 15 19ZM3 12C4.11 12 5 11.11 5 10C5 8.89 4.11 8 3 8C1.89 8 1 8.9 1 10C1 11.1 1.89 12 3 12ZM15 5C16.11 5 17 4.11 17 3C17 1.89 16.11 1 15 1C13.89 1 13 1.9 13 3C13 4.1 13.9 5 15 5Z" fill="#213B67" />
                        </svg>
                        <span>Поделиться</span>
                    </button>

                </div>

                <div class="product-inner__content__col _info-col">

                    <div class="product-sku">
                        ID:
                        <?php echo esc_html($product->get_sku() ?: '—'); ?>
                    </div>

                    <div class="product__attribute">
                        <span class="product__attribute-label">Форма</span>
                        <span class="product__attribute-value">
                            <?php
                            $form = $product->get_attribute('pa_form');
                            echo $form ? esc_html($form) : '—';
                            ?>
                        </span>
                    </div>

                    <div class="product__attribute">
                        <span class="product__attribute-label">Вес</span>
                        <span class="product__attribute-value">
                            <?php
                            $weight = $product->get_attribute('pa_weight-gr');
                            echo $weight ? esc_html($weight) : '—';
                            ?>
                        </span>
                    </div>

                    <div class="product__attribute">
                        <span class="product__attribute-label">Размер</span>
                        <span class="product__attribute-value">
                            <?php
                            $size = $product->get_attribute('pa_size-mm');
                            echo $size ? esc_html($size) : '—';
                            ?>
                        </span>
                    </div>

                    <div class="product__attribute">
                        <span class="product__attribute-label">Kurgin Score</span>
                        <span class="product__attribute-value">
                            <?php
                            $kurgin_score = $product->get_attribute('pa_kurgin-score');
                            echo $kurgin_score ? esc_html($kurgin_score) : '—';
                            ?>
                        </span>
                    </div>



                </div>


            </div>
        </div>
    </div>


    <?php //get_template_part('woocommerce/single-product/product-tabs') 
    ?>
    <?php get_template_part('template-parts/cross-up-sales') ?>

    <?php //get_template_part('sections/contacts')
    ?>
</div>