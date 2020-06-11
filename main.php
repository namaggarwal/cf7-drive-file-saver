<?php

/**
 * Plugin Name: CF7 Drive FileSaver
 * Plugin URI: https://wordpress.org/plugins/multifile-upload-field-for-contact-form-7/
 * Description: Saves uploaded file to the drive
 * Version: 1.0.0
 * Author: Naman Aggarwal
 * Author URI: https://naman.io
 * License: GPL2
 */

define('FS_CF7_CONNECTOR_VERSION', '1.0');
define('FS_CF7_CONNECTOR_ROOT', dirname(__FILE__));
define('FS_CF7_CONNECTOR_PATH', plugin_dir_path(__FILE__));
define('FS_CF7_CONNECTOR_URL', plugins_url('/', __FILE__));
include_once(FS_CF7_CONNECTOR_ROOT . '/app.php');
include_once(FS_CF7_CONNECTOR_ROOT . '/connector.php');

class CF7_File_Saver
{
  private $googleService;

  function __construct()
  {
    //run on activation of plugin
    register_activation_hook(__FILE__, array($this, 'cf7_drive_file_saver_activate'));
    //run on uninstall
    register_uninstall_hook(__FILE__, array('CF7_File_Saver', 'cf7_drive_file_saver_uninstall'));
    //validate if contact form 7 plugin exist
    add_action('admin_init', array($this, 'validate_parent_plugin_exists'));
    //register admin panel
    add_action('admin_menu', array($this, 'register_cf7_dfs_menu_pages'));

    // load the js and css files
    add_action('init', array($this, 'load_css_and_js_files'));

    add_action('wpcf7_mail_sent', array($this, 'save_to_drive'));
    add_filter('wpcf7_posted_data', array($this, 'create_folder'));
  }

  /**
   * Validate parent Plugin Contact Form 7 exist and activated
   */
  public function validate_parent_plugin_exists()
  {
    $plugin = plugin_basename(__FILE__);
    if ((!is_plugin_active('contact-form-7/wp-contact-form-7.php')) || (!file_exists(plugin_dir_path(__DIR__) . 'contact-form-7/wp-contact-form-7.php'))) {
      // add_action( 'admin_notices', array( $this, 'contact_form_7_missing_notice' ) );
      // add_action( 'network_admin_notices', array( $this, 'contact_form_7_missing_notice' ) );
      deactivate_plugins($plugin);
      if (isset($_GET['activate'])) {
        // Do not sanitize it because we are destroying the variables from URL
        unset($_GET['activate']);
      }
    }
  }

  /**
   * If Contact Form 7 plugin is not installed or activated then throw the error
   *
   */
  public function contact_form_7_missing_notice()
  {
    // $plugin_error = Gs_Connector_Utility::instance()->admin_notice(array(
    //   'type' => 'error',
    //   'message' => __('Google Sheet Connector Add-on requires Contact Form 7 plugin to be installed and activated.', 'gsconnector')
    // ));
    // echo $plugin_error;
  }

  /**
   * Do things on plugin activation
   * @since 1.0
   */
  public function cf7_drive_file_saver_activate($network_wide)
  {
    global $wpdb;
    if (function_exists('is_multisite') && is_multisite()) {
      // check if it is a network activation - if so, run the activation function for each blog id
      if ($network_wide) {
        // Get all blog ids
        $blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");
        foreach ($blogids as $blog_id) {
          switch_to_blog($blog_id);
          $this->create_site_data();
          restore_current_blog();
        }
        return;
      }
    }

    // for non-network sites only
    $this->create_site_data();
  }

  /**
   * Called on activation.
   * Creates the options and DB (required by per site)
   */
  private function create_site_data()
  {

    if (!get_option('cf7_dfs_client_id')) {
      update_option('cf7_dfs_client_id', '');
    }
    if (!get_option('cf7_dfs_client_secret')) {
      update_option('cf7_dfs_client_secret', '');
    }
    if (!get_option('cf7_dfs_token')) {
      update_option('cf7_dfs_token', '');
    }
    if (!get_option('cf7_dfs_folder_id')) {
      update_option('cf7_dfs_folder_id', '');
    }

    if (!get_option('cf7_dfs_name_column')) {
      update_option('cf7_dfs_name_column', '');
    }

    if (!get_option('cf7_dfs_template_id')) {
      update_option('cf7_dfs_template_id', '');
    }

    if (!get_option('cf7_dfs_mode')) {
      update_option('cf7_dfs_mode', '1');
    }
  }

