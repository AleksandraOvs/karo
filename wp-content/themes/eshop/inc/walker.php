<?php

class MAIN_Menu_Walker extends Walker_Nav_Menu
{
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $classes = !empty($item->classes)
            ? (array) $item->classes
            : [];

        $output .= '<li class="' . esc_attr(
            implode(' ', $classes)
        ) . '">';

        $output .= '<a href="' . esc_url($item->url) . '">';

        // Иконка пункта меню
        $icon_id = carbon_get_nav_menu_item_meta(
            $item->ID,
            'menu_item_icon'
        );

        if ($icon_id) {

            $icon_url = wp_get_attachment_image_url(
                $icon_id,
                'full'
            );

            if ($icon_url) {
                $output .= '<img
                    src="' . esc_url($icon_url) . '"
                    alt=""
                    class="menu-item__icon"
                >';
            }
        }

        // Название пункта меню
        $output .= '<span class="menu-item__title">';
        $output .= esc_html($item->title);
        $output .= '</span>';

        $output .= '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= '</li>';
    }
}
