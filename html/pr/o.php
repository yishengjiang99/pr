<?php
require_once("/var/www/lib/functions.php");
require_once("/var/www/html/pr/levels.php");
//session_start();

$idfa=$_GET['idfa'];
$mac=$_GET['mac'];
$uid=$_GET['uid'];
//$_SESSION['ids']="$idfa|$mac|$uid";

$smap=array();
$start=intval($_GET['start']);
if(!$start) $start=0;
$o=array();
if($start>20){
}
if(true){
 $sql="select uploaded_picture, 'DoneApp' as OfferType, 'Eligibility Confirmed!' as Action, s.offer_id,  s.id as refId, appid as StoreID, a.Name, a.IconURL,s.amount as Amount, 1 as canUpload from sponsored_app_installs s left join apps a on s.appid=a.id where uid=$uid and network not in ('virool', 'santa')";
 $rows=db::rows($sql);
 foreach($rows as $r){
  $smap[$r['StoreID']]=1;
  if($r['uploaded_picture']==1) continue;
  if(!$r['Name'] && $r['offer_id']){
    $sql="select name, thumbnail from offers where id=".$r['offer_id'];
    $offerdb=db::row($sql);
    $r['Name']=$offerdb['name'];
    $r['IconURL']=$offerdb['thumbnail'];
  }
  $o[]=$r;
 }
}
$badge=array();
if($start==10){
 $goodever=explode(",",file_get_contents("/var/www/html/pr/goodever.json"));
 $everbadge="http://api.everbadge.com/offersapi/offers/json?api_key=9B8yxsmXx7xv7ujVFYJNf1373448697&os=ios&country_code=US&t=".time();
 $everbadgeOffers=json_decode(file_get_contents($everbadge),1);
 $et=$everbadgeOffers['data']['offers'];
 foreach($et as $row){
  $off['OfferType']="App";
  $off['Action']="Share a Screenshot of this app";
  $preview=explode("id",$row['preview_url']);
  if(!isset($preview[1])) continue;
  $off['StoreID']=$preview[1];
  $subid=$uid.",".$off['StoreID'];
  $off['RedirectURL']=$row['offer_url']."&device_id=$mac&aff_sub=$subid";
  $off['IconURL']=$row['thumbnail_url'];
  $off['hint']="Free App";
  $off['Name']=$row['public_name'];
  $off['refId']=$preview[1];
  if(isset($smap[$off['refId']])){
     continue;
  }
   if(!in_array($off['StoreID'],$goodever)){
      if(rand(0,5)==1) {
//           error_log("giving ".$off['StoreID']." a try");
       }
      else { 
//	error_log("skipping ".$off['StoreID']."");
	continue;
	}
   }
  $smap[$off['refId']]=1;
  $payout=$row['payout']*300;
  $off['Amount']="".$payout;
  $off['Action']="Share a Screenshot of This App";
  $badge[]=$off;
 }
}

$offers=db::rows("select a.id as offer_id,active, affiliate_network, b.IconURL, click_url as RedirectURL, 'Free' as Cost,completions, a.name as Name,'App' as OfferType,thumbnail,storeID as StoreID, cash_value as Amount, description as Action
from offers a left join apps b on a.storeID=b.id where platform like '%iOS%' and active>0 order by active desc, completions desc limit $start, 15");

/*
$url="http://api.appdog.com/offerwall?limit=10&offset=$start&type=json&source=9135311512939222220&idfa=$idfa&fbid=$uid&mac=$mac";
error_log($url);
$offers=json_decode(file_get_contents($url),1);
*/

$rayoffers=array();
$showpts=rand(0,5)==2;
$showpts=1;
foreach($offers as $offer){
 if($offer['OfferType']!="App") continue;
 if($offer['Cost']!="Free") continue;
 $subid=$uid.",".$offer['offer_id'];
 $aff=$offer['affiliate_network'];
 $active=$offer['active'];
 $offer['Action']="Share a Screenshot of This App";
 if($mac!='18:34:51:1A:B1:3B' && $mac!='A0:ED:CD:75:37:88' && $active==2 ) {
  continue;
 }
 if($active==2){
   $offer['Action']="TESTING: ".$aff." id".$subid;
 }
 $offer['RedirectURL']=str_replace("SUBID_HERE",$subid,$offer['RedirectURL']);
 $offer['RedirectURL']=str_replace("IDFA_HERE",$idfa,$offer['RedirectURL']);
 $offer['RedirectURL']=str_replace("MAC_HERE",$mac,$offer['RedirectURL']);
 $points=intval($offer['Amount'])*10;
 $offer['OfferType']="App";
 $offer['Name'] = str_ireplace("download ","",$offer['Name']);
 if(!$offer['IconURL']) $offer['IconURL']=$offer['thumbnail'];
 $offer['Amount']="Free";
 $completions=intval($offer['completions']);
 if($showpts && $completions>0) $offer['Amount']=$points."";
 $offer['hint']="Download";
 if($offer['offer_id']==53){
    $offer['RedirectURL']=str_replace("UID_HERE",$uid,$offer['RedirectURL']);
    $offer['Action']="Win a $19.99 xBOX Gift Card";
    $offer['hint']="Details";
    $offer['Amount']="20k";
 }
 if($offer['StoreID']==111){
   $offer['Action']="Instant Points"; 
   $offer['hint']="Get Points";
 }

 $offer['refId']=$offer['StoreID'];
 $offer['canUpload']=0;
 if(isset($smap[$offer['refId']])) continue;
 $smap[$offer['refId']]=1;
 unset($offer['completions']);
 unset($offer['affiliate_network']);
 $rayoffers[]=$offer;
}

if(true){
 $o=array_merge($o,$badge);
 $o=array_merge($o,$rayoffers);
}else{
 $o=array_merge($o,$rayoffers);
 $o=array_merge($o,$badge);
}
$uo=array();
$sql="select 'UserOffers' as OfferType, 'Take a picture' as Action, b.fbid, a.id as refId,'localt' as IconURL, title as Name, url as RedirectURL, category as c2, cash_bid as Amount, 1 as canUpload,b.username ";
$sql.="from PictureRequest a join appuser b on a.uid = b.id where status>0 order by uploadCount limit $start, 10";	
$uo=db::rows($sql);
foreach($uo as $offer){
 $subid=$uid."_1337";
 $offer['IconURL']="http://grepawk.s3.amazonaws.com/pr2logo_blue.png";
 if($offer['Name']=="(null)") $offer['Name']="Title";
 $offer['Action']=$offer['c2']." (from ".$offer['username'].")";
 $offer['IconURL']="https://graph.facebook.com/".$offer['fbid']."/picture?width=200&height=200";
 $o[]=$offer;
}
$user=db::row("select * from appuser where id=$uid");
$xpinfo=getBonusPoints($user['xp']);
$canEnterBonus = $user['has_entered_bonus'] == 1 ? 0 : 1;
$st=$uid % 2;
//uncomment this when app's under review
$ret=array(
"offers"=>$o,
"fb"=>1,
"invite"=>$xpinfo['minbonus'],
"inviteUpper"=>$xpinfo['maxbonus'],
"enterbonus"=>$canEnterBonus,
"st"=>$st,
);
die(json_encode($ret));
