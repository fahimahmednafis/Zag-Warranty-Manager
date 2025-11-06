=== Zag Warranty Manager ===
Contributors: zagleai
Author: Zagle AI
Tags: warranty, woocommerce, product warranty, order warranty, customer dashboard
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily assign unique warranty periods to WooCommerce products and let customers view their warranty status directly from their My Account dashboard.

== Description ==

**Zag Warranty Manager** lets you assign different warranty durations to each WooCommerce product and automatically calculate and track warranty expiry dates per order.

Customers can easily view all their warranties—active or expired—directly from their **My Account → Warranty Status** page.

### 🔧 Features

- Add a custom warranty period to each product (in days)
- Automatically calculate warranty expiry upon purchase
- Display warranty info and status (Active / Expired) in order details
- Add a “Warranty Status” tab to the WooCommerce My Account dashboard
- Lightweight, simple, and admin-friendly — no bloated UI

### 💡 Example Use Cases

- Electronics, appliances, or gadgets with specific warranty terms  
- Furniture or accessories with different warranty lengths  
- Any WooCommerce store needing warranty management per product

== Installation ==

1. Upload the `zag-warranty-manager` folder to the `/wp-content/plugins/` directory.  
2. Activate the plugin through the **Plugins** menu in WordPress.  
3. Edit any WooCommerce product → go to the **General** tab → set the **Warranty Period (days)**.  
4. The customer’s order will now display warranty details automatically, and they can check warranty status from **My Account → Warranty Status**.

== Frequently Asked Questions ==

= Can I set the warranty in months or years? =
Currently, warranty duration is entered in days (e.g., 365 = 1 year). Future versions may add month/year inputs.

= Does it work for variable products? =
Yes, the warranty applies per product. Per-variation warranty settings will be supported in an upcoming update.

= Can I show warranty info on product pages? =
Yes. Use `get_post_meta( $product_id, '_warranty_period', true );` inside your product template to display it.

== Screenshots ==

1. Product edit screen with the “Warranty Period (days)” field.  
2. Customer dashboard showing the “Warranty Status” tab with orders and expiry.  
3. Order details view showing product warranty expiry and status.

== Changelog ==

= 1.0.0 =
* Initial release.
* Added per-product warranty period field.
* Displayed warranty expiry in order details.
* Added “Warranty Status” customer dashboard tab.

== Upgrade Notice ==

= 1.0.0 =
First release of Zag Warranty Manager — simple, stable, and WooCommerce-ready warranty tracking.

== Credits ==

Developed by **Zagle AI**  
Website: [https://zagle.ai](https://zagle.ai)

== License ==

This plugin is open-source and distributed under the **GPLv2 or later** license.
