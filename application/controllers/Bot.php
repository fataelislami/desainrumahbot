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
    $channelAccessToken = '7uT2ARjrbDNeK9RAqnBFiU1e7K81EYyg0WsLP1m6dFn/hAPvLftv7ckLAJXyEJ6h50omkzJy38a9gXJo6jwowWXQ36PCWCQO6mPI1hh0JnLiY3tXPqnRz0Bdmm4QuT4PYWssAUf7TlcaDI/Ked41EwdB04t89/1O/w1cDnyilFU=';
    $channelSecret = '676fd251b52ba4464be4d8518d9b38ca';//sesuaikan
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
              if ($event['type'] == 'follow')//Yang bot lakukan pertama kali saat di add oleh user
              {
                $dataInsert=array(
                  'id_users'=>$userId,
                  'nama'=>$nama,
                  'map'=>'standby',
                  'counter'=>0
                );
                $sql=$this->Dbs->insert($dataInsert,'users');
                if($sql){
                  $pre=array($messageBuilder->text("Daftar berhasil"));
                  $output=$this->reply($replyToken,$pre);
                }else{
                  $pre=array($messageBuilder->text("Daftar gagal"));
                  $output=$this->reply($replyToken,$pre);
                }
              }
              $dataUser=$this->Dbs->getdata(array('id_users'=>$userId),'users')->row();
                      // Order dan simpan ke database
            if(substr($inputMessage,0,5) == "ORDER"){
              $explodeInput=explode(" ",$inputMessage);//pecah input berdasarkan spasi
              $id_rumah=$explodeInput[1];
              $dataInsert=array(
                'id_users'=>$userId,
                'id_rumah'=>$id_rumah,
              );
              $sql=$this->Dbs->insert($dataInsert,'pesanan');
              if($sql){
                $pre=array($messageBuilder->text("Pesanan Berhasil dilakukan, Silakan lakukan pembayaran ke no : 0897XXXXX"));
                $output=$this->reply($replyToken,$pre);
              }else{
                $pre=array($messageBuilder->text("Pesanan gagal di proses"));
                $output=$this->reply($replyToken,$pre);
              }
            }
            if($inputMessage == 'MULAI'){
              $counter=1;
              $dataUpdate=array('map'=>'desain','counter'=>$counter);
              $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
              if($sql){
                $pre=array($messageBuilder->text("Mau beli rumah tipe jenis apa nih?"));
                $output=$this->reply($replyToken,$pre);
              }
            }
            // Map Desain
            if(substr($dataUser->map,0,6)=='desain'){
                  if($inputMessage == 'RESET'){
                    $dataUpdate=array('map'=>'standby','counter'=>0,'request'=>NULL);
                    $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                    if($sql){
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
                        $pre=array($messageBuilder->text("Jumlah lantainya berapa?"));
                        $output=$this->reply($replyToken,$pre);
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
                        $pre=array($messageBuilder->text("Mau style arsitektur seperti apa?"));
                        $output=$this->reply($replyToken,$pre);
                      }
                  }else 
                  // Q3
                  if(substr($dataUser->map,0,6)=='desain' && $dataUser->counter==3){
                    $counter=$dataUser->counter+1;
                      $request=$message['text']."#".$dataUser->request;
                      $dataUpdate=array('map'=>'desain','counter'=>$counter,'request'=>$request);
                      $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                      if($sql){
                        $pre=array($messageBuilder->text("Luas bangunannya berapa?"));
                        $output=$this->reply($replyToken,$pre);
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
                        $pre=array($messageBuilder->text("Bot konfirmasi dulu ya, untuk pesanan desain kamu adalah : \nTipe Rumah: ".$explodeRequest[2]."\nJumlah Lantai: ".$explodeRequest[1]."\nStyle Arsitektur: ".$explodeRequest[0]."\nLuas Bangunan: ".$message['text']));
                        $output=$this->reply($replyToken,$pre);
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
                        'luas_bangunan'=>$explodeRequest[0]
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
                      $dataUpdate=array('map'=>'standby','counter'=>0,'request'=>NULL);
                      $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                    }else{
                      $counter=1;
                      $request=$message['text']."#".$dataUser->request;
                      $dataUpdate=array('map'=>'desain','counter'=>$counter,'request'=>$request);
                      $sql=$this->Dbs->update(array('id_users'=>$userId),$dataUpdate,'users');
                      if($sql){
                        $explodeRequest=explode("#",$dataUser->request);
                        $pre=array($messageBuilder->text("Ulangi ya, \nMau beli rumah tipe jenis apa nih?"));
                        $output=$this->reply($replyToken,$pre);
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
