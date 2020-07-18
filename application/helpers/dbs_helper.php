<?php
defined('BASEPATH') OR exit('No direct script access allowed');

  function getData($from,$where=null,$limit=9,$offset=0){
    $ci =& get_instance();//$ci untuk pengganti $this->
    $ci->load->database();
    if($where!=null){
      $ci->db->where($where);
    }
    $ci->db->limit($limit, $offset);
    $db=$ci->db->get($from);
    return $db;
  }

  function insertData($data,$table){
    $ci =& get_instance();//$ci untuk pengganti $this->
    $ci->load->database();
   $insert = $ci->db->insert($table, $data);
   if ($ci->db->affected_rows()>0) {
     return true;
     }else{
     return false;
     }
 }
 function updateData($data,$table,$where){
   $ci =& get_instance();//$ci untuk pengganti $this->
   $ci->load->database();
    $ci->db->where($where);
    $db=$ci->db->update($table,$data);
    if ($ci->db->affected_rows()>0) {
      return true;
      }else{
      return false;
      }
  }

  function deleteData($from,$where=null){
    $ci =& get_instance();//$ci untuk pengganti $this->
    $ci->load->database();
    $ci->db->where($where);
    $ci->db->delete($from);
  }
