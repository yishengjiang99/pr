<?php

require_once("/var/www/lib/functions.php");
require_once("/var/www/html/pr/apns.php");

$tt=db::rows("select reminded, a.amount,c.token,b.username,b.id as uid, p.name,p.id,a.id as installid from sponsored_app_installs a join apps p on a.appid=p.id join appuser b
on a.uid=b.id join pushtokens c on b.idfa=c.idfa  where username!='yisheng' and uploaded_picture=0 and network!='virool' and network!='' and a.created>date_sub(now(), interval 10 day) group by b.id");

foreach($tt as $t){
$uid=$t['uid'];
$appname=$t['name'];
$appname=substr($appname,0,40);
$points=$t['amount'];
if($t['reminded']==1) continue;
$message="Eligibility Confirmed! Share a snapshot of '$appname'";
$iam="";
echo $message."\n";
$installid=$t['installid'];
$update="update sponsored_app_installs set reminded=1 where id=$installid";
echo "\n$update";
db::exec($update);
$url="";
 apnsUser($uid,$message,$iam,$url);
}

if(rand(0,5)!=4) exit;
$tt=db::rows("select reminded, a.amount,c.token,b.username,b.id as uid, p.name,p.id,a.id as installid from sponsored_app_installs a join apps p on a.appid=p.id join appuser b
on a.uid=b.id join pushtokens c on b.mac=c.mac_address  where a.created<date_sub(now(), interval 10 day) and username!='yisheng' and uploaded_picture=0 and network!='virool' and network!=''
and b.modified<date_sub(now(), interval 5 day) and b.stars<100 group by b.id");
foreach($tt as $t){
$uid=$t['uid'];
$appname=$t['name'];
$points=$t['amount'];
$message="Eligibility Confirmed! Share a snapshot of '$appname' for $points Points";
$iam="";
echo "10day:". $message."\n";
$installid=$t['installid'];
$update="update sponsored_app_installs set reminded=1 where id=$installid";
db::exec($update);
$url="";
 apnsUser($uid,$message,$iam,$url);
//exit;
}



