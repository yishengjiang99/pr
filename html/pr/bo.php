<?php
require_once("/var/www/lib/functions.php");
require_once("/var/www/html/pr/levels.php");

$idfa=$_GET['idfa'];
$mac=$_GET['mac'];
$uid=$_GET['uid'];
$user=db::row("select * from appuser where id=$uid");
$xpinfo=getBonusPoints($user['xp']);
$canEnterBonus = $user['has_entered_bonus'] == 1 ? 0 : 1;
$st=1;
$deviceInfo=$user['deviceInfo'];
$device="iphone";
if(stripos($deviceInfo,"ipod")!==false){
 $device='ipod';
}
if(stripos($deviceInfo,"ipad")!==false){
 $device='ipad';
}
$ua=$_SERVER['HTTP_USER_AGENT'];
$reviewer=0;
if(strpos($ua,"PictureRewards/1.4")!==false){
 $reviewer=1;
}
$smap=array();
$start=intval($_GET['start']);
if(!$start) $start=0;
$o=array();
$vcount=$user['visit_count'];
$fbliked=$user['fbliked'];

if($start==10 ||  ($fbliked==1 && $start==0)){
 $code=$user['username'];
 $message="Try apps and upload screen shots for more points. 1000 Points = $1 in PayPal Cash, Amazon or iTune Gift Cards";
 $url="https://www.facebook.com/dialog/apprequests?app_id=146678772188121&message=".urlencode($message)."&display=touch&redirect_uri=https://www.json999.com/redirect.php?from=invideDone$uid";
 $o[]=array("Name"=>"Invite Friends for XP","Amount"=>"XP","Action"=>"5 XP for each friend", "hint"=>"Invite Friends","canUpload"=>1,"OfferType"=>"CPA","RedirectURL"=>$url,
"refId"=>577,"IconURL"=>"https://fbcdn-profile-a.akamaihd.net/hprofile-ak-prn2/c35.35.442.442/s200x200/1239555_295026823968647_399436309_n.png");
}

if($start==0 && $vcount>0 && ($fbliked==0 || $uid==2902)){
  $mid=md5($uid.$idfa."fblikeh");
  $o[]=array("Name"=>"Like us on Facebook","Amount"=>"20","Action"=>"Get real-time updates on offers","canUpload"=>1,"OfferType"=>"CPA",
  "RedirectURL"=>"http://json999.com/pr/fblike.php?uid=$uid&h=$mid","IconURL"=>"https://fbcdn-profile-a.akamaihd.net/hprofile-ak-prn2/c35.35.442.442/s200x200/1239555_295026823968647_399436309_n.png","hint"=>"Go to FB",
  "refId"=>577);
}
$showpts=1;
if(true){
 $sql="select uploaded_picture, 'DoneApp' as OfferType, 'Eligibility Confirmed!' as Action, s.offer_id,  s.id as refId, appid as StoreID, a.Name, a.IconURL,s.amount as Amount, 1 as canUpload from sponsored_app_installs s left join apps a on s.appid=a.id where uid=$uid and network not in ('virool', 'santa')";
 $rows=db::rows($sql);
 foreach($rows as $r){
  $smap[$r['StoreID']]=1;
  if($r['StoreID']==5432) continue;
  if($r['uploaded_picture']==1) continue;
  if(!$r['Name'] && $r['offer_id']){
    $sql="select name, thumbnail from offers where id=".$r['offer_id'];
    $offerdb=db::row($sql);
    $r['Name']=$offerdb['name'];
    $r['IconURL']=$offerdb['thumbnail'];
  }
  if($start==0) $o[]=$r;
 }
}

