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
foreach(glob($_SERVER["DOCUMENT_ROOT"] . '/pbg/function/*.php') as $functionFiles)
{
    include_once $functionFiles;
}
include_once $_SERVER["DOCUMENT_ROOT"] . '/pbg/lib.php';
function prr($data) {echo "<pre>"; print_r($data); echo "</pre>";}
function prrh($data) {prr(htmlspecialchars(print_r($data, true)));}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$conn = new mysqli($dbhost, $dbuser, $dbpass);
$conn -> select_db($db);
$inputTime = date("Y-m-d H:i:s",time());
$thisYear = date("Y");
$now = strtotime($inputTime);
$today = date("Y-m-d", $now);
//
$accessToken = "EAAa9pM2AuqkBAI3M7eZAmH5eiKX9PZBZCFTa8WLXJFu8sf5P6kI9bbV9TalH7RA49JijFQ4BtbKxm2ZC9fvil1QwTyhZBgK3KkUVN5u3FCGTiEFpZAa10X5eSDwcNb0IHKYWqetLxEvnCRwfJrtCmZAe3hjvbRDqCWD5UC5CdmVWUyX1rg7W2Yu";
//
$inputData = json_decode(file_get_contents('php://input'), true);
$senderID = $inputData['entry'][0]['messaging'][0]['sender']['id'];
$messageText = $inputData['entry'][0]['messaging'][0]['message']['text'];
$payload = $inputData['entry'][0]['messaging'][0]['postback']['payload'];
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$appServiceID = "2000094200061648";
$senderID = "2000094200061648";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$pbg_api_key = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJiODdmYjY3MC00MjFiLTAxMzYtNDEwOS0yNTE1Y2RhMTQzMzYiLCJpc3MiOiJnYW1lbG9ja2VyIiwiaWF0IjoxNTI3MjMzNTU2LCJwdWIiOiJibHVlaG9sZSIsInRpdGxlIjoicHViZyIsImFwcCI6InBiZ19ib3QifQ.PIAUY9wARH1kymcHLDsNsQ4xcn7b3N0YtJKh0tTRSNc";
$pbg_api_key_1 = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI1MWFiYzAyMC03MWUzLTAxMzYtZmFiMS0wMWQ3M2ZiNGU4NzUiLCJpc3MiOiJnYW1lbG9ja2VyIiwiaWF0IjoxNTMyNDg2OTg4LCJwdWIiOiJibHVlaG9sZSIsInRpdGxlIjoicHViZyIsImFwcCI6InBiZ19ib3RfMiJ9.wJ3DB6tZUVIaHM14-JzjV7wjtnie092xx4CdRYpE-b8";
$pbg_api_key_2 = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI0MTFlOTYyMC03MWU2LTAxMzYtZjFmNy01Nzc3ZmRiNmJmOTEiLCJpc3MiOiJnYW1lbG9ja2VyIiwiaWF0IjoxNTMyNDg4MjQ5LCJwdWIiOiJibHVlaG9sZSIsInRpdGxlIjoicHViZyIsImFwcCI6InBiZ19ib3RfMyJ9.7RVkFdsLimQnZjXM5zVML9AYxWYGJ7a0Lyk6HSatOi8";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$query = "SELECT * FROM user WHERE userkey='$senderID' AND userActivation='1'";
$sql4user = $conn->query($query);
while($row4user = $sql4user->fetch_assoc()) {
	$userInfo[] = $row4user;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
$query = "SELECT userkey, name, accountID, mainServer FROM user WHERE alarmActivation='1'";
$sql4user = $conn->query($query);
while($row4user = $sql4user->fetch_assoc()) {
	$users[] = $row4user;
}*/
//$mainServer = "pc-as";
//$name = "SILPHTV";
//$accountID = "account.888148dbe06e4288a48b16bc66e915c0";
//
$mainServer = "pc-krjp";
//$name = "EunHu";
$accountID = "account.a700e324b7d04c939ffa72b98036c592";
//
$season = "division.bro.official.2018-07";
//$season_ko = "정규 6";
$name = "KOREA_UNKNOWN";
//$ex_pbg_id = "6270";
//$main_server = "pc-as";
//$pbg_user_match_url = "https://api.playbattlegrounds.com/shards/$mainServer/players/$accountID/seasons/$season";
//$pbg_user_match_url = "https://api.playbattlegrounds.com/shards/$mainServer/players/$accountID";
$pbg_user_match_url = "https://api.pubg.com/shards/$mainServer/players?filter[playerNames]=$name";
//$pbg_user_match_url = "https://api.pubg.com/shards/$mainServer/players?filter[playerNames]=$name";
//$pbg_user_latest_match_url = "https://api.playbattlegrounds.com/shards/$mainServer/matches/f6dbd884-6c3e-4ec6-b4cf-d164ffe4d65b";
//$pbg_user_latest_match_data = pbg_curl_get($pbg_user_latest_match_url);
//prr($pbg_user_latest_match_data); 
//$pbg_user_match_data = pbg_curl_get($pbg_user_match_url);
//prr($pbg_user_match_data);
//$pbg_latest_match = pbg_latest_match($accountID, $mainServer);
//prr($pbg_latest_match);

//$pbg_user_match_url = "https://api.pubg.com/shards/$mainServer/players/$accountID/seasons/$season/ranking";
//$pbg_user_match_url = "https://api.playbattlegrounds.com/shards/$mainServer/players/$accountID";
//$pbg_user_match_data = pbg_curl_get($pbg_user_match_url);
//prr($pbg_user_match_data);

//$pbg_user_match_url = "https://api.pubg.com/tournaments/na-ppc";
//$pbg_user_match_data = pbg_curl_get($pbg_user_match_url);
//prr($pbg_user_match_data);

include_once '/usr/share/nginx/html/pbg/class/html2pdf/html2pdf.class.php';

$users = array();
$query = "SELECT userkey, name, accountID, mainServer FROM user WHERE realtimeActivation='1'";
$sql4user = $conn->query($query);
while($row4user = $sql4user->fetch_assoc()) {
	$users[] = $row4user;
}
$usersCount = count($users);
for($i=0; $i<$usersCount; $i++) {
	if($users[$i]['userkey'] == '2000094200061648') {
		$pbg_latest_match = pbg_latest_match($users[$i]['accountID'], $users[$i]['mainServer']);
			
		$mkLatestMatchPath = "/usr/share/nginx/html/pbg/";
		
		// 공통 데이터
		$data = $pbg_latest_match['latest_match'];
		$time_s = preg_replace('/(0)(\d)/','$2', date("m월d일", $data['start']['timestamp'])) . " " . date("H:i", $data['start']['timestamp']);		// 시작시간
		$time_f = preg_replace('/(0)(\d)/','$2', date("m월d일", $data['finish']['timestamp'])) . " " . date("H:i", $data['finish']['timestamp']);		// 종료시간
		$all_party_count = $data['all_party_count'];		// 총 파티 수
		$rank = $data['rank'];		// 랭크
		$mode = $data['mode'];		// 모드
		$pbg_maps = pbg_maps($data['map']);
		$map_ko = $pbg_maps['ko'];		// 맵(한국어)
		$pbg_server = pbg_server();
		foreach($pbg_server as $value) {
			if($value['server'] == $data['server']) {
				$server_ko = $value['server_ko'];		// 서버(한국어)
			}
		}
	
		$colors = array('#CFF', '#FCF', '#FFC', '#CCF', '#CFC', '#FCC', '#AFF', '#FAF', '#FFA', '#AAF', '#AFA', '#FAA');
		$randomColor = $colors[rand(0, (count($colors) - 1))];

		// 스탯 평가 점수
		if(preg_match("/warmode/", $mode) || preg_match("/normal/", $mode) || preg_match("/flare/", $mode)) {
			$stats_evaluation_user_sum = "--";
		} else {
			$stats_evaluation = stats_evaluation($pbg_latest_match);
			$stats_evaluation_user_sum = $stats_evaluation['user']['sum'];					
		}		

		if($mode != "solo" && $mode != "solo-fpp") {
			$data_members = $data['members'];
			$data_members_count = count($data['members']);
			if($data_members && $data_members_count >= 1 && $data_members_count <= 3) {
				if($data_members_count == 1) {
					$td_width_1 = "20";
					$td_width_2 = "0";
					$td_name_padding_1 = "padding-left:140px;";
					$td_name_padding_2 = "padding-right:140px;";
				}
				if($data_members_count == 2) {
					$td_width_1 = "80";
					$td_width_2 = "0";
					$td_name_padding_1 = "padding-left:40px;";
					$td_name_padding_2 = "padding-right:40px;";
				}
				else if($data_members_count == 3) {
					$td_width_1 = "100";
					$td_width_2 = "30";
					$td_name_padding_1 = "padding-left:3px;";
					$td_name_padding_2 = "padding-right:8px;";
				}
				$td_stats_name_style = "height=11; style='font-size:10; $td_name_padding_1 padding-top:5px;'";
				$td_stats_data_style = "font-size:8; $td_name_padding_2 padding-top:5px;'";
				$td_stats_name_damage_style = "height=18; style='font-size:12; $td_name_padding_1 padding-top:5px;'";
				$td_stats_data_damage_style = "font-size:20; $td_name_padding_2 padding-top:5px;'";
				$td_stats_name_kill_style = "height=18; style='font-size:12; $td_name_padding_1 padding-top:5px;'";
				$td_stats_data_kill_style = "font-size:20; $td_name_padding_2 padding-top:5px;'";
				
				$mkMembers = "";
				$member_name_arr = array();
				for($j=0; $j<$data_members_count; $j++) {
					$member_name = $data_members[$j]['id'];

					$stats_evaluation_member_sum = $stats_evaluation['member'][$j]['sum'] . "점";
					if($stats_evaluation_member_sum == '100') {
						$ev_font_size = "font-size:8px;";
					} else {
						$ev_font_size = "font-size:14px;";
					}
					
					
					if($j == ($data_members_count-1)) {
						if(mb_strlen($member_name) >= 8) {
							$member_name_html[$j] = "<td width=$td_width_1 height=35 style='font-size:14; $td_name_padding_1 border-bottom:3px solid $randomColor;'>$member_name</td><td width=$td_width_2 style='font-size:14; text-align:right; $td_name_padding_2 border-bottom:3px solid $randomColor;'><span style='$ev_font_size'>{$stats_evaluation_member_sum}</span></td>";	
						} else {
							$member_name_html[$j] = "<td width=$td_width_1 height=35 style='font-size:20; $td_name_padding_1 border-bottom:3px solid $randomColor;'>$member_name</td><td width=$td_width_2 style='font-size:20; text-align:right; $td_name_padding_2 border-bottom:3px solid $randomColor;'><span style='$ev_font_size'>{$stats_evaluation_member_sum}</span></td>";	
						}					
						$td_style = "style='text-align:right; ";
					} else {
						if(mb_strlen($member_name) >= 8) {
							$member_name_html[$j] = "<td width=$td_width_1 height=35 style='font-size:14; $td_name_padding_1 border-bottom:3px solid $randomColor;'>$member_name</td><td width=$td_width_2 style='font-size:14; text-align:right; $td_name_padding_2 border-bottom:3px solid $randomColor; border-right:3px dotted $randomColor;'><span style='$ev_font_size'>{$stats_evaluation_member_sum}</span></td>";
						} else {
							$member_name_html[$j] = "<td width=$td_width_1 height=35 style='font-size:20; $td_name_padding_1 border-bottom:3px solid $randomColor;'>$member_name</td><td width=$td_width_2 style='font-size:20; text-align:right; $td_name_padding_2 border-bottom:3px solid $randomColor; border-right:3px dotted $randomColor;'><span style='$ev_font_size'>{$stats_evaluation_member_sum}</span></td>";
						}
						$td_style = "style='text-align:right; border-right:3px dotted $randomColor; ";
					}
					
					$member_stats = $data_members[$j]['stats'];
					$member_timeSurvived = $member_stats['timeSurvived'];
					$member_DBNOs = $member_stats['DBNOs'];
					$member_assists = $member_stats['assists'];
					$member_boosts = $member_stats['boosts'];
					$member_damage = $member_stats['damage'];
					$member_heals = $member_stats['heals'];
					$member_headshot = $member_stats['headshot'];
					$member_kill = $member_stats['kill'];
					$member_longestKill = $member_stats['longestKill']. "m";
					$member_revives = $member_stats['revives'];
					$member_distanceTravelled = number_format($member_stats['distanceTravelled'] / 1000, 2) . "km";
					
					$mkMembers_timeSurvived .= "<td {$td_stats_name_style}>생존시간</td><td {$td_style}{$td_stats_data_style}>$member_timeSurvived</td>";
					$mkMembers_DBNOs .= "<td {$td_stats_name_style}>기절시킴</td><td {$td_style}{$td_stats_data_style}>$member_DBNOs</td>";
					$mkMembers_assists .= "<td {$td_stats_name_style}>어시스트</td><td {$td_style}{$td_stats_data_style}>$member_assists</td>";
					$mkMembers_boosts .= "<td {$td_stats_name_style}>부스트</td><td {$td_style}{$td_stats_data_style}>$member_boosts</td>";
					$mkMembers_damage .= "<td {$td_stats_name_damage_style}>데미지</td><td {$td_style}{$td_stats_data_damage_style}>$member_damage</td>";
					$mkMembers_heals .= "<td {$td_stats_name_style}>힐</td><td {$td_style}{$td_stats_data_style}>$member_heals</td>";
					$mkMembers_kill_headshot .= "<td {$td_stats_name_kill_style}>킬<span style='font-size:10;'>(헤드샷)</span></td><td {$td_style}{$td_stats_data_kill_style}>$member_kill<span style='font-size:10;'>($member_headshot)</span></td>";
					$mkMembers_longestKill .= "<td {$td_stats_name_style}>최대거리킬</td><td {$td_style}{$td_stats_data_style}>$member_longestKill</td>";
					$mkMembers_revives .= "<td {$td_stats_name_style}>부활시킴</td><td {$td_style}{$td_stats_data_style}>$member_revives</td>";
					$mkMembers_distanceTravelled .= "<td {$td_stats_name_style}>이동거리</td><td {$td_style}{$td_stats_data_style}>$member_distanceTravelled</td>";
				}
				$member_name_html_imp = implode("", $member_name_html);

				$mkMembers = "<html><head></head><body>";
				//<table style='border-collapse:collapse;'>
				//<table border=1>
				$mkMembers .= 
				"
					<table style='border-collapse:collapse;'>
						<tr>
							<td rowspan=11 width=10 style='background-color:$randomColor;'></td>
							{$member_name_html_imp}
							<td rowspan=11 width=10 style='background-color:$randomColor;'></td>
						</tr>
						<tr>$mkMembers_kill_headshot</tr>
						<tr>$mkMembers_damage</tr>
						<tr>$mkMembers_timeSurvived</tr>
						<tr>$mkMembers_DBNOs</tr>
						<tr>$mkMembers_assists</tr>
						<tr>$mkMembers_heals</tr>
						<tr>$mkMembers_boosts</tr>
						<tr>$mkMembers_revives</tr>
						<tr>$mkMembers_longestKill</tr>
						<tr>$mkMembers_distanceTravelled</tr>
				";
				$mkMembers .= $mkMembersTable;
				$mkMembers .= "</table></body></html>";

				// HTML -> PDF
				$pdfName_members = $mkLatestMatchPath.'test2.pdf';
				$html2pdf = new HTML2PDF('P', 'A4');
				$html2pdf->setDefaultFont('malgun');
				$html2pdf->writeHTML($mkMembers);
				$html2pdfOutput = $html2pdf->Output($pdfName_members, 'F');
						
				// PDF -> JPG
				$imageName_members = $mkLatestMatchPath.'test.jpg';
				$im = new Imagick();
				$im->setResolution(150,150);
				$im->readImage($pdfName_members);
				$im->flattenImages();
				$im->cropImage(850, 520, 0, 0);
				$im->thumbnailImage(512, 320, 1, 1);
				$im->writeImage($imageName_members);
				$im->clear();
				$im->destroy();
			}
		}	
	}
}

