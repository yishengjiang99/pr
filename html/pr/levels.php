<?php
$xplevel=array(
-1=>0,
10=>1,
100=>2,
500=>3,
2000=>4,
5000=>5,
10000=>6,
60000=>7,
100000=>8,
300000=>9,
500000=>10,
900000=>11,
1100000=>12
);

$levelminbonus=array(
0=>200,
1=>280,
2=>320,
3=>420,
4=>480,
5=>556,
6=>590,
7=>650,
8=>690,
9=>700,
10=>730,
11=>740,
12=>750,
);

$levelmax=array(
0=>250,
1=>300,
2=>470,
3=>1999,
4=>2000,
5=>3000,
6=>4000,
7=>5000, 
8=>6000,
9=>8000,
10=>9300,
11=>9400,
12=>9500,
);

$multiplier=2;

function getBonusPoints($myxp,$country='US'){
 global $xplevel, $levelminbonus, $levelmax,$multiplier;
 if(!$multiplier) $multiplier=1;
 if($country!='US' && $country!='CA' && $country!='GB') $multiplier=0.5;
 if($country=='VN') $multiplier=0.4;

 $mylevel=0;
 $nexttlevel=0;
 $nextmin=0;$nextmax=0;
 $lastlevelxp=0;
 $tnl_percent=0;
 foreach($xplevel as $lxp=>$level){
   if($lxp>$myxp){
	$nextlevel=$level;
        $tnl=(double)($myxp-$lastlevelxp);
        $levelxptotal=(double)($lxp-$lastlevelxp);
	$tnl_percent=$tnl/$levelxptotal*100;
        $nextmin=$levelminbonus[$nextlevel]*$multiplier;
	$nextmax=$levelmax[$nextlevel]*$multiplier;
        break;
   }
   $mylevel=$level;
   $lastlevelxp=$lxp;
 }
 return array('level'=>$mylevel,'xp'=>$myxp,'minbonus'=>intval($levelminbonus[$mylevel]*$multiplier),'maxbonus'=>intval($levelmax[$mylevel]*$multiplier),'levelPercentage'=>$tnl_percent,'bonusNextLevel'=>"$nextmin to $nextmax Points");
}
