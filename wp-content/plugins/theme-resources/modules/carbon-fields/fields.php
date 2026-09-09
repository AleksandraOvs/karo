<?php

defined('ABSPATH') || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

if (!class_exists('\Carbon_Fields\Container')) {

    add_action('admin_notices', function () {
?>
        <div class="notice notice-warning">
            <p>
                <strong>Theme Resources:</strong>
                Carbon Fields не подключен.
                Для работы полей необходимо включить
                «Carbon Fields» в настройках Site Resources.
            </p>
        </div>
<?php
    });

    return;
}

add_action('carbon_fields_register_fields', function () {

    // ------------------------
    // иконки для пунктов меню
    // ------------------------

    Container::make('nav_menu_item', 'Настройки пункта меню')
        ->add_fields([
            Field::make('image', 'menu_item_icon', 'Иконка')
                ->set_value_type('id'),
        ]);


    // ------------------------
    // страница ABOUT
    // ------------------------

    Container::make('post_meta', 'About Page')
        ->where('post_type', '=', 'page')
        ->where('post_template', '=', 'page-about.php')

        // ------------------------
        // Hero
        // ------------------------

        ->add_tab('Hero', [

            Field::make('text', 'hero_title', 'Заголовок')
                ->set_width(50),

            Field::make('text', 'hero_title_accent', 'Выделенный текст')
                ->set_width(50),

            Field::make('rich_text', 'hero_description', 'Описание')
                ->set_rows(5),

            Field::make('complex', 'hero_button', 'Кнопка')
                ->set_layout('tabbed-horizontal')
                ->set_max(1)
                ->add_fields([

                    Field::make('text', 'text', 'Текст кнопки'),

                    Field::make('text', 'url', 'Ссылка кнопки')
                        ->set_attribute('type', 'url'),

                ]),

            Field::make('image', 'hero_background', 'Изображение фона')
                ->set_value_type('url'),

        ])

        // ------------------------
        // Слайдер ссылок
        // ------------------------

        ->add_tab('Слайдер ссылок', [

            Field::make('complex', 'slider-links', 'Слайды')
                ->set_layout('tabbed-vertical')
                ->add_fields([

                    Field::make('text', 'subtitle', 'Подзаголовок')
                        ->set_width(50),

                    Field::make('text', 'title', 'Заголовок H2')
                        ->set_width(50),

                    Field::make('rich_text', 'description', 'Описание')
                        ->set_width(50),

                    Field::make('complex', 'button', 'Кнопка')
                        ->set_layout('tabbed-horizontal')
                        ->set_max(1)
                        ->add_fields([

                            Field::make('text', 'text', 'Текст кнопки')
                                ->set_width(50),

                            Field::make('text', 'url', 'Ссылка кнопки')
                                ->set_width(50)
                                ->set_attribute('type', 'url'),

                        ]),

                ]),

        ])

        // ------------------------
        // FAQ
        // ------------------------

        ->add_tab('FAQ', [

            Field::make('complex', 'faq', 'Вопросы и ответы')
                ->set_layout('tabbed-vertical')
                ->add_fields([

                    Field::make('text', 'question', 'Вопрос'),

                    Field::make('rich_text', 'answer', 'Ответ')
                        ->set_rows(5),

                ]),

        ]);

    Container::make('theme_options', 'Контакты')
        ->add_tab('Контакты', [
            Field::make('complex', 'messengers', 'Мессенджеры')
                ->set_layout('tabbed-vertical')
                ->setup_labels([
                    'plural_name'   => 'Мессенджеры',
                    'singular_name' => 'Мессенджер',
                ])
                ->add_fields([
                    Field::make('image', 'icon', 'Иконка')
                        ->set_value_type('url')
                        ->set_width(25),

                    Field::make('text', 'name', 'Название')
                        ->set_width(30),

                    Field::make('text', 'link', 'Ссылка')
                        ->set_width(45),
                ]),
        ]);
});
