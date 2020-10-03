<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class getCustomProductTaxonomy {
    public function __construct(){}

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