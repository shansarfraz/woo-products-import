<?php
if (!defined('ABSPATH')) {
    exit;
}

class CSV_Importer_Admin_UI {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Product Importer',
            'Product Importer',
            'manage_options',
            'product-importer',
            array($this, 'render_admin_page'),
            'dashicons-upload',
            56
        );
    }

    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>Product Importer</h1>
            
            <div class="card">
                <h2>Test Import</h2>
                <p>Create 1000 test products to verify the importer functionality.</p>
                <form method="post">
                    <?php wp_nonce_field('run_test_import', 'test_import_nonce'); ?>
                    <button type="submit" name="run_test_import" class="button button-primary">
                        Create 1000 Test Products
                    </button>
                </form>
                
                <?php
                if (isset($_POST['run_test_import']) && check_admin_referer('run_test_import', 'test_import_nonce')) {
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
                    
                    $end_time = microtime(true);
                    $total_time = round($end_time - $start_time, 2);

                    echo '<div class="notice notice-success"><p>';
                    echo "Created {$result['count']} products in {$total_time} seconds";
                    echo '</p></div>';
                }
                ?>
            </div>
        </div>
        <?php
    }
}
