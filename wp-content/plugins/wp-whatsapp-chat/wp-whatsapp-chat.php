<?php

/**
 * Plugin Name:       WhatsApp Chat
 * Description:       WhatsApp Chat allows your visitors to contact you or your team through WhatsApp chat with a single click.
 * Plugin URI:        https://quadlayers.com/portfolio/whatsapp-chat/
 * Version:           4.6.5
 * Author:            QuadLayers
 * Author URI:        https://quadlayers.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-whatsapp-chat
 * Domain Path:       /languages
 */
if (!defined('ABSPATH')) {
  die('-1');
}

define('QLWAPP_PLUGIN_NAME', 'WhatsApp Chat');
define('QLWAPP_PLUGIN_VERSION', '4.6.5');
define('QLWAPP_PLUGIN_FILE', __FILE__);
define('QLWAPP_PLUGIN_DIR', __DIR__ . DIRECTORY_SEPARATOR);
define('QLWAPP_PREFIX', 'qlwapp');
define('QLWAPP_DOMAIN', QLWAPP_PREFIX);
define('QLWAPP_WORDPRESS_URL', 'https://wordpress.org/plugins/wp-whatsapp-chat/');
define('QLWAPP_REVIEW_URL', 'https://wordpress.org/support/plugin/woocommerce-checkout-manager/reviews/?filter=5#new-post');
define('QLWAPP_DEMO_URL', 'https://quadlayers.com/portfolio/whatsapp-chat/?utm_source=qlwapp_admin');
define('QLWAPP_PURCHASE_URL', QLWAPP_DEMO_URL);
define('QLWAPP_SUPPORT_URL', 'https://quadlayers.com/account/support/?utm_source=qlwapp_admin');
define('QLWAPP_DOCUMENTATION_URL', 'https://quadlayers.com/documentation/whatsapp-chat/?utm_source=qlwapp_admin');
define('QLWAPP_GROUP_URL', 'https://www.facebook.com/groups/quadlayers');

if (!class_exists('QLWAPP')) {
  include_once( QLWAPP_PLUGIN_DIR . 'includes/qlwapp.php' );
}

register_activation_hook(QLWAPP_PLUGIN_FILE, array('QLWAPP', 'do_activation'));
