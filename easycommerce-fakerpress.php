<?php
/**
 * Plugin Name: EasyCommerce FakerPress
 * Description: Generate fake ecommerce data (orders, products, customers, coupons) for EasyCommerce plugin using PHPFaker library
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: easycommerce-fakerpress
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ECFP_PLUGIN_FILE', __FILE__);
define('ECFP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ECFP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ECFP_VERSION', '1.0.0');

require_once ECFP_PLUGIN_DIR . 'vendor/autoload.php';

class EasyCommerceFakerPress
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('wp_ajax_ecfp_generate_data', [$this, 'handleAjaxRequest']);
        
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    public function init()
    {
        load_plugin_textdomain('easycommerce-fakerpress', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function addAdminMenu()
    {
        add_menu_page(
            __('EasyCommerce FakerPress', 'easycommerce-fakerpress'),
            __('EC FakerPress', 'easycommerce-fakerpress'),
            'manage_options',
            'easycommerce-fakerpress',
            [$this, 'renderAdminPage'],
            'dashicons-randomize',
            30
        );
    }

    public function renderAdminPage()
    {
        echo '<div id="easycommerce-fakerpress-root"></div>';
    }

    public function enqueueAdminAssets($hook)
    {
        if ($hook !== 'toplevel_page_easycommerce-fakerpress') {
            return;
        }

        wp_enqueue_script(
            'easycommerce-fakerpress-admin',
            ECFP_PLUGIN_URL . 'build/admin.js',
            ['wp-element', 'wp-i18n'],
            ECFP_VERSION,
            true
        );

        wp_enqueue_style(
            'easycommerce-fakerpress-admin',
            ECFP_PLUGIN_URL . 'build/admin.css',
            [],
            ECFP_VERSION
        );

        wp_localize_script('easycommerce-fakerpress-admin', 'ecfpAjax', [
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ecfp_nonce'),
        ]);
    }

    public function handleAjaxRequest()
    {
        check_ajax_referer('ecfp_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $type = sanitize_text_field($_POST['type']);
        $count = intval($_POST['count']);

        try {
            switch ($type) {
                case 'products':
                    $result = $this->generateProducts($count);
                    break;
                case 'customers':
                    $result = $this->generateCustomers($count);
                    break;
                case 'orders':
                    $result = $this->generateOrders($count);
                    break;
                case 'coupons':
                    $result = $this->generateCoupons($count);
                    break;
                default:
                    throw new Exception('Invalid type');
            }

            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    private function generateProducts($count)
    {
        require_once ECFP_PLUGIN_DIR . 'includes/class-product-generator.php';
        $generator = new ECFP_Product_Generator();
        return $generator->generate($count);
    }

    private function generateCustomers($count)
    {
        require_once ECFP_PLUGIN_DIR . 'includes/class-customer-generator.php';
        $generator = new ECFP_Customer_Generator();
        return $generator->generate($count);
    }

    private function generateOrders($count)
    {
        require_once ECFP_PLUGIN_DIR . 'includes/class-order-generator.php';
        $generator = new ECFP_Order_Generator();
        return $generator->generate($count);
    }

    private function generateCoupons($count)
    {
        require_once ECFP_PLUGIN_DIR . 'includes/class-coupon-generator.php';
        $generator = new ECFP_Coupon_Generator();
        return $generator->generate($count);
    }

    public function activate()
    {
        // Activation logic here
    }

    public function deactivate()
    {
        // Deactivation logic here
    }
}

EasyCommerceFakerPress::getInstance();