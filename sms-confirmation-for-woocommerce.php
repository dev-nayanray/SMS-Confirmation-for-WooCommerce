<?php
/**
 * Plugin Name: SMS Confirmation for WooCommerce
 * Plugin URI: https://dev-nayanray.github.io/
 * Description: A WooCommerce plugin that sends an SMS confirmation to customers when their order status is marked as completed. This plugin integrates with SMS.net.bd to ensure seamless SMS notifications, improving customer communication and order tracking.
 * Version: 1.0.0
 * Author: Nayan Ray
 * Author URI: https://dev-nayanray.github.io/
 * Text Domain: sms-confirmation-for-woocommerce
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * WC requires at least: 4.0
 * WC tested up to: 7.0
 */


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active.
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', 'wcsms_woocommerce_missing_notice');
    return;
}

/**
 * Display a notice if WooCommerce is not active.
 */
function wcsms_woocommerce_missing_notice() {
    echo '<div class="error"><p>';
   esc_html_e('SMS Confirmation for WooCommerce requires WooCommerce to be installed and active.', 'SMS Confirmation for WooCommerce');
    echo '</p></div>';
}

// Add admin menu.
add_action('admin_menu', 'wcsms_add_admin_menu');
add_action('admin_init', 'wcsms_settings_init');

/**
 * Add admin menu for plugin settings.
 */
function wcsms_add_admin_menu() {
    add_options_page(
        __('SMS Confirmation for WooCommerce', 'SMS Confirmation for WooCommerce'),
        __('SMS Confirmation', 'SMS Confirmation for WooCommerce'),
        'manage_options',
        'wcsms_settings',
        'wcsms_settings_page'
    );
}

/**
 * Initialize plugin settings.
 */
function wcsms_settings_init() {
    register_setting('wcsms_plugin', 'wcsms_settings', 'wcsms_sanitize_settings');

    add_settings_section(
        'wcsms_plugin_section',
        __('SMS API Settings', 'SMS Confirmation for WooCommerce'),
        null,
        'wcsms_plugin'
    );

    add_settings_field(
        'wcsms_api_key',
        __('API Key', 'SMS Confirmation for WooCommerce'),
        'wcsms_api_key_render',
        'wcsms_plugin',
        'wcsms_plugin_section'
    );
}

/**
 * Sanitize plugin settings.
 *
 * @param array $input Input data.
 * @return array Sanitized data.
 */
function wcsms_sanitize_settings($input) {
    return array_map('sanitize_text_field', $input);
}

/**
 * Render API key field.
 */
function wcsms_api_key_render() {
    $options = get_option('wcsms_settings');
    $api_key = isset($options['wcsms_api_key']) ? esc_attr($options['wcsms_api_key']) : '';
    echo '<input type="text" name="wcsms_settings[wcsms_api_key]" value="' . esc_attr($api_key) . '" style="width: 300px;">';
}

/**
 * Render settings page.
 */
function wcsms_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('SMS Confirmation Settings', 'SMS Confirmation for WooCommerce') . '</h1>';
    echo '<form action="options.php" method="post">';
    settings_fields('wcsms_plugin');
    do_settings_sections('wcsms_plugin');
    submit_button();
    echo '</form>';

    echo '<h2>' . esc_html__('Completed Orders with SMS Status', 'SMS Confirmation for WooCommerce') . '</h2>';
    $orders = wc_get_orders(['status' => 'completed', 'limit' => -1]);

    echo '<table style="width:100%; border-collapse: collapse;">
            <tr>
                <th style="border: 1px solid #ccc; padding: 8px;">' . esc_html__('Customer Name', 'SMS Confirmation for WooCommerce') . '</th>
                <th style="border: 1px solid #ccc; padding: 8px;">' . esc_html__('Phone Number', 'SMS Confirmation for WooCommerce') . '</th>
                <th style="border: 1px solid #ccc; padding: 8px;">' . esc_html__('Order ID', 'SMS Confirmation for WooCommerce') . '</th>
                <th style="border: 1px solid #ccc; padding: 8px;">' . esc_html__('SMS Status', 'SMS Confirmation for WooCommerce') . '</th>
            </tr>';

    foreach ($orders as $order) {
        $sms_status = get_post_meta($order->get_id(), '_sms_sent_status', true) ? __('Sent', 'SMS Confirmation for WooCommerce') : __('Not Sent', 'SMS Confirmation for WooCommerce');
        echo '<tr>
                <td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) . '</td>
                <td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($order->get_billing_phone()) . '</td>
                <td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($order->get_id()) . '</td>
                <td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($sms_status) . '</td>
              </tr>';
    }

    echo '</table>';
    echo '</div>';
}

// Send SMS on completed order.
add_action('woocommerce_order_status_completed', 'wcsms_send_welcome_message');

/**
 * Send SMS to customer when order is completed.
 *
 * @param int $order_id Order ID.
 */
function wcsms_send_welcome_message($order_id) {
    $order = wc_get_order($order_id);
    $phone = $order->get_billing_phone();

    // Validate and standardize phone number.
    if (!preg_match('/^\+?880/', $phone)) {
        $phone = '880' . ltrim($phone, '0');
    }

    if (empty($phone)) {
        wc_get_logger()->error('SMS sending failed: Phone number is empty.', ['source' => 'SMS Confirmation for WooCommerce']);
        update_post_meta($order_id, '_sms_sent_status', false);
        return;
    }

    // Prepare message.
    /* translators: 1: Customer's first name, 2: Order ID */
    $message = sprintf(
       // Translators: %1$s is the customer's name, %2$d is the order number.
__('Welcome %1$s! Thank you for your order #%2$d. We appreciate your business!', 'SMS Confirmation for WooCommerce'),
        $order->get_billing_first_name(),
        $order_id
    );

    $options = get_option('wcsms_settings');
    $api_key = isset($options['wcsms_api_key']) ? sanitize_text_field($options['wcsms_api_key']) : '';

    if (empty($api_key)) {
        wc_get_logger()->error('SMS sending failed: API key is missing.', ['source' => 'SMS Confirmation for WooCommerce']);
        update_post_meta($order_id, '_sms_sent_status', false);
        return;
    }

    // Send SMS via API.
    $url  = 'https://api.sms.net.bd/sendsms';
    $body = [
        'api_key' => $api_key,
        'to'      => $phone,
        'msg'     => $message,
    ];

    $response = wp_remote_post($url, [
        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        'body'    => http_build_query($body),
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        wc_get_logger()->error('SMS sending failed: ' . $response->get_error_message(), ['source' => 'SMS Confirmation for WooCommerce']);
        update_post_meta($order_id, '_sms_sent_status', false);
    } else {
        $response_body = wp_remote_retrieve_body($response);
        if (strpos($response_body, 'success') !== false) {
            update_post_meta($order_id, '_sms_sent_status', true);
            wc_get_logger()->info('Welcome SMS sent successfully to ' . $phone . '. Response: ' . $response_body, ['source' => 'sms-confirmation-for-woocommerce']);
        } else {
            update_post_meta($order_id, '_sms_sent_status', false);
            wc_get_logger()->error('SMS API response indicates failure: ' . $response_body, ['source' => 'SMS Confirmation for WooCommerce']);
        }
    }
}

// Cleanup on plugin uninstall.
register_uninstall_hook(__FILE__, 'wcsms_uninstall');

/**
 * Cleanup plugin data on uninstall.
 */
function wcsms_uninstall() {
    delete_option('wcsms_settings');
}
