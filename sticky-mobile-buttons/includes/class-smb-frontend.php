<?php
/**
 * Frontend functionality for Sticky Mobile Buttons
 */

class SMB_Frontend {
    
    private $options;
    
    public function __construct() {
        $plugin = StickyMobileButtons::get_instance();
        $this->options = $plugin->get_options();
    }
    
    public function init() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'display_sticky_buttons'));
    }
    
    // Подключение скриптов и стилей на фронтенде
    public function enqueue_scripts() {
        // Подключаем Font Awesome
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
        
        // Подключаем наши стили
        wp_enqueue_style('sticky-mobile-buttons-style', plugin_dir_url(__FILE__) . '../assets/css/style.css', array(), '3.0.0');
        
        // Добавляем инлайн-стили на основе настроек
        $css = "
            .sticky-buttons-mobile {
                background: {$this->options['background_color']} !important;
            }
        ";
        
        // Добавляем размер иконок
        $icon_size = isset($this->options['icon_size']) ? $this->options['icon_size'] : 20;
        $css .= ".sticky-button i, .sticky-button img { width: {$icon_size}px; height: {$icon_size}px; }";
        $css .= ".sticky-button i { font-size: {$icon_size}px; }";
        
        // Добавляем цвета для иконок
        for ($i = 1; $i <= 7; $i++) {
            if (isset($this->options['buttons'][$i]['enabled']) && $this->options['buttons'][$i]['enabled']) {
                $icon_color = $this->options['buttons'][$i]['icon_color'];
                $css .= ".sticky-button:nth-child({$i}) i { color: {$icon_color} !important; }";
                $css .= ".sticky-button:nth-child({$i}) img { filter: invert(" . $this->hex_to_filter($icon_color) . "); }";
            }
        }
        
        wp_add_inline_style('sticky-mobile-buttons-style', $css);
        
        // Подключаем скрипт для анимации
        wp_enqueue_script('sticky-mobile-buttons-js', plugin_dir_url(__FILE__) . '../assets/js/script.js', array(), '3.0.0', true);
    }
    
    // Конвертация HEX цвета в CSS filter (для окрашивания SVG)
    private function hex_to_filter($hex_color) {
        $rgb = sscanf($hex_color, "#%02x%02x%02x");
        return "{$rgb[0]}% {$rgb[1]}% {$rgb[2]}%";
    }
    
    // Вывод кнопок
    public function display_sticky_buttons() {
        $buttons_count = isset($this->options['number_of_buttons']) ? $this->options['number_of_buttons'] : 4;
        $show_text = isset($this->options['show_text']) ? $this->options['show_text'] : true;
        $background_color = isset($this->options['background_color']) ? $this->options['background_color'] : '#ffffff';
        $icon_size = isset($this->options['icon_size']) ? $this->options['icon_size'] : 20;
        
        // Prepare buttons data for template
        $buttons_data = array(
            'buttons' => array(),
            'show_text' => $show_text,
            'background_color' => $background_color,
            'icon_size' => $icon_size
        );
        
        for ($i = 1; $i <= $buttons_count; $i++) {
            if (!isset($this->options['buttons'][$i]['enabled']) || !$this->options['buttons'][$i]['enabled']) {
                continue;
            }
            
            $buttons_data['buttons'][$i] = $this->options['buttons'][$i];
        }
        
        // Include template
        $template_path = plugin_dir_path(__FILE__) . '../templates/frontend/sticky-buttons.php';
        if (file_exists($template_path)) {
            // Pass variables to template
            $buttons_data = apply_filters('smb_buttons_data', $buttons_data);
            
            // Используем буферизацию вывода вместо extract
            ob_start();
            include $template_path;
            $output = ob_get_clean();
            
            echo $output;
        } else {
            // Fallback to direct output if template doesn't exist
            $this->display_fallback($buttons_data);
        }
    }

    // Fallback method if template is missing
    private function display_fallback($buttons_data) {
        // Извлекаем переменные из массива
        $buttons = $buttons_data['buttons'];
        $show_text = $buttons_data['show_text'];
        $background_color = $buttons_data['background_color'];
        ?>
        <div class="sticky-buttons-mobile" style="background: <?php echo esc_attr($background_color); ?>">
            <?php foreach ($buttons as $index => $button) : 
                if (!$button['enabled']) {
                    continue;
                }
                
                // Определяем иконку и ссылку в зависимости от типа
                $href = '';
                $target = '';
                $rel = '';
                
                switch ($button['type']) {
                    case 'phone':
                        $href = 'tel:' . esc_attr($button['value']);
                        break;
                    case 'telegram':
                        $href = 'https://t.me/' . esc_attr($button['value']);
                        $target = 'target="_blank"';
                        $rel = 'rel="noopener"';
                        break;
                    case 'whatsapp':
                        $href = 'https://wa.me/' . esc_attr($button['value']);
                        $target = 'target="_blank"';
                        $rel = 'rel="noopener"';
                        break;
                    case 'cart':
                        $href = !empty($button['value']) ? esc_url($button['value']) : 
                               (function_exists('wc_get_cart_url') ? esc_url(wc_get_cart_url()) : esc_url('/cart'));
                        break;
                    case 'custom':
                        $href = esc_url($button['value']);
                        break;
                }
                
                // Определяем icon HTML
                $icon_html = '';
                if ($button['icon_type'] === 'fontawesome') {
                    $icon_html = '<i class="' . esc_attr($button['icon']) . '"></i>';
                } else {
                    $icon_url = wp_get_attachment_url($button['custom_icon']);
                    if ($icon_url) {
                        $icon_html = '<img src="' . esc_url($icon_url) . '" alt="' . esc_attr($button['text']) . '">';
                    } else {
                        $icon_html = '<i class="fas fa-question-circle"></i>';
                    }
                }
            ?>
                <a href="<?php echo $href; ?>" class="sticky-button" <?php echo $target; ?> <?php echo $rel; ?>>
                    <?php echo $icon_html; ?>
                    <?php if ($show_text) : ?>
                        <span><?php echo esc_html($button['text']); ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    }
}