<?php
/**
 * Plugin Name: Add custom taxonomy to woocommerce exporter-importer
 * Plugin URI: http://www.sebpo.com
 * Description: add custom taxonomy to woocommerce export import CSV file
 * Version: 1.0.0
 * Author: Mohammed Mohasin
 * Author URI: https://www.linkedin.com/in/md-mohasin
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: ct-woocommerce-ei
 * WC requires at least: 2.2.0
 * WC tested up to: 5.5.1
 */


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/class-import-custom-taxonmoy.php';
require __DIR__ . '/class-export-custom-taxonmoy.php';

class init
{
    private $custom_taxonomy = [];

    public function __construct()
    {
        add_action('init', [$this, 'load_export_import']);
    }

    function load_export_import()
    {
        if (is_admin()) {
            global $pagenow;
            $page = isset($_GET['page']) && (($_GET['page'] == 'product_exporter') || ($_GET['page'] == 'product_importer')) ? true : false;
            //  if (($pagenow == 'edit.php') && ($_GET['post_type'] == 'product')) {
            $this->custom_taxonomy = $this->get_custom_taxonomy();
            //  }
        }
    }

    function add_taxonomy_to_csv_column()
    {
        if (!empty($this->custom_taxonomies)) {
            foreach ($this->custom_taxonomies as $taxo) {
                $taxonoies_id = $taxo->name;
                $taxonomies_name = $taxo->labels->singular_name;
                new export_custom_taxonmoy($taxonoies_id, $taxonomies_name);
                new import_custom_taxonmoy($taxonoies_id, $taxonomies_name);
            }
        }
    }

    function get_custom_taxonomy()
    {
        $product_taxonomies = $this->get_product_taxonomy();
        $exclude_default_taxonomies = ['product_type', 'product_cat', 'product_tag'];

        // Find woocommerce Attribute Taxonomies
        if (!empty($product_taxonomies)) {
            $taxonomy_ids = array_keys($product_taxonomies);
            foreach ($taxonomy_ids as $id) {
                $pos = strpos($id, 'pa_');
                if ($pos !== false && $pos === 0) {
                    $exclude_default_taxonomies[] = $id;
                }
            }
        }

        return array_diff_key($product_taxonomies, array_flip($exclude_default_taxonomies));
    }

    function get_product_taxonomy()
    {
        $args = array(
            'object_type' => array('product')
        );
        return get_taxonomies($args, 'objects');
    }
}


$export_import_init = new init();
$export_import_init->add_taxonomy_to_csv_column();

