<?php
/**
 * Plugin Name: Clients WP - Dropbox
 * Plugin URI:  https://www.gravity2pdf.com
 * Description: Deliver to Dropbox the converted PDF from Clients WP
 * Version:     1.0
 * Author:      gravity2pdf
 * Author URI:  https://github.com/raphcadiz
 * Text Domain: cl-wp-dropbox
 */

if (!class_exists('Clients_WP_Dropbox')):

    define( 'CWPD_PATH', dirname( __FILE__ ) );
    define( 'CWPD_PATH_INCLUDES', dirname( __FILE__ ) . '/includes' );
    define( 'CWPD_PATH_CLASS', dirname( __FILE__ ) . '/class' );
    define( 'CWPD_FOLDER', basename( CWPD_PATH ) );
    define( 'CWPD_URL', plugins_url() . '/' . CWPD_FOLDER );
    define( 'CWPD_URL_INCLUDES', CWPD_URL . '/includes' );
    define( 'CWPD_URL_CLASS', CWPD_URL . '/class' );
    define( 'CWPD_VERSION', 1.0 );

    register_activation_hook( __FILE__, 'clients_wp_dropbox_activation' );
    function clients_wp_dropbox_activation(){
        if ( ! class_exists('Clients_WP') ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die('Sorry, but this plugin requires the Restrict Content Pro and Clients WP to be installed and active.');
        }

    }

    add_action( 'admin_init', 'clients_wp_dropbox_activate' );
    function clients_wp_dropbox_activate(){
        if ( ! class_exists('Clients_WP') ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
        }
    }

    /*
     * include necessary files
     */
    require_once(CWPD_PATH.'/vendor/autoload.php');
    require_once(CWPD_PATH_CLASS . '/cwp-dropbox-main.class.php');
    require_once(CWPD_PATH_CLASS . '/cwp-dropbox-pages.class.php');

    /* Intitialize licensing
     * for this plugin.
     */
    if( class_exists( 'Clients_WP_License_Handler' ) ) {
        $cwp_dropbox = new Clients_WP_License_Handler( __FILE__, 'Clients WP - Dropbox', CWPD_VERSION, 'gravity2pdf', null, null, 7533);
    }

    add_action( 'plugins_loaded', array( 'Clients_WP_Dropbox', 'get_instance' ) );
endif;