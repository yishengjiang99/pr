<?php

require_once("/var/www/lib/functions.php");
$uid=intval($_GET['uid']);

$recent=db::cols("select count(1) as cnt from pr_xp where uid=$uid and created>date_sub(now(), interval 10 minute)");
if($recent[0]>2) exit;
$xp=rand(1,2);
$e=stripslashes($_GET['e']);
db::exec("update appuser set xp = xp+$xp where id=$uid");
db::exec("insert into pr_xp set uid=$uid,xp=$xp,created=now(),event='$e'");

