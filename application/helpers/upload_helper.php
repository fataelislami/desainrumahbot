<?php
/*Cara penggunaan Kostlab File Helper
PASTIKAN FOLDER TUJUAN PERMISSION NYA SUDAH BISA WRITE
dan
PASTIKAN FORM SUDAH BERUBAH DENGAN enctype="multipart/form-data"
Keterangan
upload("Nama Form","Direktori Tujuan","Type File","Enkripsi Nama TRUE/FALSE")

contoh
jika <input type="file" name="photo">
dengan folder tujuan photo
maka
$photo=upload('photo','photo','image',TRUE);
if($photo){
  //$photo['file_name']; //Untuk mengambil nama file, dan masukan ke database
  echo "foto berhasil di upload";
}else{
  echo "gagal upload foto";
}

jika <input type="file" name="pdf">
$pdf=upload('pdf','pdf','file',TRUE);
if($pdf){
  //$pdf['file_name']; //Untuk mengambil nama file, dan masukan ke database
  echo "file berhasil di upload";
}else{
  echo "gagal upload pdf";
}

Gunakan class ini untuk FORM create
<input type="file" id="input-file-now-custom-1" class="dropify" name="file">

Gunaka class ini untuk Form Edit, dan ubah default URL nya
<input type="file" id="input-file-now-custom-1" class="dropify" data-default-file="<?php echo base_url().'xfile/{namafoldertujuan}'.$dataedit->photoUrl?>" name="file">

*/

 function upload($formname,$pathFolder,$type='image',$encrypt=FALSE){//default untuk image
    $ci =& get_instance();//$ci untuk pengganti $this->
    $config['upload_path']          = './upload/'.$pathFolder;
    if($type=='file'){
      $config['allowed_types']        = 'pdf';
      //$config['max_size']             = 100;
    }else if($type=='audio'){
      $config['allowed_types']        = 'mp3';
      //$config['max_size']             = 100;
    }else if($type=='image'){
      $config['allowed_types']        = 'gif|jpg|png|jpeg';
      //$config['max_size']             = 100;
      //$config['max_width']            = 1024;
      //$config['max_height']           = 768;
    }else if($type=='video'){
      $config['allowed_types']        = 'mp4|avi';
      //$config['max_size']             = 100;
    }
    $config['overwrite'] = FALSE;
    $config['encrypt_name'] = $encrypt;
    $ci->load->library('upload', $config);
    $ci->upload->initialize($config);
    $exec=$ci->upload->do_upload($formname);
    if($exec){
      return $ci->upload->data();
    }else{
      return FALSE;
    }
 }



 ?>
