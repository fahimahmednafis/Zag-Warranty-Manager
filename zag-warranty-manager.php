<?php
/*
Plugin Name: Zag Warranty Manager
Description: Assign different warranty periods to WooCommerce products and allow customers to view warranty status from their dashboard.
Version: 1.2.0
Author: Fahim Ahmed
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) exit;

/*---------------------------------------------
  1️⃣  ADD WARRANTY FIELD TO PRODUCT EDIT PAGE
---------------------------------------------*/
add_action('woocommerce_product_options_general_product_data', function() {
    echo '<div class="options_group">';
    woocommerce_wp_text_input([
        'id' => '_warranty_period',
        'label' => __('Warranty Period (days)', 'zag-warranty'),
        'desc_tip' => true,
        'description' => __('Enter warranty duration in days (e.g., 365 for 1 year).', 'zag-warranty'),
        'type' => 'number',
        'custom_attributes' => ['min' => '0']
    ]);
    echo '</div>';
});

// Save warranty field
add_action('woocommerce_admin_process_product_object', function($product) {
    if (isset($_POST['_warranty_period'])) {
        $product->update_meta_data('_warranty_period', sanitize_text_field($_POST['_warranty_period']));
    }
});

/*---------------------------------------------
  2️⃣  ADD WARRANTY INFO TO ORDER ITEMS (FIXED)
---------------------------------------------*/
add_action('woocommerce_checkout_create_order_line_item', function($item, $cart_item_key, $values, $order) {
    $product_id = $item->get_product_id();
    $warranty_days = get_post_meta($product_id, '_warranty_period', true);

    if (!empty($warranty_days) && intval($warranty_days) > 0) {
        // Add warranty period text
        $item->add_meta_data('Warranty Period', $warranty_days . ' days');

        // Use current time instead of order creation date (order not saved yet)
        $purchase_date = current_time('timestamp');
        $expiry_timestamp = strtotime("+{$warranty_days} days", $purchase_date);
        $expiry_date = date('Y-m-d', $expiry_timestamp);

        // Add expiry date meta
        $item->add_meta_data('Warranty Expires On', $expiry_date);
    }
}, 10, 4);


/*---------------------------------------------
  3️⃣  SHOW WARRANTY INFO ON ORDER DETAILS
---------------------------------------------*/
add_action('woocommerce_order_item_meta_end', function($item_id, $item, $order) {
    $expiry = $item->get_meta('Warranty Expires On');
    $period = $item->get_meta('Warranty Period');

    if ($expiry || $period) {
        $today = date('Y-m-d');
        $status = (strtotime($expiry) >= strtotime($today)) ? '🟢 Active' : '🔴 Expired';
        $days_left = max(0, round((strtotime($expiry) - strtotime($today)) / (60 * 60 * 24)));

        echo '<div class="zag-warranty-info" style="margin-top:8px;">';
        if ($period) echo '<strong>Warranty Period:</strong> ' . esc_html($period) . '<br>';
        if ($expiry) {
            echo '<strong>Expires On:</strong> ' . esc_html($expiry) . '<br>';
            echo '<strong>Status:</strong> ' . $status;
            if ($status === '🟢 Active') echo ' (' . $days_left . ' days left)';
        }
        echo '</div>';
    }
}, 10, 3);

/*---------------------------------------------
  4️⃣  ADD "WARRANTY STATUS" TAB IN MY ACCOUNT
---------------------------------------------*/
// Add new tab
add_filter('woocommerce_account_menu_items', function($items) {
    $items['warranty'] = __('Warranty Status', 'zag-warranty');
    return $items;
});

// Register endpoint
add_action('init', function() {
    add_rewrite_endpoint('warranty', EP_ROOT | EP_PAGES);
});

// Warranty tab content (fixed)
add_action('woocommerce_account_warranty_endpoint', function() {

    echo '<h2>' . __('My Warranty Status', 'zag-warranty') . '</h2>';

    $orders = wc_get_orders([
        'customer' => get_current_user_id(),
        'status'   => ['wc-completed', 'wc-processing'],
        'limit'    => -1,
    ]);

    if (empty($orders)) {
        echo '<p>No orders found.</p>';
        return;
    }

    foreach ($orders as $order) {
        echo '<div class="zag-warranty-order" style="background:#fafafa;padding:15px;margin-bottom:20px;border-radius:8px;">';
        echo '<h3>Order #' . esc_html($order->get_id()) . '</h3>';
        echo '<ul style="list-style:none;margin:0;padding:0;">';

        $has_items = false;

        foreach ($order->get_items() as $item_id => $item) {
            // Fetch warranty meta directly to ensure it's loaded
            $expiry = wc_get_order_item_meta($item_id, 'Warranty Expires On', true);
            $period = wc_get_order_item_meta($item_id, 'Warranty Period', true);

            if (!empty($expiry) || !empty($period)) {
                $has_items = true;
                $today = date('Y-m-d');
                $status = (strtotime($expiry) >= strtotime($today)) ? '🟢 Active' : '🔴 Expired';
                $days_left = max(0, round((strtotime($expiry) - strtotime($today)) / (60 * 60 * 24)));

                echo '<li style="margin-bottom:10px;">';
                echo '<strong>' . esc_html($item->get_name()) . '</strong><br>';
                if ($period) {
                    echo 'Warranty: ' . esc_html($period) . '<br>';
                }
                if ($expiry) {
                    echo 'Expires: ' . esc_html($expiry) . '<br>';
                    echo 'Status: ' . $status;
                    if ($status === '🟢 Active') {
                        echo ' (' . $days_left . ' days left)';
                    }
                }
                echo '</li>';
            }
        }

        if (!$has_items) {
            echo '<li>No warranty information found for this order.</li>';
        }

        echo '</ul>';
        echo '</div>';
    }
});


/*---------------------------------------------
  5️⃣  SHORTCODE [zag_warranty] FOR PRODUCT PAGE
---------------------------------------------*/
add_shortcode('zag_warranty', function() {
    global $product;

    if (empty($product) || !is_a($product, 'WC_Product')) {
        return ''; // only valid on single product pages
    }

    $warranty_days = get_post_meta($product->get_id(), '_warranty_period', true);
    if (empty($warranty_days)) return '';

    // Convert days into years/months/days
    $years  = floor($warranty_days / 365);
    $months = floor(($warranty_days % 365) / 30);
    $days   = $warranty_days % 30;

    $parts = [];
    if ($years > 0)  $parts[] = $years . ' year' . ($years > 1 ? 's' : '');
    if ($months > 0) $parts[] = $months . ' month' . ($months > 1 ? 's' : '');
    if ($days > 0 && $years == 0) $parts[] = $days . ' day' . ($days > 1 ? 's' : '');

    return esc_html(implode(' and ', $parts));
});


/*---------------------------------------------
  6️⃣  FLUSH REWRITE RULES ON ACTIVATE
---------------------------------------------*/
register_activation_hook(__FILE__, function() {
    add_rewrite_endpoint('warranty', EP_ROOT | EP_PAGES);
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
