<?php
function get_latest_post($content)
{
	$contents = array();
	$pbg_base_url = "https://cafe.naver.com/playbattlegrounds";
	if($content == "notice" || $content == "patch_note" || $content == "event") {
		if($content == "notice") {
			$url = $pbg_base_url."/ArticleList.nhn?search.clubid=28866679&search.menuid=6";
		}
		else if($content == "patch_note") {
			$url = $pbg_base_url."/ArticleList.nhn?search.clubid=28866679&search.menuid=33";
		}
		else if($content == "event") {
			$url = $pbg_base_url."/ArticleList.nhn?search.clubid=28866679&search.menuid=9";
		}
	
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                                                                                               
		$result = iconv("cp949","utf-8", curl_exec($ch));
	
		preg_match_all("'<span class=\"m-tcol-c list-count\">(.*?)</a>'si", $result, $match_all);
		
		// 최신 공지 글번호
		preg_match_all("'<span class=\"m-tcol-c list-count\">(.*?)</span>'si", $match_all[0][0], $num);
		$contents['num'] = $num[1][0];
		// 최신 공지 상세보기 URL
		preg_match_all("'<a href(.*?)</a>'si", $match_all[0][0], $address_and_title);
		preg_match_all("'<a href=\"(.*?)\"'si", $address_and_title[0][0], $address);
		$contents['address'] = $pbg_base_url.$address[1][0];
		// 최신 공지 제목
		preg_match_all("'>(.*?)</a>'si", $address_and_title[0][0], $title);
		$contents['title'] = $title[1][0];
		
		return $contents;
	}
}

if(date("H", $now) > 10 && date("H", $now) < 22 && (date("i", $now) == 00 || date("i", $now) == 20 || date("i", $now) == 40)) {
	// 공지
	if(date("i", $now) == 00) {
		$content = "notice";
		$content_ko = "공지사항";
		$imageURL = "https://bhandy.kr/pbg/image/notice.png";
	}
	// 패치노트
	else if(date("i", $now) == 20) {
		$content = "patch_note";
		$content_ko = "패치노트";
		$imageURL = "https://bhandy.kr/pbg/image/patch_note.png";
	}
	// 이벤트
	else if(date("i", $now) == 40) {
		$content = "event";
		$content_ko = "이벤트";
		$imageURL = "https://bhandy.kr/pbg/image/event.png";
	}
	
	$userkeys4oc = array();
	$query = "SELECT DISTINCT userkey FROM user WHERE cafeActivation='1'";
	$sql4user = $conn->query($query);
	while($row4user = $sql4user->fetch_assoc()) {
		$userkeys4oc[] = $row4user;
	}
	$usersCount = count($userkeys4oc);
	$get_latest_post = get_latest_post($content);
	if($get_latest_post) {
		$num = $get_latest_post['num'];
		$title = $get_latest_post['title'];
		$address = $get_latest_post['address'];
		$note_check = $content."_".$num;
		for($i=0; $i<$usersCount; $i++) {
			$query = "SELECT * FROM alarm WHERE userkey='{$userkeys4oc[$i]['userkey']}' AND note='{$note_check}' ORDER BY inputTime DESC LIMIT 1";
			$sql4alarm = $conn->query($query)->fetch_assoc();
			if($sql4alarm) {
				continue;
			} else {
				$message = array
				(
					"messaging_type" => "UPDATE",
					"recipient" => array
					(
						"id" => $userkeys4oc[$i]['userkey']
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
											"messenger_extensions" => false,
											"webview_height_ratio" => "full"
										)
									)
								)
							)
						)
					)
				);
				
				$url = "https://graph.facebook.com/v2.6/me/messages?access_token=".$accessToken;
				curlPost($url, $message);
				
				$send['text'] = "👁‍🗨: 배틀그라운드 공식 카페에 새로운 [".$content_ko."]이(가) 등록되었습니다.🤗";
				$send['text'] .= "\n\n상세 내용을 확인하려면 위 사진을 꾹 눌러주세요.❤️";
				$send['payload'] = $send['title'] = array('초기화면', '공식 카페 알림 끄기');
				messageQR($send, $userkeys4oc[$i]['userkey'], "UPDATE");
								
				$query = "INSERT INTO alarm (userkey, note, inputTime) VALUE('{$userkeys4oc[$i]['userkey']}', '{$note_check}', '{$inputTime}')";
				$conn->query($query);
				
				TypingOff($userkeys4oc[$i]['userkey']);
			}
		}
	}
}


