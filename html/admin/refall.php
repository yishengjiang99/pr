<?php
require_once("/var/www/lib/functions.php");
$uid=intval($_GET['uid']);
echo rows2table(db::rows("select banned,idfa,mac,ipaddress,a.id,a.created,b.created as enteredbonus,deviceInfo,modified,ltv 
from appuser a join referral_bonuses b on a.id=b.joinerUid where b.created>date_sub(now(), interval 1 day) order by ipaddress"));

echo rows2table(db::rows("select banned,idfa,mac,ipaddress,a.id,a.created,b.created as enteredbonus,deviceInfo,modified,ltv 
from appuser a join referral_bonuses b on a.id=b.joinerUid where b.created>date_sub(now(), interval 1 day) order by ltv"));

echo rows2table(db::rows("select banned,idfa,mac,ipaddress,a.id,a.created,b.created as enteredbonus,deviceInfo,modified,ltv 
from appuser a join referral_bonuses b on a.id=b.joinerUid where b.created>date_sub(now(), interval 1 day) order by idfa"));