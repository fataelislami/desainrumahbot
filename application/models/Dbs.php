<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dbs extends CI_Model{

  public function __construct()
  {
    parent::__construct();
  }

  function insert($data,$to){
    $insert = $this->db->insert($to, $data);
    if ($this->db->affected_rows()>0) {
      return true;
      }else{
      return false;
      }
  }

  function getdata($where,$from){
    $this->db->where($where);
    $db=$this->db->get($from);
    return $db;
  }

  function update($where,$data,$to){
    $this->db->where($where);
    $db=$this->db->update($to,$data);
    if ($this->db->affected_rows()>0) {
      return true;
      }else{
      return false;
      }
  }



}
