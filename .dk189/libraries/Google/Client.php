<?php
namespace Google;

use \Curl\Client as ClientBase;
use \Exception;
use \Google\Response;
use \Google\Exception\SDKException as GoogleSDKException;
use \Google\Exception\AuthException as GoogleAuthException;

class Client extends ClientBase {
    protected static $OAUTH_URL = "https://accounts.google.com/o/oauth2/v2/auth";
    protected static $REVOKE_API = "https://accounts.google.com/o/oauth2/revoke";
    protected static $OAUTH_API = "https://www.googleapis.com/oauth2/";

    protected $CLIENT_ID;
    protected $CLIENT_SECRET;
    protected $REDIRECT_URI;

    public function __construct ($clientId = "", $clientSecret = "", $redirect_uri = "") {
        parent::__construct();
        self::setClientId($clientId);
        self::setClientSecret($clientSecret);
        self::setRedirectUri($redirect_uri);
    }

    public function get ($api, $version = "v4") {
        return new Response(parent::get(self::$OAUTH_API . $version . "/" . $api));
    }

    public function post ($api, Array $data, $version = "v4") {
        return new Response(parent::post(self::$OAUTH_API . $version . "/" . $api, $data));
    }

    public function setClientId ($clientId) {
        $this->CLIENT_ID = $clientId;
    }

    public function setClientSecret ($clientSecret) {
        $this->CLIENT_SECRET = $clientSecret;
    }

    public function setRedirectUri ($redirect_uri) {
        $this->REDIRECT_URI = $redirect_uri;
    }

    public function setAccessToken ($access_token, $type = "Bearer") {
        self::setHeader("Authorization", $type . " " . $access_token);
    }

    public function authHelper () {
        if ( empty($this->CLIENT_ID) ) {
            throw new GoogleSDKException("Missing CLIENT_ID");
        }
        if ( empty($this->CLIENT_SECRET) ) {
            throw new GoogleSDKException("Missing CLIENT_SECRET");
        }
        if ( empty($this->REDIRECT_URI) ) {
            throw new GoogleSDKException("Missing REDIRECT_URI");
        }
        if ( isset($_GET["error"]) ) {
            $error = new \stdClass;
            $error->error = $_GET["error"];
            $error->error_description = $_GET["error"];
            return $error;
        }
        if ( !isset($_GET["code"]) ) {
            throw new GoogleAuthException("Please redirect to auth url");
        }
        try {
            self::post("token",[
                'code' => $_GET["code"],
                'client_id' => $this->CLIENT_ID,
                'client_secret' => $this->CLIENT_SECRET,
                'redirect_uri' => $this->REDIRECT_URI,
                'grant_type' => 'authorization_code'
            ], "v4");
            $response = json_decode(self::getCurrentBody());
            if ( isset($response->access_token) && isset($response->token_type) ) {
                $this->setAccessToken($response->access_token, $response->token_type);
            }
            return $response;
        } catch (Exception $e) {
            throw new GoogleAuthException("Error parse response token", -1, $e);
        }
    }

    public function createAuthUrl (Array $scopes = ['profile', 'email']) {
        return sprintf(
            "%s?%s",
            self::$OAUTH_URL,
            http_build_query([
                'client_id' => $this->CLIENT_ID,
                'scope' => implode(" ", $scopes),
                'redirect_uri' => $this->REDIRECT_URI,
                'response_type' => 'code',
                'access_type' => 'offline',
                'include_granted_scopes' => 'true',
                'prompt' => 'consent'
            ])
        );
    }

    public function refreshToken ($refresh_token) {
        try {
            self::post("token",[
                'refresh_token' => $refresh_token,
                'client_id' => $this->CLIENT_ID,
                'client_secret' => $this->CLIENT_SECRET,
                'grant_type' => 'refresh_token'
            ], "v4");
            $response = json_decode(self::getCurrentBody());
            if ( isset($response->access_token) && isset($response->token_type) ) {
                $this->setAccessToken($response->access_token, $response->token_type);
            }
            return $response;
        } catch (Exception $e) {
            throw new GoogleAuthException("Error parse response token", -1, $e);
        }
    }
}
?>
