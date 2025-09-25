# Manual Testing Guide for REST API

## Prerequisites
1. WooCommerce Dangerous Goods plugin activated
2. At least one product marked as dangerous goods
3. At least one order containing a dangerous goods product
4. WooCommerce REST API credentials (Consumer Key & Secret)

## Quick Test Using Browser/Postman

### 1. Test Order Endpoint

**URL:** `https://your-site.com/wp-json/wc/v3/orders/{order_id}`

**Expected Response Fields:**
```json
{
  "has_dangerous_goods": true,
  "line_items": [
    {
      "is_dangerous_good": true,
      "dangerous_goods_meta": {}
    }
  ],
  "dangerous_goods_summary": {
    "has_dangerous_goods": true,
    "dangerous_goods_fee": {
      "name": "Dangerous Goods Fee",
      "amount": 20
    }
  }
}
```

### 2. Test Product Endpoint

**URL:** `https://your-site.com/wp-json/wc/v3/products/{product_id}`

**Expected Response Fields:**
```json
{
  "dangerous_goods": true
}
```

## Using cURL Commands

### Get Order with Dangerous Goods Info:
```bash
curl -X GET https://your-site.com/wp-json/wc/v3/orders/123 \
  -u ck_your_key:cs_your_secret \
  | jq '.dangerous_goods_summary'
```

### Get Product Dangerous Goods Status:
```bash
curl -X GET https://your-site.com/wp-json/wc/v3/products/456 \
  -u ck_your_key:cs_your_secret \
  | jq '.dangerous_goods'
```

### Update Product Dangerous Goods Status:
```bash
curl -X PUT https://your-site.com/wp-json/wc/v3/products/456 \
  -u ck_your_key:cs_your_secret \
  -H "Content-Type: application/json" \
  -d '{"dangerous_goods": true}'
```

## Verifying Fee Label Change

1. Create a test order with a dangerous goods product
2. Check the order via REST API:
   ```bash
   curl -X GET https://your-site.com/wp-json/wc/v3/orders/{order_id} \
     -u ck_your_key:cs_your_secret \
     | jq '.fees'
   ```
3. The fee should show as "Dangerous Goods Fee" not "Fees"

## Troubleshooting

### If REST API fields are missing:

1. **Check Plugin Activation:**
   ```php
   // In WordPress admin, check if plugin is active
   is_plugin_active('wc-dangerous-goods/wc-dangerous-goods.php')
   ```

2. **Verify REST API Class is Loaded:**
   - Check if file exists: `/includes/class-wc-dangerous-goods-rest-api.php`
   - Check if class is instantiated in main plugin file

3. **Clear Cache:**
   - Clear any object cache (Redis, Memcached)
   - Clear REST API cache if using caching plugins

4. **Check Error Logs:**
   - WordPress debug.log
   - PHP error logs
   - WooCommerce Status > Logs

## Using the Test Script

1. Edit `test-rest-api.php` with your credentials:
   ```php
   $site_url = 'https://your-site.com';
   $consumer_key = 'ck_your_key';
   $consumer_secret = 'cs_your_secret';
   $test_order_id = 123; // Real order ID
   $test_product_id = 456; // Real product ID
   ```

2. Run the test:
   ```bash
   php test-rest-api.php
   ```

3. Review the output for any missing fields or errors