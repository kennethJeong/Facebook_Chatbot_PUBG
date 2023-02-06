<!DOCTYPE html>
<html>
<head>
<title>배그봇 페이지 좋아요❤︎</title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width" />
<script>
	(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = 'https://connect.facebook.net/ko_KR/sdk.js#xfbml=1&version=v3.0&appId=1897365380315817&autoLogAppEvents=1';
	fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
</script>
</head>
<body>
	<div style="max-width:100%; float:left; padding:20% 0 5% 5%;">
		<div class="fb-page"
			data-href="https://www.facebook.com/PUBG.Realtime"
			data-width="325"
			data-height="60"
			data-small-header="true"
			data-adapt-container-width="false"
			data-hide-cover="false"
			data-show-facepile="true"
		>
		<blockquote cite="https://www.facebook.com/PUBG.Realtime"
		class="fb-xfbml-parse-ignore">
		<a href="https://www.facebook.com/PUBG.Realtime">배그봇 - 배틀그라운드 전적검색</a></blockquote></div>
	</div>
</body>
</html>

<?php
header("Content-Type:text/html; charset=UTF-8");
header("Pragma: no-cache");
header("Cache-Control: no-store, no-cache, must-revalidate");
date_default_timezone_set('Asia/Seoul');
ini_set('allow_url_fopen', 'On');
ini_set('allow_url_include', 'On');
ini_set("display_errors", 1);
ini_set('memory_limit','-1');
error_reporting(E_ERROR | E_WARNING | E_PARSE);
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
include_once $_SERVER["DOCUMENT_ROOT"] . '/pbg/dbInfo.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/pbg/config.php';
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$senderID = $_POST['psid'];
if($senderID) {
	$query = "SELECT * FROM pageLike WHERE userkey='$senderID'";
	$sql4pageLike = $conn->query($query);
	if($sql4pageLike->num_rows == 0) {
		$query = "INSERT INTO pageLike (userkey, pageLike, inputTime) VALUE('{$senderID}', '1', '{$inputTime}')";
		$conn->query($query);		
	}
}



