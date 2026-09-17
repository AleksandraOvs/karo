<?php

defined('ABSPATH') || exit;

global $product;

if (! $product instanceof WC_Product) {
    return;
}
?>

<div class="product-info-card">

    <h3 class="product-info-card__title">
        Карточка камня
    </h3>


    <ul class="products-nav">

        <li>Форма</li>
        <li>Вес (гр.)</li>
        <li>Размер (мм.)</li>
        <li>Kurgin Score</li>
        <li>Цена</li>

    </ul>

    <div class="popup-product__item">
        <div class="product-info-card__data">

            <?php
            get_template_part('woocommerce/content-product-data');
            ?>

        </div>


        <div class="product-info-card__actions">

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
            get_template_part(
                'template-parts/buttons',
                null,
                [
                    'popup' => true,
                ]
            );
            ?>

        </div>
    </div>

    <div class="product-footer">
        <?php
        $product_description = carbon_get_theme_option('product_description');
        ?>

        <?php if ($product_description) : ?>



            <div class="product-request__description">
                <h3>Запросить условия</h3>
                <?php echo apply_filters('the_content', $product_description); ?>
            </div>

        <?php endif; ?>

        <?php
        $messengers = carbon_get_theme_option('messengers');
        ?>

        <?php if (!empty($messengers)) : ?>

            <div class="contacts__messengers">

                <?php foreach ($messengers as $messenger) : ?>

                    <?php
                    //  $icon = $messenger['icon'] ?? '';
                    $name = $messenger['name'] ?? '';
                    $link = $messenger['link'] ?? '';

                    if (!$link) {
                        continue;
                    }
                    ?>

                    <a
                        href="<?php echo esc_url($link); ?>"
                        class="contacts__messenger"
                        target="_blank"
                        rel="noopener noreferrer">

                        <?php //if ($icon) : 
                        ?>

                        <!-- <img
                                src="<?php //echo esc_url($icon); 
                                        ?>"
                                alt="<?php //echo esc_attr($name); 
                                        ?>"
                                class="contacts__messenger-icon"> -->

                        <?php //endif; 
                        ?>

                        <?php if ($name) : ?>

                            <span class="contacts__messenger-name">
                                <?php echo esc_html($name); ?>
                            </span>

                        <?php endif; ?>

                    </a>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>
    </div>



</div>