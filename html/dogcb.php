<?php
require_once("/var/www/lib/functions.php");
//if($_GET['pw']!="dafhfadsfkdsadlds") die(0);
$appid=intval($_GET['appid']);
$uid=intval($_GET['uid']);
$user=db::row("select * from appuser where id=$uid");
$ltv=$user['ltv'];
$dogmulti=300;
if($ltv<100) $dogmulti=500;
$points=$_GET['payout']*$dogmulti;
$points=min(500,$points);
//dogcb.php?uid=2902&amount=15&appid=468420446&pw=dafhfadsfkdsadlds&payout=0.5

$revenue=doubleval($_GET['payout'])*100;
db::exec("insert ignore into sponsored_app_installs set uid=$uid, Amount=$points, revenue='$revenue', appid=$appid, network='appdog', subid='$uid_$appid',offer_id=$appid, created=now()");
$appstr=file_get_contents("http://json999.com/appmeta.php?appid=$appid");
$app=json_decode($appstr,1);
$appname=$app['Name'];
$message="Thanks for trying $appname! Share a screenshot on Picture Rewards!";
$msg="Thanks for trying $appname. Upload a picture for $points points";
require_once("/var/www/html/pr/apns.php");
apnsUser($uid,$msg);
