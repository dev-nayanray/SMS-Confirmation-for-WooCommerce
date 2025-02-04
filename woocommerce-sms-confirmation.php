<?php
/**
 * Plugin Name: WooCommerce SMS Confirmation
 * Description: Sends an SMS confirmation to the customer when an order is completed using SMS.net.bd.
 * Version: 1.0
 * Author: Nayan Ray
 */

if ( ! defined( 'ABSPATH' ) ) exit;  

 
add_action('admin_menu', 'wcsms_add_admin_menu');
add_action('admin_init', 'wcsms_settings_init');

function wcsms_add_admin_menu() {
    add_options_page('WooCommerce SMS Confirmation', 'SMS Confirmation', 'manage_options', 'wcsms_settings', 'wcsms_settings_page');
}

function wcsms_settings_init() {
    register_setting('wcsms_plugin', 'wcsms_settings');

    add_settings_section(
        'wcsms_plugin_section', 
        __('SMS API Settings', 'wcsms'), 
        null, 
        'wcsms_plugin'
    );

    add_settings_field(
        'wcsms_api_key', 
        __('API Key', 'wcsms'), 
        'wcsms_api_key_render', 
        'wcsms_plugin', 
        'wcsms_plugin_section'
    );
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

    
    echo '<h2>Completed Orders with SMS Status</h2>';
    $args = [
        'status' => 'completed',
        'limit' => -1
    ];
    $orders = wc_get_orders($args);

    echo '<table style="width:100%; border-collapse: collapse;">';
    echo '<tr><th style="border: 1px solid #ccc; padding: 8px;">Customer Name</th><th style="border: 1px solid #ccc; padding: 8px;">Phone Number</th><th style="border: 1px solid #ccc; padding: 8px;">Order ID</th><th style="border: 1px solid #ccc; padding: 8px;">SMS Status</th></tr>';

    foreach ($orders as $order) {
        $name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $phone = $order->get_billing_phone();
        $order_id = $order->get_id();
        $sms_status = get_post_meta($order_id, '_sms_sent_status', true) ? 'Sent' : 'Not Sent';

        echo '<tr>'; 
        echo '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($name) . '</td>';
        echo '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($phone) . '</td>';
        echo '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($order_id) . '</td>';
        echo '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($sms_status) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

 
add_action('woocommerce_order_status_completed', 'wcsms_send_welcome_message');

function wcsms_send_welcome_message($order_id) {
    $order = wc_get_order($order_id);
    $phone = $order->get_billing_phone();

  
    if (preg_match('/^(01|880)/', $phone) === 0) {
        $phone = '880' . ltrim($phone, '0');
    }

    $message = "Welcome " . $order->get_billing_first_name() . "! Thank you for your order #{$order_id}. We appreciate your business!";

    $options = get_option('wcsms_settings');
    $api_key = $options['wcsms_api_key'] ?? '7TohrUdBAvWAeEnb9OKIn9FA02WOQBI712b3DJG1'; // Default API key

    if (empty($api_key) || empty($phone)) {
        error_log('SMS sending failed: Missing API key or phone number.');
        update_post_meta($order_id, '_sms_sent_status', false);
        return;
    }

    $url = 'https://api.sms.net.bd/sendsms';
    $body = [
        'api_key'   => $api_key,
        'to'        => $phone,
        'msg'       => $message
    ];

    $response = wp_remote_post($url, [
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ],
        'body'    => http_build_query($body),
        'timeout' => 30
    ]);

    if (is_wp_error($response)) {
        error_log('SMS sending failed: ' . $response->get_error_message());
        update_post_meta($order_id, '_sms_sent_status', false);
    } else {
        $response_body = wp_remote_retrieve_body($response);
        if (strpos($response_body, 'success') !== false) {
            update_post_meta($order_id, '_sms_sent_status', true);
            error_log('Welcome SMS sent successfully to ' . $phone . '. Response: ' . $response_body);
        } else {
            update_post_meta($order_id, '_sms_sent_status', false);
            error_log('SMS API response indicates failure: ' . $response_body);
        }
    }
}
