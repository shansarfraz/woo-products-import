<?php
/**
 * Plugin Name: CSV Product Importer
 * Description: Import products from CSV in batches using cron
 * Version: 1.0
 * Author: Your Name
 */

defined('ABSPATH') || exit;

define('CSV_IMPORTER_VERSION', '1.0.0');
define('CSV_IMPORTER_PLUGIN_FILE', __FILE__);

class CSV_Product_Importer {
    private $batch_size = 100;
    
    public function __construct() {
        // Load dependencies
        require_once plugin_dir_path(__FILE__) . 'includes/admin/class-admin-ui.php';
        
        // Initialize admin UI
        $this->admin_ui = new CSV_Importer_Admin_UI($this);
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('csv_product_import_cron', array($this, 'process_import_batch'));
        register_activation_hook(__FILE__, array($this, 'plugin_activation'));
        register_deactivation_hook(__FILE__, array($this, 'plugin_deactivation'));
    }

    public function plugin_activation() {
        if (!wp_next_scheduled('csv_product_import_cron')) {
            wp_schedule_event(time(), 'every_five_minutes', 'csv_product_import_cron');
        }
    }

    public function plugin_deactivation() {
        wp_clear_scheduled_hook('csv_product_import_cron');
    }

    public function add_admin_menu() {
        add_menu_page(
            'CSV Product Importer',
            'CSV Importer',
            'manage_options',
            'csv-product-importer',
            array($this, 'admin_page'),
            'dashicons-upload'
        );
    }

    public function register_settings() {
        register_setting('csv_importer_settings', 'csv_file_path');
        register_setting('csv_importer_settings', 'import_offset');
    }

    public function admin_page() {
        $this->admin_ui->render_admin_page();
    }

    public function process_import_batch() {
        $csv_file = get_option('csv_file_path');
        if (!file_exists($csv_file)) {
            return;
        }

        $offset = (int)get_option('import_offset', 0);
        $row_count = 0;
        $processed = 0;

        if (($handle = fopen($csv_file, "r")) !== FALSE) {
            // Skip header row
            if ($offset === 0) {
                fgetcsv($handle);
                $offset++;
            }

            // Skip to current offset
            while ($row_count < $offset && fgetcsv($handle)) {
                $row_count++;
            }

            // Process batch
            while (($data = fgetcsv($handle)) !== FALSE && $processed < $this->batch_size) {
                $this->import_product($data);
                $processed++;
                $offset++;
            }

            fclose($handle);
            update_option('import_offset', $offset);

            // Check if we've reached the end of file
            if ($data === FALSE) {
                update_option('import_offset', 0);
            }
        }
    }

    private function import_product($data) {
        // Assuming CSV columns: name, description, price, sku
        $product = new WC_Product_Simple();
        
        $product->set_name($data[0]);
        $product->set_description($data[1]);
        $product->set_regular_price($data[2]);
        $product->set_sku($data[3]);
        
        $product->save();
    }
}

// Initialize plugin
new CSV_Product_Importer();

// Add custom cron schedule
add_filter('cron_schedules', function($schedules) {
    $schedules['every_five_minutes'] = array(
        'interval' => 300,
        'display'  => __('Every Five Minutes')
    );
    return $schedules;
});

// Add custom cron schedule
add_filter('cron_schedules', function($schedules) {
    $schedules['every_five_minutes'] = array(
        'interval' => 300,
        'display'  => __('Every Five Minutes')
    );
    return $schedules;
});