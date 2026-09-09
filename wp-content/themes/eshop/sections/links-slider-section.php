<?php $slides = carbon_get_post_meta(get_the_ID(), 'slider-links');
if (!empty($slides)) :
?>

    <section class="links-slider-section">
        <div class="fixed-container">
            <div class="links-slider swiper">

                <div class="links-slider__wrapper swiper-wrapper">

                    <?php foreach ($slides as $slide) : ?>

                        <article class="links-slider__slide swiper-slide">

                            <?php if (!empty($slide['subtitle'])) : ?>
                                <div class="slider-links__subtitle">
                                    <?= esc_html($slide['subtitle']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($slide['title'])) : ?>
                                <h2 class="slider-links__title">
                                    <?= esc_html($slide['title']); ?>
                                </h2>
                            <?php endif; ?>

                            <?php if (!empty($slide['description'])) : ?>
                                <div class="slider-links__description">
                                    <?= apply_filters('the_content', $slide['description']); ?>
                                </div>
                            <?php endif; ?>

                            <?php
                            $button = $slide['button'][0] ?? null;
                            ?>

                            <?php if ($button && !empty($button['url'])) : ?>
                                <a
                                    class="button button-light"
                                    href="<?= esc_url($button['url']); ?>">
                                    <?= esc_html($button['text'] ?: 'Подробнее'); ?>
                                </a>
                            <?php endif; ?>

                        </article>

                    <?php endforeach; ?>

                </div>


                <!-- Пагинация -->
                <div class="slider-pagination swiper-pagination"></div>

            </div>
        </div>

    </section>

<?php endif; ?>