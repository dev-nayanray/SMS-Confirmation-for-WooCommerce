=== SMS Confirmation for WooCommerce ===
Contributors: Nayan Ray  
Tags: woocommerce, sms, order confirmation, customer notification, sms alert  
Requires at least: 5.0  
Tested up to: 6.7  
Requires PHP: 7.4  
Stable tag: 1.0.0  
License: GPL-3.0+  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Send SMS notifications when WooCommerce orders are completed using SMS.net.bd, ensuring real-time updates and better customer engagement.

== Description ==

**SMS Confirmation for WooCommerce** is a lightweight plugin that integrates with WooCommerce to send SMS notifications to customers when their order is marked as completed. This helps businesses improve customer satisfaction by providing real-time order updates.

### Features:
- Automatically sends an SMS notification when an order is marked **completed**.
- Uses **SMS.net.bd API** for SMS delivery.
- Customizable API key settings via WordPress admin panel.
- Displays sent/not sent SMS statuses in the admin panel.
- Proper WooCommerce logging support for debugging.
- Supports Bangladeshi phone numbers, with automatic formatting.

**Note:** You need an active account and API key from [SMS.net.bd](https://www.sms.net.bd/) to use this plugin.

== Installation ==

1. Download the plugin ZIP file.
2. Go to **Plugins > Add New** in your WordPress admin panel.
3. Click **Upload Plugin**, select the downloaded ZIP file, and install it.
4. Activate the plugin.
5. Navigate to **Settings > SMS Confirmation** to configure your **SMS API key**.
6. Orders marked as **Completed** will now trigger SMS notifications.

== Frequently Asked Questions ==

= Does this plugin work with any SMS provider? =  
No, this plugin is specifically designed for **SMS.net.bd**.

= Can I customize the SMS message content? =  
Not yet, but we plan to add message customization in future updates.

= Will this plugin work outside Bangladesh? =  
The plugin is optimized for Bangladeshi phone numbers. Other countries may require manual modifications.

= Is logging available for debugging? =  
Yes, the plugin uses WooCommerce's built-in logging system.

== Screenshots ==

1. **Admin Settings Panel:** Enter API key to configure SMS service.
2. **Order List View:** See SMS status (Sent/Not Sent) for completed orders.

== Changelog ==

= 1.0.1 =
- Fixed text domain mismatch issue.
- Improved phone number formatting.
- Enhanced error handling with WooCommerce logging.

= 1.0.0 =
- Initial release.

== Upgrade Notice ==

= 1.0.1 =
Fixes critical text domain and logging issues. Update recommended.

== Support ==

For support, please contact [SMS.net.bd Support](https://www.sms.net.bd/support) or open a thread in the [WordPress.org support forum](https://wordpress.org/support/plugin/sms-confirmation-for-woocommerce).

== License ==

This plugin is released under the **GPL-2.0+** license.
