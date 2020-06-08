var clientIDInput = document.getElementById('dfs-google-id');
var clientSecretInput = document.getElementById('dfs-google-secret');
var saveGoogleCredBtn = document.getElementById('dfs-google-save');
var getGoogleCodeBtn = document.getElementById('dfs-google-get-code');
var saveGoogleCredNonceInput = document.getElementById('cf7-dfs-cred-ajax-nonce');
var saveGoogleTokenNonceInput = document.getElementById('cf7-dfs-google-save-ajax-nonce');
var codeInput = document.getElementById('dfs-google-code');
var folderInput = document.getElementById('dfs-folder-id');
var templateInput = document.getElementById('dfs-template-id');
var nameInput = document.getElementById('dfs-name-col');
var modeInput = document.getElementById('dfs-mode');
var saveGoogleTokenBtn = document.getElementById('dfs-google-save-token');

function attachEvents() {
  saveGoogleCredBtn.addEventListener('click', onSaveGoogleCredClick);
  getGoogleCodeBtn.addEventListener('click', onGetGoogleCodeClick);
  saveGoogleTokenBtn.addEventListener('click', onSaveGoogleTokenClick);
}

function onSaveGoogleCredClick() {

  var clientID = clientIDInput.value.trim();
  var clientSecret = clientSecretInput.value.trim();

  if (clientID === '' || clientSecret === '') {
    alert('ClientID or ClientSecret cannot be empty');
    return;
  }

  var formData = new FormData();
  formData.append('action', 'cf7_dfs_save_google_credentials');
  formData.append('id', clientID);
  formData.append('secret', clientSecret);
  formData.append('cf7-dfs-cred-ajax-nonce', saveGoogleCredNonceInput.value);
  fetch(ajaxurl, {
    method: 'POST',
    body: formData,
  }).then(res => {
    return res.json();
  }).then(res => {
    if(res.success) {
      cf7_dfs_googleClientID = clientID;
      alert("Success");
      return;
    }
    alert("Fail");
  })
}

function onGetGoogleCodeClick() {
  var redirectUrl = `https://accounts.google.com/o/oauth2/auth?access_type=offline&approval_prompt=force&client_id=${cf7_dfs_googleClientID}&redirect_uri=urn%3Aietf%3Awg%3Aoauth%3A2.0%3Aoob&response_type=code&scope=https://www.googleapis.com/auth/drive`;
  window.open(redirectUrl, '_blank');
}

function onSaveGoogleTokenClick() {
  var code = codeInput.value.trim();
  var folderID = folderInput.value.trim();
  var templateID = templateInput.value.trim();
  var nameCol = nameInput.value.trim();
  var mode = modeInput.value.trim();
  if (code === '' || folderID == '' || templateID == '' || nameCol == '' || mode == '') {
    alert('Code/FolderID/TemplateID/Mode/Name Column cannot be empty');
    return;
  }

  var formData = new FormData();
  formData.append('action', 'cf7_dfs_save_google_token');
  formData.append('code', code);
  formData.append('folderID', folderID);
  formData.append('templateID', templateID);
  formData.append('nameCol', nameCol);
  formData.append('mode', mode);
  formData.append('cf7-dfs-google-save-ajax-nonce', saveGoogleTokenNonceInput.value);

  fetch(ajaxurl, {
    method: 'POST',
    body: formData,
  }).then(res => {
    return res.json();
  }).then(res => {
    if(res.success) {
      alert("Success");
      return;
    }
    alert("Fail");
  })
}

(function () {
  attachEvents();
})();