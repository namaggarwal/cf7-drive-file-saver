var saveGoogleCredBtn = document.getElementById('dfs-google-save');
var getGoogleCodeBtn = document.getElementById('dfs-google-get-code');
var saveGoogleNonce = document.getElementById('cf7-dfs-cred-ajax-nonce');

function attachEvents() {
  saveGoogleCredBtn.addEventListener('click', onSaveGoogleCredClick);
  getGoogleCodeBtn.addEventListener('click', onGetGoogleCodeClick);
}

function onSaveGoogleCredClick() {

  var formData = new FormData();
  formData.append('action', 'cf7_dfs_save_google_credentials');
  formData.append('id', 'thisisid');
  formData.append('secret', 'thisissecret');
  formData.append('cf7-dfs-cred-ajax-nonce', saveGoogleNonce.value);
  fetch(ajaxurl, {
    method: 'POST',
    body: formData,
  }).then(res => {
    googleClientID = '557085567897-ddltthpbk60hq5h7m9tabhcli7p65dol.apps.googleusercontent.com';
  });
}

function onGetGoogleCodeClick() {
  var redirectUrl = `https://accounts.google.com/o/oauth2/auth?access_type=offline&approval_prompt=force&client_id=${googleClientID}&redirect_uri=urn%3Aietf%3Awg%3Aoauth%3A2.0%3Aoob&response_type=code&scope=https://www.googleapis.com/auth/drive`;
  window.open(redirectUrl, '_blank');

}

(function () {
  attachEvents();
})();