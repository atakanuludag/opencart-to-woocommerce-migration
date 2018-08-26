<?php
$wpUrl = 'https://www.url.com'; //Wordpress url
$token = 'xxx';  //Consumer Key
$tokenSecret = 'xxx'; //Consumer Secret

$txtFile = "counter.txt";

try {
     $db = new PDO( "mysql:host=localhost;dbname=opencart_db;charset=utf8", "user", "password" );
} catch ( PDOException $e ){
     print $e->getMessage();
}


function getCounter(){
	global $txtFile;

	$counterTXT = fOpen($txtFile,"r");
	$TXTRead = fRead($counterTXT,fileSize($txtFile));
	$TXTRead = (int)$TXTRead;
	fClose($counterTXT);

	return $TXTRead;
}



function counter(){
    global $txtFile;
    global $TXTRead;
    $open = fopen ($txtFile , 'w') or die ("File not open");
   	fwrite($open, $TXTRead + 1);
   	fclose($open);
}