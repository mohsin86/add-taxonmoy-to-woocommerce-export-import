<?php
/**
 * Plugin Name: Add custom taxonomy to woocomrc exporter-importer
 * Plugin URI: http://www.sebpo.com
 * Description: add custom taxonomy to woocommerce export import CSV file
 * Version: 1.0.0
 * Author: Mohammed Mohasin
 * Author URI: https://www.linkedin.com/in/md-mohasin
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: ct-woocommerce-ei
 * WC requires at least: 4.2
 * WC tested up to: 5.5.1
 */


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/inc/class-import-custom-taxonmoy.php';
require __DIR__ . '/inc/class-export-custom-taxonmoy.php';
require __DIR__ . '/inc/class-get-custom-product-taxonomy.php';

class eiInit
{
    private $custom_taxonomy = [];
    private $taxonomy;

    public function __construct(getCustomProductTaxonomy $customTaxonomy)
    {

        if ( $this->is_woocommerce_activated() === false ) {
            add_action( 'admin_notices', [$this,'wc_add_notices'] );
            return;
        }

        $this->taxonomy = $customTaxonomy;
        add_action('init', [$this, 'load_export_import']);
        add_filter( 'woocommerce_get_sections_products' , [$this,'csv_add_settings_tab'] );
        add_filter( 'woocommerce_get_settings_products' , [$this,'csv_get_settings'] , 10, 2 );

    }

    function load_export_import()
    {

        if (is_admin()) {
            global $pagenow;
            $page = isset($_GET['page']) && (($_GET['page'] == 'product_exporter') || ($_GET['page'] == 'product_importer')) ? true : false;
                $this->custom_taxonomy = $this->taxonomy->get_custom_taxonomy();
                // Set tabs and Value on Woocommerce Seting Product Tab
                $this->add_taxonomy_to_csv_column();
        }
    }

    function csv_add_settings_tab( $settings_tab ){
        $settings_tab['add_c_taxonomies'] = __( 'Add Custom Taxonomies to Export Import' );
        return $settings_tab;
    }

    function csv_get_settings( $settings, $current_section ) {
        $custom_settings = array();

        $field[] = array(
            'name' => __( 'Add Custom Taxonomy to Export/Import' ),
            'type' => 'title',
            'desc' => __( 'Select Taxonomy to add on CSV' ),
            'id'   => 'free_shipping'
        );

        if (!empty($this->custom_taxonomy)) {
            foreach ($this->custom_taxonomy as $taxo) {
                $taxonoies_id = $taxo->name;
                $taxonomies_name = $taxo->labels->singular_name;

                $field[] = array(
                    'name' => __( $taxonomies_name ),
                    'type' => 'checkbox',
                    'desc' => __( 'Add '.$taxonomies_name),
                    'id'	=> 'ct_csv_'.$taxonoies_id
                );
            }
        }


        $field[] =  array(
            'name' => __( 'Activate' ),
            'type' => 'button',
            'desc' => __( 'Activate plugin'),
            'desc_tip' => true,
            'class' => 'button-secondary',
            'id'	=> 'activate',
        );

        $field[] = array( 'type' => 'sectionend', 'id' => 'free_shipping' );

        if( 'add_c_taxonomies' == $current_section ) {

            $custom_settings =  $field;

            return $custom_settings;
        } else {
            return $settings;
        }
    }

    function add_taxonomy_to_csv_column()
    {

        if (!empty($this->custom_taxonomy)) {
            foreach ($this->custom_taxonomy as $taxo) {
                $taxonoies_id = $taxo->name;
                $taxonomies_name = $taxo->labels->singular_name;
                $status = WC_Admin_Settings::get_option( 'ct_csv_' . $taxonoies_id );
                if($status=='yes'){
                    new export_custom_taxonmoy($taxonoies_id, $taxonomies_name);
                    new import_custom_taxonmoy($taxonoies_id, $taxonomies_name);
                }

            }
        }
    }

    function get_taxonomy(){
        return $this->custom_taxonomy;
    }

    function wc_add_notices(){
        $error = sprintf( __( '<b>Add custom taxonomy to woocommerce exporter-importer</b> Plugin requires %sWooCommerce%s to be installed & activated!' , 'ct-woocommerce-ei' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );
        $message = '<div class="error"><p>' . $error . '</p></div>';

        echo $message;
    }

    /**
     * Check if woocommerce is activated
     */
    public function is_woocommerce_activated() {
        $blog_plugins = get_option( 'active_plugins', array() );
        $site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option('active_sitewide_plugins' ) ) : array();

        if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
            return true;
        } else {
            return false;
        }
    }
}

$ei_custom_taxonomy = new getCustomProductTaxonomy();
$ei_export_import_init = new eiInit($ei_custom_taxonomy);

//$ei_export_import_init->add_taxonomy_to_csv_column();



