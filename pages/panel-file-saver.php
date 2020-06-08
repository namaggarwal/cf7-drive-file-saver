<?php

$form_id = sanitize_text_field($_GET['post']);
$form_data = get_post_meta($form_id, 'gs_settings');

?>

<div id="cf7_dfs_settings" class="dfs-card">
  <div class="dfs-card-content">
    <div class="dfs-card-head">
      File Saver Settings
    </div>
    <div class="dfs-card-form-cont">
      <div class="dfs-card-form-input-cont">
        <label for="dfs-google-secret">TemplateID</label>
        <input type="text" name="cf7-gs[template-id]" value="<?php echo (isset($form_data[0]['template-id'])) ? esc_attr($form_data[0]['template-id']) : ''; ?>" />
      </div>
    </div>
  </div>
</div>