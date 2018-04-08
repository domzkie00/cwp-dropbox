<?php
class Clients_WP_Dropbox_Pages {

    public function __construct() {
        add_action('admin_init', array( $this, 'settings_options_init' ));
        add_action('admin_menu', array( $this, 'admin_menus'), 12 );
    }

    public function settings_options_init() {
        register_setting( 'cwpdropbox_settings_options', 'cwpdropbox_settings_options', '' );
    }

    public function admin_menus() {
        add_submenu_page ( 'edit.php?post_type=bt_client' , 'Dropbox' , 'Dropbox' , 'manage_options' , 'cwp-dropbox' , array( $this , 'cwp_dropbox' ));
    }

    public function cwp_dropbox() {
        include_once(CWPD_PATH_INCLUDES.'/cwp_dropbox.php');
    }
}

new Clients_WP_Dropbox_Pages();