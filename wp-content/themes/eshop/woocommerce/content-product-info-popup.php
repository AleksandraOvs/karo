<?php

defined('ABSPATH') || exit;

$product = $args['product'] ?? null;

if (!$product instanceof WC_Product) {
    return;
}

$product_id = $product->get_id();

?>

<div class="product-info-popup">

    <h2 class="product-info-popup__title">
        <?php echo esc_html($product->get_name()); ?>
    </h2>

    <div class="product-info-popup__id">
        ID:
        <?php echo $product->get_sku()
            ? esc_html($product->get_sku())
            : '—';
        ?>
    </div>

    <div class="product-info-popup__attributes">

        <div class="product-info-popup__attribute">
            <span>Форма</span>
            <strong>
                <?php
                $form = $product->get_attribute('pa_form');
                echo $form ? esc_html($form) : '—';
                ?>
            </strong>
        </div>

        <div class="product-info-popup__attribute">
            <span>Вес</span>
            <strong>
                <?php
                $weight = $product->get_attribute('pa_weight-gr');
                echo $weight ? esc_html($weight) : '—';
                ?>
            </strong>
        </div>

        <div class="product-info-popup__attribute">
            <span>Размер</span>
            <strong>
                <?php
                $size = $product->get_attribute('pa_size-mm');
                echo $size ? esc_html($size) : '—';
                ?>
            </strong>
        </div>

        <div class="product-info-popup__attribute">
            <span>Kurgin Score</span>
            <strong>
                <?php
                $kurgin_score = $product->get_attribute('pa_kurgin-score');
                echo $kurgin_score ? esc_html($kurgin_score) : '—';
                ?>
            </strong>
        </div>

    </div>

    <div class="product-info-popup__messengers">
        <!-- мессенджеры добавим следующим шагом -->
    </div>

</div>