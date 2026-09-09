<section class="hero">

    <div class="container">

        <div class="hero__inner">
            <div class="fixed-container">
                <?php
                $title = carbon_get_post_meta(get_the_ID(), 'hero_title');
                $title_accent = carbon_get_post_meta(get_the_ID(), 'hero_title_accent');
                $description = carbon_get_post_meta(get_the_ID(), 'hero_description');
                $button = carbon_get_post_meta(get_the_ID(), 'hero_button');
                $background = carbon_get_post_meta(get_the_ID(), 'hero_background');
                ?>

                <?php if ($title || $title_accent) : ?>
                    <h1 class="hero__title" data-scroll-animation="brightness">
                        <?php echo esc_html($title); ?>

                        <?php if ($title_accent) : ?>
                            <span>
                                <?php echo esc_html($title_accent); ?>
                            </span>
                        <?php endif; ?>
                    </h1>
                <?php endif; ?>

                <?php if ($description) : ?>
                    <div class="hero__description">
                        <?php echo apply_filters('the_content', $description); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($button[0])) : ?>
                    <a
                        class="button button-light"
                        href="<?php echo esc_url($button[0]['url']); ?>">
                        <?php echo esc_html($button[0]['text']); ?>
                    </a>
            </div>
        </div>


    <?php endif; ?>

    </div>

</section>