<?php
require_once 'PHPExcel.php';

$getDataPost = $_POST;

$username = $getDataPost['username'];
$password = $getDataPost['password'];
$pin = $getDataPost['pin'];

$uploaddir = 'file_bulk/';
$fileName = $_FILES['file_bulk']['name'];

$splitName = explode(".", $fileName);
$newFileName = time().".".end($splitName);
$uploadfile = $uploaddir . basename($newFileName);

echo '<pre>';
if (move_uploaded_file($_FILES['file_bulk']['tmp_name'], $uploadfile)) {
  echo "File is valid, and was successfully uploaded.\n";
} else {
  echo "Failed upload file!\n";
  die();
}

$objPHPExcel = PHPExcel_IOFactory::load($uploadfile);
$worksheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

echo "<pre>";print_r($worksheet);
for ($i=2; $i <= count($worksheet); $i++) { 
  for ($j=0; $j < 100; $j++) { 
    $noTujuan = $worksheet[$i]['A'];
    $kdProduk = $worksheet[$i]['B'];
    $data = file_get_contents("https://webhook.site/b3f813df-292d-44d4-85be-24a45e69a90b?pin=$pin&username=$username&password=$password&kd_produk=$kdProduk&no_tujuan=$noTujuan");
    echo "JALAN $j";

    echo $data."\n";
  }
  
}
?>