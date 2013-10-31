<?php
require_once("/var/www/lib/functions.php");

$uid=$_GET['uid'];
$offerId=$_GET['offerId'];
if(strpos($_GET['refId'],"_")!==FALSE){
 $t=explode("_",$_GET['refId']);
 $refId=intval($t[1]);
}else{
 $refId=intval($_GET['refId']);
}
$dealtype=$_GET['otype'];
$title='';
$id=md5($uid.time());
$msg="";
$points=0;

if($dealtype=="DoneApp" || $dealtype=="App"){
 $install=db::row("select * from sponsored_app_installs where id=$refId and uid=$uid");
 if(!$install){
    $msg="Try this app first";
 }
 if($install['network']=="santa"){
	$prevUploadedByThisUser=db::row("select * from UploadPictures where refId=$refId and uid=$uid");
	if($prevUploadedByThisUser){
		$msg="You already uploaded a picture for this";
        }else{
         db::exec("update sponsored_app_installs set uploaded_picture=1 where id=$refId");
         db::exec("insert into UploadPictures set uid='$uid',refId='$refId',id='$id',offer_id='$offerId', type='SANTA',title='$title',created=now(), reviewed=0,points_earned=$points");
         error_log("insert into UploadPictures set uid='$uid',refId='$refId',id='$id',offer_id='$offerId', type='SANTA',title='$title',created=now(), reviewed=0,points_earned=$points");
       	$msg="Thanks for uploading this screenshot. It will be reviewed by our editorial staff. Points awarded will be based on the quality of the picture";
	}
 }
 else if($install['uploaded_picture']==0){
	 $points=$install['Amount'];
	 db::exec("update appuser set stars=stars+".$points." where id=$uid");
   	db::exec("update sponsored_app_installs set uploaded_picture=1 where id=$refId");
	$title=getRealIP();
	db::exec("insert into UploadPictures set uid='$uid',refId='$refId',id='$id',offer_id='$offerId', type='$dealtype',title='$title',created=now(), reviewed=0,points_earned=$points");
 }else{
    $msg="You already uploaded a picture for this offer";
 }
}
else if($dealtype=='UserOffers'){
 $prevUploadedByThisUser=db::row("select * from UploadPictures where refId=$refId and uid=$uid");
 if(!$prevUploadedByThisUser){
	$recent=db::row("select count(1) as cnt from UploadPictures where uid=$uid and created>date_sub(now(), interval 60 minute) and type='UserOffers'");
	if($recent['cnt']>3) die("0|".$id.".jpeg|http://www.json999.com/pr/postPicture.php|Please slow down and upload quality pictures.|");
	$recent=db::row("select count(1) as cnt from UploadPictures where uid=$uid and created>date_sub(now(), interval 24 hour) and type='UserOffers'");
	if($recent['cnt']>20) die("0|".$id.".jpeg|http://www.json999.com/pr/postPicture.php|Please slow down and upload quality pictures.|");
	$offer=db::row("select * from PictureRequest where id=$refId");
	$points=$offer['cash_bid'];
 	$offeringUid=$offer['uid'];
	$title=$offer['title'];
	if($uid==$offeringUid) $points=0;
        $offeringUser=db::row("select * from appuser where id=$offeringUid");
	$status=1;
	$uploadCount=$offer['uploadCount'];
        $cap=$offer['max_cap'];
        if($offeringUser['stars']-$points<=0){
		db::exec("update PictureRequest set status=-1,cash_bid=0 where id=$refId limit 1");
		$msg="The seller ran out of money :(";	
		$points=0;		
	}else{
  	        if($offer['status']!=3){
			db::exec("update appuser set stars=stars-".$points." where id=$offeringUid");
			db::exec("update appuser set xp=xp+".($points*20)." where id=$offeringUid limit 1");
			if($uploadCount+1>=$cap){
         			       $status=-1;
        		}
		}else{
			$status=3;
		}
		db::exec("update appuser set stars=stars+".$points." where id=$uid limit 1");
	}
	db::exec("insert into UploadPictures set uid='$uid',refId='$refId',id='$id',type='$dealtype',title='$title',created=now(), reviewed=1,points_earned=$points");
	error_log("insert into UploadPictures set uid='$uid',refId='$refId',id='$id',type='$dealtype',title='$title',created=now(), reviewed=1,points_earned=$points");
	db::exec("update PictureRequest set uploadCount=uploadCount+1, status=$status where id=$refId");
	require_once("/var/www/html/pr/apns.php");
	if(rand(1,5)==4 && $uploadCount<$cap) apnsUser($offeringUid,"Someone post a picture to your title","");
  }else{
 	$points=0;
	$msg="You already uploaded a picture for this offer";
  }
}
if(!$points) $points=0;
if($points>0){
  $user=db::row("select * from appuser where id=$uid");
  checkBonusInviter($user);
}
echo trim($points."|".$id.".jpeg|http://www.json999.com/pr/postPicture.php|$msg|You earned $points for this picture");
error_log($points."|".$id.".jpeg|http://www.json999.com/pr/postPicture.php|$msg|You earned $points for this picture!");
?>
