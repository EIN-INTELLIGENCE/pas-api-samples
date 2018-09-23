<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PasSample extends CI_Controller
{
  const LOGIN = 'login';
  const SECRET = 'secret';

  public function index()
  {
    $this->pas->getToken();
  }

  public function task_create_space()
  {
    $name = $this->input->post('name');
    $org_guid = $this->input->post('org_guid');
    $this->pas->createSpace($name, $org_guid);
  }

}