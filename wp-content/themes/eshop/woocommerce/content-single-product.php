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
            <!-- <div class="product-inner__images">
                <?php //do_action('woocommerce_before_single_product_summary'); 
                ?>
            </div> -->
            <div class="single-product__inner__content">
                <div class="product-inner__content__col _buy-col">

                    <div class="product-card__price">
                        <?php echo $product->get_price_html(); ?>
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
                                <?php
                                $svg = '
       <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M15.7895 15.7895C16.3478 15.7895 16.8833 16.0113 17.2781 16.4061C17.6729 16.8009 17.8947 17.3364 17.8947 17.8947C17.8947 18.4531 17.6729 18.9886 17.2781 19.3834C16.8833 19.7782 16.3478 20 15.7895 20C15.2311 20 14.6956 19.7782 14.3008 19.3834C13.906 18.9886 13.6842 18.4531 13.6842 17.8947C13.6842 17.3364 13.906 16.8009 14.3008 16.4061C14.6956 16.0113 15.2311 15.7895 15.7895 15.7895ZM15.7895 16.8421C15.5103 16.8421 15.2426 16.953 15.0452 17.1504C14.8477 17.3478 14.7368 17.6156 14.7368 17.8947C14.7368 18.1739 14.8477 18.4417 15.0452 18.6391C15.2426 18.8365 15.5103 18.9474 15.7895 18.9474C16.0686 18.9474 16.3364 18.8365 16.5338 18.6391C16.7312 18.4417 16.8421 18.1739 16.8421 17.8947C16.8421 17.6156 16.7312 17.3478 16.5338 17.1504C16.3364 16.953 16.0686 16.8421 15.7895 16.8421ZM6.31579 15.7895C6.87414 15.7895 7.40962 16.0113 7.80444 16.4061C8.19925 16.8009 8.42105 17.3364 8.42105 17.8947C8.42105 18.4531 8.19925 18.9886 7.80444 19.3834C7.40962 19.7782 6.87414 20 6.31579 20C5.75744 20 5.22196 19.7782 4.82714 19.3834C4.43233 18.9886 4.21053 18.4531 4.21053 17.8947C4.21053 17.3364 4.43233 16.8009 4.82714 16.4061C5.22196 16.0113 5.75744 15.7895 6.31579 15.7895ZM6.31579 16.8421C6.03661 16.8421 5.76887 16.953 5.57147 17.1504C5.37406 17.3478 5.26316 17.6156 5.26316 17.8947C5.26316 18.1739 5.37406 18.4417 5.57147 18.6391C5.76887 18.8365 6.03661 18.9474 6.31579 18.9474C6.59496 18.9474 6.86271 18.8365 7.06011 18.6391C7.25752 18.4417 7.36842 18.1739 7.36842 17.8947C7.36842 17.6156 7.25752 17.3478 7.06011 17.1504C6.86271 16.953 6.59496 16.8421 6.31579 16.8421ZM17.8947 3.15789H3.44211L6.12632 9.47368H14.7368C15.0842 9.47368 15.3895 9.30526 15.5789 9.05263L18.7368 4.84211C18.8737 4.66316 18.9474 4.44211 18.9474 4.21053C18.9474 3.93135 18.8365 3.66361 18.6391 3.4662C18.4417 3.2688 18.1739 3.15789 17.8947 3.15789ZM14.7368 10.5263H6.17895L5.36842 12.1684L5.26316 12.6316C5.26316 12.9108 5.37406 13.1785 5.57147 13.3759C5.76887 13.5733 6.03661 13.6842 6.31579 13.6842H17.8947V14.7368H6.31579C5.75744 14.7368 5.22196 14.515 4.82714 14.1202C4.43233 13.7254 4.21053 13.1899 4.21053 12.6316C4.21021 12.2744 4.30077 11.923 4.47368 11.6105L5.23158 10.0632L1.41053 1.05263H0V0H2.10526L3 2.10526H17.8947C18.4531 2.10526 18.9886 2.32707 19.3834 2.72188C19.7782 3.11669 20 3.65218 20 4.21053C20 4.73684 19.8211 5.17895 19.5263 5.53684L16.4632 9.63158C16.0842 10.1684 15.4526 10.5263 14.7368 10.5263Z" fill="white"/>
</svg>
';
                                echo $svg;
                                echo  'В корзину';
                                ?>
                            </button>

                        </form>

                        <button
                            type="button"
                            class="button buy-one-click"
                            data-popup="buy-one-click-popup"
                            data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                            data-product-name="<?php echo esc_attr($product->get_name()); ?>"
                            data-product-sku="<?php echo esc_attr($product->get_sku() ?: '—'); ?>"
                            data-product-price="<?php echo esc_attr(wp_strip_all_tags($product->get_price_html())); ?>"
                            data-product-url="<?php echo esc_url($product->get_permalink()); ?>">
                            Купить в один клик
                        </button>

                    <?php endif; ?>

                    <button
                        type="button"
                        class="product-card__info --share button"
                        data-product_id="<?php echo esc_attr($product_id); ?>"
                        data-product_url="<?php echo esc_url(get_permalink($product_id)); ?>">
                        <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.7 4.92L5.81 8.94C5.93 9.27 6 9.63 6 10C6 10.37 5.93 10.73 5.81 11.06L12.7 15.08C13.094 14.6059 13.6247 14.265 14.2197 14.1036C14.8146 13.9422 15.4449 13.9683 16.0245 14.1782C16.604 14.3882 17.1048 14.7718 17.4583 15.2768C17.8118 15.7818 18.001 16.3836 18 17C18 18.66 16.66 20 15 20C13.34 20 12 18.66 12 17C12 16.63 12.07 16.27 12.19 15.94L5.3 11.92C4.90596 12.3941 4.37528 12.735 3.78033 12.8964C3.18538 13.0578 2.55514 13.0317 1.97555 12.8218C1.39597 12.6118 0.895254 12.2282 0.541708 11.7232C0.188163 11.2182 -0.00100561 10.6164 4.02049e-06 10C-0.00100561 9.38356 0.188163 8.7818 0.541708 8.27682C0.895254 7.77183 1.39597 7.3882 1.97555 7.17823C2.55514 6.96827 3.18538 6.9422 3.78033 7.10358C4.37528 7.26496 4.90596 7.60594 5.3 8.08L12.19 4.06C12.07 3.73 12 3.37 12 3C12 1.34 13.34 0 15 0C16.66 0 18 1.34 18 3C18.001 3.61644 17.8118 4.2182 17.4583 4.72318C17.1048 5.22817 16.604 5.6118 16.0245 5.82177C15.4449 6.03173 14.8146 6.0578 14.2197 5.89642C13.6247 5.73504 13.094 5.39406 12.7 4.92ZM15 19C16.11 19 17 18.11 17 17C17 15.89 16.11 15 15 15C13.89 15 13 15.9 13 17C13 18.1 13.9 19 15 19ZM3 12C4.11 12 5 11.11 5 10C5 8.89 4.11 8 3 8C1.89 8 1 8.9 1 10C1 11.1 1.89 12 3 12ZM15 5C16.11 5 17 4.11 17 3C17 1.89 16.11 1 15 1C13.89 1 13 1.9 13 3C13 4.1 13.9 5 15 5Z" fill="#22543d" />
                        </svg>
                        Поделиться
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