<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once('line_class.php');
require_once('MessageBuilder.php');

class Bot extends MY_Controller {

     public function __construct()
          {
            parent::__construct();
            //Codeigniter : Write Less Do More
      $this->load->model(array('Dbs'));

          }

  public function webhook()
  {
    //Konfigurasi Chatbot
    $channelAccessToken = '5VGsqVGyS7KRTdiQYIbJYJe0ITrXM/A0yU6jRMtbV1dJUnk4KViJi8VIkpJVaiYVcBThRIZWtdJm29ZroBHxUMuVAlQYpRjsUVRziSXtJj+3TjCosynuUm0ZLFheH5XHpi2U1rGygMOFpvLDiswwxQdB04t89/1O/w1cDnyilFU=';
    $channelSecret = 'd082b5a95dff36484507bc48e76b8671';//sesuaikan
    //Konfigurasi Chatbot END

    $client = new LINEBotTiny($channelAccessToken, $channelSecret);
    $messageBuilder= new MessageBuilder();


        $userId   = $client->parseEvents()[0]['source']['userId'];
        $replyToken = $client->parseEvents()[0]['replyToken'];
        $timestamp  = $client->parseEvents()[0]['timestamp'];
        $message  = $client->parseEvents()[0]['message'];
        $postback=$client->parseEvents() [0]['postback'];
        $profil = $client->profil($userId);
        $nama=$profil->displayName;
        $inputMessage = strtoupper($message['text']);
        $namapanggilan=$pecahnama[0];
        $event=$client->parseEvents() [0];
        $dataUser=$this->Dbs->getdata(array('id_users'=>$userId),'users')->row();

              if ($event['type'] == 'follow')//Yang bot lakukan pertama kali saat di add oleh user
              {
                $dataInsert=array(
                  'id_users'=>$userId,
                  'nama'=>$nama,
                  'map'=>'registrasi',
                  'counter'=>0
                );
                $sql=$this->Dbs->insert($dataInsert,'users');
                if($sql){
                  $messages=[];
                  $msg1=$messageBuilder->text("Daftar Berhasil");
                  $msg2=$messageBuilder->text("Sebelum masuk ke pemesanan kamu harap input email ya");
                  $msg3=$messageBuilder->text("Masukkan Email mu dengan membalas pesan ini ");
                  array_push($messages,$msg1,$msg2,$msg3);
                  $output=$this->reply($replyToken,$messages);
                  // $pre=array($messageBuilder->text("Daftar berhasil"));
                  // $output=$this->reply($replyToken,$pre);
                }else{
                  $pre=array($messageBuilder->text("Daftar gagal"));
                  $output=$this->reply($replyToken,$pre);
                }
              }
           
            if($dataUser->map == 'registrasi' && $dataUser->counter == '0'){
                if(preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/si', $inputMessage)){
                    $dataUpdate = array(
                      'email' => $message['text'],
                      'map'   => 'registrasi_tlp',
                      'counter' => 1  
                    );
                    // $sql=$this->Dbs->insert($dataInsert,'users');
                    $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                    if ($sql) {
                      $pre=array($messageBuilder->text("Email kamu sudah didaftarkan, sekarang masukkan no telpon kamu"));
                      $output=$this->reply($replyToken,$pre);
                    }
                }else{
                  $pre=array($messageBuilder->text("Email gagal didaftarkan"));
                  $output=$this->reply($replyToken,$pre);
                }
            }
            if($dataUser->map == 'registrasi_tlp' && $dataUser->counter == '1'){
              if (is_numeric($message['text'])) {
                $dataUpdate = array(
                      'no_telepon' => $message['text'],
                      'map'   => 'belum order',
                      'counter' => 0  
                    );
                $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                if ($sql) {
                  $messages=[];
                  $msg1=$messageBuilder->text("No Telp Berhasil Didaftarakan, Sekarang kamu bisa mulai order dengan tekan tombol dibawah ini");
                  $msg2=array (
                          'type' => 'flex',
                          'altText' => 'Flex Message',
                          'contents' => 
                          array (
                            'type' => 'bubble',
                            'direction' => 'ltr',
                            'header' => 
                            array (
                              'type' => 'box',
                              'layout' => 'vertical',
                              'contents' => 
                              array (
                                0 => 
                                array (
                                  'type' => 'text',
                                  'text' => 'Tekan Tombol Di Bawah Ini',
                                  'align' => 'center',
                                ),
                              ),
                            ),
                            'footer' => 
                            array (
                              'type' => 'box',
                              'layout' => 'horizontal',
                              'contents' => 
                              array (
                                0 => 
                                array (
                                  'type' => 'button',
                                  'action' => 
                                  array (
                                    'type' => 'message',
                                    'label' => 'MULAI',
                                    'text' => 'MULAI',
                                  ),
                                ),
                              ),
                            ),
                          ),
                        );
                  array_push($messages,$msg1,$msg2);
                  $output=$this->reply($replyToken,$messages);
                }
              }else{
                $pre=array($messageBuilder->text("No telpon gagal didaftarkan"));
                  $output=$this->reply($replyToken,$pre);
              }
            }
                      // Order dan simpan ke database
            if(substr($inputMessage,0,5) == "ORDER"){ 
              //explode = (separator,string,limit)
              $explodeInput=explode(" ",$inputMessage);//pecah input berdasarkan spasi
              $id_rumah=$explodeInput[1];
              $dataInsert=array(
                'id_users'=>$userId,
                'id_rumah'=>$id_rumah,
              );
              $sql=$this->Dbs->insert($dataInsert,'pesanan');
              if($sql){
                $dataPesanan = $this->Dbs->getdata($dataInsert,'pesanan')->row();
                $dataInsert = array(
                  'id_pesanan' => $dataPesanan->id_pesanan,
                  'status_bayar' => 'belum bayar'
                );
                $sql=$this->Dbs->insert($dataInsert,'pembayaran');
                if ($sql) {
                  $pesan = "sudah bayar ".$id_rumah;
                  $messages=[];
                  $msg1=$messageBuilder->text("Pesanan Berhasil dilakukan, Silahkan lakukan pembayaran Uang DP ke no : 08997148238, Jika sudah membayar Uang DP silahkan klik Sudah Bayar DP");
                  $msg2=array (
                          'type' => 'flex',
                          'altText' => 'Flex Message',
                          'contents' => 
                          array (
                            'type' => 'bubble',
                            'direction' => 'ltr',
                            'header' => 
                            array (
                              'type' => 'box',
                              'layout' => 'vertical',
                              'contents' => 
                              array (
                                0 => 
                                array (
                                  'type' => 'text',
                                  'text' => 'Konfirmasi Pembayaran',
                                  'align' => 'center',
                                ),
                              ),
                            ),
                            'footer' => 
                            array (
                              'type' => 'box',
                              'layout' => 'horizontal',
                              'contents' => 
                              array (
                                0 => 
                                array (
                                  'type' => 'button',
                                  'action' => 
                                  array (
                                    'type' => 'message',
                                    'label' => 'SUDAH BAYAR DP',
                                    'text' => $pesan,
                                  ),
                                ),
                              ),
                            ),
                          ),
                        );
                  array_push($messages,$msg1,$msg2);
                  $output=$this->reply($replyToken,$messages);
                  // $pesan = "sudah bayar ".$id_rumah;
                  // $pre=array($messageBuilder->text("Pesanan Berhasil dilakukan, Silakan lakukan pembayaran ke no : 08997148238 ketik ".$pesan));
                  // $output=$this->reply($replyToken,$pre);
                }
                
              }else{
                $pre=array($messageBuilder->text("Pesanan gagal di proses"));
                $output=$this->reply($replyToken,$pre);
              }
            }

            if (substr($inputMessage,0,11) == 'SUDAH BAYAR') {
                $explodeInput=explode(" ",$inputMessage);
                $id_rumah=$explodeInput[2];
                $dataWhere = array(
                  'id_users'=> $userId,
                  'id_rumah'=> $id_rumah
                );
                $dataPesanan = $this->Dbs->getdata($dataWhere,'pesanan')->row();
                $id_pesanan = $dataPesanan->id_pesanan;
                $dataBayar = $this->Dbs->getPesanan($id_pesanan);
                if ($dataBayar->status_bayar == 'sudah bayar') {
                  $pre=array($messageBuilder->text("Anda sudah membayar desain rumah, untuk konsultasi lanjutan bisa hubungi kontak : 08997148238"));
                $output=$this->reply($replyToken,$pre);
                }else{
                  $pre=array($messageBuilder->text("Anda belum membayar pemesanan desain rumah"));
                $output=$this->reply($replyToken,$pre);
                }
            }


            if($inputMessage == 'MULAI'){
              $counter=1;
              $dataUpdate=array('map'=>'desain','counter'=>$counter);
              $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
              if($sql){
                $messages=[];
                  $msg1=$messageBuilder->text("Mau beli rumah tipe jenis apa nih?");
                  $msg2=array (
                        'type' => 'flex',
                        'altText' => 'Flex Message',
                        'contents' => 
                        array (
                          'type' => 'carousel',
                          'contents' => 
                          array (
                            0 => 
                            array (
                              'type' => 'bubble',
                              'direction' => 'ltr',
                              'header' => 
                              array (
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'text',
                                    'text' => 'Rumah',
                                    'align' => 'center',
                                  ),
                                ),
                              ),
                              'footer' => 
                              array (
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'button',
                                    'action' => 
                                    array (
                                      'type' => 'message',
                                      'label' => 'Tipe 36',
                                      'text' => 'Tipe 36',
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            1 => 
                            array (
                              'type' => 'bubble',
                              'direction' => 'ltr',
                              'header' => 
                              array (
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'text',
                                    'text' => 'Rumah',
                                    'align' => 'center',
                                  ),
                                ),
                              ),
                              'footer' => 
                              array (
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'button',
                                    'action' => 
                                    array (
                                      'type' => 'message',
                                      'label' => 'Tipe 45',
                                      'text' => 'Tipe 45',
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            2 => 
                            array (
                              'type' => 'bubble',
                              'direction' => 'ltr',
                              'header' => 
                              array (
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'text',
                                    'text' => 'Rumah',
                                    'align' => 'center',
                                  ),
                                ),
                              ),
                              'footer' => 
                              array (
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'button',
                                    'action' => 
                                    array (
                                      'type' => 'message',
                                      'label' => 'Tipe 60',
                                      'text' => 'Tipe 60',
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            3 => 
                            array (
                              'type' => 'bubble',
                              'direction' => 'ltr',
                              'header' => 
                              array (
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'text',
                                    'text' => 'Rumah',
                                    'align' => 'center',
                                  ),
                                ),
                              ),
                              'footer' => 
                              array (
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'button',
                                    'action' => 
                                    array (
                                      'type' => 'message',
                                      'label' => 'Tipe 70',
                                      'text' => 'Tipe 70',
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      );
                  array_push($messages,$msg1,$msg2);
                  $output=$this->reply($replyToken,$messages);
                // $pre=array($messageBuilder->text("Mau beli rumah tipe jenis apa nih?
                //                                 \nOpsi: \n1. Tipe 36"));
                // $output=$this->reply($replyToken,$pre);
              }
            }
            // Map Desain
            if(substr($dataUser->map,0,6)=='desain'){
                  if($inputMessage == 'RESET'){
                    $dataUpdate=array('map'=>'belum order','counter'=>0,'request'=>NULL);
                    $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                    if($sql){
                      //explode = (separator,string,limit)
                      $explodeRequest=explode("#",$dataUser->request);
                      $pre=array($messageBuilder->text("Bot berhasil di reset"));
                      $output=$this->reply($replyToken,$pre);
                    }
                  }else
                  // Q1
                  if(substr($dataUser->map,0,6)=='desain' && $dataUser->counter==1){
                    $inputExplode=explode(" ",$inputMessage);
                    if($inputExplode[0]=='TIPE'){
                      $counter=$dataUser->counter+1;
                      $request=$inputExplode[1];
                      $dataUpdate=array('map'=>'desain','counter'=>$counter,'request'=>$request);
                      $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                      if($sql){
                        $messages=[];
                  $msg1=$messageBuilder->text("Jumlah Lantainya Berapa?");
                  $msg2=array (
                        'type' => 'flex',
                        'altText' => 'Flex Message',
                        'contents' => 
                        array (
                          'type' => 'carousel',
                          'contents' => 
                          array (
                            0 => 
                            array (
                              'type' => 'bubble',
                              'direction' => 'ltr',
                              'header' => 
                              array (
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'text',
                                    'text' => 'Rumah',
                                    'align' => 'center',
                                  ),
                                ),
                              ),
                              'footer' => 
                              array (
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'button',
                                    'action' => 
                                    array (
                                      'type' => 'message',
                                      'label' => 'Lantai 1',
                                      'text' => 'Lantai 1',
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            1 => 
                            array (
                              'type' => 'bubble',
                              'direction' => 'ltr',
                              'header' => 
                              array (
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'text',
                                    'text' => 'Rumah',
                                    'align' => 'center',
                                  ),
                                ),
                              ),
                              'footer' => 
                              array (
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'button',
                                    'action' => 
                                    array (
                                      'type' => 'message',
                                      'label' => 'Lantai 2',
                                      'text' => 'Lantai 2',
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      );
                  array_push($messages,$msg1,$msg2);
                  $output=$this->reply($replyToken,$messages);
                        // $pre=array($messageBuilder->text("Jumlah lantainya berapa?"));
                        // $output=$this->reply($replyToken,$pre);
                      }
                    }else{
                      $pre=array($messageBuilder->text("Masukan tidak diketahui, ketik TIPE [nomor]"));
                      $output=$this->reply($replyToken,$pre);
                    }
                  }else 
                  // Q2
                  if(substr($dataUser->map,0,6)=='desain' && $dataUser->counter==2){
                    preg_match_all('!\d+!', $inputMessage, $getNumber);
                    $counter=$dataUser->counter+1;
                      $request=$getNumber[0][0]."#".$dataUser->request;
                      $dataUpdate=array('map'=>'desain','counter'=>$counter,'request'=>$request);
                      $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                      if($sql){
                        $messages=[];
                        $msg1=$messageBuilder->text("Mau style rumah seperti apa?");
                        $msg2=array (
                              'type' => 'flex',
                              'altText' => 'Flex Message',
                              'contents' => 
                              array (
                                'type' => 'carousel',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'bubble',
                                    'direction' => 'ltr',
                                    'header' => 
                                    array (
                                      'type' => 'box',
                                      'layout' => 'vertical',
                                      'contents' => 
                                      array (
                                        0 => 
                                        array (
                                          'type' => 'text',
                                          'text' => 'Rumah',
                                          'align' => 'center',
                                        ),
                                      ),
                                    ),
                                    'footer' => 
                                    array (
                                      'type' => 'box',
                                      'layout' => 'horizontal',
                                      'contents' => 
                                      array (
                                        0 => 
                                        array (
                                          'type' => 'button',
                                          'action' => 
                                          array (
                                            'type' => 'message',
                                            'label' => 'Modern',
                                            'text' => 'Modern',
                                          ),
                                        ),
                                      ),
                                    ),
                                  ),
                                  1 => 
                                  array (
                                    'type' => 'bubble',
                                    'direction' => 'ltr',
                                    'header' => 
                                    array (
                                      'type' => 'box',
                                      'layout' => 'vertical',
                                      'contents' => 
                                      array (
                                        0 => 
                                        array (
                                          'type' => 'text',
                                          'text' => 'Rumah',
                                          'align' => 'center',
                                        ),
                                      ),
                                    ),
                                    'footer' => 
                                    array (
                                      'type' => 'box',
                                      'layout' => 'horizontal',
                                      'contents' => 
                                      array (
                                        0 => 
                                        array (
                                          'type' => 'button',
                                          'action' => 
                                          array (
                                            'type' => 'message',
                                            'label' => 'Tropis',
                                            'text' => 'Tropis',
                                          ),
                                        ),
                                      ),
                                    ),
                                  ),
                                  2 => 
                                  array (
                                    'type' => 'bubble',
                                    'direction' => 'ltr',
                                    'header' => 
                                    array (
                                      'type' => 'box',
                                      'layout' => 'vertical',
                                      'contents' => 
                                      array (
                                        0 => 
                                        array (
                                          'type' => 'text',
                                          'text' => 'Rumah',
                                          'align' => 'center',
                                        ),
                                      ),
                                    ),
                                    'footer' => 
                                    array (
                                      'type' => 'box',
                                      'layout' => 'horizontal',
                                      'contents' => 
                                      array (
                                        0 => 
                                        array (
                                          'type' => 'button',
                                          'action' => 
                                          array (
                                            'type' => 'message',
                                            'label' => 'Modern Tropis',
                                            'text' => 'Modern Tropis',
                                          ),
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                            );
                        array_push($messages,$msg1,$msg2);
                        $output=$this->reply($replyToken,$messages);
                        // $pre=array($messageBuilder->text("Mau style arsitektur seperti apa?"));
                        // $output=$this->reply($replyToken,$pre);
                      }
                  }else 
                  // Q3
                  if(substr($dataUser->map,0,6)=='desain' && $dataUser->counter==3){
                    $counter=$dataUser->counter+1;
                      $request=$message['text']."#".$dataUser->request;
                      $dataUpdate=array('map'=>'desain','counter'=>$counter,'request'=>$request);
                      $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                      if($sql){
                        $messages=[];
                  $msg1=$messageBuilder->text("Luas Bangunan?");
                  $msg2=array (
                        'type' => 'flex',
                        'altText' => 'Flex Message',
                        'contents' => 
                        array (
                          'type' => 'carousel',
                          'contents' => 
                          array (
                            0 => 
                            array (
                              'type' => 'bubble',
                              'direction' => 'ltr',
                              'header' => 
                              array (
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'text',
                                    'text' => 'Luas Bangunan',
                                    'align' => 'center',
                                  ),
                                ),
                              ),
                              'footer' => 
                              array (
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'button',
                                    'action' => 
                                    array (
                                      'type' => 'message',
                                      'label' => '1-100 m/2',
                                      'text' => '1-100 m/2',
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      );
                  array_push($messages,$msg1,$msg2);
                  $output=$this->reply($replyToken,$messages);
                        // $pre=array($messageBuilder->text("Luas bangunannya berapa?"));
                        // $output=$this->reply($replyToken,$pre);
                      }
                  }else 
                  // Q4 KONFIRMASI
                  if(substr($dataUser->map,0,6)=='desain' && $dataUser->counter==4){
                    $counter=$dataUser->counter+1;
                      $request=$message['text']."#".$dataUser->request;
                      $dataUpdate=array('map'=>'desain','counter'=>$counter,'request'=>$request);
                      $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                      if($sql){
                        $explodeRequest=explode("#",$dataUser->request);
                        $messages=[];
                        $msg1=$messageBuilder->text("Bot konfirmasi dulu ya, untuk pesanan desain kamu adalah : \nTipe Rumah: ".$explodeRequest[2]."\nJumlah Lantai: ".$explodeRequest[1]."\nStyle Arsitektur: ".$explodeRequest[0]."\nLuas Bangunan: ".$message['text']." \n Klik <Benar> jika anda sudah setuju dengan inputan portofolio diatas :)");
                        $msg2=array (
                                'type' => 'flex',
                                'altText' => 'Flex Message',
                                'contents' => 
                                array (
                                  'type' => 'bubble',
                                  'direction' => 'ltr',
                                  'header' => 
                                  array (
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => 
                                    array (
                                      0 => 
                                      array (
                                        'type' => 'text',
                                        'text' => '-',
                                        'align' => 'center',
                                      ),
                                    ),
                                  ),
                                  'footer' => 
                                  array (
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'contents' => 
                                    array (
                                      0 => 
                                      array (
                                        'type' => 'button',
                                        'action' => 
                                        array (
                                          'type' => 'message',
                                          'label' => 'BENAR',
                                          'text' => 'benar',
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              );
                        array_push($messages,$msg1,$msg2);
                        $output=$this->reply($replyToken,$messages);
                      }
                  }else 
                  // Q5 FINAL
                  if(substr($dataUser->map,0,6)=='desain' && $dataUser->counter==5){
                    if($inputMessage=="BENAR"){
                      $explodeRequest=explode("#",$dataUser->request);
                      $whereData=array(
                        'tipe'=>$explodeRequest[3],
                        'jumlah_lantai'=>$explodeRequest[2],
                        'style_arsitektur'=>$explodeRequest[1],
                        'luas_bangunan'=>$explodeRequest[0],
                        'status_rumah' => 'tersedia'
                      );
                      $getDataRumah=$this->Dbs->getdata($whereData,'rumah');
                      $itemsRumah=[];//memasukan item rumah kedalam carousel
                      if($getDataRumah->num_rows()>0){
                        foreach ($getDataRumah->result() as $key){
                          $itemRumah=array(
                            'type' => 'bubble',
                            'direction' => 'ltr',
                            'hero' => 
                            array (
                              'type' => 'image',
                              'url' => base_url()."bot_file/".$key->gambar,
                              'size' => 'full',
                              'aspectRatio' => '20:13',
                              'aspectMode' => 'cover',
                            ),
                            'body' => 
                            array (
                              'type' => 'box',
                              'layout' => 'vertical',
                              'contents' => 
                              array (
                                0 => 
                                array (
                                  'type' => 'text',
                                  'text' => 'Desain Rumah Tipe '.$key->tipe,
                                  'size' => 'lg',
                                  'weight' => 'bold',
                                ),
                                1 => 
                                array (
                                  'type' => 'text',
                                  'text' => 'Oleh : '.$key->owner,
                                  'margin' => 'lg',
                                  'size' => 'md',
                                  'align' => 'start',
                                  'gravity' => 'center',
                                  'weight' => 'bold',
                                ),
                                2 => 
                                array (
                                  'type' => 'box',
                                  'layout' => 'vertical',
                                  'spacing' => 'sm',
                                  'margin' => 'lg',
                                  'contents' => 
                                  array (
                                    0 => 
                                    array (
                                      'type' => 'box',
                                      'layout' => 'vertical',
                                      'spacing' => 'sm',
                                      'contents' => 
                                      array (
                                        0 => 
                                        array (
                                          'type' => 'text',
                                          'text' => 'Id Rumah :'.$key->id_rumah,
                                          'margin' => 'none',
                                          'size' => 'md',
                                          'color' => '#000000',
                                        ),
                                        1 => 
                                        array (
                                          'type' => 'text',
                                          'text' => 'Jumlah Lantai : '.$key->jumlah_lantai,
                                          'margin' => 'none',
                                          'size' => 'md',
                                          'color' => '#1D1D1D',
                                        ),
                                        2 => 
                                        array (
                                          'type' => 'text',
                                          'text' => 'Style Arsitektur : '.$key->style_arsitektur,
                                          'margin' => 'none',
                                          'size' => 'md',
                                          'color' => '#121212',
                                        ),
                                        3 => 
                                        array (
                                          'type' => 'text',
                                          'text' => 'Luas Bangunan : '.$key->luas_bangunan,
                                          'margin' => 'none',
                                          'size' => 'md',
                                          'color' => '#000000',
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            'footer' => 
                            array (
                              'type' => 'box',
                              'layout' => 'vertical',
                              'flex' => 0,
                              'spacing' => 'sm',
                              'contents' => 
                              array (
                                0 => 
                                array (
                                  'type' => 'button',
                                  'action' => 
                                  array (
                                    'type' => 'postback',
                                    'label' => 'Pilih',
                                    'text' => "Order ".$key->id_rumah,
                                    'data' => '#',
                                  ),
                                  'color' => '#FA4E4E',
                                  'style' => 'primary',
                                ),
                              ),
                            ),
                          );
                          array_push($itemsRumah,$itemRumah);
                        }
                        
                      }
                      $messages=[];
                      $msg1=$messageBuilder->text("Ini rekomendasi untuk kamu");
                      $msg2=array (
                        'type' => 'flex',
                        'altText' => 'Flex Message',
                        'contents' => 
                        array (
                          'type' => 'carousel',
                          'contents' => $itemsRumah
                        ),
                      );
                      array_push($messages,$msg1,$msg2);
                      $output=$this->reply($replyToken,$messages);
                      // RESET
                      $dataUpdate=array('map'=>'belum order','counter'=>0,'request'=>NULL);
                      $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                    }else{
                      $counter=1;
                      $request=$message['text']."#".$dataUser->request;
                      $dataUpdate=array('map'=>'desain','counter'=>$counter,'request'=>$request);
                      $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                      if($sql){
                        $explodeRequest=explode("#",$dataUser->request);
                        $messages=[];
                  $msg1=$messageBuilder->text("Mau beli rumah tipe jenis apa nih?");
                  $msg2=array (
                        'type' => 'flex',
                        'altText' => 'Flex Message',
                        'contents' => 
                        array (
                          'type' => 'carousel',
                          'contents' => 
                          array (
                            0 => 
                            array (
                              'type' => 'bubble',
                              'direction' => 'ltr',
                              'header' => 
                              array (
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'text',
                                    'text' => 'Rumah',
                                    'align' => 'center',
                                  ),
                                ),
                              ),
                              'footer' => 
                              array (
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'button',
                                    'action' => 
                                    array (
                                      'type' => 'message',
                                      'label' => 'Tipe 36',
                                      'text' => 'Tipe 36',
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            1 => 
                            array (
                              'type' => 'bubble',
                              'direction' => 'ltr',
                              'header' => 
                              array (
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'text',
                                    'text' => 'Rumah',
                                    'align' => 'center',
                                  ),
                                ),
                              ),
                              'footer' => 
                              array (
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'button',
                                    'action' => 
                                    array (
                                      'type' => 'message',
                                      'label' => 'Tipe 45',
                                      'text' => 'Tipe 45',
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            2 => 
                            array (
                              'type' => 'bubble',
                              'direction' => 'ltr',
                              'header' => 
                              array (
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'text',
                                    'text' => 'Rumah',
                                    'align' => 'center',
                                  ),
                                ),
                              ),
                              'footer' => 
                              array (
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'button',
                                    'action' => 
                                    array (
                                      'type' => 'message',
                                      'label' => 'Tipe 60',
                                      'text' => 'Tipe 60',
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            3 => 
                            array (
                              'type' => 'bubble',
                              'direction' => 'ltr',
                              'header' => 
                              array (
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'text',
                                    'text' => 'Rumah',
                                    'align' => 'center',
                                  ),
                                ),
                              ),
                              'footer' => 
                              array (
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'contents' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'button',
                                    'action' => 
                                    array (
                                      'type' => 'message',
                                      'label' => 'Tipe 70',
                                      'text' => 'Tipe 70',
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      );
                  array_push($messages,$msg1,$msg2);
                  $output=$this->reply($replyToken,$messages);
                      }
                    }
                  }
            }
            
      
        $client->replyMessage($output);


  }

  public function reply($replyToken,$messageArray){//Fungsi utama
      $reply=array('replyToken'=>$replyToken,'messages'=>$messageArray);
      return $reply;
  }

  public function push($id_user,$messageArray){
    $reply=array('to'=>$id_user,'messages'=>$messageArray);
    return $reply;
  }

  public function checkKeyword($masukan,$keyword){
    $w = new Winnowing($masukan, $keyword);
    $w->SetPrimeNumber(2);
    $w->SetNGramValue(2);
    $w->SetNWindowValue(3);
    $w->process();
    if($w->GetJaccardCoefficient()>50){
      return TRUE;
    }else{
      return FALSE;
    }
  }


}
