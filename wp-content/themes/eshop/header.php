<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package eshop
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <!-- Favicon -->
    <link rel="icon" href="<?php echo get_stylesheet_directory_uri() . '/imgs/favi/favicon.ico' ?>" sizes="any" />

    <link rel="icon" href="<?php echo get_stylesheet_directory_uri() . '/imgs/favi/favicon.svg' ?>" type="image/svg+xml" />
    <link rel="apple-touch-icon" href="<?php echo get_stylesheet_directory_uri() . '/imgs/favi/favicon.svg' ?>" />

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <div class="wrapper">
        <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'eshop'); ?></a>

        <header id="masthead" class="header">
            <div class="fixed-container">
                <!-- header logo -->
                <?php
                $header_logo_id = get_theme_mod('header_logo');
                $header_logo_url = $header_logo_id ? wp_get_attachment_image_url($header_logo_id, 'full') : '';

                $site_name = get_bloginfo('name');
                $site_description = get_bloginfo('description');
                ?>

                <div class="header-logo">

                    <?php if ($header_logo_url): ?>
                        <a class="header-logo__image" href="<?php echo esc_url(home_url('/')); ?>">
                            <img
                                src="<?= esc_url($header_logo_url); ?>"
                                alt="<?= esc_attr($site_name); ?>">
                        </a>

                    <?php endif; ?>

                    <a href="<?php echo esc_url(home_url('/')); ?>" class="header-logo__site-info">

                        <?php if ($site_name): ?>
                            <p class="site-name">
                                <?= esc_html($site_name); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($site_description): ?>
                            <p class="site-description">
                                <?= esc_html($site_description); ?>
                            </p>
                        <?php endif; ?>

                    </a>

                </div>

                <?php
                wp_nav_menu([
                    'theme_location' => 'main_menu',
                    'container'      => false,
                    'menu_class'     => 'main-menu',
                    'menu_id'        => '',
                    'fallback_cb'    => false,
                    'link_before'    => '',
                    'link_after'     => '',
                    'walker'           => new MAIN_Menu_Walker
                ]);
                ?>

                <button class="menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>



                <!-- end of header logo -->
            </div>





        </header><!-- #masthead -->


        <div class="mob-menu">
            <?php
            wp_nav_menu([
                'theme_location' => 'main_menu',
                'container'      => false,
                'menu_class'     => 'main-menu',
                'menu_id'        => '',
                'fallback_cb'    => false,
                'link_before'    => '',
                'link_after'     => '',
                'walker'           => new MAIN_Menu_Walker
            ]);
            ?>
        </div>