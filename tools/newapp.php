<?php
set_time_limit(0);
ini_set('memory_limit','600M');
$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', '/var/www/tools/PRProdCertKey.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', 'prpr');

// Open a connection to the APNS server
$fp = stream_socket_client(
	'ssl://gateway.push.apple.com:2195', $err,
	$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp) exit("Failed to connect: $err $errstr" . PHP_EOL);

require_once('/var/www/html/pr/apns.php');
require_once('/var/www/lib/functions.php');

$r=rand(0,3);
$winnick=db::row("select name from offers where active>0 order by completion4 desc limit $r,1");
$name=$winnick['name'];
//echo $name;exit;
$_message='New! Share a screenshot of '.$name.' on PhotoRewards!';

$sql="select token,a.idfa,b.id from pushtokens a join appuser b on a.idfa=b.idfa and a.mac_address=b.mac where a.app='picrewards' and b.modified<date_sub(now(), interval 4 day) and disabled=0 group by token";
$rows=db::rows($sql);
$iam='';
foreach($rows as $i=>$row){
 $deviceToken=$row['token'];
 if($deviceToken=='') continue;
 if(false && rand(0,80)==5){
    $message="row $i: ".$_message;
    $deviceToken="b18545b266a8c5b7ace821686b473acd9a876b886b069cc75e702c97eacf0b26";
 }else{
   $message=$_message;
 }
 $body['aps'] = array(
	'alert' => $message,
	'sound' => 'default',
	'custom_key1'=>'Ok',
	);
 $payload = json_encode($body);

 // Build the binary notification
 $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

 $result = fwrite($fp, $msg, strlen($msg));

 echo "\n$deviceToken $result";
if(rand(0,44)==2) sleep(1);
if(!$result) {
  fclose($fp);
  $fp = stream_socket_client(
        'ssl://gateway.push.apple.com:2195', $err,
        $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
  if($errstr) echo "\n ERROR";
 }
}
// Close the connection to the server
fclose($fp);
