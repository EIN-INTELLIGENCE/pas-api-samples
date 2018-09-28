<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PAS_model extends CI_Model
{
  const BASE_URL = 'https://api.sys.sec.mzdemo.net';
  const API_VERSION = '/v3';

  private $token = 'bearer eyJhbGciOiJSUzI1NiIsImtpZCI6ImtleS0xIiwidHlwIjoiSldUIn0.eyJqdGkiOiJkOWE1MDkzNDlhODg0YzU2OTc1OGFiMWVjM2U3OGU1NyIsInN1YiI6ImQ1ZTg3YmYwLTVlNWItNDI5ZS05MmMxLWFjMjQ4NmI5YzE2MCIsInNjb3BlIjpbImNsb3VkX2NvbnRyb2xsZXIucmVhZCIsInBhc3N3b3JkLndyaXRlIiwiY2xvdWRfY29udHJvbGxlci53cml0ZSIsIm9wZW5pZCIsInNjaW0ud3JpdGUiLCJzY2ltLnJlYWQiLCJjbG91ZF9jb250cm9sbGVyLmFkbWluIiwidWFhLnVzZXIiXSwiY2xpZW50X2lkIjoiY2YiLCJjaWQiOiJjZiIsImF6cCI6ImNmIiwiZ3JhbnRfdHlwZSI6InBhc3N3b3JkIiwidXNlcl9pZCI6ImQ1ZTg3YmYwLTVlNWItNDI5ZS05MmMxLWFjMjQ4NmI5YzE2MCIsIm9yaWdpbiI6InVhYSIsInVzZXJfbmFtZSI6ImVpbiIsImVtYWlsIjoiZWluIiwicmV2X3NpZyI6ImM4NjFmMTQiLCJpYXQiOjE1MzgxMTM2OTgsImV4cCI6MTUzODEyMDg5OCwiaXNzIjoiaHR0cHM6Ly91YWEuc3lzLnNlYy5temRlbW8ubmV0L29hdXRoL3Rva2VuIiwiemlkIjoidWFhIiwiYXVkIjpbInNjaW0iLCJjbG91ZF9jb250cm9sbGVyIiwicGFzc3dvcmQiLCJjZiIsInVhYSIsIm9wZW5pZCJdfQ.K7oLrbCdQncfuAtA8au5wsRleXtokMbNXjWNSxAx3Jx5hoiRdN6moMXjTB8vsEnD2nsCi1BoogZ7KzQzQFWnpEgVRlN39qNGc0CWzeFWn28BzCOwOtLYvhNpnwYLem2qxTeC2Dnf8x7PmnkqKqOEcgf1TXaH81LGVhlYVg4ktPRIljXNHB1jguB_6ir10zFFTr9pqW-SeYgdRXXzXQcK1TfCr26nAlo6fwPdOJ3tZ84S8R4QirfY-ovhucRAaU04_02Hq-IKrGX0MMNpL8Z81uPBg-aGER6XSy0iq5gUo0_yNcEeD8f8ZqDZB2f4RbzvdMGKzc0JWy3F0GC5KWyjSw';

  public function __construct()
  {
    parent::__construct();
  }

  private function getHeader()
  {
    if ($this->token) {
      return array(
        'Content-Type: application/json',
        'Authorization: ' . $this->token
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
        'Content-Type: application/x-www-form-urlencoded',
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

    $this->token = $result->access_token;

    echo "<script>alert('<?php $result ?>');</script>";

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

  /**
   * ref: http://v3-apidocs.cloudfoundry.org/version/3.47.0/#list-organizations
   *
   * @return object
   */
  public function listOrganizations()
  {
    $url = '/organizations';

    $ch = curl_init(PAS_model::BASE_URL .PAS_model::API_VERSION .$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $header = $this->getHeader();
    if ($header === null) {
      echo "<script>alert('<?php test1 ?>');</script>";
      curl_close($ch);
      return null;
    }

    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $result = curl_exec($ch);
    if ($result === false) {
      echo "<script>alert('<?php test2 ?>');</script>";

      curl_close($ch);
      return null;
    }
    var_dump($result);
    curl_close($ch);
    return $result;
  }

}