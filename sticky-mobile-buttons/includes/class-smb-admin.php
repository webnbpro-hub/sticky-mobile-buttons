<?php
/**
 * Admin functionality for Sticky Mobile Buttons
 */
class SMB_Admin {
    
    private $options;
    private $defaults;
    private $font_awesome_icons;
    
    public function __construct() {
        // Настройки по умолчанию
        $this->defaults = array(
            'number_of_buttons' => 4,
            'show_text' => true,
            'icon_size' => 20,
            'background_color' => '#ffffff',
            'buttons' => array(
                1 => array(
                    'type' => 'phone',
                    'text' => __('Call', 'sticky-mobile-buttons'),
                    'value' => '+79991234567',
                    'icon_color' => '#3498db',
                    'icon_type' => 'fontawesome',
                    'icon' => 'fas fa-phone-alt',
                    'custom_icon' => 0,
                    'enabled' => true
                ),
                // ... остальные настройки по умолчанию
            )
        );
        
        // Загрузка опций
        $this->options = wp_parse_args(get_option('smb_options'), $this->defaults);
    }
    
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    public function get_options() {
        return $this->options;
    }
    
    // Добавление меню в админку
    public function add_admin_menu() {
        add_options_page(
            __('Sticky Mobile Buttons Settings', 'sticky-mobile-buttons'),
            __('Sticky Buttons', 'sticky-mobile-buttons'),
            'manage_options',
            'sticky-mobile-buttons',
            array($this, 'options_page')
        );
    }
    
    // Инициализация настроек
    public function settings_init() {
        register_setting('smb_settings', 'smb_options', array($this, 'sanitize_settings'));
        
        // Основная секция
        add_settings_section(
            'smb_general_section',
            __('General Settings', 'sticky-mobile-buttons'),
            array($this, 'general_section_callback'),
            'smb_settings'
        );
        
        // Добавление полей настроек...
        // Поля общих настроек
        add_settings_field(
            'number_of_buttons',
            __('Number of Buttons', 'sticky-mobile-buttons'),
            array($this, 'number_of_buttons_render'),
            'smb_settings',
            'smb_general_section'
        );
        
        add_settings_field(
            'show_text',
            __('Show Text', 'sticky-mobile-buttons'),
            array($this, 'show_text_render'),
            'smb_settings',
            'smb_general_section'
        );
        
        add_settings_field(
            'icon_size',
            __('Icon Size (px)', 'sticky-mobile-buttons'),
            array($this, 'icon_size_render'),
            'smb_settings',
            'smb_general_section'
        );
        
        add_settings_field(
            'background_color',
            __('Background Color', 'sticky-mobile-buttons'),
            array($this, 'background_color_render'),
            'smb_settings',
            'smb_general_section'
        );
        
        // Секция для кнопок
        add_settings_section(
            'smb_buttons_section',
            __('Buttons Settings', 'sticky-mobile-buttons'),
            array($this, 'buttons_section_callback'),
            'smb_settings'
        );
        
        // Добавляем поля для каждой кнопки
        for ($i = 1; $i <= 7; $i++) {
            add_settings_field(
                "button_$i",
                sprintf(__('Button %d', 'sticky-mobile-buttons'), $i),
                array($this, 'button_render'),
                'smb_settings',
                'smb_buttons_section',
                array('index' => $i)
            );
        }
    }
    
    // Добавляем отсутствующие методы callback
    public function general_section_callback() {
        echo '<p>' . esc_html__('General settings for sticky mobile buttons.', 'sticky-mobile-buttons') . '</p>';
    }
    
    public function buttons_section_callback() {
        echo '<p>' . esc_html__('Configure each button individually. Buttons will be displayed in order from 1 to 7.', 'sticky-mobile-buttons') . '</p>';
    }
    
    // Санитизация настроек
    public function sanitize_settings($input) {
        // Проверка nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'smb_settings-options')) {
            add_settings_error('smb_options', 'invalid_nonce', __('Security check failed.', 'sticky-mobile-buttons'));
            return $this->options;
        }
        
        // Проверка прав доступа
        if (!current_user_can('manage_options')) {
            add_settings_error('smb_options', 'no_permission', __('You do not have sufficient permissions to update these settings.', 'sticky-mobile-buttons'));
            return $this->options;
        }
        
        $output = array();
        
        // Общие настройки
        $output['number_of_buttons'] = absint($input['number_of_buttons']);
        if ($output['number_of_buttons'] < 1 || $output['number_of_buttons'] > 7) {
            add_settings_error('smb_options', 'invalid_number_of_buttons', __('Number of buttons must be between 1 and 7.', 'sticky-mobile-buttons'));
            $output['number_of_buttons'] = 4;
        }
        
