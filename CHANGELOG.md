# Changelog

All notable changes to the WooCommerce Dangerous Goods plugin will be documented in this file.

## [1.0.2] - 2025-09-25

### Added
- Comprehensive REST API documentation in README.md
- REST API usage examples for order and product endpoints

### Changed
- Improved code formatting consistency
- Enhanced documentation with @since tags for better version tracking

### Fixed
- Code formatting issues in REST API class
- Removed development and utility files from production

### Maintenance
- Cleaned up repository structure
- Improved WordPress coding standards compliance
- Enhanced PHPDoc comments

## [1.0.1] - 2025-09-25

### Added
- REST API integration for WooCommerce orders and products
  - Order endpoints now include `has_dangerous_goods` and `dangerous_goods_summary` fields
  - Line items include `is_dangerous_good` and `dangerous_goods_meta` properties
  - Products/variations include `dangerous_goods` boolean field
- REST API documentation and testing tools
- Database update script for existing installations

### Changed
- Default fee label changed from "Dangerous Goods Handling Fee" to "Dangerous Goods Fee"
- Improved fee label handling throughout the plugin

### Fixed
- Fee label consistency across order displays

## [1.0.0] - 2025-09-25

### Added
- Initial release of WooCommerce Dangerous Goods plugin
- Product classification system for dangerous goods
- Automatic handling fee for orders containing dangerous goods
- Admin interface for managing dangerous goods settings
- Customer notifications at product, cart, and checkout stages
- IATA/IMDG classification support
- Compatibility with WooCommerce 3.0+ and WordPress 5.0+