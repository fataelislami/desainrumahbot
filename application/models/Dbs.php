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

  function getPesanan($id_pesanan){
    $dml="SELECT * FROM `pembayaran` WHERE id_pesanan = $id_pesanan ";
    $query = $this->db->query($dml);
    return $query->row();
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

  function delete($where,$value,$table){
    $this->db->where($where,$value);
    $this->db->delete($table);
  }

  // function getType(){
  //   $dml = "SELECT MIN(id_rumah) AS id, tipe FROM rumah GROUP BY tipe";
  //   $query = $this->db->query($dml);
  //   return $query->result();
  // }

}