        $output['show_text'] = isset($input['show_text']);
        $output['icon_size'] = absint($input['icon_size']);
        if ($output['icon_size'] < 10 || $output['icon_size'] > 40) {
            add_settings_error('smb_options', 'invalid_icon_size', __('Icon size must be between 10 and 40 pixels.', 'sticky-mobile-buttons'));
            $output['icon_size'] = 20;
        }
        
        $output['background_color'] = sanitize_hex_color($input['background_color']);
        
        // Настройки кнопок
        for ($i = 1; $i <= 7; $i++) {
            $output['buttons'][$i]['enabled'] = isset($input['buttons'][$i]['enabled']);
            $output['buttons'][$i]['type'] = sanitize_text_field($input['buttons'][$i]['type']);
            $output['buttons'][$i]['text'] = sanitize_text_field($input['buttons'][$i]['text']);
            $output['buttons'][$i]['value'] = sanitize_text_field($input['buttons'][$i]['value']);
            $output['buttons'][$i]['icon_type'] = sanitize_text_field($input['buttons'][$i]['icon_type']);
            $output['buttons'][$i]['icon_color'] = sanitize_hex_color($input['buttons'][$i]['icon_color']);
            
            if ($output['buttons'][$i]['icon_type'] === 'fontawesome') {
                $output['buttons'][$i]['icon'] = sanitize_text_field($input['buttons'][$i]['icon']);
                $output['buttons'][$i]['custom_icon'] = 0;
            } else {
                $output['buttons'][$i]['icon'] = '';
                $output['buttons'][$i]['custom_icon'] = absint($input['buttons'][$i]['custom_icon']);
            }
        }
        
