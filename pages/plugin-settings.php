<?php

?>

<div class="dfs-container">
  <h1>Contact Form 7 - Drive File Saver</h1>
  <div id="google-cred" class="dfs-card">
    <div class="dfs-card-content">
      <div class="dfs-card-head">
        Google Credentials
      </div>
      <div class="dfs-card-subhead">
        Please enter your Google project Client ID and Client Secret. Make sure your project has access to
        Google Drive and Google Docs APIs.
      </div>
      <div class="dfs-card-form-cont">
        <div class="dfs-card-form-input-cont">
          <label for="dfs-google-id">Google ClientID</label>
          <input type="text" id="dfs-google-id" value="<?php echo get_option('cf7_dfs_client_id') ?>" />
        </div>
        <div class="dfs-card-form-input-cont">
          <label for="dfs-google-secret">Google ClientSecret</label>
          <input type="text" id="dfs-google-secret" value="<?php echo get_option('cf7_dfs_client_secret') ?>" />
        </div>
        <div class="dfs-card-form-input-cont">
          <input type="button" id="dfs-google-save" value="Save" />
        </div>
        <input type="hidden" id="cf7-dfs-cred-ajax-nonce" value="<?php echo wp_create_nonce('cf7-dfs-cred-ajax-nonce'); ?>" />
      </div>
    </div>
  </div>
  <div id="google-token" class="dfs-card">
    <div class="dfs-card-content">
      <div class="dfs-card-head">
        Google Token
      </div>
      <div class="dfs-card-form-cont">
        <div class="dfs-card-form-input-cont">
          <input type="button" id="dfs-google-get-code" value="Get Code" />
        </div>
        <div class="dfs-card-form-input-cont">
          <label for="dfs-google-code">Code</label>
          <input type="text" id="dfs-google-code" />
        </div>
        <div class="dfs-card-form-input-cont">
          <label for="dfs-folder-id">FolderID</label>
          <input type="text" id="dfs-folder-id" value="<?php echo get_option('cf7_dfs_folder_id') ?>" />
        </div>
        <div class="dfs-card-form-input-cont">
          <label for="dfs-template-id">TemplateID</label>
          <input type="text" id="dfs-template-id" value="<?php echo get_option('cf7_dfs_template_id') ?>" />
        </div>
        <div class="dfs-card-form-input-cont">
          <label for="dfs-name-col">Name Column</label>
          <input type="text" id="dfs-name-col" value="<?php echo get_option('cf7_dfs_name_column') ?>" />
        </div>
        <div class="dfs-card-form-input-cont">
          <label for="dfs-mode">Mode</label>
          <input type="text" id="dfs-mode" value="<?php echo get_option('cf7_dfs_mode') ?>" />
        </div>
        <div class="dfs-card-form-input-cont">
          <input type="hidden" id="cf7-dfs-google-save-ajax-nonce" value="<?php echo wp_create_nonce('cf7-dfs-google-save-ajax-nonce'); ?>" />
          <input type="button" id="dfs-google-save-token" value="Save Token" />
        </div>
      </div>
    </div>
  </div>
</div>