  /**
   *  Runs on plugin uninstall.
   *  a static class method or function can be used in an uninstall hook
   *
   *  @since 1.5
   */
  public static function cf7_drive_file_saver_uninstall()
  {
    global $wpdb;

    if (!is_plugin_active('cf7-drive-file-saver/main.php') || (!file_exists(plugin_dir_path(__DIR__) . 'cf7-drive-file-saver/main.php'))) {
      return;
    }

    if (function_exists('is_multisite') && is_multisite()) {
      //Get all blog ids; foreach of them call the uninstall procedure
      $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");

      //Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
      foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        CF7_File_Saver::delete_site_data();
        restore_current_blog();
      }
      return;
    }
    CF7_File_Saver::delete_site_data();
  }

  private static function delete_site_data()
  {
    delete_option('cf7_dfs_client_id');
    delete_option('cf7_dfs_client_secret');
    delete_option('cf7_dfs_token');
    delete_option('cf7_dfs_folder_id');
    delete_option('cf7_dfs_name_column');
    delete_option('cf7_dfs_template_id');
    delete_option('cf7_dfs_mode');
  }


  /**
   * Create/Register menu items for the plugin.
   */
  public function register_cf7_dfs_menu_pages()
  {
    if (current_user_can('wpcf7_edit_contact_forms')) {
      $current_role = Gs_Connector_Utility::instance()->get_current_user_role();
      add_submenu_page('wpcf7', __('Drive File Saver'), __('Drive File Saver'), $current_role, 'wpcf7-dfs-admin-config', array($this, 'plugin_configuration_page'));
    }
  }

  public function plugin_configuration_page()
  {
    include(FS_CF7_CONNECTOR_PATH . "pages/plugin-settings.php");
  }

  public function load_css_and_js_files()
  {
    add_action('admin_print_scripts', array($this, 'add_js_files'));
  }

  public function add_js_files()
  {
    if (is_admin() && isset($_GET['page']) && $_GET['page'] == 'wpcf7-dfs-admin-config') {
      wp_enqueue_script('fs-cf7-admin-js', FS_CF7_CONNECTOR_URL . 'assets/js/settings.js', [], FS_CF7_CONNECTOR_VERSION, true);
    }
  }

  private function getGoogleService()
  {
    if ($this->googleService != null) {
      return $this->googleService;
    }

    $clientID = get_option('cf7_dfs_client_id');
    if ($clientID == '') {
      return null;
    }

    $clientSecret = get_option('cf7_dfs_client_secret');
    $googleClient = new GoogleClient($clientID, $clientSecret);
    $token = get_option('cf7_dfs_token');
    if ($token == '') {
      return null;
    }
    $token = json_decode($token, true);
    try {
      $authGoogleClient = $googleClient->getAuthenticatedGoogleClient($token);
      $this->googleService = new GoogleService($authGoogleClient);
    } catch (Exception $e) {
      error_log(print_r($e->getTraceAsString(), true));
      return null;
    }

    return $this->googleService;
  }

  private function create_drive_folder($googleService, $data)
  {
    $name_column = get_option('cf7_dfs_name_column');
    $name = $data[$name_column];
    $folderID = get_option('cf7_dfs_folder_id');
    return $googleService->createFolder($name, $folderID);
  }

  public function create_folder($data)
  {
    $mode = get_option('cf7_dfs_mode');
    if ($mode != '1') {
      return;
    }
    $googleService = $this->getGoogleService();
    if ($googleService == null) {
      return;
    }
    try {
      $folder = $this->create_drive_folder($googleService, $data);
      if ($folder != null) {
        $data['created_folder'] = $folder->getId();
      }
    } catch (Exception $e) {
      error_log(print_r($e->getTraceAsString(), true));
    }

    return $data;
  }

  public function save_to_drive($form)
  {

    error_log("save_to_drive:start");

    $templateID = get_option('cf7_dfs_template_id');
    $nameCol = get_option('cf7_dfs_name_column');
    $mode = get_option('cf7_dfs_mode');

    $submission = WPCF7_Submission::get_instance();
    if ($submission == null) {
      return;
    }
    $posted_data = $submission->get_posted_data();
    $uploaded_files = $submission->uploaded_files();

    $folderID = '';
    $name = $posted_data[$nameCol];
    try {
      if (isset($posted_data['created_folder'])) {
        $folderID = $posted_data['created_folder'];
      } else {
        // create folder
        $service = $this->getGoogleService();
        $folder = $this->create_drive_folder($service, $posted_data);
        $folderID =  $folder->getId();
      }
      $this->handle_posted_data($name, $folderID, $templateID,$mode, $posted_data, $uploaded_files);
    } catch (Exception $e) {
      error_log(print_r($e->getTraceAsString(), true));
    }

    error_log("save_to_drive:end");
  }

  private function handle_posted_data($name, $folderID, $templateID,$mode, $posted_data, $uploaded_files)
  {

    try {
      if (is_array($uploaded_files) && count($uploaded_files) > 0) {
        $filesContent = array();
        foreach ($uploaded_files as $uploaded_file) {
          $fileName = basename($uploaded_file);
          $filesContent[$fileName] = file_get_contents($uploaded_file);
        }
        $this->uploadFilesToDrive($folderID, $filesContent);
      }
    } catch (Exception $e) {
      error_log(print_r($e->getTraceAsString(), true));
    }

    if($mode == '1'){
      return;
    }

    $data = array();
    foreach ($posted_data as $key => $value) {
      // exclude the default wpcf7 fields in object
      if (strpos($key, '_wpcf7') !== false || strpos($key, '_wpnonce') !== false) {
        // do nothing
      } else {
        // handle strings and array elements
        if (is_array($value)) {
          $data[$key] = implode(', ', $value);
        } else {
          $data[$key] = stripcslashes($value);
        }
      }
    }
    $this->processRow($name, $folderID, $templateID, $data);
  }

  private function uploadFilesToDrive($folderID, $uploadedFiles)
  {
    $service = $this->getGoogleService();
    if ($service == null) {
      return;
    }
    foreach ($uploadedFiles as $fileName => $uploadedFile) {
      $service->uploadFile($fileName, $uploadedFile, $folderID);
    }
  }

  private function processRow($name, $folderID, $templateID, $data)
  {
    $service = $this->getGoogleService();
    if ($service == null) {
      return;
    }
    try {
      $copiedFile = $service->copyDocument($templateID);
      $service->updateDocument($copiedFile->getId(), $data);
      $pdfContent = $service->getFileContentAsPDF($copiedFile->getId());
      $service->uploadFileAsPDF($name, $pdfContent, $folderID);
      $service->deleteFile($copiedFile->getId());
    } catch (Exception $e) {
      error_log($e->getTraceAsString());
    }
  }
}

$client = new CF7_File_Saver();
