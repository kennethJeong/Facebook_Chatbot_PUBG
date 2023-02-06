<?php
if(date("d") % 3 == 0 && date("H:i") == "20:00") {
	$userkeyPL = array();
	$query = "SELECT DISTINCT user.userkey FROM user LEFT JOIN pageLike ON user.userkey = pageLike.userkey WHERE pageLike.userkey IS NULL AND (user.realtimeActivation='1' OR user.cafeActivation='1')";
	$sql4userkeys = $conn->query($query);
	while($row4userkeys = $sql4userkeys->fetch_assoc()) {
		$userkeyPL[] = $row4userkeys;
	}
	$userkeyPLCount = count($userkeyPL);
	
	if($userkeyPLCount > 0) {
		for($i=0; $i<$userkeyPLCount; $i++) {
			$title = "【페이지 좋아요】는 저에게 큰 힘이됩니다. 위 이미지를 눌러주세요.❤️";
			$imageURL = "https://bhandy.kr/pbg/image/page_like.png";
			$address = "https://bhandy.kr/pbg/webview/pageLike_ex.php";
			
			$message = array
			(
				"messaging_type" => "UPDATE",
				"recipient" => array
				(
					"id" => $userkeyPL[$i]['userkey']
				),
				"message" =>	array
				(
					"attachment" => array
					(
						"type" => "template",
						"payload" => array
						(
							"template_type" => "generic",
							"elements" => array
							(
								array
								(
									"title" => $title,
									"image_url" => $imageURL,
									"default_action" => array
									(
										"type" => "web_url",
										"url" => $address,
										"messenger_extensions" => true,
										"webview_height_ratio" => "compact"
									)
								)
							)
						)
					)
				)
			);
			
			$url = "https://graph.facebook.com/v2.6/me/messages?access_token=".$accessToken;
			curlPost($url, $message);
			
			$send['text'] = "👁‍🗨: 초기화면으로 돌아가려면 아래 버튼을 눌러주세요.😍";
			$send['payload'] = $send['title'] = array('초기화면');
			messageQR($send, $userkeyPL[$i]['userkey'], "UPDATE");
		
			TypingOff($userkeyPL[$i]['userkey']);
		}
	}
}