<?php
require_once("/var/www/lib/functions.php");
$r=$_REQUEST;
$uid=intval($r['uid']);
$pid=$r['pid'];
$pid=str_replace("t/","",$pid);
$pid=str_replace("m/","",$pid);

$info=db::row("select a.uid as uploader, b.uid as offerer, a.points_earned from UploadPictures a join PictureRequest b on a.refId=b.id where a.id='$pid';");
error_log("select a.uid as uploader, b.uid as offerer, a.points_earned from UploadPictures a join PictureRequest b on a.refId=b.id where a.id='$pid'");
$uploader=$info['uploader'];
$offerer=$info['offerer'];

if($uid==2902){
//var_dump($info);
}
$points=$info['points_earned'];
$xp=ceil($points*13);
$h="";
if(false && $uid==$offerer){
 $h=md5($uid."dddd");
 $confirm="onsubmit=\"return confirm('Do you want to demand a refund for $points Points. You will lose $xp XP')\"";
}else{
 $confirm="";
}
$reportedandreturn=0;
if(isset($r['report'])){
   $reportedandreturn=1;
 error_log("update UploadPictures set reviewed=reviewed-1 where id='$pid'");

 $user=db::row("select * from appuser where id=$uid");
 $username=$user['username'];
 if($username=='superadmin') $username='redcat';
 db::exec("update UploadPictures set reviewed=-1 where id='$pid'");
 $pic=db::row("select * from UploadPicture where id='$pid'");
 $upuid=$pic['uid'];
 require_once("/var/www/html/pr/apns.php");
 $cc=$r['complaint'];
 apnsUser($upuid,"$username reported that your picture: $cc","$username reported that your picture: $cc","http://www.json999.com/pr/picture.php?id=$pid");
 apnsUser(2902,"$username reported that your picture is: $cc","$username reported that your picture: $cc","http://www.json999.com/pr/p.php?pid=$pid&uid=$upuid");

 if(false && isset($r['h']) && $r['h']!='' && $r['h']=$h && $uid=$offerer){
//   db::exec("update appuser set stars=stars-$points where id=$uploader");
//   db::exec("update appuser set	stars=stars+$points where id=$offerer");
//    db::exec("update appuser set xp=xp-$xp where id=$offerer");
   $reportedandreturn=1;
 }
}
?>

<html>
<head>
<meta name = "viewport" content = "user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width">
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
</head>
 <a href='picrewards://' class=btn><h1>Back To PhotoRewards</h1></a>
<br>
<a href="http://c.mobpartner.mobi/?s=706540&a=2471&country=US&p=67985&cr=401003"><img src="http://r.mobpartner.mobi?s=706540&a=2471&country=US&p=67985&cr=401003" /></a><br>
<form method=POST <?=$confirm?>><input type=hidden value='<?= $_REQUEST['pid'] ?>' name='pid' />
<input type=hidden name=report value=1 />
<input type=hidden name=h value='<?= $h ?>' />

<input type=hidden name=uid value=<?= $uid ?> />
Report this picture!
<br><select name='complaint'>
<option value='none'>Select reason</option>
<option value='Poor Quality'>Picture is poor quality</option>
<option value='spam'>Poster is spammer</option>
<option value='off topic'>Off-topic</option>
<option value='offensive'>Offensive/explicit</option>
</select>
<input type=submit value='complain' />
</form>
<img width=100% src='http://json999.com/pr/uploads/<?= $pid?>.jpeg'>
<script>
<?php if($reportedandreturn==1){
echo ' alert("Thanks for reporting this picture to the committee!");';
echo 'window.location="picrewards://"';
}?>
</script>
</html>
