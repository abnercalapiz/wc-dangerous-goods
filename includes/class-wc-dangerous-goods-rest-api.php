<?php
/**
 * REST API Extensions for WooCommerce Dangerous Goods
 *
 * @package WC_Dangerous_Goods
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WC_Dangerous_Goods_REST_API
 * 
 * Extends WooCommerce REST API to include dangerous goods information
 */
class WC_Dangerous_Goods_REST_API {

    /**
     * Constructor
     */
    public function __construct() {
        // Register REST API extensions
        add_action( 'rest_api_init', array( $this, 'register_rest_fields' ) );
        
        // Add dangerous goods data to order line items
        add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'add_dangerous_goods_to_order' ), 10, 3 );
    }

    /**
     * Register REST API fields
     */
    public function register_rest_fields() {
        // Add dangerous goods field to products in REST API
        register_rest_field( 'product', 'dangerous_goods', array(
            'get_callback'    => array( $this, 'get_product_dangerous_goods' ),
            'update_callback' => array( $this, 'update_product_dangerous_goods' ),
            'schema'          => array(
                'description' => 'Whether the product is classified as dangerous goods',
                'type'        => 'boolean',
                'context'     => array( 'view', 'edit' ),
            ),
        ) );

        // Add dangerous goods field to product variations
        register_rest_field( 'product_variation', 'dangerous_goods', array(
            'get_callback'    => array( $this, 'get_product_dangerous_goods' ),
            'update_callback' => array( $this, 'update_product_dangerous_goods' ),
            'schema'          => array(
                'description' => 'Whether the product variation is classified as dangerous goods',
                'type'        => 'boolean',
                'context'     => array( 'view', 'edit' ),
            ),
        ) );
    }

    /**
     * Get dangerous goods status for a product
     * 
     * @param array $object Product object
     * @return boolean
     */
    public function get_product_dangerous_goods( $object ) {
        $product = wc_get_product( $object['id'] );
        return WC_Dangerous_Goods::is_dangerous_good( $product );
    }

    /**
     * Update dangerous goods status for a product
     * 
     * @param mixed $value
     * @param WP_Post $object
     * @param string $field_name
     * @return void
     */
    public function update_product_dangerous_goods( $value, $object, $field_name ) {
        $product = wc_get_product( $object->ID );
        if ( $product ) {
            update_post_meta( $product->get_id(), '_dangerous_goods', $value ? 'yes' : 'no' );
        }
    }

    /**
     * Add dangerous goods information to order response
     * 
     * @param WP_REST_Response $response
     * @param WC_Order $order
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function add_dangerous_goods_to_order( $response, $order, $request ) {
        $data = $response->get_data();
        
        // Add dangerous goods flag to order level
        $data['has_dangerous_goods'] = false;
        $dangerous_goods_items = array();

        // Check each line item
        if ( ! empty( $data['line_items'] ) ) {
            foreach ( $data['line_items'] as &$item ) {
                $product_id = ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];
                $product = wc_get_product( $product_id );
                
                if ( $product && WC_Dangerous_Goods::is_dangerous_good( $product ) ) {
                    // Add dangerous goods flag to line item
                    $item['is_dangerous_good'] = true;
                    
                    // Add product meta information
                    $item['dangerous_goods_meta'] = array(
                        'classification' => get_post_meta( $product_id, '_dangerous_goods_classification', true ),
                        'un_number' => get_post_meta( $product_id, '_dangerous_goods_un_number', true ),
                        'shipping_name' => get_post_meta( $product_id, '_dangerous_goods_shipping_name', true ),
                    );
                    
                    $data['has_dangerous_goods'] = true;
                    $dangerous_goods_items[] = $item['product_id'];
                } else {
                    $item['is_dangerous_good'] = false;
                    $item['dangerous_goods_meta'] = null;
                }
            }
        }
        
        // Add order-level dangerous goods summary
        $data['dangerous_goods_summary'] = array(
            'has_dangerous_goods' => $data['has_dangerous_goods'],
            'dangerous_goods_fee' => $this->get_dangerous_goods_fee( $order ),
            'dangerous_goods_items' => $dangerous_goods_items,
            'requires_special_handling' => $data['has_dangerous_goods'],
        );
        
        $response->set_data( $data );
        return $response;
    }

    /**
     * Get dangerous goods fee from order
     * 
     * @param WC_Order $order
     * @return array|null
     */
    private function get_dangerous_goods_fee( $order ) {
        $fees = $order->get_fees();
        $settings = get_option( 'wc_dangerous_goods_settings', array() );
        $fee_label = isset( $settings['fee_label'] ) ? $settings['fee_label'] : 'Dangerous Goods Fee';
        
        foreach ( $fees as $fee ) {
            if ( $fee->get_name() === $fee_label ) {
                return array(
                    'name' => $fee->get_name(),
                    'amount' => $fee->get_amount(),
                    'tax' => $fee->get_total_tax(),
                    'total' => $fee->get_total(),
                );
            }
        }
        
        return null;
    }
}