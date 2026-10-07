<?php

defined('ABSPATH') || exit;

global $product;

if (! $product instanceof WC_Product) {
    return;
}

?>

<div <?php wc_product_class('', $product); ?>>

    <div class="product-card__attributes">

        <?php
        get_template_part('woocommerce/content-product-data');
        ?>

        <div class="product-card__attributes-row product-card__attributes-row--actions">

            <div class="product-card__sku">

                <span class="product-card__sku-label">
                    ID:
                </span>

                <span class="product-card__sku-value">
                    <?php
                    echo $product->get_sku()
                        ? esc_html($product->get_sku())
                        : '—';
                    ?>
                </span>

            </div>

            <?php
            get_template_part('template-parts/buttons');
            ?>

        </div>

    </div>

</div>