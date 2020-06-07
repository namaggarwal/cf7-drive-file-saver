<?php

require_once dirname( __FILE__ ).'/vendor/autoload.php';

class GoogleClient
{

  private static $GOOGLE_CLIENT_ID = '';
  private static $GOOGLE_CLIENT_SECRET = '';
  private static $GOOGLE_REDIRECT_URI = 'urn:ietf:wg:oauth:2.0:oob';
  private static $GOOGLE_AUTH_CODE = '';
  private static $GOOGLE_TOKEN = [
  ];

  private function getGoogleClient()
  {
    $client = new Google_Client();
    $client->setClientId(GoogleClient::$GOOGLE_CLIENT_ID);
    $client->setClientSecret(GoogleClient::$GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GoogleClient::$GOOGLE_REDIRECT_URI);
    $client->setScopes(Google_Service_Drive::DRIVE);
    $client->setAccessType('offline');
    return $client;
  }

  public function getAuthenticatedGoogleClient()
  {
    $client = $this->getGoogleClient();
    $client->fetchAccessTokenWithRefreshToken(GoogleClient::$GOOGLE_TOKEN['refresh_token']);
    return $client;
  }

  public function getGoogleAuthURL()
  {
    $client = $this->getGoogleClient();
    return $client->createAuthUrl();
  }

  public function getGoogleToken()
  {
    $client = $this->getGoogleClient();
    $client->fetchAccessTokenWithAuthCode(GoogleClient::$GOOGLE_AUTH_CODE);
    $tokenData = $client->getAccessToken();
    return $tokenData;
  }
}

class GoogleService
{
  private $client;

  public function __construct(Google_Client $client)
  {
    $this->client = $client;
  }

  function createFolder($name, $parentId)
  {
    $file = new Google_Service_Drive_DriveFile();
    $file->setName($name);
    $file->setMimeType("application/vnd.google-apps.folder");
    if ($parentId != null) {
      $file->setParents(array($parentId));
    }
    $service = new Google_Service_Drive($this->client);
    return $service->files->create($file);
  }

  function updateDocument($documentId, $updateData)
  {
    $service = new Google_Service_Docs($this->client);
    $requests = array();
    foreach ($updateData as $key => $value) {
      array_push($requests, new Google_Service_Docs_Request(array(
        "replaceAllText" => array(
          "replaceText" => $value,
          "containsText" => array(
            "text" => "%" . $key . "%",
            "matchCase" => true
          )
        )
      )));
    }

    $updateRequest = new Google_Service_Docs_BatchUpdateDocumentRequest(
      array(
        'requests' => $requests
      )
    );
    $service->documents->batchUpdate($documentId, $updateRequest);
  }

  function copyDocument($fileId, $parentId = null)
  {
    $copiedFile = new Google_Service_Drive_DriveFile();
    if ($parentId != null) {
      $copiedFile->setParents(array($parentId));
    }
    $service = new Google_Service_Drive($this->client);
    return $service->files->copy($fileId, $copiedFile);
  }

  function uploadFileAsPDF($name, $contents, $parentId = null)
  {
    $file = new Google_Service_Drive_DriveFile();
    $file->setName($name);
    $file->setMimeType("application/pdf");
    if ($parentId != null) {
      $file->setParents(array($parentId));
    }
    $service = new Google_Service_Drive($this->client);
    return $service->files->create($file, [
      'data' => $contents,
      'mimeType' => 'application/pdf',
    ]);
  }

  function uploadFile($name, $contents, $parentId = null)
  {
    $file = new Google_Service_Drive_DriveFile();
    $file->setName($name);
    if ($parentId != null) {
      $file->setParents(array($parentId));
    }
    $service = new Google_Service_Drive($this->client);
    return $service->files->create($file, [
      'data' => $contents,
    ]);
  }

  function getFileContentAsPDF($fileId)
  {
    $service = new Google_Service_Drive($this->client);
    $file = $service->files->export($fileId, 'application/pdf', array('alt' => 'media'));
    return $file->getBody()->getContents();
  }

  function deleteFile($fileId)
  {
    $service = new Google_Service_Drive($this->client);
    $service->files->delete($fileId);
  }
}


// $name = 'Naman';
// $folderID = '1EIsW_BjWSCwpkxHo62znsiPwZRKCbLW-';
// $templateID = '1-GaKEPji27FiIu34Ft1nVANwA53r4G7x-u6dWmzdnjE';

// processRow($name, $folderID, $templateID);
