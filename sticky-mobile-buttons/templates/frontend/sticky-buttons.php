<?php
/**
 * Template for displaying sticky mobile buttons
 *
 * @package Sticky_Mobile_Buttons
 * @var array $buttons_data
 * @var bool $show_text
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Extract variables passed from the main class
$buttons = $buttons_data['buttons'];
$show_text = $buttons_data['show_text'];
$background_color = $buttons_data['background_color'];
$icon_size = $buttons_data['icon_size'];
?>

<div class="sticky-buttons-mobile" style="background: <?php echo esc_attr($background_color); ?>">
    <?php foreach ($buttons as $index => $button) : 
        if (!$button['enabled']) {
            continue;
        }
        
        // Determine link attributes based on button type
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
        
        // Determine icon HTML
        $icon_html = '';
        if ($button['icon_type'] === 'fontawesome') {
            $icon_html = '<i class="' . esc_attr($button['icon']) . '" style="color: ' . esc_attr($button['icon_color']) . '; font-size: ' . esc_attr($icon_size) . 'px"></i>';
        } else {
            $icon_url = wp_get_attachment_url($button['custom_icon']);
            if ($icon_url) {
                $icon_html = '<img src="' . esc_url($icon_url) . '" alt="' . esc_attr($button['text']) . '" style="width: ' . esc_attr($icon_size) . 'px; height: ' . esc_attr($icon_size) . 'px">';
            } else {
                $icon_html = '<i class="fas fa-question-circle" style="color: ' . esc_attr($button['icon_color']) . '; font-size: ' . esc_attr($icon_size) . 'px"></i>';
            }
        }
    ?>
        <a href="<?php echo $href; ?>" class="sticky-button sticky-button-<?php echo esc_attr($index); ?>" <?php echo $target; ?> <?php echo $rel; ?>>
            <?php echo $icon_html; ?>
            <?php if ($show_text) : ?>
                <span><?php echo esc_html($button['text']); ?></span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>