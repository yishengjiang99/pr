<?
require_once("/var/www/lib/functions.php");
$uid=intval($_GET['uid']);
$refstr=$_GET['refId'];
$appId=intval($refstr);
$install=db::row("select * from sponsored_app_installs where uid=$uid and appid=$appId");
if($install) {
 die($install['id']."");
}else if($otherInstall=db::row("select * from sponsored_app_installs where id=$appId")){
 die($otherInstall['id']);
}
else {
 $tried=db::row("select * from sponsored_app_tried_upload where uid=$uid and appid=$appId");
 if($tried && $tried['count']>1){
   db::exec("insert ignore into sponsored_app_installs set uid=$uid, appid=$appId,Amount=0, created=now(),revenue=0, network='santa', uploaded_picture=0");
   $installId=db::lastID();
   die($installId);
 }
 db::exec("insert ignore into sponsored_app_tried_upload set uid=$uid,count=1, appid=$appId,created=now() on duplicate key update count = count+1");
 die("no");
}