        return $output;
    }
    
    // Рендер полей настроек с улучшенным экранированием
    public function number_of_buttons_render() {
        $value = isset($this->options['number_of_buttons']) ? $this->options['number_of_buttons'] : 4;
        ?>
        <select name="smb_options[number_of_buttons]">
            <?php for ($i = 1; $i <= 7; $i++): ?>
                <option value="<?php echo esc_attr($i); ?>" <?php selected($value, $i); ?>><?php echo esc_html($i); ?></option>
            <?php endfor; ?>
        </select>
        <p class="description"><?php esc_html_e('Select how many buttons to display (1-7)', 'sticky-mobile-buttons'); ?></p>
        <?php
    }
    
    public function show_text_render() {
        $value = isset($this->options['show_text']) ? $this->options['show_text'] : true;
        ?>
        <label>
            <input type="checkbox" name="smb_options[show_text]" value="1" <?php checked($value, true); ?>>
            <?php esc_html_e('Show text under icons', 'sticky-mobile-buttons'); ?>
        </label>
        <?php
    }
    
    public function icon_size_render() {
        $value = isset($this->options['icon_size']) ? $this->options['icon_size'] : 20;
        ?>
        <input type="number" name="smb_options[icon_size]" value="<?php echo esc_attr($value); ?>" min="10" max="40">
        <p class="description"><?php esc_html_e('Icon size in pixels (10-40)', 'sticky-mobile-buttons'); ?></p>
        <?php
    }
    
    public function background_color_render() {
        $value = isset($this->options['background_color']) ? $this->options['background_color'] : '#ffffff';
        ?>
        <input type="text" name="smb_options[background_color]" value="<?php echo esc_attr($value); ?>" class="smb-color-field">
        <?php
    }
    
    public function button_render($args) {
        $index = $args['index'];
        $button = isset($this->options['buttons'][$index]) ? $this->options['buttons'][$index] : array();
        
        $enabled = isset($button['enabled']) ? $button['enabled'] : false;
        $type = isset($button['type']) ? $button['type'] : 'phone';
        $text = isset($button['text']) ? $button['text'] : '';
        $value = isset($button['value']) ? $button['value'] : '';
        $icon_type = isset($button['icon_type']) ? $button['icon_type'] : 'fontawesome';
        $icon = isset($button['icon']) ? $button['icon'] : '';
        $custom_icon = isset($button['custom_icon']) ? $button['custom_icon'] : 0;
        $icon_color = isset($button['icon_color']) ? $button['icon_color'] : '#000000';
        
        // Получаем URL кастомной иконки
        $custom_icon_url = $custom_icon ? wp_get_attachment_url($custom_icon) : '';
        ?>
        <div class="smb-button-settings">
            <label>
                <input type="checkbox" name="smb_options[buttons][<?php echo esc_attr($index); ?>][enabled]" value="1" <?php checked($enabled, true); ?>>
                <?php printf(esc_html__('Enable Button %d', 'sticky-mobile-buttons'), $index); ?>
            </label>
            
            <div style="margin-top: 10px;">
                <label><?php esc_html_e('Button Type:', 'sticky-mobile-buttons'); ?></label>
                <select name="smb_options[buttons][<?php echo esc_attr($index); ?>][type]" class="smb-button-type">
                    <option value="phone" <?php selected($type, 'phone'); ?>><?php esc_html_e('Phone', 'sticky-mobile-buttons'); ?></option>
                    <option value="telegram" <?php selected($type, 'telegram'); ?>><?php esc_html_e('Telegram', 'sticky-mobile-buttons'); ?></option>
                    <option value="whatsapp" <?php selected($type, 'whatsapp'); ?>><?php esc_html_e('WhatsApp', 'sticky-mobile-buttons'); ?></option>
                    <option value="cart" <?php selected($type, 'cart'); ?>><?php esc_html_e('Cart', 'sticky-mobile-buttons'); ?></option>
                    <option value="custom" <?php selected($type, 'custom'); ?>><?php esc_html_e('Custom', 'sticky-mobile-buttons'); ?></option>
                </select>
            </div>
            
            <div style="margin-top: 10px;">
                <label><?php esc_html_e('Button Text:', 'sticky-mobile-buttons'); ?></label>
                <input type="text" name="smb_options[buttons][<?php echo esc_attr($index); ?>][text]" value="<?php echo esc_attr($text); ?>">
            </div>
            
            <div style="margin-top: 10px;" class="smb-button-value">
                <label class="smb-value-label">
                    <?php 
                    switch($type) {
                        case 'phone': 
                            esc_html_e('Phone Number:', 'sticky-mobile-buttons'); 
                            break;
                        case 'telegram': 
                            esc_html_e('Telegram Username:', 'sticky-mobile-buttons'); 
                            break;
                        case 'whatsapp': 
                            esc_html_e('WhatsApp Number:', 'sticky-mobile-buttons'); 
                            break;
                        case 'cart': 
                            esc_html_e('Cart URL (leave empty for default):', 'sticky-mobile-buttons'); 
                            break;
                        case 'custom': 
                            esc_html_e('Link URL:', 'sticky-mobile-buttons'); 
                            break;
                    }
                    ?>
                </label>
                <input type="text" name="smb_options[buttons][<?php echo esc_attr($index); ?>][value]" value="<?php echo esc_attr($value); ?>">
            </div>
            
            <div style="margin-top: 10px;">
                <label><?php esc_html_e('Icon Type:', 'sticky-mobile-buttons'); ?></label>
                <select name="smb_options[buttons][<?php echo esc_attr($index); ?>][icon_type]" class="smb-icon-type">
                    <option value="fontawesome" <?php selected($icon_type, 'fontawesome'); ?>><?php esc_html_e('Font Awesome', 'sticky-mobile-buttons'); ?></option>
                    <option value="custom" <?php selected($icon_type, 'custom'); ?>><?php esc_html_e('Custom Image', 'sticky-mobile-buttons'); ?></option>
                </select>
            </div>
            
            <div style="margin-top: 10px; display: <?php echo $icon_type === 'fontawesome' ? 'block' : 'none'; ?>" class="smb-fontawesome-icon">
                <label><?php esc_html_e('Select Icon:', 'sticky-mobile-buttons'); ?></label>
                <select name="smb_options[buttons][<?php echo esc_attr($index); ?>][icon]" class="smb-icon-select">
                    <?php foreach ($this->get_font_awesome_icons() as $icon_class => $icon_name): ?>
                        <option value="<?php echo esc_attr($icon_class); ?>" <?php selected($icon, $icon_class); ?> data-icon="<?php echo esc_attr($icon_class); ?>">
                            <?php echo esc_html($icon_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="smb-icon-preview">
                    <i class="<?php echo esc_attr($icon); ?>"></i>
                </div>
            </div>
            
            <div style="margin-top: 10px; display: <?php echo $icon_type === 'custom' ? 'block' : 'none'; ?>" class="smb-custom-icon">
                <label><?php esc_html_e('Custom Icon:', 'sticky-mobile-buttons'); ?></label>
                <div class="smb-custom-icon-upload">
                    <input type="hidden" name="smb_options[buttons][<?php echo esc_attr($index); ?>][custom_icon]" value="<?php echo esc_attr($custom_icon); ?>" class="smb-custom-icon-id">
                    <div class="smb-custom-icon-preview">
                        <?php if ($custom_icon_url): ?>
                            <img src="<?php echo esc_url($custom_icon_url); ?>" style="max-width: 50px; max-height: 50px;">
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button smb-upload-icon"><?php esc_html_e('Upload Icon', 'sticky-mobile-buttons'); ?></button>
                    <button type="button" class="button smb-remove-icon" style="<?php echo !$custom_icon ? 'display:none;' : ''; ?>"><?php esc_html_e('Remove Icon', 'sticky-mobile-buttons'); ?></button>
                </div>
            </div>
            
            <div style="margin-top: 10px;">
                <label><?php esc_html_e('Icon Color:', 'sticky-mobile-buttons'); ?></label>
                <input type="text" name="smb_options[buttons][<?php echo esc_attr($index); ?>][icon_color]" value="<?php echo esc_attr($icon_color); ?>" class="smb-color-field">
            </div>
        </div>
        <?php
    }
    
    // Метод для получения иконок Font Awesome
    private function get_font_awesome_icons() {
        return array(
            'fas fa-phone' => __('Phone', 'sticky-mobile-buttons'),
            'fas fa-phone-alt' => __('Phone (alt)', 'sticky-mobile-buttons'),
            'fab fa-whatsapp' => __('WhatsApp', 'sticky-mobile-buttons'),
            'fab fa-telegram' => __('Telegram', 'sticky-mobile-buttons'),
            'fas fa-shopping-cart' => __('Cart', 'sticky-mobile-buttons'),
            'fas fa-envelope' => __('Email', 'sticky-mobile-buttons'),
            'fas fa-map-marker-alt' => __('Location', 'sticky-mobile-buttons'),
            'fas fa-user' => __('User', 'sticky-mobile-buttons'),
            'fas fa-heart' => __('Heart', 'sticky-mobile-buttons'),
            'fas fa-star' => __('Star', 'sticky-mobile-buttons'),
            'fas fa-home' => __('Home', 'sticky-mobile-buttons'),
            'fas fa-info' => __('Info', 'sticky-mobile-buttons'),
            'fas fa-globe' => __('Globe', 'sticky-mobile-buttons'),
            'fas fa-search' => __('Search', 'sticky-mobile-buttons'),
            'fas fa-bars' => __('Menu', 'sticky-mobile-buttons'),
            'fas fa-times' => __('Close', 'sticky-mobile-buttons'),
            'fas fa-check' => __('Check', 'sticky-mobile-buttons'),
            'fas fa-arrow-up' => __('Arrow Up', 'sticky-mobile-buttons'),
            'fas fa-arrow-down' => __('Arrow Down', 'sticky-mobile-buttons'),
            'fas fa-arrow-left' => __('Arrow Left', 'sticky-mobile-buttons'),
            'fas fa-arrow-right' => __('Arrow Right', 'sticky-mobile-buttons')
        );
    }
    
    // Подключение скриптов и стилей в админке
    public function admin_enqueue_scripts($hook) {
        if ($hook !== 'settings_page_sticky-mobile-buttons') {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        wp_enqueue_style('font-awesome-admin', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
        
        wp_enqueue_style('smb-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css', array(), '3.0.0');
        
        // Правильно подключаем скрипт с зависимостями
        wp_enqueue_script('smb-admin-script', plugin_dir_url(__FILE__) . '../assets/js/admin-script.js', 
            array('jquery', 'wp-color-picker', 'media-upload'), '3.0.0', true);
        
        // Локализация скрипта
        wp_localize_script('smb-admin-script', 'smb_admin', array(
            'title' => __('Select or Upload Icon', 'sticky-mobile-buttons'),
            'button_text' => __('Use this icon', 'sticky-mobile-buttons'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('smb_upload_nonce'),
            'labels' => array(
                'phone_number' => __('Phone Number:', 'sticky-mobile-buttons'),
                'telegram_username' => __('Telegram Username:', 'sticky-mobile-buttons'),
                'whatsapp_number' => __('WhatsApp Number:', 'sticky-mobile-buttons'),
                'cart_url' => __('Cart URL (leave empty for default):', 'sticky-mobile-buttons'),
                'link_url' => __('Link URL:', 'sticky-mobile-buttons')
            )
        ));
    }
    
    // Страница настроек
    public function options_page() {
        // Проверка прав доступа
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('smb_settings');
                do_settings_sections('smb_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}