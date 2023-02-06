<?php
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// [2018.07.10] [function]pbg_find_real_id($ex_pbg_id, $pbg_servers) -> [function]pbg_find_real_id_v2($ex_pbg_id) 바꿈

/*
if($payload || $payloadQR || $messageText) {
	// 유저 페이스북 이름
	$senderFullName = findUserName($senderID);

	// 등록된 유저 정보
	$query = "SELECT * FROM user WHERE userkey='$senderID' AND userActivation='1'";
	$sql4user = $conn->query($query);
	while($row4user = $sql4user->fetch_assoc()) {
		$userInfo[] = $row4user;
	}

	// latest logging data	
	$query = "SELECT inProgress, name, accountID, mainServer FROM logging WHERE userkey='$senderID' ORDER BY inputTime DESC LIMIT 1";
	$sql4logging = $conn->query($query);
	while($row4logging = $sql4logging->fetch_assoc()) {
		$inProgress = $row4logging['inProgress'];
		$loggingName = $row4logging['name'];
		$loggingAccountID = $row4logging['accountID'];
		$loggingMainServer = $row4logging['mainServer'];
	}
	
	// map name(en, ko)
	//// $map_official_name => 'Desert_Main', 'Erangel_Main', 'Savage_Main'
	$map_names = pbg_maps($map_official_name);
	
	if($payload == "시작하기" || preg_match("/^시작/", $messageText) || preg_match("/^초기화면$/", $payload) || preg_match("/^초기화면$/", $messageText) || preg_match("/^초기화면$/", $payloadQR) || preg_match("/^안녕/", $messageText) || preg_match("/^하이/", $messageText)) {
		if(!$userInfo) {
			if(!$inProgress) {
				$send['text'] = "👁‍🗨: 안녕하세요. 저는 배틀그라운드 전적 알림 봇 【배그봇】 입니다.";
				$send['text'] .= "\n\n【배그봇】의 기능을 알려드릴게요.";
				$send['text'] .= "\n1️⃣ 실시간 전적 알림";
				$send['text'] .= "\n2️⃣ 나의 시즌 전적 보기";
				$send['text'] .= "\n3️⃣ 유저 전적 검색";
				$send['text'] .= "\n\n각 기능에 대한 설명은 차차 알려드리고..\n먼저 {$senderFullName}님의 배틀그라운드 아이디를 알려주시겠어요?";
				message($send);
				
				$send['elementsTitle'] = "배틀그라운드 아이디 등록";
				$send['elementsButtonsTitle'] = array("배틀그라운드 아이디 등록");
				messageTemplate($send);
				
				$query = queryInsert('logging', 'REGISTER');
				$conn->query($query);				
			} else {
				$send['text'] = "👁‍🗨: {$senderFullName}님의 배틀그라운드 아이디를 알려주세요.😍";
				message($send);
				
				$send['elementsTitle'] = "배틀그라운드 아이디 등록";
				$send['elementsButtonsTitle'] = array("배틀그라운드 아이디 등록");
				messageTemplate($send);
				
				$query = queryInsert('logging', 'REGISTER');
				$conn->query($query);							
			}
		} else {
			// check -> inProgress='ALARM_REALTIME_TUTORIAL_SKIP'
			$query = "SELECT inProgress FROM logging WHERE userkey='$senderID' AND inProgress='ALARM_REALTIME_TUTORIAL_SKIP'";
			$checkTutorialFinish = $conn->query($query)->fetch_assoc();
			
			// check -> realtime alarm
			$query = "SELECT alarmActivation FROM user WHERE userkey='$senderID' ORDER BY inputTime DESC LIMIT 1";
			$checkRealtime = $conn->query($query)->fetch_assoc();
			$checkRealtime['alarmActivation'] == 1 ? $realtimeAlarm_ko = "실시간 전적 알림 끄기" : $realtimeAlarm_ko = "실시간 전적 알림 받기";
			$checkRealtime['alarmActivation'] == 1 ? $realtimeAlarm_text = "비활성화" : $realtimeAlarm_text = "활성화";
			
			if(!$checkTutorialFinish) {
				$send['text'] = "👁‍🗨: 안녕하세요. {$senderFullName}님";
				$send['text'] .= "\n\n[실시간 전적 알림 받기]를 활성화 해볼까요?";
				$send['text'] .= "\n아래의 [실시간 전적 알림 받기] 버튼을 눌러주세요.😍";
				$send['payload'] = $send['title'] = array('실시간 전적 알림 받기', '건너뛰기');
				messageQR($send);
				
				$query = queryInsert('logging', 'ALARM_REALTIME_TUTORIAL');
				$conn->query($query);
			} else {
				$send['text'] = "👁‍🗨: {$senderFullName}님 반가워요.";
				message($send);
				
				$send['title'] = $send['buttonsTitle'] = $send['payload'] = array("나의 시즌 전적 보기", "전적 검색", "부계정 등록하기");
				$imagePath = 'https://bhandy.kr/pbg/image/';
				$send['imageURL'] = array($imagePath.'stats.jpg', $imagePath.'search.jpg', $imagePath.'sign_up.jpg');
				messageTemplateLeftSlideWithImage($send);

				$send['text'] = "👁‍🗨: 실시간 알림 받기를 {$realtimeAlarm_text}하려면 아래 버튼을 눌러주세요.";
				$send['payload'] = $send['title'] = array($realtimeAlarm_ko, "계정 삭제하기");
				messageQR($send);
				
				$query = queryInsert('logging', 'START');
				$conn->query($query);
			}
		}
	}
	else if($payload == "나의 시즌 전적 보기" || $payloadQR == "나의 시즌 전적 보기") {
		$countUserInfo = count($userInfo);
		if($countUserInfo == 1) {
			$userInfos = $userInfo[0];
			$name = $userInfos['name'];
			$accountID = $userInfos['accountID'];
			$mainServer = $userInfos['mainServer'];
			$query = "SELECT * FROM season WHERE server='$mainServer' AND season_current='1'";
			$sql4season = $conn->query($query)->fetch_assoc();
			$season_ko = $sql4season['season_ko'];
			
			// make images about season stats
			mkSeasonStats($senderID, $name, $accountID, $mainServer, $season_ko);
			
			$userSeasonStatsDir = $_SERVER["DOCUMENT_ROOT"] . "/pbg/stats/season/{$senderID}/{$mainServer}/{$name}";
			$handle = opendir($userSeasonStatsDir);
			$userSeasonStatsImages = array();
			while (false !== ($filename = readdir($handle))) {
			    if($filename == "." || $filename == ".."){
			        continue;
			    }
			    if(is_file($userSeasonStatsDir . "/" . $filename)){
			        $userSeasonStatsImages[] = $filename;
			    }
			}
			closedir($handle);
			
			$old_imageNumber = "";
			if(count($userSeasonStatsImages) > 0) {
				foreach($userSeasonStatsImages as $images) {
					preg_match_all("/[\d]{1,5}/", $images, $old_imageNumber);
					$old_imageNumber = $old_imageNumber[0][0];
					if($old_imageNumber) {
						break;
					}
				}
			}	
			
			$modeSort = array("solo", "duo", "squad", "solo_fpp", "duo_fpp", "squad_fpp");
			$seasonStatsUrl = "https://bhandy.kr/pbg/stats/season/{$senderID}/{$mainServer}/{$name}/";
			$sortedUserSeasonStats = array();
			$sortedUserSeasonStats_title = array();
			foreach($modeSort as $ms) {
				foreach($userSeasonStatsImages as $ussi) {
					$userSeasonStatsImagesNoExtn = preg_replace("/_[\d]{1,5}$/", "", str_replace(".jpg", "", $ussi));
					if($userSeasonStatsImagesNoExtn == $ms) {
						$sortedUserSeasonStats[] = $seasonStatsUrl.$ms."_".$old_imageNumber.".jpg";
					}
				}
			}
			foreach($sortedUserSeasonStats as $suss) {
				$sortedUserSeasonStats_title[] = ucwords(str_replace($seasonStatsUrl, "", preg_replace("/_[\d]{1,5}\.jpg$/", "", $suss)));
			}

			// 현재 시즌 전적
			if(count($sortedUserSeasonStats) > 0) {
				$send['text'] = "👁‍🗨: {$senderFullName}님의 아이디 [{$name}]의 {$season_ko} 시즌 전적을 알려드립니다.✌️";
				message($send);				
				
				$send['title'] = $sortedUserSeasonStats_title;
				$send['imgUrl'] = $sortedUserSeasonStats;
				messageImageList($send);
				
				$query = "SELECT season_ko FROM season WHERE server='$mainServer' AND season_ko LIKE '정규%'";
				$sql4season = $conn->query($query);
				while($row4season = $sql4season->fetch_assoc()) {
					$seasonPrev[] = $row4season['season_ko'];
				}
				array_unshift($seasonPrev, "주 서버 변경하기");
				array_unshift($seasonPrev, "초기화면");
				$send['text'] = "👁‍🗨: 이전 시즌의 전적을 보고싶다면 원하는 시즌의 버튼을 눌러주세요.";
				$send['payload'] = $send['title'] = $seasonPrev;
				messageQR($send);			
			} else {
				$season_ko_exp = explode(" ", $season_ko);
				$season_ko_prev = $season_ko_exp[0] . " " . ($season_ko_exp[1] - 1);
				
				// make images about season stats with previous season agian
				mkSeasonStats($senderID, $name, $accountID, $mainServer, $season_ko_prev);
				
				$userSeasonStatsDir = $_SERVER["DOCUMENT_ROOT"] . "/pbg/stats/season/{$senderID}/{$mainServer}/{$name}";
				$handle = opendir($userSeasonStatsDir);
				$userSeasonStatsImages = array();
				while (false !== ($filename = readdir($handle))) {
				    if($filename == "." || $filename == ".."){
				        continue;
				    }
				    if(is_file($userSeasonStatsDir . "/" . $filename)){
				        $userSeasonStatsImages[] = $filename;
				    }
				}
				closedir($handle);
				
				$old_imageNumber = "";
				if(count($userSeasonStatsImages) > 0) {
					foreach($userSeasonStatsImages as $images) {
						preg_match_all("/[\d]{1,5}/", $images, $old_imageNumber);
						$old_imageNumber = $old_imageNumber[0][0];
						if($old_imageNumber) {
							break;
						}
					}
				}	
				
				$modeSort = array("solo", "duo", "squad", "solo_fpp", "duo_fpp", "squad_fpp");
				$seasonStatsUrl = "https://bhandy.kr/pbg/stats/season/{$senderID}/{$mainServer}/{$name}/";
				$sortedUserSeasonStats = array();
				$sortedUserSeasonStats_title = array();
				foreach($modeSort as $ms) {
					foreach($userSeasonStatsImages as $ussi) {
						$userSeasonStatsImagesNoExtn = preg_replace("/_[\d]{1,5}$/", "", str_replace(".jpg", "", $ussi));
						if($userSeasonStatsImagesNoExtn == $ms) {
							$sortedUserSeasonStats[] = $seasonStatsUrl.$ms."_".$old_imageNumber.".jpg";
						}
					}
				}
				foreach($sortedUserSeasonStats as $suss) {
					$sortedUserSeasonStats_title[] = ucwords(str_replace($seasonStatsUrl, "", preg_replace("/_[\d]{1,5}\.jpg$/", "", $suss)));
				}
				
				// 이전 시즌 전적
				if(count($sortedUserSeasonStats) > 0) {
					$send['text'] = "👁‍🗨: {$senderFullName}님의 아이디 [{$name}]의 {$season_ko} 시즌 전적 정보가 없어, {$season_ko_prev} 시즌 전적을 알려드립니다.✌️";
					message($send);						
			
					$send['title'] = $sortedUserSeasonStats_title;
					$send['imgUrl'] = $sortedUserSeasonStats;
					messageImageList($send);
					
					$query = "SELECT season_ko FROM season WHERE server='$mainServer' AND season_ko LIKE '정규%'";
					$sql4season = $conn->query($query);
					while($row4season = $sql4season->fetch_assoc()) {
						$seasonPrev[] = $row4season['season_ko'];
					}
					array_unshift($seasonPrev, "주 서버 변경하기");
					array_unshift($seasonPrev, "초기화면");
					$send['text'] = "👁‍🗨: 이전 시즌의 전적을 보고싶다면 원하는 시즌의 버튼을 눌러주세요.";
					$send['payload'] = $send['title'] = $seasonPrev;
					messageQR($send);
				}
				// (현재 && 이전) 시즌 없음 -> 다른 시즌 전적 검색 유도
				else {
					$query = "SELECT season_ko FROM season WHERE server='$mainServer' AND season_ko LIKE '정규%'";
					$sql4season = $conn->query($query);
					while($row4season = $sql4season->fetch_assoc()) {
						$seasonPrev[] = $row4season['season_ko'];
					}
					array_unshift($seasonPrev, "주 서버 변경하기");
					array_unshift($seasonPrev, "초기화면");
					$send['text'] = "👁‍🗨: {$senderFullName}님의 아이디 [{$name}]의 {$season_ko} 시즌 전적과 {$season_ko_prev} 시즌 전적이 모두 존재하지않네요.😭️";
					$send['text'] .= "\n\n더 이전의 시즌 전적을 보기 원하시면 아래의 원하는 시즌의 버튼을 눌러주세요.";
					$send['payload'] = $send['title'] = $seasonPrev;
					messageQR($send);
				}
			}	
			$query = queryInsert('logging', 'MY_STATS_SELECT_SEASON', array("name"=>$name, "accountID"=>$accountID, "mainServer"=>$mainServer));
			$conn->query($query);
		}
		else if($countUserInfo == 2) {
			for($i=0; $i<$countUserInfo; $i++) {
				$userInfoNames[$i] = $userInfo[$i]['name'];
			}
			$send['text'] = "👁‍🗨: 전적을 보고 싶은 아이디를 선택해주세요.";
			array_unshift($userInfoNames, "초기화면");
			$send['payload'] = $send['title'] = $userInfoNames;
			messageQR($send);
			
			$query = queryInsert('logging', 'MY_STATS_SELECT_ID');
			$conn->query($query);
		}
	}
	else if($payload == "전적 검색" || $payloadQR == "전적 검색") {
		$query = "SELECT DISTINCT search FROM logging WHERE userkey='$senderID' AND inProgress='SEARCH_FIN' ORDER BY inputTime DESC LIMIT 9";
		$sql4logging = $conn->query($query);
		while($row4logging = $sql4logging->fetch_assoc()) {
			$search_text_prev[] = $row4logging['search'];
		}
		if(count($search_text_prev) > 0) {
			$send['elementsTitle'] = "이전 검색 기록";
			$send['elementsButtonsTitle'] = $search_text_prev;
			messageTemplate($send);
			
			$send['text'] = "👁‍🗨: 이전에 검색에 성공한 전적을 선택하거나, 새로운 아이디를 아래 형식에 맞게 입력해주세요.🤗";
			$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";
		} else {
			$send['text'] = "👁‍🗨: 전적을 보고 싶은 아이디를 아래 형식에 맞게 입력해주세요.🤗";
			$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";
			$send['text'] .= "\n서버 종류 👉[한국(kr), 카카오(kakao), 아시아(as), 남아메리카(sa), 북아메리카(na), 오세아니아(oc), 유럽(eu), 동남아시아(sea)]";
			
			$query = "SELECT season_ko FROM season WHERE season_current='1' AND server='pc-krjp'";
			$sql4season = $conn->query($query)->fetch_assoc();
			preg_match_all("/[\d]{1,5}/", $sql4season['season_ko'], $match);
			$current_season_num = $match[0][0];
			$send['text'] .= "\n현재 시즌 👉 한국(kr)서버 기준, 정규 [{$current_season_num}] 시즌";
			$send['text'] .= "\n\n예시1) 카카오/5/AFTV_Benz 👉 [카카오서버, 정규 5 시즌, AFTV_Benz] 시즌전적";
			$send['text'] .= "\n예시2) kr//YoonRoot 👉 [한국서버, 현재 정규 시즌(정규 {$current_season_num} 시즌), YoonRoot] 시즌전적";
		}
		$send['payload'] = $send['title'] = array('초기화면');
		messageQR($send);
		
		$query = queryInsert('logging', 'SEARCH');
		$conn->query($query);
	}
	else if($payload == "부계정 등록하기") {
		$userIDcount = count($userInfo);
		if($userIDcount == 1) {
			$send['text'] = "👁‍🗨: 부계정 등록을 시작합니다.";
			$send['text'] .= "\n\n배틀그라운드 아이디를 입력해주세요.😍";
			message($send);
			
			$query = queryInsert('logging', 'REGISTER_SUB_INSERT');
			$conn->query($query);
		}
		else if($userIDcount == 2) {
			for($i=0; $i<$userIDcount; $i++) {
				$userInfoNames[] = $userInfo[$i]['name'];
			}
			$userInfoNamesImp = implode(", ", $userInfoNames);
			$send['text'] = "👁‍🗨: {$senderFullName}님께서는 주계정 및 부계정을 모두 등록하셨기 때문에 추가로 계정을 등록하실 수 없고, 지금 등록된 아이디 중 하나 이상을 삭제하셔야 추가 등록이 가능합니다.😭";
			$send['text'] .= "\n\n[$userInfoNamesImp] 중 하나를 삭제하시겠습니까?";
			$send['payload'] = $send['title'] = array('⭕', '❌');
			messageQR($send);
			
			$query = queryInsert('logging', 'DELETE');
			$conn->query($query);
		}
	}
	else if($payloadQR == "실시간 전적 알림 받기" || $payloadQR == "실시간 전적 알림 끄기") {
		if($payloadQR == "실시간 전적 알림 받기") {
			$query = "UPDATE user SET alarmActivation='1' WHERE userkey='$senderID'";
			$conn->query($query);
			
			$send['text'] = "👁‍🗨: {$senderFullName}님의 [실시간 전적 알림]이 활성화되었습니다.";
			message($send);
			
			$send['title'] = $send['buttonsTitle'] = $send['payload'] = array("나의 시즌 전적 보기", "전적 검색", "부계정 등록하기");
			$imagePath = 'https://bhandy.kr/pbg/image/';
			$send['imageURL'] = array($imagePath.'stats.jpg', $imagePath.'search.jpg', $imagePath.'sign_up.jpg');
			messageTemplateLeftSlideWithImage($send);

			$send['text'] = "👁‍🗨: 실시간 알림 받기를 비활성화하려면 아래 버튼을 눌러주세요.";
			$send['payload'] = $send['title'] = array("실시간 전적 알림 끄기", "계정 삭제하기");
			messageQR($send);
			
			$query = queryInsert('logging', 'ALARM_REALTIME_ON');
			$conn->query($query);
		}
		else if($payloadQR == "실시간 전적 알림 끄기") {
			$query = "UPDATE user SET alarmActivation='0' WHERE userkey='$senderID'";
			$conn->query($query);			
			
			$send['text'] = "👁‍🗨: {$senderFullName}님의 [실시간 전적 알림]이 비활성화되었습니다.";
			message($send);
			
			$send['title'] = $send['buttonsTitle'] = $send['payload'] = array("나의 시즌 전적 보기", "전적 검색", "부계정 등록하기");
			$imagePath = 'https://bhandy.kr/pbg/image/';
			$send['imageURL'] = array($imagePath.'stats.jpg', $imagePath.'search.jpg', $imagePath.'sign_up.jpg');
			messageTemplateLeftSlideWithImage($send);

			$send['text'] = "👁‍🗨: 실시간 알림 받기를 활성화하려면 아래 버튼을 눌러주세요.";
			$send['payload'] = $send['title'] = array("실시간 전적 알림 받기", "계정 삭제하기");
			messageQR($send);
			
			$query = queryInsert('logging', 'ALARM_REALTIME_OFF');
			$conn->query($query);		
		}
	}
	else if($payloadQR == "계정 삭제하기") {
		$userIDcount = count($userInfo);
		if($userIDcount == 1) {
			$userInfoName = $userInfo[0]['name'];
			$send['text'] = "👁‍🗨: 【{$userInfoName}】를 정말로 삭제하시겠습니까?";
			$send['text'] .= "\n(주의❗️ 한번 삭제된 아이디는 다시 복구할 수 없고, 모든 알림은 비활성화됩니다.)";
			$send['payload'] = $send['title'] = array('⭕', '❌');
			messageQR($send);		
			
			$query = queryInsert('logging', 'DELETE_CHECK', array('name' => $userInfoName));
			$conn->query($query);			
		}
		else if($userIDcount == 2) {
			for($i=0; $i<$userIDcount; $i++) {
				$userInfoNames[] = $userInfo[$i]['name'];
			}
			$userInfoNamesImp = implode(", ", $userInfoNames);
			$send['text'] = "👁‍🗨: {$senderFullName}님께서는";
			$send['text'] .= "\n[$userInfoNamesImp]의 주계정 및 부계정 아이디을 등록하셨습니다.";
			$send['text'] .= "\n어떤 아이디를 삭제하시겠습니까?";
			array_unshift($userInfoNames, "초기화면");
			$send['payload'] = $send['title'] = $userInfoNames;
			messageQR($send);
			
			$query = queryInsert('logging', 'DELETE_SELECT_ID');
			$conn->query($query);
		}
	}
	else if($payloadQR == "주 서버 변경하기") {
		// after than inProgress = "MY_STATS_SELECT_SEASON"
		$query = "SELECT * FROM logging WHERE inProgress='MY_STATS_SELECT_SEASON' ORDER BY inputTime DESC LIMIT 1";
		$sql4logging = $conn->query($query)->fetch_assoc();
		$loggingName = $sql4logging['name'];
		$loggingAccountID = $sql4logging['accountID'];
		$loggingMainServer = $sql4logging['mainServer'];
		
		$countUserInfo = count($userInfo);
		if($countUserInfo == 1) {
			$userInfos = $userInfo[0];
			$name = $userInfos['name'];
			$accountID = $userInfos['accountID'];
			$mainServer = $userInfos['mainServer'];
			$query = "SELECT server_ko FROM server WHERE server!='$mainServer'";
			$sql4server = $conn->query($query);
			while($row4server = $sql4server->fetch_assoc()) {
				$serverList_ko[] = $row4server['server_ko'];
			}
			array_unshift($serverList_ko, "초기화면");
			
			$query = "SELECT server_ko FROM server WHERE server='$mainServer'";
			$sql4server = $conn->query($query)->fetch_assoc();
			$old_mainServer_ko = $sql4server['server_ko'];			
			$send['text'] = "👁‍🗨: {$senderFullName}님의 아이디 [{$name}]의 주 서️버를 [{$old_mainServer_ko}]에서 어디로 바꾸시겠어요?";	
			$send['payload'] = $send['title'] = $serverList_ko;
			messageQR($send);
			
			$query = queryInsert('logging', 'CHANGE_MAIN_SERVER', array("name"=>$name, "accountID"=>$accountID, "mainServer"=>$mainServer));
			$conn->query($query);
		}
		else if($countUserInfo == 2) {
			for($i=0; $i<$countUserInfo; $i++) {
				$userInfoNames[] = $userInfo[$i]['name'];
			}
			$userInfoNamesImp = implode(", ", $userInfoNames);
			$send['text'] = "👁‍🗨: {$senderFullName}님께서는";
			$send['text'] .= "\n[$userInfoNamesImp]의 주계정 및 부계정 아이디을 등록하셨습니다.";
			$send['text'] .= "\n어떤 아이디의 주 서버를 변경하시겠어요?";
			array_unshift($userInfoNames, "초기화면");
			$send['payload'] = $send['title'] = $userInfoNames;
			messageQR($send);

			$query = queryInsert('logging', 'CHANGE_MAIN_SERVER_SELECT_ID');
			$conn->query($query);
		}	
	}
	else if($payloadQR == "이전으로") {
		
			
	
	}
	else {
		if(preg_match("/REGISTER/", $inProgress)) {
			if($inProgress == "REGISTER") {
				if($payload && $payload == "배틀그라운드 아이디 등록") {
					$send['text'] = "👁‍🗨: 배틀그라운드 아이디를 입력해주세요.😍";
					message($send);
					
					$query = queryInsert('logging', 'REGISTER_INSERT');
					$conn->query($query);
				} else {
					$send['text'] = "👁‍🗨: {$senderFullName}님의 배틀그라운드 아이디를 알려주세요.😍";
					message($send);
					
					$send['elementsTitle'] = "배틀그라운드 아이디 등록";
					$send['elementsButtonsTitle'] = array("배틀그라운드 아이디 등록");
					messageTemplate($send);						
				}
			}
			else if(preg_match("/INSERT$/", $inProgress) || preg_match("/SUB_INSERT$/", $inProgress)) {
				if($messageText) {
					$ex_pbg_id = trim($messageText);
					if(preg_match('/^[0-9A-Za-z_\-]{4,12}$/', $ex_pbg_id) && preg_match('/^[a-zA-Z]/', $ex_pbg_id)) {
						$pbg_servers = pbg_server();
						$pbg_id_arr = pbg_find_real_id($ex_pbg_id, $pbg_servers);
						$pbg_id = $pbg_id_arr['result']['id'];
						$pbg_account_id = $pbg_id_arr['result']['account_id'];
						
						$send['text'] = "👁‍🗨: {$senderFullName}님의 배틀그라운드 아이디는 【{$pbg_id}】이 맞나요?";
						$send['payload'] = $send['title'] = array('⭕', '❌');
						messageQR($send);
						
						$query = queryInsert('logging', 'REGISTER_INSERT_CHECK', array('name'=>$pbg_id, 'accountID'=>$pbg_account_id));
						$conn->query($query);
					} else {
						$send['text'] = "👁‍🗨: 잘못된 아이디 형식입니다.😓";
						$send['text'] .= "\n\n배틀그라운드 아이디 형식은 [영문], [숫자], [-(하이픈), _(언더바)]로 이루어진 4~16글자 입니다.";
						$send['text'] .= "\n\n확인 후 다시 입력해주세요.😍";
						message($send);
					}
				} else {
					$send['text'] = "👁‍🗨: 배틀그라운드 아이디를 입력해주세요.😍";
					message($send);
				}
			}
			else if(preg_match("/INSERT_CHECK$/", $inProgress)) {
				if($payloadQR) {
					if($payloadQR == '⭕') {
						$pbg_servers = pbg_server();
						foreach($pbg_servers as $value) {
							$pbg_servers_ko[] = $value['server_ko'];
						}
						$send['text'] = "👁‍🗨: 그럼 【{$loggingName}】를 플레이하는 주 서버는 어디인가요?";
						$send['text'] .= "\n(주 서버는 다시 변경 가능합니다.)";
						$send['payload'] = $send['title'] = $pbg_servers_ko;
						messageQR($send);
						
						$query = queryInsert('logging', 'REGISTER_SERVER', array('name'=>$loggingName, 'accountID'=>$loggingAccountID));
						$conn->query($query);
					}
					else if($payloadQR == '❌') {
						$send['text'] = "👁‍🗨: 【{$loggingName}】가 {$senderFullName}님의 배틀그라운드 아이디가 아닌가요?😭";
						$send['text'] .= "\n\n그럼 다시 입력해주세요.😍";
						message($send);
						
						$query = queryInsert('logging', 'REGISTER_INSERT');
						$conn->query($query);	
					} else {
						$send['text'] = "👁‍🗨: {$senderFullName}님의 배틀그라운드 아이디는 【{$loggingName}】이 맞나요?";
						$send['payload'] = $send['title'] = array('⭕', '❌');
						messageQR($send);
					}
				} else {
					$send['text'] = "👁‍🗨: {$senderFullName}님의 배틀그라운드 아이디는 【{$loggingName}】이 맞나요?";
					$send['payload'] = $send['title'] = array('⭕', '❌');
					messageQR($send);					
				}
			}
			else if(preg_match("/SERVER$/", $inProgress)) {
				if($payloadQR) {
					$mainServer_ko = $payloadQR;
					$pbg_servers = pbg_server();
					foreach($pbg_servers as $value) {
						if($value['server_ko'] == $mainServer_ko) {
							$mainServer = $value['server'];
						}
					}
					$send['text'] = "👁‍🗨: 【{$loggingName}】를 플레이하는 주 서버는 [$mainServer_ko]서버가 맞나요?";
					$send['payload'] = $send['title'] = array('⭕', '❌');
					messageQR($send);
					
					$query = queryInsert('logging', 'REGISTER_SERVER_CHECK', array('name'=>$loggingName, 'accountID'=>$loggingAccountID, 'mainServer'=>$mainServer));
					$conn->query($query);
				} else {
					$pbg_servers = pbg_server();
					foreach($pbg_servers as $value) {
						$pbg_servers_ko[] = $value['server_ko'];
					}
					$send['text'] = "👁‍🗨: 그럼 【{$loggingName}】를 플레이하는 주 서버는 어디인가요?";
					$send['text'] .= "\n(주 서버는 다시 변경 가능합니다.)";
					$send['payload'] = $send['title'] = $pbg_servers_ko;
					messageQR($send);					
				}
			}
			else if(preg_match("/SERVER_CHECK$/", $inProgress)) {
				if($payloadQR) {
					if($payloadQR == '⭕') {
						$query = "INSERT IGNORE INTO user (userkey, userActivation, alarmActivation, name, accountID, mainServer, inputTime)
											SELECT userkey, '1', '0', name, accountID, mainServer, '$inputTime' FROM logging 
												WHERE inProgress='REGISTER_SERVER_CHECK' AND userkey='$senderID'
												ORDER BY inputTime DESC LIMIT 1";
						$conn->query($query);
						
						$userIDcount = count($userInfo);
						if($userIDcount == 0) {
							$userIDs_ko = "주계정";
						}
						else if($userIDcount == 1) {
							$userIDs_ko = "부계정";
						}
						$send['text'] = "👁‍🗨: {$senderFullName}님의 배틀그라운드 아이디 【{$loggingName}】가 [$userIDs_ko]으로 등록되었습니다. 🎉";
						$send['text'] .= "\n(계정은 최대 2개까지 등록 가능합니다.😘)";
						message($send);
						
						if($userIDcount > 0) {
							$send['text'] = "👁‍🗨: 아래 버튼을 눌러 초기화면으로 이동해주세요.😍";
							$send['payload'] = $send['title'] = array('초기화면');
							messageQR($send);
						} else {
							$send['text'] = "그럼 이제 [실시간 전적 알림 받아보기]를 활성화 해볼까요?";
							$send['text'] .= "\n아래의 [실시간 전적 알림 받아보기] 버튼을 눌러주세요.😍";
							$send['payload'] = $send['title'] = array('실시간 전적 알림 받아보기', '건너뛰기');
							messageQR($send);
							
							$query = queryInsert('logging', 'ALARM_REALTIME_TUTORIAL');
							$conn->query($query);							
						}
					}
					else if($payloadQR == '❌') {
						$pbg_servers = pbg_server();
						foreach($pbg_servers as $value) {
							$pbg_servers_ko[] = $value['server_ko'];
						}
						$send['text'] = "👁‍🗨: 그럼 【{$loggingName}】를 플레이하는 주 서버는 어디인가요?";
						$send['text'] .= "\n(주 서버는 다시 변경 가능합니다.)";
						$send['payload'] = $send['title'] = $pbg_servers_ko;
						messageQR($send);
						
						$query = queryInsert('logging', 'REGISTER_SERVER', array('name'=>$loggingName, 'accountID'=>$loggingAccountID));
						$conn->query($query);
					}
				} else {
					$mainServer = $loggingMainServer;
					$pbg_servers = pbg_server();
					foreach($pbg_servers as $value) {
						if($value['server'] == $mainServer) {
							$mainServer_ko = $value['server_ko'];
						}
					}
					$send['text'] = "👁‍🗨: 【{$loggingName}】를 플레이하는 주 서버는 [$mainServer_ko]서버가 맞나요?";
					$send['payload'] = $send['title'] = array('⭕', '❌');
					messageQR($send);					
				}	
			}
		}
		else if(preg_match("/SEARCH/", $inProgress)) {
			if(preg_match("/SEARCH$/", $inProgress) || preg_match("/FIN$/", $inProgress)) {
				if($messageText || $payload) {
					if($messageText) {
						$searchText = $messageText;
					}
					else if($payload) {
						$searchText = $payload;
					}
					$searchText_exp = explode("/", $searchText);
					if(count($searchText_exp) == 3) {
						$search_server = $searchText_exp[0];
						$search_season = $searchText_exp[1];
						$search_name = $searchText_exp[2];
						
						$query = "SELECT server, server_ko FROM server WHERE server LIKE '%{$search_server}%' OR server_ko='{$search_server}'";
						$server_exist_check = $conn->query($query);
						if($server_exist_check->num_rows == 1) {
							$server_data = $server_exist_check->fetch_assoc();
							$server = $server_data['server'];
							$server_ko = $server_data['server_ko'];
							if(preg_match("/[\d]{1,5}/", $search_season) || empty($search_season)) {
								if(preg_match("/[\d]{1,5}/", $search_season)) {
									$query = "SELECT season, season_ko FROM season WHERE server='$server' AND server_ko='$server_ko' AND season_ko='정규 $search_season'";
									$season_exist_check = $conn->query($query)->fetch_assoc();
									if($season_exist_check) {
										$season_exist = TRUE;
										$season_data = $season_exist_check;
									} else {
										$send['text'] = "👁‍🗨: 입력하신 시즌이 존재하지않습니다.😓";
										$send['text'] .= "\n입력하신 시즌보다 이전 시즌을 검색해보세요.😍";
										$send['payload'] = $send['title'] = array('초기화면');
										messageQR($send);									
									}
								}
								else if($search_season == "") {
									$season_exist = TRUE;
									$query = "SELECT season, season_ko FROM season WHERE season_current='1' AND server='pc-krjp'";
									$sql4season = $conn->query($query)->fetch_assoc();
									$season_data = $sql4season;
								}
								
								if($season_exist == TRUE) {
									$season = $season_data['season'];
									$season_ko = $season_data['season_ko'];
									
									if(preg_match('/^[0-9A-Za-z_\-]{4,12}$/', $search_name) && preg_match('/^[a-zA-Z]/', $search_name)) {
										$pbg_find_real_id = pbg_find_real_id($search_name, array($server));
										$name = $pbg_find_real_id['result']['id'];
										$accountID = $pbg_find_real_id['result']['account_id'];

										$send['text'] = "👁‍🗨: [{$name}]의 {$season_ko} 시즌 전적 이미지를 생성중입니다. 잠시만 기다려주세요.😍";
										message($send);

										$searchSeasonStatsDir = $_SERVER["DOCUMENT_ROOT"] . "/pbg/stats/season/{$senderID}/{$server}/search/{$name}";
										$handle = opendir($userSeasonStatsDir);
										$searchSeasonStatsImages = array();
										while (false !== ($filename = readdir($handle))) {
										    if($filename == "." || $filename == ".."){
										        continue;
										    }
										    if(is_file($searchSeasonStatsDir . "/" . $filename)){
										        $searchSeasonStatsImages[] = $filename;
										    }
										}
										closedir($handle);
										
										$old_imageNumber = "";
										if(count($searchSeasonStatsImages) > 0) {
											foreach($searchSeasonStatsImages as $images) {
												preg_match_all("/[\d]{1,5}/", $images, $old_imageNumber);
												$old_imageNumber = $old_imageNumber[0][0];
												if($old_imageNumber) {
													break;
												}
											}
										}	
										
										$modeSort = array("solo", "duo", "squad", "solo_fpp", "duo_fpp", "squad_fpp");
										$searchSeasonStatsUrl = "https://bhandy.kr/pbg/stats/season/{$senderID}/{$server}/search/{$name}/";
										$sortedSearchSeasonStats = array();
										$sortedSearchSeasonStats_title = array();
										foreach($modeSort as $ms) {
											foreach($searchSeasonStatsImages as $sssi) {
												$searchSeasonStatsImagesNoExtn = preg_replace("/_[\d]{1,5}$/", "", str_replace(".jpg", "", $sssi));
												if($searchSeasonStatsImagesNoExtn == $ms) {
													$sortedSearchSeasonStats[] = $searchSeasonStatsUrl.$ms."_".$old_imageNumber.".jpg";
												}
											}
										}
										foreach($sortedSearchSeasonStats as $ssss) {
											$sortedSearchSeasonStats_title[] = ucwords(str_replace($searchSeasonStatsUrl, "", preg_replace("/_[\d]{1,5}\.jpg$/", "", $ssss)));
										}
										
										if(count($sortedSearchSeasonStats) > 0) {
											$send['text'] = "👁‍🗨: [{$name}]의 {$season_ko} 시즌 전적을 알려드립니다.✌️";
											message($send);
											
											$send['title'] = $sortedSearchSeasonStats_title;
											$send['imgUrl'] = $sortedSearchSeasonStats;
											messageImageList($send);

											$query = "SELECT DISTINCT search FROM logging WHERE userkey='$senderID' AND inProgress='SEARCH_FIN' ORDER BY inputTime DESC LIMIT 9";
											$sql4logging = $conn->query($query);
											while($row4logging = $sql4logging->fetch_assoc()) {
												$search_text_prev[] = $row4logging['search'];
											}
											if(count($search_text_prev) > 0) {
												$send['elementsTitle'] = "이전 검색 기록";
												$send['elementsButtonsTitle'] = $search_text_prev;
												messageTemplate($send);
												
												$send['text'] = "👁‍🗨: 다시 검색하고싶다면 이전에 검색에 성공한 전적을 선택하거나, 새로운 아이디를 아래 형식에 맞게 입력해주세요.🤗";
												$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";
											} else {											
												$send['text'] = "👁‍🗨: 다시 검색하고싶다면 아래 형식에 맞게 입력해주세요.😍";
												$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";
												$send['text'] .= "\n서버 종류 👉[한국(kr), 카카오(kakao), 아시아(as), 남아메리카(sa), 북아메리카(na), 오세아니아(oc), 유럽(eu), 동남아시아(sea)]";
												
												$query = "SELECT season_ko FROM season WHERE season_current='1' AND server='pc-krjp'";
												$sql4season = $conn->query($query)->fetch_assoc();
												preg_match_all("/[\d]{1,5}/", $sql4season['season_ko'], $match);
												$current_season_num = $match[0][0];
												$send['text'] .= "\n현재 시즌 👉 한국(kr)서버 기준, 정규 [{$current_season_num}] 시즌";
												$send['text'] .= "\n\n예시1) 카카오/5/AFTV_Benz 👉 [카카오서버, 정규 5 시즌, AFTV_Benz] 시즌전적";
												$send['text'] .= "\n예시2) kr//YoonRoot 👉 [한국서버, 현재 정규 시즌(정규 5 시즌), YoonRoot] 시즌전적";
											}		
											$send['payload'] = $send['title'] = array('초기화면');
											messageQR($send);
											
											$query = queryInsert('logging', 'SEARCH_FIN', array('search'=>$searchText));
											$conn->query($query);
										} else {
											$query = "SELECT DISTINCT search FROM logging WHERE userkey='$senderID' AND inProgress='SEARCH_FIN' ORDER BY inputTime DESC LIMIT 9";
											$sql4logging = $conn->query($query);
											while($row4logging = $sql4logging->fetch_assoc()) {
												$search_text_prev[] = $row4logging['search'];
											}
											if(count($search_text_prev) > 0) {
												$send['text'] = "👁‍🗨: [{$name}]의 {$season_ko} 시즌 전적 정보가 없어요.😓";
												message($send);
												
												$send['elementsTitle'] = "이전 검색 기록";
												$send['elementsButtonsTitle'] = $search_text_prev;
												messageTemplate($send);
												
												$send['text'] = "👁‍🗨: 다시 검색하고싶다면 이전에 검색에 성공한 전적을 선택하거나, 새로운 아이디를 아래 형식에 맞게 입력해주세요.🤗";
												$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";
											} else {
												$send['text'] = "👁‍🗨: [{$name}]의 {$season_ko} 시즌 전적 정보가 없어요.😓";
												$send['text'] .= "\n\n다시 검색하고싶다면 아래 형식에 맞게 입력해주세요.😍";
												$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";
												$send['text'] .= "\n서버 종류 👉[한국(kr), 카카오(kakao), 아시아(as), 남아메리카(sa), 북아메리카(na), 오세아니아(oc), 유럽(eu), 동남아시아(sea)]";
												
												$query = "SELECT season_ko FROM season WHERE season_current='1' AND server='pc-krjp'";
												$sql4season = $conn->query($query)->fetch_assoc();
												preg_match_all("/[\d]{1,5}/", $sql4season['season_ko'], $match);
												$current_season_num = $match[0][0];
												$send['text'] .= "\n현재 시즌 👉 한국(kr)서버 기준, 정규 [{$current_season_num}] 시즌";
												$send['text'] .= "\n\n예시1) 카카오/5/AFTV_Benz 👉 [카카오서버, 정규 5 시즌, AFTV_Benz] 시즌전적";
												$send['text'] .= "\n예시2) kr//YoonRoot 👉 [한국서버, 현재 정규 시즌(정규 5 시즌), YoonRoot] 시즌전적";
											}
											$send['payload'] = $send['title'] = array('초기화면');
											messageQR($send);
										}
									} else {
										$query = "SELECT DISTINCT search FROM logging WHERE userkey='$senderID' AND inProgress='SEARCH_FIN' ORDER BY inputTime DESC LIMIT 9";
										$sql4logging = $conn->query($query);
										while($row4logging = $sql4logging->fetch_assoc()) {
											$search_text_prev[] = $row4logging['search'];
										}
										if(count($search_text_prev) > 0) {
											$send['text'] = "👁‍🗨: 잘못된 아이디 형식입니다.😓";
											message($send);
											
											$send['elementsTitle'] = "이전 검색 기록";
											$send['elementsButtonsTitle'] = $search_text_prev;
											messageTemplate($send);
											
											$send['text'] = "👁‍🗨: 다시 검색하고싶다면 이전에 검색에 성공한 전적을 선택하거나, 새로운 아이디를 아래 형식에 맞게 입력해주세요.🤗";
											$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";											
										} else {
											$send['text'] = "👁‍🗨: 잘못된 아이디 형식입니다.😓";
											$send['text'] .= "\n\n배틀그라운드 아이디 형식은 [영문], [숫자], [-(하이픈), _(언더바)]로 이루어진 4~16글자 입니다.";
											$send['text'] .= "\n\n확인 후 다시 입력해주세요.😍";
										}
										$send['payload'] = $send['title'] = array('초기화면');
										messageQR($send);
									}
								}
							} else {
								$query = "SELECT DISTINCT search FROM logging WHERE userkey='$senderID' AND inProgress='SEARCH_FIN' ORDER BY inputTime DESC LIMIT 9";
								$sql4logging = $conn->query($query);
								while($row4logging = $sql4logging->fetch_assoc()) {
									$search_text_prev[] = $row4logging['search'];
								}
								if(count($search_text_prev) > 0) {
									$send['text'] = "👁‍🗨: 시즌은 숫자 또는 공백(=현재 정규 시즌)으로만 입력해주세요.😓";
									message($send);
									
									$send['elementsTitle'] = "이전 검색 기록";
									$send['elementsButtonsTitle'] = $search_text_prev;
									messageTemplate($send);
									
									$send['text'] = "👁‍🗨: 다시 검색하고싶다면 이전에 검색에 성공한 전적을 선택하거나, 새로운 아이디를 아래 형식에 맞게 입력해주세요.🤗";
									$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";			
								} else {
									$send['text'] = "👁‍🗨: 시즌은 숫자 또는 공백(=현재 정규 시즌)으로만 입력해주세요.😓";
								}
								$send['payload'] = $send['title'] = array('초기화면');
								messageQR($send);	
							}
						} else {
							$query = "SELECT DISTINCT search FROM logging WHERE userkey='$senderID' AND inProgress='SEARCH_FIN' ORDER BY inputTime DESC LIMIT 9";
							$sql4logging = $conn->query($query);
							while($row4logging = $sql4logging->fetch_assoc()) {
								$search_text_prev[] = $row4logging['search'];
							}
							if(count($search_text_prev) > 0) {
								$send['text'] = "👁‍🗨: 모르는 서버네요..😓";
								message($send);
								
								$send['elementsTitle'] = "이전 검색 기록";
								$send['elementsButtonsTitle'] = $search_text_prev;
								messageTemplate($send);
								
								$send['text'] = "👁‍🗨: 다시 검색하고싶다면 이전에 검색에 성공한 전적을 선택하거나, 새로운 아이디를 아래 형식에 맞게 입력해주세요.🤗";
								$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";			
							} else {
								$send['text'] = "👁‍🗨: 모르는 서버네요..😓";
								$send['text'] .= "\n아래 형식에 맞게 다시 입력해주세요.😍";
								$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";
								$send['text'] .= "\n서버 종류 👉[한국(kr), 카카오(kakao), 아시아(as), 남아메리카(sa), 북아메리카(na), 오세아니아(oc), 유럽(eu), 동남아시아(sea)]";
								
								$query = "SELECT season_ko FROM season WHERE season_current='1' AND server='pc-krjp'";
								$sql4season = $conn->query($query)->fetch_assoc();
								preg_match_all("/[\d]{1,5}/", $sql4season['season_ko'], $match);
								$current_season_num = $match[0][0];
								$send['text'] .= "\n현재 시즌 👉 한국(kr)서버 기준, 정규 [{$current_season_num}] 시즌";
								$send['text'] .= "\n\n예시1) 카카오/5/AFTV_Benz 👉 [카카오서버, 정규 5 시즌, AFTV_Benz] 시즌전적";
								$send['text'] .= "\n예시2) kr//YoonRoot 👉 [한국서버, 현재 정규 시즌(정규 5 시즌), YoonRoot] 시즌전적";
							}
							$send['payload'] = $send['title'] = array('초기화면');
							messageQR($send);
						}
					} else {
						$query = "SELECT DISTINCT search FROM logging WHERE userkey='$senderID' AND inProgress='SEARCH_FIN' ORDER BY inputTime DESC LIMIT 9";
						$sql4logging = $conn->query($query);
						while($row4logging = $sql4logging->fetch_assoc()) {
							$search_text_prev[] = $row4logging['search'];
						}
						if(count($search_text_prev) > 0) {
							$send['text'] = "👁‍🗨: 몇 개 빠진것 같습니다..?😓";
							message($send);
							
							$send['elementsTitle'] = "이전 검색 기록";
							$send['elementsButtonsTitle'] = $search_text_prev;
							messageTemplate($send);
							
							$send['text'] = "👁‍🗨: 다시 검색하고싶다면 이전에 검색에 성공한 전적을 선택하거나, 새로운 아이디를 아래 형식에 맞게 입력해주세요.🤗";
							$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";			
						} else {
							$send['text'] = "👁‍🗨: 몇 개 빠진것 같습니다..?😓";
							$send['text'] .= "\n아래 형식에 맞게 다시 입력해주세요.😍";
							$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";
							$send['text'] .= "\n서버 종류 👉[한국(kr), 카카오(kakao), 아시아(as), 남아메리카(sa), 북아메리카(na), 오세아니아(oc), 유럽(eu), 동남아시아(sea)]";
							
							$query = "SELECT season_ko FROM season WHERE season_current='1' AND server='pc-krjp'";
							$sql4season = $conn->query($query)->fetch_assoc();
							preg_match_all("/[\d]{1,5}/", $sql4season['season_ko'], $match);
							$current_season_num = $match[0][0];
							$send['text'] .= "\n현재 시즌 👉 한국(kr)서버 기준, 정규 [{$current_season_num}] 시즌";
							$send['text'] .= "\n\n예시1) 카카오/5/AFTV_Benz 👉 [카카오서버, 정규 5 시즌, AFTV_Benz] 시즌전적";
							$send['text'] .= "\n예시2) kr//YoonRoot 👉 [한국서버, 현재 정규 시즌(정규 5 시즌), YoonRoot] 시즌전적";
						}
						$send['payload'] = $send['title'] = array('초기화면');
						messageQR($send);
					}
				} else {
					$query = "SELECT DISTINCT search FROM logging WHERE userkey='$senderID' AND inProgress='SEARCH_FIN' ORDER BY inputTime DESC LIMIT 9";
					$sql4logging = $conn->query($query);
					while($row4logging = $sql4logging->fetch_assoc()) {
						$search_text_prev[] = $row4logging['search'];
					}
					if(count($search_text_prev) > 0) {
						$send['elementsTitle'] = "이전 검색 기록";
						$send['elementsButtonsTitle'] = $search_text_prev;
						messageTemplate($send);
						
						$send['text'] = "👁‍🗨: 이전에 검색에 성공한 전적을 선택하거나, 새로운 아이디를 아래 형식에 맞게 입력해주세요.🤗";
						$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";
					} else {
						$send['text'] = "👁‍🗨: 전적을 보고 싶은 아이디를 아래 형식에 맞게 입력해주세요.🤗";
						$send['text'] .= "\n\n❗️검색 형식: 서버/몇 번째 정규 시즌/아이디";
						$send['text'] .= "\n서버 종류 👉[한국(kr), 카카오(kakao), 아시아(as), 남아메리카(sa), 북아메리카(na), 오세아니아(oc), 유럽(eu), 동남아시아(sea)]";
						
						$query = "SELECT season_ko FROM season WHERE season_current='1' AND server='pc-krjp'";
						$sql4season = $conn->query($query)->fetch_assoc();
						preg_match_all("/[\d]{1,5}/", $sql4season['season_ko'], $match);
						$current_season_num = $match[0][0];
						$send['text'] .= "\n현재 시즌 👉 한국(kr)서버 기준, 정규 [{$current_season_num}] 시즌";
						$send['text'] .= "\n\n예시1) 카카오/5/AFTV_Benz 👉 [카카오서버, 정규 5 시즌, AFTV_Benz] 시즌전적";
						$send['text'] .= "\n예시2) kr//YoonRoot 👉 [한국서버, 현재 정규 시즌(정규 {$current_season_num} 시즌), YoonRoot] 시즌전적";
					}
					$send['payload'] = $send['title'] = array('초기화면');
					messageQR($send);
				}
			}		
		}
		else if(preg_match("/ALARM/", $inProgress)) {
			if(preg_match("/REALTIME/", $inProgress)) {
				if(preg_match("/TUTORIAL/", $inProgress)) {
					if(preg_match("/TUTORIAL$/", $inProgress)) {
						if($payloadQR) {
							if($payloadQR == "실시간 전적 알림 받아보기") {
								$send['text'] = "[실시간 전적 알림 받기]를 활성화시키면 {$senderFullName}님이 [{$userInfo[0]['name']}] 아이디로 진행한 게임이 종료되고 데이터가 수집되고 난 후, 해당 게임에 대한 전적이 실시간으로 전달됩니다.";
								$send['text'] .= "\n또한 [실시간 전적 알림 받기]는 언제든지 비활성화가 가능합니다.";
								$send['text'] .= "\n\n설명은 여기까지 하고, 이제부터 【배그봇】의 모든 기능을 자유롭게 사용하실 수 있습니다.🎉🎉";
								$send['text'] .= "\n\n배린이는 배린이 탈출까지, 덜 고인물은 고인물이 될 때까지, 고인물은 랭커까지‼️";
								$send['text'] .= "【배그봇】이 {$senderFullName}님을 항상 응원합니다. 파이팅‼️";
								message($send);								

								$query = "UPDATE user SET alarmActivation='1' WHERE userkey='$senderID'";
								$conn->query($query);
							
								$query = queryInsert('logging', 'ALARM_REALTIME_TUTORIAL_FINISH');
								$conn->query($query);
								$query = queryInsert('logging', 'START');
								$conn->query($query);
								
								$send['title'] = $send['buttonsTitle'] = $send['payload'] = array("나의 시즌 전적 보기", "전적 검색", "부계정 등록하기");
								$imagePath = 'https://bhandy.kr/pbg/image/';
								$send['imageURL'] = array($imagePath.'stats.jpg', $imagePath.'search.jpg', $imagePath.'sign_up.jpg');
								messageTemplateLeftSlideWithImage($send);								

								$send['text'] = "👁‍🗨: 실시간 알림 받기를 비활성화하려면 아래 버튼을 눌러주세요.";
								$send['payload'] = $send['title'] = array('실시간 전적 알림 끄기');
								messageQR($send);								
							}
							else if($payloadQR == "건너뛰기") {
								$send['text'] = "👁‍🗨: [실시간 전적 알림 받기] 기능은 언제든 켜고 끌 수 있지만 설명은 이번 한 번 뿐입니다.";
								$send['text'] .= "\n\n정말로 건너뛰시겠어요?😓";
								$send['payload'] = $send['title'] = array('⭕', '❌');
								messageQR($send);
													
								$query = queryInsert('logging', 'ALARM_REALTIME_TUTORIAL_SKIP');
								$conn->query($query);								
							}
						} else {
							$send['text'] = "👁‍🗨: 실시간 전적 알림을 받기 위해 아래의 [실시간 전적 알림 받기] 버튼을 눌러주세요.😍";
							$send['payload'] = $send['title'] = array('실시간 전적 알림 받기', '건너뛰기');
							messageQR($send);
						}
					}
					else if(preg_match("/SKIP$/", $inProgress)) {
						if($payloadQR) {
							if($payloadQR == '⭕') {
								$query = queryInsert('logging', 'ALARM_REALTIME_TUTORIAL_FINISH');
								$conn->query($query);
								$query = queryInsert('logging', 'START');
								$conn->query($query);
																
								$send['text'] = "👁‍🗨: [실시간 전적 알림 받기]에 대한 설명을 건너뛰었습니다.😔";
								$send['text'] .= "\n\n이제부터 【배그봇】의 모든 기능을 자유롭게 사용하실 수 있습니다.🎉🎉";
								message($send);	
		
								$send['title'] = $send['buttonsTitle'] = $send['payload'] = array("나의 시즌 전적 보기", "전적 검색", "부계정 등록하기");
								$imagePath = 'https://bhandy.kr/pbg/image/';
								$send['imageURL'] = array($imagePath.'stats.jpg', $imagePath.'search.jpg', $imagePath.'sign_up.jpg');
								messageTemplateLeftSlideWithImage($send);
								
								$send['text'] = "👁‍🗨: 실시간 알림 받기를 활성화하려면 아래 버튼을 눌러주세요.";
								$send['payload'] = $send['title'] = array('실시간 전적 알림 받기', "계정 삭제하기");
								messageQR($send);
							}
							else if($payloadQR == '❌') {
								$send['text'] = "👁‍🗨: [실시간 전적 알림 받기]에 대한 설명 건너뛰기가 취소되었습니다.😍";
								message($send);	
								
								$send['text'] = "👁‍🗨: 실시간 전적 알림을 받기 위해 아래의 [실시간 전적 알림 받기] 버튼을 눌러주세요.😍";
								$send['payload'] = $send['title'] = array('실시간 전적 알림 받기', '건너뛰기');
								messageQR($send);
								
								$query = queryInsert('logging', 'ALARM_REALTIME_TUTORIAL');
								$conn->query($query);
							}
						} else {
							$send['text'] = "👁‍🗨: [실시간 전적 알림 받기] 기능은 언제든 켜고 끌 수 있지만 설명은 이번 한 번 뿐입니다.";
							$send['text'] .= "\n\n정말로 건너뛰시겠어요?😓";
							$send['payload'] = $send['title'] = array('⭕', '❌');
							messageQR($send);							
						}
					}
				}				
			}
		}
		else if(preg_match("/MY_STATS/", $inProgress)) {
			if(preg_match("/SELECT_ID$/", $inProgress)) {
				if($payloadQR) {
					$selectedName = $payloadQR;
					$query = "SELECT name, accountID, mainServer FROM user WHERE name='$selectedName'";
					$sql4user = $conn->query($query)->fetch_assoc();
					$name = $sql4user['name'];
					$accountID = $sql4user['accountID'];
					$mainServer = $sql4user['mainServer'];
					$query = "SELECT season_ko FROM season WHERE server='$mainServer' AND season_current='1'";
					$sql4season = $conn->query($query)->fetch_assoc();
					$season_ko = $sql4season['season_ko'];
					
					$send['text'] = "👁‍🗨: {$season_ko} 시즌 전적 이미지를 생성중입니다. 잠시만 기다려주세요.😍";
					message($send);
					
					// make images about season stats
					mkSeasonStats($senderID, $name, $accountID, $mainServer, $season_ko);
					
					$userSeasonStatsDir = $_SERVER["DOCUMENT_ROOT"] . "/pbg/stats/season/{$senderID}/{$mainServer}/{$name}";
					$handle = opendir($userSeasonStatsDir);
					$userSeasonStatsImages = array();
					while (false !== ($filename = readdir($handle))) {
					    if($filename == "." || $filename == ".."){
					        continue;
					    }
					    if(is_file($userSeasonStatsDir . "/" . $filename)){
					        $userSeasonStatsImages[] = $filename;
					    }
					}
					closedir($handle);
					
					$old_imageNumber = "";
					if(count($userSeasonStatsImages) > 0) {
						foreach($userSeasonStatsImages as $images) {
							preg_match_all("/[\d]{1,5}/", $images, $old_imageNumber);
							$old_imageNumber = $old_imageNumber[0][0];
							if($old_imageNumber) {
								break;
							}
						}
					}	
					
					$modeSort = array("solo", "duo", "squad", "solo_fpp", "duo_fpp", "squad_fpp");
					$seasonStatsUrl = "https://bhandy.kr/pbg/stats/season/{$senderID}/{$mainServer}/{$name}/";
					$sortedUserSeasonStats = array();
					$sortedUserSeasonStats_title = array();
					foreach($modeSort as $ms) {
						foreach($userSeasonStatsImages as $ussi) {
							$userSeasonStatsImagesNoExtn = preg_replace("/_[\d]{1,5}$/", "", str_replace(".jpg", "", $ussi));
							if($userSeasonStatsImagesNoExtn == $ms) {
								$sortedUserSeasonStats[] = $seasonStatsUrl.$ms."_".$old_imageNumber.".jpg";
							}
						}
					}
					foreach($sortedUserSeasonStats as $suss) {
						$sortedUserSeasonStats_title[] = ucwords(str_replace($seasonStatsUrl, "", preg_replace("/_[\d]{1,5}\.jpg$/", "", $suss)));
					}
					
					// 현재 시즌 전적
					if(count($sortedUserSeasonStats) > 0) {
						$send['text'] = "👁‍🗨: {$senderFullName}님의 아이디 [{$name}]의 {$season_ko} 시즌 전적을 알려드립니다.✌️";
						message($send);		
						
						$send['title'] = $sortedUserSeasonStats_title;
						$send['imgUrl'] = $sortedUserSeasonStats;
						messageImageList($send);
	
						$query = "SELECT season_ko FROM season WHERE server='$mainServer' AND season_ko LIKE '정규%'";
						$sql4season = $conn->query($query);
						while($row4season = $sql4season->fetch_assoc()) {
							$seasonPrev[] = $row4season['season_ko'];
						}
						array_unshift($seasonPrev, "주 서버 변경하기");
						array_unshift($seasonPrev, "초기화면");
						$send['text'] = "👁‍🗨: 이전 시즌의 전적을 보고싶다면 원하는 시즌의 버튼을 눌러주세요.";
						$send['payload'] = $send['title'] = $seasonPrev;
						messageQR($send);
					} else {
						$season_ko_exp = explode(" ", $season_ko);
						$season_ko_prev = $season_ko_exp[0] . " " . ($season_ko_exp[1] - 1);
						
						// make images about season stats with previous season agian
						mkSeasonStats($senderID, $name, $accountID, $mainServer, $season_ko_prev);
						
						$userSeasonStatsDir = $_SERVER["DOCUMENT_ROOT"] . "/pbg/stats/season/{$senderID}/{$mainServer}/{$name}";
						$handle = opendir($userSeasonStatsDir);
						$userSeasonStatsImages = array();
						while (false !== ($filename = readdir($handle))) {
						    if($filename == "." || $filename == ".."){
						        continue;
						    }
						    if(is_file($userSeasonStatsDir . "/" . $filename)){
						        $userSeasonStatsImages[] = $filename;
						    }
						}
						closedir($handle);
						
						$old_imageNumber = "";
						if(count($userSeasonStatsImages) > 0) {
							foreach($userSeasonStatsImages as $images) {
								preg_match_all("/[\d]{1,5}/", $images, $old_imageNumber);
								$old_imageNumber = $old_imageNumber[0][0];
								if($old_imageNumber) {
									break;
								}
							}
						}	
						
						$modeSort = array("solo", "duo", "squad", "solo_fpp", "duo_fpp", "squad_fpp");
						$seasonStatsUrl = "https://bhandy.kr/pbg/stats/season/{$senderID}/{$mainServer}/{$name}/";
						$sortedUserSeasonStats = array();
						$sortedUserSeasonStats_title = array();
						foreach($modeSort as $ms) {
							foreach($userSeasonStatsImages as $ussi) {
								$userSeasonStatsImagesNoExtn = preg_replace("/_[\d]{1,5}$/", "", str_replace(".jpg", "", $ussi));
								if($userSeasonStatsImagesNoExtn == $ms) {
									$sortedUserSeasonStats[] = $seasonStatsUrl.$ms."_".$old_imageNumber.".jpg";
								}
							}
						}
						foreach($sortedUserSeasonStats as $suss) {
							$sortedUserSeasonStats_title[] = ucwords(str_replace($seasonStatsUrl, "", preg_replace("/_[\d]{1,5}\.jpg$/", "", $suss)));
						}
						
						// 이전 시즌 전적
						if(count($sortedUserSeasonStats) > 0) {
							$send['text'] = "👁‍🗨: {$senderFullName}님의 아이디 [{$name}]의 {$season_ko} 시즌 전적 정보가 없어, {$season_ko_prev} 시즌 전적을 알려드립니다.✌️";
							message($send);						
							
							$send['title'] = $sortedUserSeasonStats_title;
							$send['imgUrl'] = $sortedUserSeasonStats;
							messageImageList($send);
							
							$query = "SELECT season_ko FROM season WHERE server='$mainServer' AND season_ko LIKE '정규%'";
							$sql4season = $conn->query($query);
							while($row4season = $sql4season->fetch_assoc()) {
								$seasonPrev[] = $row4season['season_ko'];
							}
							array_unshift($seasonPrev, "주 서버 변경하기");
							array_unshift($seasonPrev, "초기화면");
							$send['text'] = "👁‍🗨: 이전 시즌의 전적을 보고싶다면 원하는 시즌의 버튼을 눌러주세요.";
							$send['payload'] = $send['title'] = $seasonPrev;
							messageQR($send);
						}
						// (현재 && 이전) 시즌 없음 -> 다른 시즌 전적 검색 유도
						else {
							$query = "SELECT season_ko FROM season WHERE server='$mainServer' AND season_ko LIKE '정규%'";
							$sql4season = $conn->query($query);
							while($row4season = $sql4season->fetch_assoc()) {
								$seasonPrev[] = $row4season['season_ko'];
							}
							array_unshift($seasonPrev, "주 서버 변경하기");
							array_unshift($seasonPrev, "초기화면");
							$send['text'] = "👁‍🗨: {$senderFullName}님의 아이디 [{$name}]의 {$season_ko} 시즌 전적과 {$season_ko_prev} 시즌 전적이 모두 존재하지않네요.😭️";
							$send['text'] .= "\n\n더 이전의 시즌 전적을 보기 원하시면 아래의 원하는 시즌의 버튼을 눌러주세요.";
							$send['payload'] = $send['title'] = $seasonPrev;
							messageQR($send);
						}
					}	
					$query = queryInsert('logging', 'MY_STATS_SELECT_SEASON', array("name"=>$name, "accountID"=>$accountID, "mainServer"=>$mainServer));
					$conn->query($query);
				} else {
					$countUserInfo = count($userInfo);
					for($i=0; $i<$countUserInfo; $i++) {
						$userInfoNames[$i] = $userInfo[$i]['name'];
					}
					$send['text'] = "👁‍🗨: 전적을 보고 싶은 아이디를 선택해주세요.";
					array_unshift($userInfoNames, "초기화면");
					$send['payload'] = $send['title'] = $userInfoNames;
					messageQR($send);					
				}
			}
			else if(preg_match("/SELECT_SEASON$/", $inProgress)) {
				if($payloadQR) {
					$selectedSeason_ko = $payloadQR;
					// make images about season stats
					mkSeasonStats($senderID, $loggingName, $loggingAccountID, $loggingMainServer, $selectedSeason_ko);
					
					$userSeasonStatsDir = $_SERVER["DOCUMENT_ROOT"] . "/pbg/stats/season/{$senderID}/{$loggingMainServer}/{$loggingName}";
					$handle = opendir($userSeasonStatsDir);
					$userSeasonStatsImages = array();
					while (false !== ($filename = readdir($handle))) {
					    if($filename == "." || $filename == ".."){
					        continue;
					    }
					    if(is_file($userSeasonStatsDir . "/" . $filename)){
					        $userSeasonStatsImages[] = $filename;
					    }
					}
					closedir($handle);
					
					$old_imageNumber = "";
					if(count($userSeasonStatsImages) > 0) {
						foreach($userSeasonStatsImages as $images) {
							preg_match_all("/[\d]{1,5}/", $images, $old_imageNumber);
							$old_imageNumber = $old_imageNumber[0][0];
							if($old_imageNumber) {
								break;
							}
						}
					}	
					
					$modeSort = array("solo", "duo", "squad", "solo_fpp", "duo_fpp", "squad_fpp");
					$seasonStatsUrl = "https://bhandy.kr/pbg/stats/season/{$senderID}/{$loggingMainServer}/{$loggingName}/";
					$sortedUserSeasonStats = array();
					$sortedUserSeasonStats_title = array();
					foreach($modeSort as $ms) {
						foreach($userSeasonStatsImages as $ussi) {
							$userSeasonStatsImagesNoExtn = preg_replace("/_[\d]{1,5}$/", "", str_replace(".jpg", "", $ussi));
							if($userSeasonStatsImagesNoExtn == $ms) {
								$sortedUserSeasonStats[] = $seasonStatsUrl.$ms."_".$old_imageNumber.".jpg";
							}
						}
					}
					foreach($sortedUserSeasonStats as $suss) {
						$sortedUserSeasonStats_title[] = ucwords(str_replace($seasonStatsUrl, "", preg_replace("/_[\d]{1,5}\.jpg$/", "", $suss)));
					}
					
					// 해당 시즌 전적
					if(count($sortedUserSeasonStats) > 0) {
						$send['text'] = "👁‍🗨: {$senderFullName}님의 아이디 [{$loggingName}]의 {$selectedSeason_ko} 시즌 전적을 알려드립니다.✌️";
						message($send);				
						
						$send['title'] = $sortedUserSeasonStats_title;
						$send['imgUrl'] = $sortedUserSeasonStats;
						messageImageList($send);
						
						$query = "SELECT season_ko FROM season WHERE server='{$loggingMainServer}' AND season_ko LIKE '정규%'";
						$sql4season = $conn->query($query);
						while($row4season = $sql4season->fetch_assoc()) {
							$seasonPrev[] = $row4season['season_ko'];
						}
						array_unshift($seasonPrev, "주 서버 변경하기");
						array_unshift($seasonPrev, "초기화면");
						$send['text'] = "👁‍🗨: 이전 시즌의 전적을 보고싶다면 원하는 시즌의 버튼을 눌러주세요.";
						$send['payload'] = $send['title'] = $seasonPrev;
						messageQR($send);
					} else {
						$send['text'] = "👁‍🗨: {$senderFullName}님의 아이디 [{$loggingName}]의 {$selectedSeason_ko} 시즌 전적이 없네요.😓️";
						message($send);	
						
						$query = "SELECT season_ko FROM season WHERE server='{$loggingMainServer}' AND season_ko LIKE '정규%'";
						$sql4season = $conn->query($query);
						while($row4season = $sql4season->fetch_assoc()) {
							$seasonPrev[] = $row4season['season_ko'];
						}
						array_unshift($seasonPrev, "주 서버 변경하기");
						array_unshift($seasonPrev, "초기화면");
						$send['text'] = "👁‍🗨: 이전 시즌의 전적을 보고싶다면 원하는 시즌의 버튼을 눌러주세요.";
						$send['payload'] = $send['title'] = $seasonPrev;
						messageQR($send);
					}
				} else {
					$query = "SELECT season_ko FROM season WHERE server='$loggingMainServer' AND season_ko LIKE '정규%'";
					$sql4season = $conn->query($query);
					while($row4season = $sql4season->fetch_assoc()) {
						$seasonPrev[] = $row4season['season_ko'];
					}
					array_unshift($seasonPrev, "주 서버 변경하기");
					array_unshift($seasonPrev, "초기화면");
					$send['text'] = "👁‍🗨: 이전 시즌의 전적을 보고싶다면 원하는 시즌의 버튼을 눌러주세요.";
					$send['payload'] = $send['title'] = $seasonPrev;
					messageQR($send);					
				}
			}
		}
		else if(preg_match("/DELETE/", $inProgress)) {
			if(preg_match("/DELETE$/", $inProgress)) {
				if($payloadQR) {
					if($payloadQR == '⭕') {
						$userIDcount = count($userInfo);
						for($i=0; $i<$userIDcount; $i++) {
							$userInfoNames[] = $userInfo[$i]['name'];
						}
						$userInfoNamesImp = implode(", ", $userInfoNames);
						$send['text'] = "👁‍🗨: {$senderFullName}님께서는";
						$send['text'] .= "\n[$userInfoNamesImp] $userIDcount개의 아이디을 등록하셨습니다.";
						$send['text'] .= "\n어떤 아이디를 삭제하시겠습니까?";
						array_unshift($userInfoNames, "초기화면");
						$send['payload'] = $send['title'] = $userInfoNames;
						messageQR($send);
						
						$query = queryInsert('logging', 'DELETE_SELECT_ID');
						$conn->query($query);						
					}
					else if($payloadQR == '❌') {
						// check -> inProgress='ALARM_REALTIME_TUTORIAL_SKIP'
						$query = "SELECT inProgress FROM logging WHERE userkey='$senderID' AND inProgress='ALARM_REALTIME_TUTORIAL_SKIP'";
						$checkTutorialFinish = $conn->query($query)->fetch_assoc();
						
						// check -> realtime alarm
						$query = "SELECT alarmActivation FROM user WHERE userkey='$senderID' ORDER BY inputTime DESC LIMIT 1";
						$checkRealtime = $conn->query($query)->fetch_assoc();
						$checkRealtime['alarmActivation'] == 1 ? $realtimeAlarm_ko = "실시간 전적 알림 끄기" : $realtimeAlarm_ko = "실시간 전적 알림 받기";
						$checkRealtime['alarmActivation'] == 1 ? $realtimeAlarm_text = "비활성화" : $realtimeAlarm_text = "활성화";
						
						if(!$checkTutorialFinish) {
							$send['text'] = "👁‍🗨: 안녕하세요. {$senderFullName}님";
							$send['text'] .= "\n\n[실시간 전적 알림 받기]를 활성화 해볼까요?";
							$send['text'] .= "\n아래의 [실시간 전적 알림 받기] 버튼을 눌러주세요.😍";
							$send['payload'] = $send['title'] = array('실시간 전적 알림 받기', '건너뛰기');
							messageQR($send);
							
							$query = queryInsert('logging', 'ALARM_REALTIME_TUTORIAL');
							$conn->query($query);
						} else {
							$send['text'] = "👁‍🗨: {$senderFullName}님 반가워요.";
							message($send);
							
							$send['title'] = $send['buttonsTitle'] = $send['payload'] = array("나의 시즌 전적 보기", "전적 검색", "부계정 등록하기");
							$imagePath = 'https://bhandy.kr/pbg/image/';
							$send['imageURL'] = array($imagePath.'stats.jpg', $imagePath.'search.jpg', $imagePath.'sign_up.jpg');
							messageTemplateLeftSlideWithImage($send);
			
							$send['text'] = "👁‍🗨: 실시간 알림 받기를 {$realtimeAlarm_text}하려면 아래 버튼을 눌러주세요.";
							$send['payload'] = $send['title'] = array($realtimeAlarm_ko, "계정 삭제하기");
							messageQR($send);
							
							$query = queryInsert('logging', 'START');
							$conn->query($query);
						}						
					}
				} else {
					$send['text'] = "👁‍🗨: 지금 등록된 아이디 중 하나를 삭제하시겠습니까?";
					$send['payload'] = $send['title'] = array('⭕', '❌');
					messageQR($send);					
				}
			}
			else if(preg_match("/SELECT_ID$/", $inProgress)) {
				if($payloadQR) {
					$selectedName = $payloadQR;
					$send['text'] = "👁‍🗨: 【{$selectedName}】를 정말로 삭제하시겠습니까?";
					$send['text'] .= "\n(주의❗️ 한번 삭제된 아이디는 다시 복구할 수 없고, 모든 알림은 비활성화됩니다.)";
					$send['payload'] = $send['title'] = array('⭕', '❌');
					messageQR($send);		
					
					$query = queryInsert('logging', 'DELETE_CHECK', array('name' => $selectedName));
					$conn->query($query);	
				} else {
					$userIDcount = count($userInfo);
					for($i=0; $i<$userIDcount; $i++) {
						$userInfoNames[] = $userInfo[$i]['name'];
					}
					$userInfoNamesImp = implode(", ", $userInfoNames);
					$send['text'] = "👁‍🗨: {$senderFullName}님께서는";
					$send['text'] .= "\n[$userInfoNamesImp] $userIDcount개의 아이디을 등록하셨습니다.";
					$send['text'] .= "\n어떤 아이디를 삭제하시겠습니까?";
					array_unshift($userInfoNames, "초기화면");
					$send['payload'] = $send['title'] = $userInfoNames;
					messageQR($send);
				}
			}
			else if(preg_match("/CHECK$/", $inProgress)) {
				if($payloadQR) {
					if($payloadQR == '⭕') {
						$query = "UPDATE user SET userActivation='0', alarmActivation='0' WHERE userkey='$senderID'";
						$conn->query($query);						
						
						$send['text'] = "👁‍🗨: 【{$loggingName}】 아이디가 삭제되었습니다.";
						message($send);
						
						$send['text'] = "👁‍🗨: 아래 버튼을 눌러 초기화면으로 이동해주세요.😍";
						$send['payload'] = $send['title'] = array('초기화면');
						messageQR($send);						
					}
					else if($payloadQR == '❌') {
						// check -> inProgress='ALARM_REALTIME_TUTORIAL_SKIP'
						$query = "SELECT inProgress FROM logging WHERE userkey='$senderID' AND inProgress='ALARM_REALTIME_TUTORIAL_SKIP'";
						$checkTutorialFinish = $conn->query($query)->fetch_assoc();
						
						// check -> realtime alarm
						$query = "SELECT alarmActivation FROM user WHERE userkey='$senderID' ORDER BY inputTime DESC LIMIT 1";
						$checkRealtime = $conn->query($query)->fetch_assoc();
						$checkRealtime['alarmActivation'] == 1 ? $realtimeAlarm_ko = "실시간 전적 알림 끄기" : $realtimeAlarm_ko = "실시간 전적 알림 받기";
						$checkRealtime['alarmActivation'] == 1 ? $realtimeAlarm_text = "비활성화" : $realtimeAlarm_text = "활성화";
						
						if(!$checkTutorialFinish) {
							$send['text'] = "👁‍🗨: 안녕하세요. {$senderFullName}님";
							$send['text'] .= "\n\n[실시간 전적 알림 받기]를 활성화 해볼까요?";
							$send['text'] .= "\n아래의 [실시간 전적 알림 받기] 버튼을 눌러주세요.😍";
							$send['payload'] = $send['title'] = array('실시간 전적 알림 받기', '건너뛰기');
							messageQR($send);
							
							$query = queryInsert('logging', 'ALARM_REALTIME_TUTORIAL');
							$conn->query($query);
						} else {
							$send['text'] = "👁‍🗨: {$senderFullName}님 반가워요.";
							message($send);
							
							$send['title'] = $send['buttonsTitle'] = $send['payload'] = array("나의 시즌 전적 보기", "전적 검색", "부계정 등록하기");
							$imagePath = 'https://bhandy.kr/pbg/image/';
							$send['imageURL'] = array($imagePath.'stats.jpg', $imagePath.'search.jpg', $imagePath.'sign_up.jpg');
							messageTemplateLeftSlideWithImage($send);
			
							$send['text'] = "👁‍🗨: 실시간 알림 받기를 {$realtimeAlarm_text}하려면 아래 버튼을 눌러주세요.";
							$send['payload'] = $send['title'] = array($realtimeAlarm_ko, "계정 삭제하기");
							messageQR($send);
							
							$query = queryInsert('logging', 'START');
							$conn->query($query);
						}										
					}
				} else {
					$send['text'] = "👁‍🗨: 【{$loggingName}】를 정말로 삭제하시겠습니까?";
					$send['text'] .= "\n(주의❗️ 한번 삭제된 아이디는 다시 복구할 수 없고, 모든 알림은 비활성화됩니다.)";
					$send['payload'] = $send['title'] = array('⭕', '❌');
					messageQR($send);
				}
			} else {
				if($inProgress == "START" || $inProgress == "ALARM_REALTIME_ON" || $inProgress == "ALARM_REALTIME_OFF") {
					$send['text'] = "👁‍🗨: 잘못된 접근입니다.😓\n아래 버튼을 눌러 초기화면으로 이동해주세요.😍";
					$send['payload'] = $send['title'] = array('초기화면');
					messageQR($send);					
				}
			}
		}
		else if(preg_match("/CHANGE_MAIN_SERVER/", $inProgress)) {
			if(preg_match("/CHANGE_MAIN_SERVER$/", $inProgress)) {
				if($payloadQR) {
					$selectedServer_ko = $payloadQR;
					$query = "SELECT server FROM server WHERE server_ko='$selectedServer_ko'";
					$sql4server = $conn->query($query)->fetch_assoc();
					$selectedServer = $sql4server['server'];
					
					$query = "SELECT server_ko FROM server WHERE server='$loggingMainServer'";
					$sql4server = $conn->query($query)->fetch_assoc();
					$old_mainServer_ko = $sql4server['server_ko'];
					
					$query = "UPDATE user SET mainServer='$selectedServer'
										WHERE userkey='$senderID' AND name='$loggingName' AND accountID='$loggingAccountID' AND mainServer='$loggingMainServer'";
					$conn->query($query);
					
					$send['text'] = "👁‍🗨: {$senderFullName}님의 아이디 [{$loggingName}]의 주 서버가";
					$send['text'] .= "\n[$old_mainServer_ko]서버에서 [$selectedServer_ko]서버로 변경되었습니다.😘";
					$send['text'] .= "\n\n주 서버 변경 적용을 위해 초기화면으로 돌아가주세요.";
					$send['payload'] = $send['title'] = array("초기화면");
					messageQR($send);
				} else {
					$query = "SELECT server_ko FROM server WHERE server='$loggingMainServer'";
					$sql4server = $conn->query($query)->fetch_assoc();
					$old_mainServer_ko = $sql4server['server_ko'];
	
					$query = "SELECT server_ko FROM server WHERE server!='$loggingMainServer'";
					$sql4server = $conn->query($query);
					while($row4server = $sql4server->fetch_assoc()) {
						$serverList_ko[] = $row4server['server_ko'];
					}
					$send['text'] = "👁‍🗨: {$senderFullName}님의 아이디 [{$loggingName}]의 주 서️버를 [{$old_mainServer_ko}]에서 어디로 바꾸시겠어요?";
					array_unshift($serverList_ko, "초기화면");
					$send['payload'] = $send['title'] = $serverList_ko;
					messageQR($send);				
				}
			}
			else if(preg_match("/SELECT_ID$/", $inProgress)) {
				if($payloadQR) {
					$selectedName = $payloadQR;
					$query = "SELECT name, accountID, mainServer FROM user WHERE name='$selectedName'";
					$sql4user = $conn->query($query)->fetch_assoc();
					$name = $sql4user['name'];
					$accountID = $sql4user['accountID'];
					$mainServer = $sql4user['mainServer'];
					$query = "SELECT server_ko FROM server WHERE server!='$mainServer'";
					$sql4server = $conn->query($query);
					while($row4server = $sql4server->fetch_assoc()) {
						$serverList_ko[] = $row4server['server_ko'];
					}
					array_unshift($serverList_ko, "초기화면");
					
					$query = "SELECT server_ko FROM server WHERE server='$mainServer'";
					$sql4server = $conn->query($query)->fetch_assoc();
					$old_mainServer_ko = $sql4server['server_ko'];
							
					$send['text'] = "👁‍🗨: {$senderFullName}님의 아이디 [{$name}]의 주 서️버를 [{$old_mainServer_ko}]에서 어디로 바꾸시겠어요?";	
					$send['payload'] = $send['title'] = $serverList_ko;
					messageQR($send);
					
					$query = queryInsert('logging', 'CHANGE_MAIN_SERVER', array("name"=>$name, "accountID"=>$accountID, "mainServer"=>$mainServer));
					$conn->query($query);	
				} else {
					$countUserInfo = count($userInfo);
					for($i=0; $i<$countUserInfo; $i++) {
						$userInfoNames[] = $userInfo[$i]['name'];
					}
					$userInfoNamesImp = implode(", ", $userInfoNames);
					$send['text'] = "👁‍🗨: {$senderFullName}님께서는";
					$send['text'] .= "\n[$userInfoNamesImp] {$countUserInfo}개의 아이디을 등록하셨습니다.";
					$send['text'] .= "\n어떤 아이디의 주 서버를 변경하시겠어요?";
					array_unshift($userInfoNames, "초기화면");
					$send['payload'] = $send['title'] = $userInfoNames;
					messageQR($send);					
				}
			}
		}
	}
}
*/