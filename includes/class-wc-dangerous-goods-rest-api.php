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
     *
     * @since 1.0.1
     */
    public function __construct() {
        // Register REST API extensions
        add_action( 'rest_api_init', array( $this, 'register_rest_fields' ) );
        
        // Add dangerous goods data to order line items
        add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'add_dangerous_goods_to_order' ), 10, 3 );
        
        // Save dangerous goods meta when order is created
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_dangerous_goods_order_item_meta' ), 10, 4 );
    }

    /**
     * Register REST API fields
     *
     * @since 1.0.1
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
     * @since 1.0.1
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
     * @since 1.0.1
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
                    // Add dangerous goods attribute to line item
                    $item['dangerous_goods'] = true;
                    
                    // Add dangerous goods details as meta_data
                    if ( ! isset( $item['meta_data'] ) ) {
                        $item['meta_data'] = array();
                    }
                    
                    // Add dangerous goods meta_data entry
                    $item['meta_data'][] = array(
                        'id' => 0,
                        'key' => 'dangerous_goods',
                        'value' => 'yes',
                        'display_key' => 'Dangerous Goods',
                        'display_value' => 'Yes'
                    );
                    
                    // Add classification if available
                    $classification = get_post_meta( $product_id, '_dangerous_goods_classification', true );
                    if ( $classification ) {
                        $item['meta_data'][] = array(
                            'id' => 0,
                            'key' => 'dangerous_goods_classification',
                            'value' => $classification,
                            'display_key' => 'Classification',
                            'display_value' => $classification
                        );
                    }
                    
                    // Add UN number if available
                    $un_number = get_post_meta( $product_id, '_dangerous_goods_un_number', true );
                    if ( $un_number ) {
                        $item['meta_data'][] = array(
                            'id' => 0,
                            'key' => 'dangerous_goods_un_number',
                            'value' => $un_number,
                            'display_key' => 'UN Number',
                            'display_value' => $un_number
                        );
                    }
                    
                    $data['has_dangerous_goods'] = true;
                    $dangerous_goods_items[] = $item['product_id'];
                } else {
                    $item['dangerous_goods'] = false;
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
    
    /**
     * Save dangerous goods meta to order line item
     * 
     * @since 1.0.1
     * @param WC_Order_Item_Product $item
     * @param string $cart_item_key
     * @param array $values
     * @param WC_Order $order
     */
    public function save_dangerous_goods_order_item_meta( $item, $cart_item_key, $values, $order ) {
        if ( isset( $values['data'] ) ) {
            $product = $values['data'];
            
            if ( WC_Dangerous_Goods::is_dangerous_good( $product ) ) {
                // Add dangerous goods meta
                $item->add_meta_data( 'dangerous_goods', 'yes', true );
                $item->add_meta_data( '_dangerous_goods', 'yes', true ); // Hidden meta
                
                // Add classification if available
                $product_id = $product->get_id();
                $classification = get_post_meta( $product_id, '_dangerous_goods_classification', true );
                if ( $classification ) {
                    $item->add_meta_data( 'dangerous_goods_classification', $classification, true );
                }
                
                // Add UN number if available
                $un_number = get_post_meta( $product_id, '_dangerous_goods_un_number', true );
                if ( $un_number ) {
                    $item->add_meta_data( 'dangerous_goods_un_number', $un_number, true );
                }
            }
        }
    }
}