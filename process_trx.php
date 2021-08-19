<?php
require_once 'PHPExcel.php';
require_once 'vendor/autoload.php';
$client = new GuzzleHttp\Client();

echo '<pre>';
// get all data post
$getDataPost = $_POST;

// check emty data
if (empty($getDataPost)) {
  echo "Silahkan akses melalui form \n";
  echo "<a href='http://localhost/bulk_upload'> Kembali </a>";
  die();
}

// initiate data
$username = $getDataPost['username'];
$password = $getDataPost['password'];
$pin = $getDataPost['pin'];
$id = $getDataPost['id'];
$host = $getDataPost['host'];
$uploaddir = 'file_bulk/';

// check file for safety
$checkFile = scandir($uploaddir);
if (count($checkFile) > 2) {
    // check delete old mode
    if (!isset($_POST['delete_old'])) {
        echo "Silahkan hapus file pada folder $uploaddir \n";
        echo "<a href='http://localhost/bulk_upload'> Kembali </a>";
        die();  
    }
    // delete file
    unlink($uploaddir.$checkFile[2]);
}

// initiate file name
$fileName = $_FILES['file_bulk']['name'];
$splitName = explode(".", $fileName);
$newFileName = time().".".end($splitName);
$uploadfile = $uploaddir . basename($newFileName);

// upload file
if (move_uploaded_file($_FILES['file_bulk']['tmp_name'], $uploadfile)) {
    echo "File is valid, and was successfully uploaded.\n\n";
} else {
    echo "Failed upload file!\n";
    die();
}

// initiate excel reader
$objPHPExcel = PHPExcel_IOFactory::load($uploadfile);
$worksheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
$report = array();

// loop data excel
for ($i=2; $i <= count($worksheet); $i++) { 
    $noTujuan = str_replace("'","",$worksheet[$i]['A']);
    if ($noTujuan != "") {
        $kdProduk = $worksheet[$i]['B'];
        $trxOrderID = bin2hex(random_bytes(13));

        // hit trx
        $res = $client->request('GET', "$host?id=$id&pin=$pin&user=$username&pass=$password&kodeproduk=$kdProduk&tujuan=$noTujuan&idtrx=$trxOrderID", [
            'connect_timeout' => 60,
            'timeout' => 60,
            'http_errors' => false
        ]);

        echo "Order ID : $trxOrderID \n";
        echo "No Tujuan : $noTujuan \n";
        echo "Produk Alias : $kdProduk \n";
        echo "Status Code: ".$res->getStatusCode()." \n";
        echo "Body Response : ".$res->getBody()." \n\n";

        // collect data by status code
        if (array_key_exists($res->getStatusCode(),$report)) {
            $report[$res->getStatusCode()] = $report[$res->getStatusCode()] + 1;
        }else{
            $report[$res->getStatusCode()] = 1;
        }
        sleep(0.5);
    }
}

// generate report information
echo "-----------------------------------------\n";
echo "<b>REPORT \n</b>";
echo "-----------------------------------------\n";
foreach ($report as $key => $value) {
    if ($key != 201) {
        echo "Transaksi Gagal (".$key.") : ".$value."\n";
    }else{
        echo "Transaksi Success (".$key.") : ".$value."\n";
    }
}
echo "-----------------------------------------\n";
echo "<a href='http://localhost/bulk_upload'> Kembali </a>";
?>