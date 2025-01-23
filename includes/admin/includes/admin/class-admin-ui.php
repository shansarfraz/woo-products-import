<?php
defined('ABSPATH') || exit;

class CSV_Importer_Admin_UI {
    private $importer;
    private $settings_key = 'csv_importer_settings';
    
    public function __construct($importer) {
        $this->importer = $importer;
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_csv-product-importer' !== $hook) {
            return;
        }

        wp_enqueue_style('csv-importer-admin', 
            plugins_url('/assets/css/admin.css', CSV_IMPORTER_PLUGIN_FILE),
            array(), 
            CSV_IMPORTER_VERSION
        );

        wp_enqueue_script('csv-importer-admin',
            plugins_url('/assets/js/admin.js', CSV_IMPORTER_PLUGIN_FILE),
            array('jquery'),
            CSV_IMPORTER_VERSION,
            true
        );

        wp_localize_script('csv-importer-admin', 'csvImporterAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('csv-importer-nonce')
        ));
    }

    public function render_admin_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'import';
        ?>
        <div class="wrap csv-importer-admin">
            <h1 class="wp-heading-inline"><?php _e('CSV Product Importer', 'csv-product-importer'); ?></h1>
            <hr class="wp-header-end">

            <nav class="nav-tab-wrapper">
                <a href="?page=csv-product-importer&tab=import" 
                   class="nav-tab <?php echo $active_tab === 'import' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Import', 'csv-product-importer'); ?>
                </a>
                <a href="?page=csv-product-importer&tab=mapping" 
                   class="nav-tab <?php echo $active_tab === 'mapping' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Field Mapping', 'csv-product-importer'); ?>
                </a>
                <a href="?page=csv-product-importer&tab=settings" 
                   class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Settings', 'csv-product-importer'); ?>
                </a>
                <a href="?page=csv-product-importer&tab=logs" 
                   class="nav-tab <?php echo $active_tab === 'logs' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Logs', 'csv-product-importer'); ?>
                </a>
            </nav>

            <div class="tab-content">
                <?php
                switch ($active_tab) {
                    case 'mapping':
                        $this->render_mapping_tab();
                        break;
                    case 'settings':
                        $this->render_settings_tab();
                        break;
                    case 'logs':
                        $this->render_logs_tab();
                        break;
                    default:
                        $this->render_import_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    private function render_import_tab() {
        ?>
        <div class="csv-importer-card">
            <h2><?php _e('Import Products', 'csv-product-importer'); ?></h2>
            
            <div class="import-status">
                <?php
                $total_rows = $this->importer->get_total_rows();
                $processed_rows = $this->importer->get_processed_rows();
                if ($total_rows > 0) {
                    $progress = ($processed_rows / $total_rows) * 100;
                    ?>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?php echo esc_attr($progress); ?>%"></div>
                    </div>
                    <p>
                        <?php printf(__('Processed %d of %d products', 'csv-product-importer'), 
                            $processed_rows, $total_rows); ?>
                    </p>
                    <?php
                }
                ?>
            </div>

            <form method="post" action="" class="import-form">
                <?php wp_nonce_field('csv_importer_import', 'csv_importer_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="csv_file"><?php _e('CSV File', 'csv-product-importer'); ?></label>
                        </th>
                        <td>
                            <input type="file" name="csv_file" id="csv_file" accept=".csv">
                            <p class="description">
                                <?php _e('Select a CSV file containing your product data.', 'csv-product-importer'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary" name="start_import">
                        <?php _e('Start Import', 'csv-product-importer'); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
    }

    private function render_mapping_tab() {
        $csv_headers = $this->importer->get_csv_headers();
        $field_mapping = get_option($this->settings_key . '_mapping', array());
        ?>
        <div class="csv-importer-card">
            <h2><?php _e('Field Mapping', 'csv-product-importer'); ?></h2>
            
            <form method="post" action="options.php">
                <?php settings_fields($this->settings_key); ?>
                
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php _e('CSV Column', 'csv-product-importer'); ?></th>
                            <th><?php _e('Product Field', 'csv-product-importer'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($csv_headers as $index => $header): ?>
                        <tr>
                            <td><?php echo esc_html($header); ?></td>
                            <td>
                                <select name="<?php echo $this->settings_key; ?>_mapping[<?php echo $index; ?>]">
                                    <option value=""><?php _e('-- Select Field --', 'csv-product-importer'); ?></option>
                                    <?php foreach ($this->get_product_fields() as $key => $label): ?>
                                        <option value="<?php echo esc_attr($key); ?>" 
                                            <?php selected(isset($field_mapping[$index]) ? $field_mapping[$index] : '', $key); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php submit_button(__('Save Mapping', 'csv-product-importer')); ?>
            </form>
        </div>
        <?php
    }

    private function render_settings_tab() {
        ?>
        <div class="csv-importer-card">
            <h2><?php _e('Import Settings', 'csv-product-importer'); ?></h2>
            
            <form method="post" action="options.php">
                <?php settings_fields($this->settings_key); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="batch_size"><?php _e('Batch Size', 'csv-product-importer'); ?></label>
                        </th>
                        <td>
                            <input type="number" 
                                   id="batch_size" 
                                   name="<?php echo $this->settings_key; ?>[batch_size]" 
                                   value="<?php echo esc_attr(get_option($this->settings_key)['batch_size'] ?? 100); ?>"
                                   min="1" 
                                   max="500">
                            <p class="description">
                                <?php _e('Number of products to import per batch', 'csv-product-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="cron_interval"><?php _e('Import Frequency', 'csv-product-importer'); ?></label>
                        </th>
                        <td>
                            <select id="cron_interval" name="<?php echo $this->settings_key; ?>[cron_interval]">
                                <?php foreach ($this->get_cron_intervals() as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" 
                                        <?php selected(get_option($this->settings_key)['cron_interval'] ?? 'every_five_minutes', $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Save Settings', 'csv-product-importer')); ?>
            </form>
        </div>
        <?php
    }

    private function render_logs_tab() {
        ?>
        <div class="csv-importer-card">
            <h2><?php _e('Import Logs', 'csv-product-importer'); ?></h2>
            
            <div id="import-logs" class="import-logs">
                <?php echo $this->importer->get_logs(); ?>
            </div>
            
            <p>
                <button type="button" class="button" id="clear-logs">
                    <?php _e('Clear Logs', 'csv-product-importer'); ?>
                </button>
            </p>
        </div>
        <?php
    }

    private function get_product_fields() {
        return array(
            'name' => __('Product Name', 'csv-product-importer'),
            'description' => __('Description', 'csv-product-importer'),
            'short_description' => __('Short Description', 'csv-product-importer'),
            'sku' => __('SKU', 'csv-product-importer'),
            'regular_price' => __('Regular Price', 'csv-product-importer'),
            'sale_price' => __('Sale Price', 'csv-product-importer'),
            'stock_quantity' => __('Stock Quantity', 'csv-product-importer'),
            'weight' => __('Weight', 'csv-product-importer'),
            'length' => __('Length', 'csv-product-importer'),
            'width' => __('Width', 'csv-product-importer'),
            'height' => __('Height', 'csv-product-importer'),
            'categories' => __('Categories', 'csv-product-importer'),
            'tags' => __('Tags', 'csv-product-importer')
        );
    }

    private function get_cron_intervals() {
        return array(
            'every_five_minutes' => __('Every 5 Minutes', 'csv-product-importer'),
            'hourly' => __('Every Hour', 'csv-product-importer'),
            'twicedaily' => __('Twice Daily', 'csv-product-importer'),
            'daily' => __('Once Daily', 'csv-product-importer')
        );
    }
} 