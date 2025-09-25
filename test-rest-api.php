<?php
/**
 * REST API Test Script for WooCommerce Dangerous Goods
 * 
 * Usage: Update the configuration below and run this script
 */

// Configuration - UPDATE THESE VALUES
$site_url = 'https://your-site.com';
$consumer_key = 'ck_your_consumer_key';
$consumer_secret = 'cs_your_consumer_secret';

// Test configuration
$test_order_id = 123; // Replace with a real order ID
$test_product_id = 456; // Replace with a real product ID

echo "WooCommerce Dangerous Goods - REST API Test\n";
echo "===========================================\n\n";

// Function to make REST API request
function make_api_request($endpoint, $consumer_key, $consumer_secret) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $consumer_key . ':' . $consumer_secret);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return array(
        'code' => $http_code,
        'response' => json_decode($response, true)
    );
}

// Test 1: Check Order Endpoint
echo "Test 1: Checking Order Endpoint\n";
echo "-------------------------------\n";
$order_endpoint = $site_url . '/wp-json/wc/v3/orders/' . $test_order_id;
$order_result = make_api_request($order_endpoint, $consumer_key, $consumer_secret);

if ($order_result['code'] === 200) {
    echo "✓ Order endpoint accessible\n";
    
    // Check for dangerous goods fields
    $order_data = $order_result['response'];
    
    // Check order-level fields
    if (isset($order_data['has_dangerous_goods'])) {
        echo "✓ 'has_dangerous_goods' field present: " . ($order_data['has_dangerous_goods'] ? 'true' : 'false') . "\n";
    } else {
        echo "✗ 'has_dangerous_goods' field missing\n";
    }
    
    if (isset($order_data['dangerous_goods_summary'])) {
        echo "✓ 'dangerous_goods_summary' field present\n";
        
        // Check summary contents
        $summary = $order_data['dangerous_goods_summary'];
        echo "  - has_dangerous_goods: " . ($summary['has_dangerous_goods'] ? 'true' : 'false') . "\n";
        echo "  - dangerous_goods_items: " . implode(', ', $summary['dangerous_goods_items'] ?? []) . "\n";
        
        if (isset($summary['dangerous_goods_fee'])) {
            echo "  - Fee: " . $summary['dangerous_goods_fee']['name'] . " ($" . $summary['dangerous_goods_fee']['amount'] . ")\n";
        }
    } else {
        echo "✗ 'dangerous_goods_summary' field missing\n";
    }
    
    // Check line items
    if (!empty($order_data['line_items'])) {
        echo "\nLine Items Check:\n";
        foreach ($order_data['line_items'] as $index => $item) {
            echo "  Item #" . ($index + 1) . " (Product ID: " . $item['product_id'] . "):\n";
            
            if (isset($item['is_dangerous_good'])) {
                echo "    ✓ 'is_dangerous_good': " . ($item['is_dangerous_good'] ? 'true' : 'false') . "\n";
            } else {
                echo "    ✗ 'is_dangerous_good' field missing\n";
            }
            
            if (isset($item['dangerous_goods_meta'])) {
                echo "    ✓ 'dangerous_goods_meta' field present\n";
                if ($item['dangerous_goods_meta']) {
                    foreach ($item['dangerous_goods_meta'] as $key => $value) {
                        echo "      - $key: $value\n";
                    }
                }
            } else {
                echo "    ✗ 'dangerous_goods_meta' field missing\n";
            }
        }
    }
} else {
    echo "✗ Failed to access order endpoint (HTTP " . $order_result['code'] . ")\n";
    echo "Error: " . json_encode($order_result['response']) . "\n";
}

// Test 2: Check Product Endpoint
echo "\n\nTest 2: Checking Product Endpoint\n";
echo "---------------------------------\n";
$product_endpoint = $site_url . '/wp-json/wc/v3/products/' . $test_product_id;
$product_result = make_api_request($product_endpoint, $consumer_key, $consumer_secret);

if ($product_result['code'] === 200) {
    echo "✓ Product endpoint accessible\n";
    
    $product_data = $product_result['response'];
    
    if (isset($product_data['dangerous_goods'])) {
        echo "✓ 'dangerous_goods' field present: " . ($product_data['dangerous_goods'] ? 'true' : 'false') . "\n";
    } else {
        echo "✗ 'dangerous_goods' field missing\n";
    }
} else {
    echo "✗ Failed to access product endpoint (HTTP " . $product_result['code'] . ")\n";
    echo "Error: " . json_encode($product_result['response']) . "\n";
}

echo "\n\nTest Summary\n";
echo "============\n";
echo "Note: If fields are missing, ensure:\n";
echo "1. The WooCommerce Dangerous Goods plugin is activated\n";
echo "2. The REST API extensions are properly loaded\n";
echo "3. You have proper authentication credentials\n";
echo "4. The order/product IDs exist in your store\n";

// Provide example of expected response structure
echo "\n\nExpected Order Response Structure:\n";
echo "---------------------------------\n";
echo '{
  "id": 123,
  "has_dangerous_goods": true,
  "line_items": [{
    "product_id": 456,
    "is_dangerous_good": true,
    "dangerous_goods_meta": {
      "classification": "Class 9",
      "un_number": "UN3480",
      "shipping_name": "Lithium batteries"
    }
  }],
  "dangerous_goods_summary": {
    "has_dangerous_goods": true,
    "dangerous_goods_fee": {
      "name": "Dangerous Goods Fee",
      "amount": 20
    },
    "dangerous_goods_items": [456]
  }
}';

echo "\n\nExpected Product Response Structure:\n";
echo "-----------------------------------\n";
echo '{
  "id": 456,
  "name": "Product Name",
  "dangerous_goods": true
}';