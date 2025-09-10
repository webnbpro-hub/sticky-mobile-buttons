<?php
/**
 * Plugin Name: Sticky Mobile Buttons
 * Plugin URI: https://webnbpro.com/sticky-mobile-buttons
 * Description: Adds customizable sticky buttons at the bottom of the screen on mobile devices for quick contact and cart access.
 * Version: 3.0.0
 * Author: Webnbpro
 * Author URI: https://webnbpro.com
 * Text Domain: sticky-mobile-buttons
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Tested up to: 6.5
 * Requires PHP: 7.0
 */

// Запрещаем прямое обращение к файлу
if (!defined('ABSPATH')) {
    exit;
}

// Ручная загрузка классов вместо автозагрузки
require_once plugin_dir_path(__FILE__) . 'includes/class-smb-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-smb-frontend.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-smb-ajax.php';

// Инициализация плагина
class StickyMobileButtons {
    
    private static $instance = null;
    private $admin;
    private $frontend;
    private $ajax;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        // Загрузка текстового домена для локализации
        load_plugin_textdomain('sticky-mobile-buttons', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Инициализация компонентов
        $this->ajax = new SMB_Ajax();
        $this->admin = new SMB_Admin();
        $this->frontend = new SMB_Frontend();
        
        // Инициализация компонентов
        $this->ajax->init();
        $this->admin->init();
        $this->frontend->init();
    }
    
    public function get_options() {
        return $this->admin->get_options();
    }
}

// Запуск плагина
StickyMobileButtons::get_instance();