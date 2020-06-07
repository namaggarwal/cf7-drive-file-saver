<?php

class CF7_File_Saver_Connector
{

  public function __construct() {
    add_action('wp_ajax_cf7_dfs_save_google_credentials', array($this, 'save_google_credentials'));
    add_action('wp_ajax_cf7_dfs_generate_token', array($this, 'generate_token'));
  }

  public function save_google_credentials()
  {
    check_ajax_referer('cf7-dfs-cred-ajax-nonce', 'cf7-dfs-cred-ajax-nonce');

    if(!is_admin()) {
      wp_send_json_error();
    }
    $clientID = sanitize_text_field($_POST["id"]);
    $clientSecret = sanitize_text_field($_POST["secret"]);
    wp_send_json_success();
  }

  public function generate_token()
  {
    check_ajax_referer('cf7-dfs-google-save-ajax-nonce', 'cf7-dfs-google-save-ajax-nonce');

    if(!is_admin()) {
      wp_send_json_error();
    }
    $code = sanitize_text_field($_POST["code"]);
    wp_send_json_success();
  }
}

$connector = new CF7_File_Saver_Connector();