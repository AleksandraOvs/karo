<?php
/*
Plugin Name: Theme Resources
Description: Подключение основных ресурсов темы (Swiper, Fancybox, Typography, Carbon Fields).
Version: 1.0
Author: Shura
*/

if (!defined('ABSPATH')) {
    exit;
}

// ------------------------
// Путь к плагину
// ------------------------
define('THEME_RESOURCES_PATH', plugin_dir_path(__FILE__));
define('THEME_RESOURCES_URL', plugin_dir_url(__FILE__));

// ------------------------
// Carbon Fields
// ------------------------

$options = get_option('tr_settings');

if (!empty($options['enable_carbon_fields'])) {

    $carbon_fields_path = THEME_RESOURCES_PATH . 'carbon-fields';

    $autoload = $carbon_fields_path . '/vendor/autoload.php';
    $plugin   = $carbon_fields_path . '/carbon-fields-plugin.php';

    // Autoload Carbon Fields и его зависимостей
    if (file_exists($autoload)) {
        require_once $autoload;
    }

    // Подключаем сам Carbon Fields
    if (file_exists($plugin)) {
        require_once $plugin;
    }

    // Запускаем Carbon Fields после setup темы
    add_action('after_setup_theme', function () {

        if (class_exists('\Carbon_Fields\Carbon_Fields')) {
            \Carbon_Fields\Carbon_Fields::boot();
        }
    });
}

require_once THEME_RESOURCES_PATH . 'modules/carbon-fields/fields.php';

// ------------------------
// Админ-меню
// ------------------------
// ------------------------
// Меню плагина
// ------------------------
add_action('admin_menu', function () {
    add_menu_page(
        'Site Resources',           // Заголовок страницы
        'Site Resources',           // Название в меню
        'manage_options',           // Права доступа
        'theme-resources',          // slug страницы
        'theme_resources_page',     // callback для контента
        'dashicons-admin-generic',  // иконка
        50                          // позиция в меню
    );
});

// ------------------------
// Контент страницы
// ------------------------
function theme_resources_page()
{

    $options = get_option('tr_settings');

?>
    <div class="wrap">
        <h1>Site Resources</h1>

        <form method="post" action="options.php">
            <?php
            settings_fields('tr_settings_group');
            ?>

            <table class="form-table">

                <tr>
                    <th scope="row">Включить Swiper</th>
                    <td>
                        <input type="checkbox"
                            name="tr_settings[enable_swiper]"
                            value="1"
                            <?php checked($options['enable_swiper'] ?? '', 1); ?>>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Включить Fancybox</th>
                    <td>
                        <input type="checkbox"
                            name="tr_settings[enable_fancybox]"
                            value="1"
                            <?php checked($options['enable_fancybox'] ?? '', 1); ?>>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Включить Carbon Fields
                        <p style="font-style: italic; font-size: 12px; font-weight: 300;">Файл для редактирования полей Carbon fields находится в /modules/carbon-fields/fields.php</p>
                    </th>
                    <td>
                        <input type="checkbox"
                            name="tr_settings[enable_carbon_fields]"
                            value="1"
                            <?php checked($options['enable_carbon_fields'] ?? '', 1); ?>>


                    </td>
                </tr>

            </table>

            <?php submit_button(); ?>

        </form>
    </div>
<?php
}


// ------------------------
// Подключение скриптов и стилей на фронтенде
// ------------------------
add_action('wp_enqueue_scripts', function () {

    $plugin_url = plugin_dir_url(__FILE__);
    $options = get_option('tr_settings');

    $enable_swiper   = $options['enable_swiper']   ?? false;
    $enable_fancybox = $options['enable_fancybox'] ?? false;

    // ======================
    // Swiper
    // ======================
    if ($enable_swiper) {

        wp_enqueue_style(
            'tr-swiper',
            $plugin_url . 'assets/swiper/swiper-bundle.min.css',
            [],
            '11.0.0'
        );

        wp_enqueue_script(
            'tr-swiper',
            $plugin_url . 'assets/swiper/swiper-bundle.min.js',
            [],
            '11.0.0',
            true
        );

        wp_enqueue_script(
            'tr-swiper-init',
            $plugin_url . 'assets/js/swiper-init.js',
            ['tr-swiper'],
            '1.0.0',
            true
        );
    }

    // ======================
    // Fancybox
    // ======================
    if ($enable_fancybox) {

        wp_enqueue_style(
            'tr-fancybox',
            $plugin_url . 'assets/fancybox/fancybox.css',
            [],
            '5.0.0'
        );

        wp_enqueue_script(
            'tr-fancybox',
            $plugin_url . 'assets/fancybox/fancybox.umd.js',
            [],
            '5.0.0',
            true
        );

        wp_enqueue_script(
            'tr-fancybox-init',
            $plugin_url . 'assets/js/fancybox-init.js',
            ['tr-fancybox'],
            '1.0.0',
            true
        );
    }
});

// ------------------------ // Подключение скриптов и стилей админки // ------------------------

add_action('enqueue_block_editor_assets', function () {

    // CSS для редактора
    wp_enqueue_style(
        'theme-resources-editor',
        THEME_RESOURCES_URL . 'assets/css/admin.css',
        ['wp-edit-blocks'],
        '1.0.0'
    );

    // JS для редактора
    wp_enqueue_script(
        'theme-resources-editor',
        THEME_RESOURCES_URL . 'assets/js/admin.js',
        ['wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor'],
        '1.0.0',
        true
    );
});

// ------------------------
// Регистрация настроек
// ------------------------
add_action('admin_init', function () {

    register_setting(
        'tr_settings_group',
        'tr_settings'
    );
});

require_once plugin_dir_path(__FILE__) . 'modules/fonts/font-selector.php';
