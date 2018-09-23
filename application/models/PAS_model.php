<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PAS_model extends CI_Model
{
  const BASE_URL = 'https://api.example.org';
  const API_VERSION = '/v3';

  private $token = null;

  public function __construct()
  {
    parent::__construct();
  }

  private function getHeader()
  {
    if ($this->token) {
      return array(
        'Content-Type: application/json',
        'Authorization: bearer' . $this->token
      );
    }
    return null;
  }

  /**
   * ref: https://docs.cloudfoundry.org/api/uaa/version/4.19.0/index.html#client-credentials-grant
   *
   * @param $login - login
   * @param $secret - loginsecret
   * @return bool
   */

  public function getToken($login, $secret)
  {
    $url = '/oauth/token';

    $ch = curl_init(PAS_model::BASE_URL .$url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $header = array(
        'Content-Type: application/json',
        'Accept: application/json'
      );
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $param =
      'client_id' . $login . '&'
     .'client_secret' . $secret . '&'
     .'grant_type=client_credentials&token_format=opaque&response_type=token';

    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

    $result = curl_exec($ch);
    if ($result === false) {
      curl_close($ch);
      return false;
    }

    /*
    {
      "access_token" : "65d8cfa454b443f9848c6628155255b7",
      "token_type" : "bearer",
      "expires_in" : 43199,
      "scope" : "clients.read emails.write scim.userids password.write idps.write notifications.write oauth.login scim.write critical_notifications.write",
      "jti" : "65d8cfa454b443f9848c6628155255b7"
    }
    */
    $this->token = $result->access_token;

    curl_close($ch);
    return true;

  }

  /**
   * ref: http://v3-apidocs.cloudfoundry.org/version/3.47.0/#create-a-space
   *
   * @param $name - space name
   * @param $org_guid - org uid
   * @return bool
   */
  public function createSpace($name, $org_guid)
  {
    $url = '/spaces';

    $ch = curl_init(PAS_model::BASE_URL .PAS_model::API_VERSION .$url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $header = $this->getHeader();
    if ($header === null) {
      curl_close($ch);
      return false;
    }

    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $param = array(
      'name' => $name,
      'relationships' => array(
        'organization' => array(
          'data' => array(
            'guid' => $org_guid
          )
        )
      )
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));

    $result = curl_exec($ch);
    if ($result === false) {
      curl_close($ch);
      return false;
    }
    curl_close($ch);
    return true;

  }

}