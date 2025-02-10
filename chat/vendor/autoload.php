<?php
// Autoload for Google API Client

class Google_Client {
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $scopes = [];
    private $accessToken;

    public function setClientId($clientId) {
        $this->clientId = $clientId;
    }

    public function setClientSecret($clientSecret) {
        $this->clientSecret = $clientSecret;
    }

    public function setRedirectUri($redirectUri) {
        $this->redirectUri = $redirectUri;
    }

    public function addScope($scope) {
        $this->scopes[] = $scope;
    }

    public function createAuthUrl() {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $this->scopes),
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    public function fetchAccessTokenWithAuthCode($code) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $token = json_decode($response, true);
        $this->setAccessToken($token['access_token']);

        return $token;
    }

    public function setAccessToken($token) {
        $this->accessToken = $token;
    }

    public function getAccessToken() {
        return $this->accessToken;
    }
}

class Google_Service_Oauth2 {
    private $client;

    public function __construct($client) {
        $this->client = $client;
    }

    public function getUserinfo() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->client->getAccessToken()
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return new Google_User(json_decode($response, true));
    }
}

class Google_Service_Oauth2_Userinfo {
    public function get() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $_GET['code']
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return new Google_User(json_decode($response, true));
    }
}

class Google_User {
    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function getId() {
        return $this->data['sub'] ?? null;
    }

    public function getEmail() {
        return $this->data['email'] ?? null;
    }

    public function getName() {
        return $this->data['name'] ?? null;
    }

    public function getPicture() {
        return $this->data['picture'] ?? null;
    }
}
?>