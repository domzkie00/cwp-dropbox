<?php if ( ! defined( 'ABSPATH' ) ) exit;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;

class Clients_WP_Dropbox{
    
    private static $instance;

    public static function get_instance()
    {
        if( null == self::$instance ) {
            self::$instance = new Clients_WP_Dropbox();
        }

        return self::$instance;
    }

    function __construct(){
        add_action('admin_init', array($this, 'register_integration'));
        add_action('admin_init', array($this, 'get_access_token'));
        add_action('admin_enqueue_scripts', array( $this, 'cwp_dropbox_add_admin_scripts' ));
        add_action('wp_enqueue_scripts', array($this, 'cwp_dropbox_add_wp_scripts'), 20, 1);
        add_filter('the_content', array($this, 'folder_content_table'), 6);
        add_action('wp_ajax_upload_file', array($this, 'upload_file_ajax'));
    }

    public function cwp_dropbox_add_admin_scripts() {
        wp_register_script('cwp_dropbox_admin_scripts', CWPD_URL . '/assets/js/cwp-dropbox-admin-scripts.js', '1.0', true);
        wp_enqueue_script('cwp_dropbox_admin_scripts');
    }

    public function cwp_dropbox_add_wp_scripts() {
        wp_register_script('cwp_dropbox_wp_scripts', CWPD_URL . '/assets/js/cwp-dropbox-scripts.js', '1.0', true);
        $cwpd_wp_script = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        );
        wp_localize_script('cwp_dropbox_wp_scripts', 'cwpd_wp_script', $cwpd_wp_script );
        wp_enqueue_script('cwp_dropbox_wp_scripts');
    }

    public function register_integration($array) {
        $dropbox = array(
            'dropbox' => array(
                'key'       => 'dropbox',
                'label'     => 'Dropbox'
            )
        );

        $clients_wp_integrations = get_option('clients_wp_integrations');

        if(is_array($clients_wp_integrations)) {
            $merge_integrations = array_merge($clients_wp_integrations, $dropbox);
            update_option('clients_wp_integrations', $merge_integrations);
        } else {
            update_option('clients_wp_integrations', $dropbox);
        }
        
    }

    public function get_access_token(){
        if (isset($_REQUEST['cwpintegration']) && $_REQUEST['cwpintegration'] == 'dropbox' ):
            $cwpdropbox_settings_options = get_option('cwpdropbox_settings_options');
            $app_key    = isset($cwpdropbox_settings_options['app_key']) ? $cwpdropbox_settings_options['app_key'] : '';
            $app_secret = isset($cwpdropbox_settings_options['app_secret']) ? $cwpdropbox_settings_options['app_secret'] : '';
            $app_token  = isset($cwpdropbox_settings_options['app_token']) ? $cwpdropbox_settings_options['app_token'] : '';

            if(!empty($app_key) && !empty($app_secret)) {
                $app = new DropboxApp($app_key, $app_secret);
                $dropbox = new Dropbox($app);
                $authHelper = $dropbox->getAuthHelper();
                $callbackUrl = admin_url( 'edit.php?post_type=bt_client&page=cwp-dropbox&cwpintegration=dropbox' );

                session_start();
                if (! isset($_GET['code'])) {
                    $authUrl = $authHelper->getAuthUrl($callbackUrl);
                    header('Location: '.$authUrl);
                } else {
                    $code = $_GET['code'];
                    $state = $_GET['state'];
                    $accessToken = $authHelper->getAccessToken($code, $state, $callbackUrl);

                    $cwpdropbox_settings_options['app_token'] = $accessToken->getToken();
                    update_option( 'cwpdropbox_settings_options', $cwpdropbox_settings_options );
                    header('Location: ' . admin_url( 'edit.php?post_type=bt_client&page=cwp-dropbox' ));
                }
                
            }
            
        endif;
    }

    public function folder_content_table() {
        global $pages;

        foreach($pages as $page) {
            if (strpos($page, '[cwp_') !== FALSE) {
                $args = array(
                    'meta_key' => '_clients_page_shortcode',
                    'meta_value' => $page,
                    'post_type' => 'bt_client_page',
                    'post_status' => 'any',
                    'posts_per_page' => -1
                );
                $posts = get_posts($args);

                foreach($posts as $post) {
                    echo $post->post_content;

                    $integration = get_post_meta($post->ID, '_clients_page_integration', true);
                    $root_folder = get_post_meta($post->ID, '_clients_page_integration_folder', true);

                    if (isset($integration) && isset($root_folder)) {
                        if((!empty($integration) && $integration == 'dropbox') && !empty($root_folder)) {
                            $cwpdropbox_settings_options = get_option('cwpdropbox_settings_options');
                            $app_key    = isset($cwpdropbox_settings_options['app_key']) ? $cwpdropbox_settings_options['app_key'] : '';
                            $app_secret = isset($cwpdropbox_settings_options['app_secret']) ? $cwpdropbox_settings_options['app_secret'] : '';
                            $app_token  = isset($cwpdropbox_settings_options['app_token']) ? $cwpdropbox_settings_options['app_token'] : '';

                            $linked_client_id = get_post_meta($post->ID, '_clients_page_client', true);
                            $client_email = get_post_meta($linked_client_id, '_bt_client_group_owner', true);

                            if(is_user_logged_in()) {
                                $current_user = wp_get_current_user();
                                if(!current_user_can('administrator')) {
                                    if($current_user->user_email != $client_email) {
                                        echo 'You are not allowed to see this contents.';
                                        return;
                                    }
                                } else {
                                    if($current_user->user_email != $client_email) {
                                        echo 'You are not allowed to see this contents.';
                                        return;
                                    }
                                }
                            } else {
                                echo 'You are not allowed to see this contents.';
                                return;
                            }

                            if(!empty($app_key) && !empty($app_secret)) {
                                /*$app = new DropboxApp($app_key, $app_secret, $app_token);
                                $dropbox = new Dropbox($app);
                                $listFolderContents = $dropbox->listFolder($root_folder); // error here
                                $items = $listFolderContents->getItems();
                                $all_items = $items->all();*/

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $app_token, 'Content-Type: application/json'));
                                curl_setopt($ch, CURLOPT_URL, 'https://api.dropboxapi.com/2/files/list_folder');
                                curl_setopt($ch, CURLOPT_POSTFIELDS, 
                                    json_encode(array(
                                        'path'=> $root_folder,
                                        'recursive'=> false,
                                        'include_media_info'=> false,
                                        'include_deleted'=> false,
                                        'include_has_explicit_shared_members'=> false,
                                        'include_mounted_folders'=> true,
                                    )
                                ));

                                $result = curl_exec($ch);
                                $result_array = json_decode(trim($result), TRUE);
                                curl_close($ch);

                                include_once(CWPD_PATH_INCLUDES . '/cwp-dropbox-table.php');
                            }
                        }
                    }
                }
            }
        }
    } 

    public function upload_file_ajax() {
        print_r($_POST['data']);
        die();
        /*$cwpdropbox_settings_options = get_option('cwpdropbox_settings_options');
        $app_key    = isset($cwpdropbox_settings_options['app_key']) ? $cwpdropbox_settings_options['app_key'] : '';
        $app_secret = isset($cwpdropbox_settings_options['app_secret']) ? $cwpdropbox_settings_options['app_secret'] : '';
        $app_token  = isset($cwpdropbox_settings_options['app_token']) ? $cwpdropbox_settings_options['app_token'] : '';
        var_dump($_POST['data']['file']);
        die();

        if(!empty($app_key) && !empty($app_secret)) {
            $app = new DropboxApp($app_key, $app_secret, $app_token);
            $dropbox = new Dropbox($app);
            $dropbox->upload($dropboxFile, $path, ['autorename' => true]);
        }*/
    }
}