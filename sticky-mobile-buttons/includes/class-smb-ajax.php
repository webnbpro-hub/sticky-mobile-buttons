<?php
/**
 * AJAX functionality for Sticky Mobile Buttons
 */

class SMB_Ajax {
    
    public function init() {
        add_action('wp_ajax_smb_upload_icon', array($this, 'ajax_upload_icon'));
    }
    
    // AJAX-обработчик для загрузки иконок
    public function ajax_upload_icon() {
        // Проверка nonce и прав доступа
        if (!check_ajax_referer('smb_upload_nonce', 'nonce', false) || !current_user_can('upload_files')) {
            wp_die(esc_html__('Unauthorized access.', 'sticky-mobile-buttons'));
        }
        
        // Обработка загрузки файла
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $uploadedfile = $_FILES['file'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            // Создание attachment
            $attachment = array(
                'post_mime_type' => $movefile['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $movefile['file']);
            
            if (!is_wp_error($attach_id)) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
                
                // Оптимизация изображения
                $this->optimize_image($movefile['file']);
                
                wp_send_json_success(array(
                    'id' => $attach_id,
                    'url' => wp_get_attachment_url($attach_id)
                ));
            }
        }
        
        wp_send_json_error(esc_html__('Upload failed.', 'sticky-mobile-buttons'));
    }
    
    // Оптимизация изображения
    private function optimize_image($file_path) {
        if (function_exists('wp_get_image_editor')) {
            $editor = wp_get_image_editor($file_path);
            if (!is_wp_error($editor)) {
                // Изменение размера до 80x80px (2x для Retina)
                $editor->resize(80, 80, true);
                
                // Установка качества сжатия
                $editor->set_quality(80);
                
                // Сохранение изображения
                $editor->save($file_path);
            }
        }
    }
}