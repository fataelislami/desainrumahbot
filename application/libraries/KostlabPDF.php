<?php
/**
 *Contoh penggunaan
 *Panggil library
 *$this->load->library('kostlabpdf');
 *$this->kostlabpdf->generate();
 *$pdf = new FPDF('L','mm','A4'); //Hidupkan Kelas FPDF
 *$image1 = base_url()."assets-front/images/certificate-clean.png";//Data Gambar
 *add new freescript font
 *$pdf->AddFont('Comforta','','Comforta.php');//Tambahkan jenis Font yang sudah di convert ke PHP
 *add new jokerman font
 *$pdf->AddFont('Sourcesans','','SourceSansPro-Regular.php');
 *$pdf->AddPage();//Untuk Menambah Page
 *$pdf->Image($image1, 0, 0, 297);
 *Training
 *$pdf->SetFont('Comforta','',18);
 *$pdf->SetXY(125, 58);
 *$pdf->SetTextColor(0,204,153);
 *$pdf->Cell( 0, 0, 'no.19/sert-pb02/September/2019', 0, 0, 'L' );
 *$pdf->SetFont('Sourcesans','',18);
 *$pdf->SetXY(60, 90);
 *$pdf->SetTextColor(87,86,86);
 *$pdf->Cell( 0, 0, 'Diberikan Kepada:', 0, 0, 'L' );
 *$pdf->SetFont('Comforta','',40);
 *$pdf->SetXY(61, 105);
 *$pdf->SetTextColor(0,204,153);
 *$pdf->Cell( 0, 0, 'Fata El Islami', 0, 0, 'L' );
 *$pdf->Output();
 */
 require('fpdf17/fpdf.php');

class KostlabPDF
{

  function __construct()
  {
    // code...
  }

  function generate(){

  }

}
 ?>
