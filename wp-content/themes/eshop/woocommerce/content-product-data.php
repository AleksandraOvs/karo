<?php

defined('ABSPATH') || exit;

global $product;

if (! $product instanceof WC_Product) {
    return;
}
?>

<div class="product-card__attributes-row product-card__attributes-row--specs">

    <div class="product-card__attribute --pa-form">

        <span class="product-card__attribute-value">
            <?php
            $terms = get_the_terms($product->get_id(), 'pa_form');

            if ($terms && !is_wp_error($terms)) :
                foreach ($terms as $term) :
                    $icon = carbon_get_term_meta($term->term_id, 'pa_form_icon');
            ?>


                    <?php if ($icon) : ?>
                        <img
                            class="product-card__attribute-icon"
                            src="<?php echo esc_url($icon); ?>"
                            alt=""
                            aria-hidden="true">
                    <?php endif; ?>

                    <?php echo esc_html($term->name); ?>


            <?php
                endforeach;
            else :
                echo '—';
            endif;
            ?>
        </span>
    </div>

    <div class="product-card__attribute">
        <span class="product-card__attribute-value">
            <?php
            $weight = $product->get_attribute('pa_weight-gr');
            echo $weight ? esc_html($weight) : '—';
            ?>
        </span>
    </div>

    <div class="product-card__attribute">
        <span class="product-card__attribute-value">
            <?php
            $size = $product->get_attribute('pa_size-mm');
            echo $size ? esc_html($size) : '—';
            ?>
        </span>
    </div>

    <div class="product-card__attribute">
        <span class="product-card__attribute-value">
            <?php
            $kurgin_score = $product->get_attribute('pa_kurgin-score');
            echo $kurgin_score ? esc_html($kurgin_score) : '—';
            ?>
        </span>
    </div>

    <div class="product-card__attribute product-card__attribute--price">
        <span class="product-card__attribute-value">
            <?php woocommerce_template_loop_price(); ?>
        </span>
    </div>

</div>