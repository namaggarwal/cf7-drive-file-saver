var clientIDInput = document.getElementById('dfs-google-id');
var clientSecretInput = document.getElementById('dfs-google-secret');
var saveGoogleCredBtn = document.getElementById('dfs-google-save');
var getGoogleCodeBtn = document.getElementById('dfs-google-get-code');
var saveGoogleCredNonceInput = document.getElementById('cf7-dfs-cred-ajax-nonce');
var saveGoogleTokenNonceInput = document.getElementById('cf7-dfs-google-save-ajax-nonce');
var codeInput = document.getElementById('dfs-google-code');
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

  if (code === '') {
    alert('Code cannot be empty');
    return;
  }

  var formData = new FormData();
  formData.append('action', 'cf7_dfs_save_google_token');
  formData.append('code', code);
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