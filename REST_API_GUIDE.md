# WooCommerce Dangerous Goods - REST API Guide

## Overview

The WooCommerce Dangerous Goods plugin extends the WooCommerce REST API to include dangerous goods information in orders and products.

## Order Endpoints

### GET `/wp-json/wc/v3/orders/{id}`

When retrieving an order, the response now includes:

#### Order-Level Fields:
- `has_dangerous_goods` (boolean) - Whether the order contains any dangerous goods
- `dangerous_goods_summary` (object) - Summary of dangerous goods in the order
  - `has_dangerous_goods` (boolean)
  - `dangerous_goods_fee` (object|null) - Fee details if applied
    - `name` (string)
    - `amount` (float)
    - `tax` (float)
    - `total` (float)
  - `dangerous_goods_items` (array) - Product IDs of dangerous goods
  - `requires_special_handling` (boolean)

#### Line Item Fields:
Each line item now includes:
- `dangerous_goods` (boolean) - Whether this item is classified as dangerous
- `meta_data` (array) - Contains dangerous goods information when applicable:
  - `dangerous_goods` - "yes" if item is dangerous
  - `dangerous_goods_classification` - Classification code (if set)
  - `dangerous_goods_un_number` - UN number (if set)

### Example Order Response:
```json
{
  "id": 123,
  "status": "processing",
  "has_dangerous_goods": true,
  "line_items": [
    {
      "id": 1,
      "product_id": 456,
      "name": "Lithium Battery Pack",
      "quantity": 2,
      "dangerous_goods": true,
      "meta_data": [
        {
          "id": 0,
          "key": "dangerous_goods",
          "value": "yes",
          "display_key": "Dangerous Goods",
          "display_value": "Yes"
        },
        {
          "id": 0,
          "key": "dangerous_goods_classification",
          "value": "Class 9",
          "display_key": "Classification",
          "display_value": "Class 9"
        },
        {
          "id": 0,
          "key": "dangerous_goods_un_number",
          "value": "UN3480",
          "display_key": "UN Number",
          "display_value": "UN3480"
        }
      ]
    },
    {
      "id": 2,
      "product_id": 789,
      "name": "Regular Product",
      "quantity": 1,
      "dangerous_goods": false,
      "meta_data": []
    }
  ],
  "dangerous_goods_summary": {
    "has_dangerous_goods": true,
    "dangerous_goods_fee": {
      "name": "Dangerous Goods Handling Fee",
      "amount": 20,
      "tax": 2,
      "total": 22
    },
    "dangerous_goods_items": [456],
    "requires_special_handling": true
  }
}
```

## Product Endpoints

### GET/POST `/wp-json/wc/v3/products/{id}`

Products now include:
- `dangerous_goods` (boolean) - Whether the product is classified as dangerous

### Example Product Response:
```json
{
  "id": 456,
  "name": "Lithium Battery Pack",
  "type": "simple",
  "dangerous_goods": true,
  "price": "99.99"
}
```

### Updating Product Dangerous Goods Status:
```bash
curl -X PUT https://yoursite.com/wp-json/wc/v3/products/456 \
  -H "Authorization: Basic YOUR_AUTH" \
  -H "Content-Type: application/json" \
  -d '{"dangerous_goods": true}'
```

## Product Variations

### GET/POST `/wp-json/wc/v3/products/{id}/variations/{variation_id}`

Product variations also include:
- `dangerous_goods` (boolean) - Whether the variation is classified as dangerous

## Filtering Orders

To find orders containing dangerous goods programmatically:

```php
// Example PHP code
$orders = wc_get_orders(array(
    'limit' => -1,
    'return' => 'ids',
));

$dangerous_orders = array();
foreach ($orders as $order_id) {
    $response = wp_remote_get(
        "https://yoursite.com/wp-json/wc/v3/orders/{$order_id}",
        array('headers' => array('Authorization' => 'Basic YOUR_AUTH'))
    );
    
    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!empty($data['has_dangerous_goods'])) {
        $dangerous_orders[] = $order_id;
    }
}
```

## Authentication

Use standard WooCommerce REST API authentication:
- Basic Authentication
- OAuth 1.0a
- Application Passwords (WordPress 5.6+)

## Rate Limiting

Follow WooCommerce REST API rate limits. The dangerous goods fields add minimal overhead to existing endpoints.

## Webhooks

The dangerous goods data is included in standard WooCommerce webhooks for orders and products.

## Error Handling

The API maintains backward compatibility. If the dangerous goods plugin is deactivated:
- Additional fields will not be included in responses
- No errors will be thrown
- Standard WooCommerce data remains accessible