<?php

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="aura-compare">

    <?php if (empty($products)) : ?>

        <div class="aura-compare__empty">

            <p>В сравнении пока нет товаров.</p>

            <a href="/shop" class="button">
                В каталог
            </a>

        </div>

    <?php else : ?>


        <!-- Фильтр -->

        <div class="aura-compare__filter">

            <label class="aura-compare__filter-label">

                <input
                    type="checkbox"
                    class="aura-compare__different">

                <span class="aura-compare__filter-text">
                    Различающиеся характеристики
                </span>

            </label>

        </div>


        <!-- Товары -->

        <div class="aura-compare__products swiper">

            <div class="swiper-wrapper">


                <?php foreach ($products as $index => $compare_product) : ?>

                    <?php

                    global $product;

                    $old_product = $product;

                    $product = $compare_product;

                    ?>


                    <div class="aura-compare__item swiper-slide">


                        <!-- Карточка товара -->

                        <div class="product-card aura-compare-product">

                            <!-- <div class="product-card__image">

                                <a href="<?php //echo esc_url($product->get_permalink()); 
                                            ?>">

                                    <?php //echo $product->get_image(); 
                                    ?>

                                </a>

                            </div> -->


                            <div class="product-card__title">

                                <a href="<?php echo esc_url($product->get_permalink()); ?>">

                                    <?php echo esc_html($product->get_name()); ?>

                                </a>

                            </div>


                            <div class="product-card__bottom">

                                <?php

                                if ($product->is_type('variable')) {

                                    echo '<span class="product-card__details">Подробнее</span>';
                                } else {

                                    woocommerce_template_loop_price();
                                }

                                ?>


                                <div class="product-card__cart">

                                    <?php
                                    woocommerce_template_loop_add_to_cart();
                                    ?>

                                </div>

                            </div>


                            <button
                                type="button"
                                class="aura-compare__remove"
                                data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                                <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.625 1.125H3.5C3.56875 1.125 3.625 1.06875 3.625 1V1.125ZM3.625 1.125H8.375V1C8.375 1.06875 8.43125 1.125 8.5 1.125H8.375V2.25H9.5V1C9.5 0.448438 9.05156 0 8.5 0H3.5C2.94844 0 2.5 0.448438 2.5 1V2.25H3.625V1.125ZM11.5 2.25H0.5C0.223437 2.25 0 2.47344 0 2.75V3.25C0 3.31875 0.05625 3.375 0.125 3.375H1.06875L1.45469 11.5469C1.47969 12.0797 1.92031 12.5 2.45312 12.5H9.54688C10.0813 12.5 10.5203 12.0813 10.5453 11.5469L10.9312 3.375H11.875C11.9438 3.375 12 3.31875 12 3.25V2.75C12 2.47344 11.7766 2.25 11.5 2.25ZM9.42656 11.375H2.57344L2.19531 3.375H9.80469L9.42656 11.375Z" fill="#ffffff" />
                                </svg>

                            </button>

                        </div>


                        <!-- Характеристики -->

                        <div class="aura-compare__attribute-values">

                            <?php foreach ($compare_attributes as $attribute) : ?>

                                <?php

                                /*
         * Проверяем, отличается ли характеристика.
         */

                                $attribute_values = [];

                                foreach ($products as $compare_item) {

                                    $compare_product_id = $compare_item->get_id();

                                    $attribute_values[] = isset($attribute['values'][$compare_product_id])
                                        ? trim((string) $attribute['values'][$compare_product_id])
                                        : '—';
                                }

                                $is_different = count(array_unique($attribute_values)) > 1;


                                /*
         * Значение текущего товара.
         */

                                $product_id = $product->get_id();

                                $value = isset($attribute['values'][$product_id])
                                    ? trim((string) $attribute['values'][$product_id])
                                    : '—';

                                ?>

                                <div
                                    class="aura-compare__attribute-value <?php echo $is_different ? 'is-different' : 'is-same'; ?>">

                                    <div class="aura-compare__attribute-name">
                                        <?php echo esc_html($attribute['name']); ?>
                                    </div>

                                    <div class="aura-compare__attribute-text">
                                        <?php echo esc_html($value); ?>
                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>


                    </div>


                    <?php

                    $product = $old_product;

                    ?>

                <?php endforeach; ?>


            </div>


            <div class="aura-compare__products__controls">

                <div class="aura-compare__prev swiper-button-prev"></div>

                <div class="aura-compare__next swiper-button-next"></div>

            </div>


        </div>

    <?php endif; ?>

</div>