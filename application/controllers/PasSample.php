<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PasSample extends CI_Controller
{
  const LOGIN = 'ein';
  const SECRET = 'ein';

  public function index()
  {
    //$this->pas->getToken(PasSample::LOGIN, PasSample::SECRET);
  }

  public function task_create_space()
  {
    $name = 'EIN-SPACE-01';
    $org_guid = 'ee1f93e0-2121-4c02-973a-c92b3773144b';
    $this->pas->createSpace($name, $org_guid);
  }

  public function task_list_organizations()
  {
    $this->pas->listOrganizations();
  }

}