$offers=db::rows("select a.id as offer_id,active, affiliate_network, b.IconURL, click_url as RedirectURL, platform, 'Free' as Cost,completions, a.name as Name,'App' as OfferType,thumbnail,storeID as StoreID, cash_value as Amount, 
description as Action,completion4
from offers a left join apps b on a.storeID=b.id where platform like '%iOS%' and active>0 order by active desc, completions desc limit $start, 45");

/*
$url="http://api.appdog.com/offerwall?limit=10&offset=$start&type=json&source=9135311512939222220&idfa=$idfa&fbid=$uid&mac=$mac";
error_log($url);
$offers=json_decode(file_get_contents($url),1);
*/

$rayoffers=array();
foreach($offers as $offer){
 $platformT=explode("-",strtolower($offer['platform']));
 if(isset($platformT[1])){
  $supported=explode(",",trim($platformT[1]));
  if(!in_array($device,$supported)){
     continue;
  }
 }
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
 $offer['RedirectURL']=str_replace("HMAC_HERE",md5($mac),$offer['RedirectURL']);
 $offer['RedirectURL']=str_replace("SUBID_HERE",$subid,$offer['RedirectURL']);
 $offer['RedirectURL']=str_replace("IDFA_HERE",$idfa,$offer['RedirectURL']);
 $offer['RedirectURL']=str_replace("MAC_HERE",$mac,$offer['RedirectURL']);
 $points=intval($offer['Amount'])*10;
 $offer['OfferType']="App";
 $offer['Name'] = str_ireplace("download ","",$offer['Name']);
 if(!$offer['IconURL']) $offer['IconURL']=$offer['thumbnail'];
 $completions=intval($offer['completion4']);
 if($completions<1){
  if($vcount<5 || rand(0,10)<5) continue;
 }

 $offer['Amount']=$points."";
 if($showpts==0 || $reviewer==1) $offer['Amount']="Free"; 

 $offer['hint']="Download";
 if($offer['offer_id']==53){
    $offer['RedirectURL']=str_replace("UID_HERE",$uid,$offer['RedirectURL']);
    $offer['Action']="Win a $19.99 xBOX Gift Card";
    $offer['hint']="Details";
    $offer['Amount']="20k";
 }
 if($offer['StoreID']<1000){
   $offer['Action']="Instant Points"; 
   $offer['hint']="Get Points";
   $offer['OfferType']="CPA";
 }

 $offer['refId']=$offer['StoreID'];
 $offer['canUpload']=0;
 if(isset($smap[$offer['refId']])) continue;
 $smap[$offer['refId']]=1;
 unset($offer['completions']);
 unset($offer['affiliate_network']);
 $rayoffers[]=$offer;
}


$badge=array();
if($start<=10){
 $goodever=explode(",",file_get_contents("/var/www/html/pr/goodever.json"));
 $data=json_decode(file_get_contents("/var/www/cache/badgecache"),1);
 if(!$data || $data['ttl']<time()){
        $everbadge="http://api.everbadge.com/offersapi/offers/json?api_key=9B8yxsmXx7xv7ujVFYJNf1373448697&os=ios&country_code=US&t=".time();
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $everbadge);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $badgeStr=curl_exec($ch);
        $everbadgeOffers = json_decode($badgeStr,1);
        curl_close($ch);
	error_log("calling $everbadge");
        $data=array("rows"=>$everbadgeOffers, "ttl"=>time()+60*5);
        file_put_contents("/var/www/cache/badgecache",json_encode($data));
 }
 $everbadgeOffers=$data['rows'];
 $et=$everbadgeOffers['data']['offers'];

 foreach($et as $row){
  $off['OfferType']="App";
  $off['Action']="Share a Screenshot of this app";
  $preview=explode("id",$row['preview_url']);
  if(!isset($preview[1]) || intval($preview[1])==0) continue;
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
      if($vcount<10 || rand(0,7)!=1) { // error_log("giving ".$off['StoreID']." a try");
         continue;
	}
   }
  if($device=="ipod" && stripos($row['description'],"ipod")!==false) continue;

  $smap[$off['refId']]=1;
  $payout=$row['payout']*200;
  $off['Amount']="".$payout;
  if($reviewer==1 || $showpts==0) $off['Amount']="Free";
  $off['Action']="Share a Screenshot of This App";
  $badge[]=$off;
 }
}

$offers=db::rows("select a.id as offer_id,active, affiliate_network, b.IconURL, click_url as RedirectURL, platform, 'Free' as Cost,completions, a.name as Name,'App' as OfferType,thumbnail,storeID as StoreID, cash_value as Amount, 
description as Action from offers a left join apps b on a.storeID=b.id where platform like '%iOS%' and active>0 order by active desc, completions desc limit $start, 10");

/*
$url="http://api.appdog.com/offerwall?limit=10&offset=$start&type=json&source=9135311512939222220&idfa=$idfa&fbid=$uid&mac=$mac";
error_log($url);
$offers=json_decode(file_get_contents($url),1);
*/

if(false){
 $o=array_merge($o,$badge);
 $o=array_merge($o,$rayoffers);
}else{
 $o=array_merge($o,$rayoffers);
 $o=array_merge($o,$badge);
}
$uo=array();
if($start==20 || $start==10){
 if($start==10) $dd='desc';
 else $dd='asc';
 $ustart=$start-10;
 $sql="select 'UserOffers' as OfferType, 'Take a picture' as Action, b.fbid, a.id as refId,'localt' as IconURL, title as Name, url as RedirectURL, category as c2, cash_bid as Amount, 1 as canUpload,b.username ";
 $sql.="from PictureRequest a join appuser b on a.uid = b.id where status>0 and cash_bid>0 and cash_bid<5 and b.stars>0 order by b.modified $dd limit $ustart, 10";	
 $uo=db::rows($sql);
 foreach($uo as $offer){
   $subid=$uid."_1337";
//   $offer['IconURL']="http://grepawk.s3.amazonaws.com/pr2logo_blue.png";
   if($offer['Name']=="(null)") continue;
   $offer['category']=$offer['c2'];
   $offer['Name']=$offer['Name']." ".$offer['c2'];
   $offer['Action']="Bonus code: ".$offer['username'];
   if($offer['fbid']!=0) $offer['IconURL']="https://graph.facebook.com/".$offer['fbid']."/picture?width=200&height=200";
   $o[]=$offer;
 }
}

//uncomment this when app's under review
if($reviewer==1){
 $xpinfo['minbonus']=0;
 $canEnterBonus=0;
}

$ret=array(
"offers"=>$o,
"fb"=>0,
"invite"=>$xpinfo['minbonus'],
"inviteUpper"=>$xpinfo['maxbonus'],
"enterbonus"=>$canEnterBonus,
"st"=>$st,
);

die(json_encode($ret));