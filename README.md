# WooCommerce-SMS-Confirmation
WooCommerce SMS Confirmation
=== WooCommerce SMS Confirmation ===
Contributors: NayanRay
Tags: WooCommerce, SMS, Order Confirmation, SMS Notification, WooCommerce SMS
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.0
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send SMS confirmations to customers when their WooCommerce order is completed using SMS.net.bd API.

== Description ==

WooCommerce SMS Confirmation is a lightweight and easy-to-use plugin that sends an SMS notification to customers when their order status changes to "Completed." This plugin integrates with SMS.net.bd API to deliver real-time order updates.

**Features:**

- Automatically send SMS notifications when an order is completed.
- Customizable SMS message template.
- Manage API settings directly from the WordPress dashboard.
- View SMS sending status for completed orders.
- Simple and effective integration with WooCommerce.

== Installation ==

1. Upload the `woocommerce-sms-confirmation` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Settings > SMS Confirmation** to configure your API key.
4. Ensure you have a valid SMS.net.bd API key to send SMS notifications.

== Frequently Asked Questions ==

= How do I configure the API settings? =
Go to **Settings > SMS Confirmation** in your WordPress admin panel and enter your SMS.net.bd API key.

= Does this plugin work with other SMS providers? =
Currently, this plugin supports only SMS.net.bd API. Future updates may include support for additional providers.

= Will this plugin work with custom WooCommerce order statuses? =
No, the plugin is designed to trigger SMS notifications only when an order status is marked as "Completed."

== Screenshots ==

1. Plugin settings page where you can enter your API key.
2. List of completed orders with SMS status.

== Changelog ==

= 1.0 =
* Initial release.
* Sends SMS notifications for completed WooCommerce orders.
* Admin settings page for API configuration.

== Upgrade Notice ==

= 1.0 =
Initial release with basic SMS notification functionality.

