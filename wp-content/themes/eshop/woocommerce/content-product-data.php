<?php

defined('ABSPATH') || exit;

global $product;

if (! $product instanceof WC_Product) {
    return;
}
?>

<div class="product-card__attributes-row product-card__attributes-row--specs">

    <div class="product-card__attribute">
        <span class="product-card__attribute-value">
            <?php
            $form = $product->get_attribute('pa_form');
            echo $form ? esc_html($form) : '—';
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