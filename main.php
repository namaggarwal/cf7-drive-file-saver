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

define('FS_CF7_CONNECTOR_ROOT', dirname(__FILE__));
include(FS_CF7_CONNECTOR_ROOT . '/app.php');



class CF7_File_Saver
{

  function __construct()
  {

    //run on activation of plugin
    register_activation_hook(__FILE__, array($this, 'cf7_drive_file_saver_activate'));

    //run on uninstall
    register_uninstall_hook(__FILE__, array('CF7_File_Saver', 'cf7_drive_file_saver_uninstall'));

    // add_action('wpcf7_mail_sent', array($this, 'save_to_drive'));
    // add_filter('wpcf7_posted_data', array($this, 'create_folder'));
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
    if (!get_option('cf7_dfs_access_code')) {
      update_option('cf7_dfs_access_code', '');
    }
    if (!get_option('cf7_dfs_token')) {
      update_option('cf7_dfs_token', '');
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
    delete_option('cf7_dfs_access_code');
    delete_option('cf7_dfs_token');
  }


  private function create_folder($data)
  {
    $data['created_folder'] = 'sdsdjsdjhsd';
    return $data;
  }

  private function save_to_drive($form)
  {
    error_log("save_to_drive:start");

    $submission = WPCF7_Submission::get_instance();
    if ($submission) {
      $posted_data = $submission->get_posted_data();
      $uploaded_files = $submission->uploaded_files();
      // handle_posted_data($posted_data, $uploaded_files);
    }
    error_log("save_to_drive:end");
  }

  private function handle_posted_data($posted_data, $uploaded_files)
  {
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
    $name = $data['your-name'];
    $folderID = '';
    $templateID = '';
    $filesContent = array();
    if (is_array($uploaded_files) && count($uploaded_files) > 0) {
      foreach ($uploaded_files as $uploaded_file) {
        $fileName = basename($uploaded_file);
        $filesContent[$fileName] = file_get_contents($uploaded_file);
      }
    }
    $this->processRow($name, $folderID, $templateID, $data, $filesContent);
  }

  private function processRow($name, $parentFolderID, $templateID, $data, $uploadedFiles)
  {
    try {
      $clientObj = new GoogleClient();
      $googleClient = $clientObj->getAuthenticatedGoogleClient();
      $service = new GoogleService($googleClient);

      $folder = $service->createFolder($name, $parentFolderID);
      foreach ($uploadedFiles as $fileName => $uploadedFile) {
        $service->uploadFile($fileName, $uploadedFile, $folder->getId());
      }
      $copiedFile = $service->copyDocument($templateID);
      $service->updateDocument($copiedFile->getId(), $data);
      $pdfContent = $service->getFileContentAsPDF($copiedFile->getId());
      $service->uploadFileAsPDF($name, $pdfContent, $folder->getId());
      $service->deleteFile($copiedFile->getId());
    } catch (Exception $e) {
      error_log($e->message);
    }
  }
}

$client = new CF7_File_Saver();
