<?php
require_once("/var/www/lib/functions.php");
$go=urldecode($_GET['go']);
$subid=$_GET['subid'];
$st=explode(",",$subid);
$uid=intval($st[0]);
$offerID=$st[1];
$sql="insert into prclicks set subid='$subid', uid=$uid, offer_id='$offerID',created=now(), url='$go'";
db::exec($sql);
?>

<html>
<head>
<meta name="apple-mobile-web-app-capable" content="yes" />
</head>
<body>
<script>
      window.location = '<?php echo $go; ?>';
</script>
</body></html>

