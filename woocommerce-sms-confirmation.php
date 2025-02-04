<?php

/**
 * Plugin Name: SMS Confirmation for WooCommerce
 * Description: Sends an SMS confirmation to the customer when an order is completed using SMS.net.bd.
 * Version: 1.0.0
 * Author: Nayan Ray
 * Text Domain: sms-confirmation-for-woocommerce
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Add admin menu
add_action('admin_menu', 'wcsms_add_admin_menu');
add_action('admin_init', 'wcsms_settings_init');

function wcsms_add_admin_menu() {
    add_options_page(
        __('SMS Confirmation for WooCommerce', 'sms-confirmation-for-woocommerce'),
        __('SMS Confirmation', 'sms-confirmation-for-woocommerce'),
        'manage_options',
        'wcsms_settings',
        'wcsms_settings_page'
    );
}

function wcsms_settings_init() {
    register_setting('wcsms_plugin', 'wcsms_settings', 'wcsms_sanitize_settings');

    add_settings_section(
        'wcsms_plugin_section',
        __('SMS API Settings', 'sms-confirmation-for-woocommerce'),
        null,
        'wcsms_plugin'
    );

    add_settings_field(
        'wcsms_api_key',
        __('API Key', 'sms-confirmation-for-woocommerce'),
        'wcsms_api_key_render',
        'wcsms_plugin',
        'wcsms_plugin_section'
    );
}

function wcsms_sanitize_settings($input) {
    return array_map('sanitize_text_field', $input);
}

function wcsms_api_key_render() {
    $options = get_option('wcsms_settings');
    echo "<input type='text' name='wcsms_settings[wcsms_api_key]' value='" . esc_attr($options['wcsms_api_key'] ?? '') . "' style='width: 300px;'>";
}

function wcsms_settings_page() {
    echo '<form action="options.php" method="post">';
    settings_fields('wcsms_plugin');
    do_settings_sections('wcsms_plugin');
    submit_button();
    echo '</form>';

    echo '<h2>' . __('Completed Orders with SMS Status', 'sms-confirmation-for-woocommerce') . '</h2>';
    $orders = wc_get_orders(['status' => 'completed', 'limit' => -1]);

    echo '<table style="width:100%; border-collapse: collapse;">
            <tr>
                <th style="border: 1px solid #ccc; padding: 8px;">' . __('Customer Name', 'sms-confirmation-for-woocommerce') . '</th>
                <th style="border: 1px solid #ccc; padding: 8px;">' . __('Phone Number', 'sms-confirmation-for-woocommerce') . '</th>
                <th style="border: 1px solid #ccc; padding: 8px;">' . __('Order ID', 'sms-confirmation-for-woocommerce') . '</th>
                <th style="border: 1px solid #ccc; padding: 8px;">' . __('SMS Status', 'sms-confirmation-for-woocommerce') . '</th>
            </tr>';

    foreach ($orders as $order) {
        echo '<tr>
                <td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) . '</td>
                <td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($order->get_billing_phone()) . '</td>
                <td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($order->get_id()) . '</td>
                <td style="border: 1px solid #ccc; padding: 8px;">' . esc_html(get_post_meta($order->get_id(), '_sms_sent_status', true) ? __('Sent', 'sms-confirmation-for-woocommerce') : __('Not Sent', 'sms-confirmation-for-woocommerce')) . '</td>
              </tr>';
    }

    echo '</table>';
}

// Send SMS on completed order
add_action('woocommerce_order_status_completed', 'wcsms_send_welcome_message');

function wcsms_send_welcome_message($order_id) {
    $order = wc_get_order($order_id);
    $phone = $order->get_billing_phone();

    // Standardize phone format
    if (!preg_match('/^\+?880/', $phone)) {
        $phone = '880' . ltrim($phone, '0');
    }

    /* translators: 1: Customer Name, 2: Order ID */
    $message = sprintf(
        __('Welcome %1$s! Thank you for your order #%2$d. We appreciate your business!', 'sms-confirmation-for-woocommerce'),
        $order->get_billing_first_name(),
        $order_id
    );

    $options = get_option('wcsms_settings');
    $api_key = $options['wcsms_api_key'] ?? '';

    if (empty($api_key) || empty($phone)) {
        wc_get_logger()->error('SMS sending failed: Missing API key or phone number.');
        update_post_meta($order_id, '_sms_sent_status', false);
        return;
    }

    $url = 'https://api.sms.net.bd/sendsms';
    $body = [
        'api_key' => $api_key,
        'to'      => $phone,
        'msg'     => $message
    ];

    $response = wp_remote_post($url, [
        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        'body'    => http_build_query($body),
        'timeout' => 30
    ]);

    if (is_wp_error($response)) {
        wc_get_logger()->error('SMS sending failed: ' . $response->get_error_message());
        update_post_meta($order_id, '_sms_sent_status', false);
    } else {
        $response_body = wp_remote_retrieve_body($response);
        if (strpos($response_body, 'success') !== false) {
            update_post_meta($order_id, '_sms_sent_status', true);
            wc_get_logger()->info('Welcome SMS sent successfully to ' . $phone . '. Response: ' . $response_body);
        } else {
            update_post_meta($order_id, '_sms_sent_status', false);
            wc_get_logger()->error('SMS API response indicates failure: ' . $response_body);
        }
    }
}
