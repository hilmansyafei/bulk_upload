<?php
require_once 'PHPExcel.php';
require_once 'vendor/autoload.php';

$client = new GuzzleHttp\Client();


echo '<pre>';
$getDataPost = $_POST;

if (empty($_POST)) {
  echo "Silahkan akses melalui form \n";
  echo "<a href='http://localhost/bulk_upload' >Kembali </a>";
  die();
}
$username = $getDataPost['username'];
$password = $getDataPost['password'];
$pin = $getDataPost['pin'];
$id = $getDataPost['id'];
$host = $getDataPost['host'];
$uploaddir = 'file_bulk/';

// check file for safety
$checkFile = scandir($uploaddir);
if (count($checkFile) > 2) {
  echo "Silahkan hapus file pada folder $uploaddir \n";
  echo "<a href='http://localhost/bulk_upload' >Kembali </a>";
  die();
}

$fileName = $_FILES['file_bulk']['name'];
$splitName = explode(".", $fileName);
$newFileName = time().".".end($splitName);
$uploadfile = $uploaddir . basename($newFileName);


if (move_uploaded_file($_FILES['file_bulk']['tmp_name'], $uploadfile)) {
  echo "File is valid, and was successfully uploaded.\n\n";
} else {
  echo "Failed upload file!\n";
  die();
}

$objPHPExcel = PHPExcel_IOFactory::load($uploadfile);
$worksheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

$report = array();

for ($i=2; $i <= count($worksheet); $i++) { 
  for ($j=0; $j < 10; $j++) { 
    $noTujuan = $worksheet[$i]['A'];
    $kdProduk = $worksheet[$i]['B'];
    $trxOrderID = bin2hex(random_bytes(13));

    $res = $client->request('GET', "$host?id=$id&pin=$pin&user=$username&pass=$password&kodeproduk=$kdProduk&tujuan=$noTujuan", [
      'connect_timeout' => 60,
      'timeout' => 60
    ]);

    echo "Order ID : $trxOrderID \n";
    echo "No Tujuan : $noTujuan \n";
    echo "Produk Alias : $kdProduk \n";
    echo "Status : ".$res->getStatusCode()." \n";
    echo "Body : ".$res->getBody()." \n\n";

    if (array_key_exists($res->getStatusCode(),$report)) {
      $report[$res->getStatusCode()] = $report[$res->getStatusCode()] + 1;
    }else{
      $report[$res->getStatusCode()] = 1;
    }
  }
}

print_r($report);
?>