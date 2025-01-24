<?php
/**
 * Plugin Name: WooCommerce Products Import
 * Description: Import products from CSV/Excel in batches using direct database queries
 * Version: 1.0
 * Author: Your Name
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 */

defined('ABSPATH') || exit;

define('CSV_IMPORTER_VERSION', '1.0.0');
define('CSV_IMPORTER_PLUGIN_FILE', __FILE__);
define('WC_VERSION', '8.0.0'); // Add WooCommerce version constant

// Load Product Importer class
require_once plugin_dir_path(__FILE__) . 'includes/class-product-importer.php';

/**
 * Example usage of the Product Importer:
 * 
 * $importer = new WC_Product_DB_Importer();
 * 
 * $product_data = array(
 *     'name' => 'Product Name',              // Required
 *     'description' => 'Full description',    // Optional
 *     'short_description' => 'Short desc',    // Optional
 *     'sku' => 'PROD001',                    // Optional
 *     'regular_price' => '29.99',            // Optional
 *     'sale_price' => '24.99',               // Optional
 *     'stock_quantity' => 100,               // Optional
 *     'stock_status' => 'instock',           // Optional (instock/outofstock)
 *     'categories' => 'Cat1, Cat2',          // Optional (comma-separated)
 *     'tags' => 'Tag1, Tag2',                // Optional (comma-separated)
 *     'image' => 'https://example.com/img.jpg', // Optional (URL)
 *     'gallery' => [                         // Optional (array or comma-separated URLs)
 *         'https://example.com/img1.jpg',
 *         'https://example.com/img2.jpg'
 *     ],
 *     'virtual' => 'no',                     // Optional (yes/no)
 *     'downloadable' => 'no',                // Optional (yes/no)
 *     'tax_status' => 'taxable',             // Optional (taxable/none)
 *     'tax_class' => '',                     // Optional
 *     'weight' => '1.5',                     // Optional
 *     'length' => '10',                      // Optional
 *     'width' => '5',                        // Optional
 *     'height' => '3'                        // Optional
 * );
 * 
 * $result = $importer->create_product($product_data);
 * 
 * if ($result['success']) {
 *     echo "Product created with ID: " . $result['id'];
 * } else {
 *     echo "Error: " . $result['error'];
 * }
 */

// Test function to create dummy products
function create_test_products() {
    if (!isset($_GET['create_test_products'])) {
        return;
    }

    $importer = new WC_Product_DB_Importer();
    $products = [];
    $start_time = microtime(true);

    for ($i = 1; $i <= 1000; $i++) {
        $products[] = array(
            'name' => "Test Product {$i}",
            'description' => "This is test product number {$i}",
            'short_description' => "Short description {$i}",
            'sku' => "TEST-{$i}",
            'regular_price' => rand(10, 100),
            'sale_price' => rand(5, 90),
            'stock_quantity' => rand(1, 1000),
            'categories' => 'Test Category',
            'tags' => 'test, sample'
        );
    }

    $result = $importer->create_products($products);
    wp_die("Created {$result['count']} products in " . (microtime(true) - $start_time) . " seconds");
}

add_action('admin_init', 'create_test_products');

// Initialize admin UI if in admin area
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'includes/class-admin-ui.php';
    new CSV_Importer_Admin_UI();
}