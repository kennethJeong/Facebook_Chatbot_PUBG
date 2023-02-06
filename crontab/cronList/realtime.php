<?php
include_once '/usr/share/nginx/html/pbg/class/html2pdf/html2pdf.class.php';

$users = array();
$query = "SELECT userkey, name, accountID, mainServer FROM user WHERE realtimeActivation='1'";
$sql4user = $conn->query($query);
while($row4user = $sql4user->fetch_assoc()) {
	$users[] = $row4user;
}
$usersCount = count($users);
for($i=0; $i<$usersCount; $i++) {
	if(substr(date("i"), 1, 2) == substr($i, -1, 1) || substr(date("i"), 1, 2) == substr(($i+5), -1, 1)) {
//	if(substr(date("i"), 1, 2) == substr($i, -1, 1)) {
		$send = array();
		$pbg_latest_match = pbg_latest_match($users[$i]['accountID'], $users[$i]['mainServer']);
		$latest_match_id = $pbg_latest_match['latest_match']['match_id'];	
		$mkLatestMatchPath = "/usr/share/nginx/html/pbg/stats/latestMatch/{$users[$i]['userkey']}/{$users[$i]['mainServer']}/{$users[$i]['name']}";
		$mkLatestMatchURL = "https://bhandy.kr/pbg/stats/latestMatch/{$users[$i]['userkey']}/{$users[$i]['mainServer']}/{$users[$i]['name']}";
		
		$query = "SELECT * FROM alarm WHERE userkey='{$users[$i]['userkey']}' AND name='{$users[$i]['name']}'
																	AND accountID='{$users[$i]['accountID']}' AND server='{$users[$i]['mainServer']}' ORDER BY inputTime DESC LIMIT 1";
		$sql4alarm = $conn->query($query);
		if($sql4alarm->num_rows == 0) {
			umask(0);
			if(!is_dir($mkLatestMatchPath)) {
				if(@mkdir($mkLatestMatchPath, 0777, true)) {
					if(is_dir($mkLatestMatchPath)) {
						@chmod($mkLatestMatchPath, 0777);
					}
				}
			}		
			$query = "INSERT INTO alarm (userkey, name, accountID, server, matchID, inputTime)
													VALUE('{$users[$i]['userkey']}', '{$users[$i]['name']}', '{$users[$i]['accountID']}',
																'{$users[$i]['mainServer']}', '{$latest_match_id}', '{$inputTime}')";
			$conn->query($query);
		} else {
			$prev_match = $sql4alarm->fetch_assoc();
			$prev_match_id = $prev_match['matchID'];
			if($latest_match_id == $prev_match_id || empty($latest_match_id) || $latest_match_id == "") {
				continue;
			} else {
				$handle = opendir($mkLatestMatchPath);
				$mkdLatestMatchImages = array();
				while (false !== ($filename = readdir($handle))) {
				    if($filename == "." || $filename == ".."){
				        continue;
				    }
				    if(is_file($mkLatestMatchPath . "/" . $filename)){
				        $mkdLatestMatchImages[] = $filename;
				    }
				}
				closedir($handle);
		
				$imageNumber = "";
				$old_imageNumbers = array();
				if($mkdLatestMatchImages) {
					foreach($mkdLatestMatchImages as $images) {
						if(preg_match("/stats/", $images)) {
							preg_match_all("/[\d]{1,5}/", $images, $old_imageNumber);
							$old_imageNumbers[] = $old_imageNumber[0][0];
						}
					}
					$imageNumber = max($old_imageNumbers) + 1;
				} else {
					$imageNumber = 1;
				}
	
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
				
				// 스탯 데이터
				$data_stats = $data['stats'];
				$damage = $data_stats['damage'];
				$DBNOs = $data_stats['DBNOs'];
				$assists = $data_stats['assists'];
				$boosts = $data_stats['boosts'];
				$heals = $data_stats['heals'];
				$headshot = $data_stats['headshot'];
				$kill = $data_stats['kill'];
				$timeSurvived = $data_stats['timeSurvived'];
				$longestKill = $data_stats['longestKill'] . "m";
				$revives = $data_stats['revives'];
				$distanceTravelled = number_format($data_stats['distanceTravelled'] / 1000 , 2) . "km";
				$ratingDelta = $data_stats['ratingDelta'];
				if($ratingDelta < 0) {
					$ratingColor = "red";
					$ratingDeltaAbs = abs($ratingDelta);
					$ratingDeltaMark = "▼";
				} else {
					$ratingColor = "green";
					$ratingDeltaAbs = $ratingDelta;
					$ratingDeltaMark = "▲";
				}
				
				$colors = array('#CFF', '#FCF', '#FFC', '#CCF', '#CFC', '#FCC', '#AFF', '#FAF', '#FFA', '#AAF', '#AFA', '#FAA');
				$randomColor = $colors[rand(0, (count($colors) - 1))];
	
				// 스탯 평가 점수
				if(preg_match("/warmode/", $mode) || preg_match("/normal/", $mode) || preg_match("/flare/", $mode)) {
					$stats_evaluation_user_sum = "";
					$stats_evaluation_user_sum_ko = "";
				} else {
					$stats_evaluation = stats_evaluation($pbg_latest_match);
					$stats_evaluation_user_sum = $stats_evaluation['user']['sum'];
					$stats_evaluation_user_sum_ko = $stats_evaluation_user_sum."점";
				}	
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				$query = "SELECT season FROM season WHERE server='{$users[$i]['mainServer']}' AND season_current='1'";
				$sql4season = $conn->query($query)->fetch_assoc();
				$season = $sql4season['season'];
				$pbg_user_season_stat_url = "https://api.playbattlegrounds.com/shards/{$users[$i]['mainServer']}/players/{$users[$i]['accountID']}/seasons/$season";
				$pbg_user_season_stat_data = pbg_curl_get($pbg_user_season_stat_url);
				
				if($mode == "solo" || $mode == "duo" || $mode == "squad") {
					$modeImageHtml = "<img src='https://bhandy.kr/pbg/image/{$mode}.png'>";
				}
				else if($mode == "solo-fpp" || $mode == "duo-fpp" || $mode == "squad-fpp") {
					$mode_replace = str_replace("-fpp", "", $mode);
					$modeImageHtml = "<img src='https://bhandy.kr/pbg/image/{$mode_replace}.png'><span style='color:red; font-size:10;'>FPP</span>";
				}
				else if(preg_match("/warmode/", $mode)) {
					$modeImageHtml = "<span style='font-size:30;'>&nbsp;WarMode</span>";
				}
				else if(preg_match("/normal/", $mode)) {
					$modeImageHtml = "<span style='font-size:30;'>&nbsp;Normal</span>";
				}
				else if(preg_match("/flare/", $mode)) {
					$modeImageHtml = "<span style='font-size:30;'>&nbsp;플레어건</span>";
				}

				
				if(preg_match("/warmode/", $mode) || preg_match("/normal/", $mode) || preg_match("/flare/", $mode)) {
					$current_rating = $cRating = $pRating = 0;		
				} else {
					$current_season_data = $pbg_user_season_stat_data['data']['attributes']['gameModeStats'][$mode];
					if($current_season_data) {
						$current_rating = floor($current_season_data['winPoints'] + $current_season_data['killPoints'] * 0.2);
						$cRating = $current_rating;
						$pRating = $cRating - $ratingDelta;
					} else {
						$query = "SELECT currentRating FROM stats WHERE userkey='{$users[$i]['userkey']}' AND name='{$users[$i]['name']}'
																									AND accountID='{$users[$i]['accountID']}' AND server='{$users[$i]['mainServer']}'
																									AND mode='{$mode}'
																								ORDER BY inputTime DESC LIMIT 1";
						$sql4stats = $conn->query($query)->fetch_assoc();
						$prev_rating = $sql4stats['currentRating'];
						if($prev_rating) {
							$current_rating = $prev_rating + $ratingDelta;
							$cRating = $current_rating;
							$pRating = $prev_rating;						
						} else {
							$current_rating = "----";
							$cRating = NULL;
							$pRating = NULL;
						}
					}					
				}
				
				$mkStats = "<html><head></head><body>";
				if(preg_match("/solo/", $mode)) {
					$mkStats .= 
					"
						<table style='border-collapse:collapse;'>
							<tr>
								<td rowspan=7 width=10 style='background-color:$randomColor;'></td>
								<td width=130 height=45 style='padding-top:15; text-align:center;'>$modeImageHtml</td>
								<td width=120 style='text-align:center; border-right:3px dotted $randomColor; padding-top:15;'><span style='font-size:12;'>{$time_s}<br><span style='padding-left:24'>&nbsp;&nbsp;&nbsp;~ {$time_f}</span></span></td>
								<td rowspan=2 width=135 height=45 style='border-bottom:5px solid $randomColor; padding-top:12; padding-left:12;'><span style='font-size:38;'>{$current_rating}</span><span style='color:$ratingColor; font-size:20;'>&nbsp;{$ratingDeltaAbs}</span><span style='color:$ratingColor; font-size:12;'>{$ratingDeltaMark}</span></td>
								<td rowspan=2 width=75 height=45 style='border-bottom:5px solid $randomColor; padding-top:12; padding-left:12;'><span style='font-size:38; padding-right:15; text-align:right;'>{$stats_evaluation_user_sum_ko}</span></td>
								<td rowspan=7 width=10 style='background-color:$randomColor;'></td>
							</tr>
							<tr>
								<td style='text-align:center; border-bottom:5px solid $randomColor; padding:0 0 10 0;'>{$server_ko} 서버</td>
								<td style='text-align:center; border-right:3px dotted $randomColor; border-bottom:5px solid $randomColor; padding:0 0 10 12;'>{$map_ko}</td>
							</tr>
							<tr>
								<td height=30 style='padding-left:12px; padding-top:15px;'>순위</td>
								<td style='text-align:right; padding-right:18px; padding-top:15px; border-right:3px dotted $randomColor;'><span style='font-size:40;'>{$rank}</span> / {$all_party_count}</td>
								<td style='padding-left:12; padding-top:15px;'>힐</td>
								<td style='text-align:right; padding-right:18px; padding-top:15px;'>{$heals}</td>			
							</tr>
							<tr>
								<td height=20 style='padding-left:12px; padding-top:15px;'>킬<span style='font-size:10;'>(헤드샷)</span></td>
								<td style='text-align:right; border-right:3px dotted $randomColor; padding-right:18px; padding-top:15px;'><span style='font-size:25;'>{$kill}</span><span> ({$headshot})</span></td>
								<td style='padding-left:12; padding-top:15px;'>부스트</td>
								<td style='text-align:right; padding-right:18px; padding-top:15px;'>{$boosts}</td>
							</tr>
							<tr>
								<td height=20 style='padding-left:12px; padding-top:15px;'>데미지</td>
								<td style='text-align:right; border-right:3px dotted $randomColor; padding-right:18px; padding-top:15px;'><span style='font-size:25;'>{$damage}</span></td>
								<td style='padding-left:12; padding-top:15px;'>이동거리</td>
								<td style='text-align:right; padding-right:18px; padding-top:15px;'>{$distanceTravelled}</td>
							</tr>
							<tr>
								<td height=20 style='padding-left:12px; padding-top:15px;'>생존시간</td>
								<td style='text-align:right; border-right:3px dotted $randomColor; padding-right:18px; padding-top:15px;'>{$timeSurvived}</td>
								<td style='padding-left:12; padding-top:15px;'>최대거리킬</td>
								<td style='text-align:right; padding-right:18px; padding-top:15px;'>{$longestKill}</td>
							</tr>
						</table>
					";
				} else {
					$mkStats .= 
					"
						<table style='border-collapse:collapse;'>
							<tr>
								<td rowspan=9 width=10 style='background-color:$randomColor;'></td>
								<td width=130 height=45 style='padding-top:15; text-align:center;'>$modeImageHtml</td>
								<td width=120 style='text-align:center; border-right:3px dotted $randomColor; padding-top:15;'><span style='font-size:12;'>{$time_s}<br><span style='padding-left:24'>&nbsp;&nbsp;&nbsp;~ {$time_f}</span></span></td>
								<td rowspan=2 width=135 height=45 style='border-bottom:5px solid $randomColor; padding-top:12; padding-left:12;'><span style='font-size:38;'>{$current_rating}</span><span style='color:$ratingColor; font-size:20;'>&nbsp;{$ratingDeltaAbs}</span><span style='color:$ratingColor; font-size:12;'>{$ratingDeltaMark}</span></td>
								<td rowspan=2 width=75 height=45 style='border-bottom:5px solid $randomColor; padding-top:12; padding-left:12;'><span style='font-size:38; padding-right:15; text-align:right;'>{$stats_evaluation_user_sum_ko}</span></td>
								<td rowspan=9 width=10 style='background-color:$randomColor;'></td>
							</tr>
							<tr>
								<td style='text-align:center; border-bottom:5px solid $randomColor; padding:0 0 10 0;'>{$server_ko} 서버</td>
								<td style='text-align:center; border-right:3px dotted $randomColor; border-bottom:5px solid $randomColor; padding:0 0 10 12;'>{$map_ko}</td>
							</tr>
							<tr>
								<td height=20 rowspan=2 style='padding-left:12;'>순위</td>
								<td rowspan=2 style='text-align:right; padding-right:18; border-right:3px dotted $randomColor;'><span style='font-size:40;'>{$rank}</span> / {$all_party_count}</td>
								<td style='padding-left:12; padding-top:10px;'>어시스트</td>
								<td style='text-align:right; padding-right:18; padding-top:10px;'>{$assists}</td>
							</tr>
							<tr>
								<td style='padding-left:12; padding-top:15px;'>기절시킴</td>
								<td style='text-align:right; padding-right:18; padding-top:15px;'>{$DBNOs}</td>
							</tr>
							<tr>
								<td height=15 style='padding-left:12; padding-top:10px;'>킬<span style='font-size:10;'>(헤드샷)</span></td>
								<td style='text-align:right; border-right:3px dotted $randomColor; padding-right:18; padding-top:10px;'><span style='font-size:25;'>{$kill}</span><span> ({$headshot})</span></td>
								<td style='padding-left:12; padding-top:10px;'>부활시킴</td>
								<td style='text-align:right; padding-right:18; padding-top:10px;'>{$revives}</td>
							</tr>
							<tr>
								<td height=15 style='padding-left:12; padding-top:10px;'>데미지</td>
								<td style='text-align:right; border-right:3px dotted $randomColor; padding-right:18; padding-top:10px;'><span style='font-size:25;'>{$damage}</span></td>
								<td style='padding-left:12; padding-top:10px;'>힐</td>
								<td style='text-align:right; padding-right:18; padding-top:10px;'>{$heals}</td>
							</tr>
							<tr>
								<td height=10 style='padding-left:12; padding-top:12px;'>생존시간</td>
								<td style='text-align:right; border-right:3px dotted $randomColor; padding-right:18; padding-top:12px;'>{$timeSurvived}</td>
								<td style='padding-left:12; padding-top:12px;'>부스트</td>
								<td style='text-align:right; padding-right:18; padding-top:12px;'>{$boosts}</td>
							</tr>
							<tr>
								<td height=10 style='padding-left:12; padding-bottom:23px;'>이동거리</td>
								<td style='text-align:right; border-right:3px dotted $randomColor; padding-right:18; padding-bottom:23px;'>{$distanceTravelled}</td>
								<td style='padding-left:12; padding-bottom:23px;'>최대거리킬</td>
								<td style='text-align:right; padding-right:18; padding-bottom:23px;'>{$longestKill}</td>
							</tr>
						</table>
					";		
				}
				$mkStats .= "</body></html>";
				
				// HTML -> PDF
				$pdfName_stats = $mkLatestMatchPath.'/stats_'.$imageNumber.'.pdf';
				$html2pdf = new HTML2PDF('P', 'A4');
				$html2pdf->setDefaultFont('malgun');
				$html2pdf->writeHTML($mkStats);
				$html2pdfOutput = $html2pdf->Output($pdfName_stats, 'F');
				
				// PDF -> JPG
				$imageName_stats = $mkLatestMatchPath.'/stats_'.$imageNumber.'.jpg';
				$im = new Imagick();
				$im->setResolution(150,150);
				$im->readImage($pdfName_stats);
				$im->flattenImages();
				$im->cropImage(920, 540, 0, 0);
				$im->thumbnailImage(512, 320, 1, 1);
				$im->writeImage($imageName_stats);
				$im->clear();
				$im->destroy();
				
				if($imageName_stats) {
					unlink($pdfName_stats);
					
					$mkLatestMatchURL_stats = $mkLatestMatchURL.'/stats_'.$imageNumber.'.jpg';
					$send['title'][] = "나의 전적";
					$send['imgUrl'][] = $mkLatestMatchURL_stats;
				}
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////			

				if(!preg_match("/warmode/", $mode) && !preg_match("/normal/", $mode) && !preg_match("/flare/", $mode)) {
					// 킬 데이터
					$data_kill = $data['kill'];
					// 데스 데이터
					$data_be_killed = $data['be_killed'][0];
					$die_time = $data_be_killed['time'];
					$die_name = $data_be_killed['name'];
					$die_account_id = $data_be_killed['accountID'];
					$die_distance = abs($data_be_killed['distance']) . "m";
					$die_category = $data_be_killed['damageCategory'];
					if($die_category == "Damage_Gun" || $die_category == "Damage_Melee") {
						$die_ko = $data_be_killed['weapon_nickname'];
					} else {
						$die_ko = $data_be_killed['damageCategory_translation'];
					}
					if($data_be_killed) {
						$die_text = "{$die_time} - &nbsp;&nbsp;&nbsp;{$die_name}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$die_ko} ({$die_distance})";
					} else {
						$die_text = "<span style='font-size:25; display: block; text-align:center;'>WINNER WINNER<br>&nbsp;&nbsp;&nbsp;&nbsp;CHICKEN DINNER</span>";
					}
					
					if($data_kill) {
						$data_kill_count = count($data_kill);
						$data_kill_count_rowspan = $data_kill_count+1;
						if($data_kill_count >= 0 && $data_kill_count < 10) {
							$css_font_size = "12";
							$css_text_padding = "7 7 7 10";
						}
						else if($data_kill_count >= 10 && $data_kill_count < 15) {
							$css_font_size = "10";	
							$css_text_padding = "4 4 4 15";	
						}
						else if($data_kill_count >= 15 && $data_kill_count < 20) {
							$css_font_size = "8";		
							$css_text_padding = "2 2 2 20";				
						} else {
							$css_font_size = "6";		
							$css_text_padding = "1 1 1 25";
						}
						
						$mkKillingTable = "";
						for($j=0; $j<$data_kill_count; $j++) {
							$killing_time = $data_kill[$j]['time'];
							$killing_name = $data_kill[$j]['name'];
							$killing_distance = abs($data_kill[$j]['distance']) . "m";
							$killing_category = $data_kill[$j]['damageCategory'];
							if($killing_category == "Damage_Gun") {
								$killing_ko = $data_kill[$j]['weapon_nickname'];
							} else {
								$killing_ko = $data_kill[$j]['damageCategory_translation'];
							}
							
							if($j == 0) {
								$mkKillingTable = 
								"
									<tr>
										<td width=300 style='font-size:$css_font_size; padding:$css_text_padding; border-right:3px dotted $randomColor;'><span>{$killing_time} - &nbsp;&nbsp;&nbsp;{$killing_name}</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$killing_ko} ({$killing_distance})</span></td>
										<td width=260 height=320 rowspan={$data_kill_count} style='font-size:14; padding-left:12;'>{$die_text}</td>
									</tr>		
								";						
							} else {
								$mkKillingTable .= 
								"
									<tr>
										<td width=300 style='font-size:$css_font_size; padding:$css_text_padding; border-right:3px dotted $randomColor;'>{$killing_time} - &nbsp;&nbsp;&nbsp;{$killing_name}<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$killing_ko} ({$killing_distance})</span></td>
									</tr>	
								";												
							}
						}
						
						$mkKilling = "<html><head></head><body>";
						$mkKilling .= 
						"
							<table style='border-collapse:collapse;'>
								<tr>
									<td rowspan={$data_kill_count_rowspan} width=10 style='background-color:$randomColor;'></td>
									<td width=300 height=50 style='text-align:center; font-size:40px; border-right:3px dotted $randomColor; border-bottom:3px solid $randomColor;'>KILL</td>
									<td width=260 height=50 style='text-align:center; font-size:40px; border-bottom:3px solid $randomColor;'>DIE</td>
									<td rowspan={$data_kill_count_rowspan} width=10 style='background-color:$randomColor;'></td>
								</tr>
						";
						$mkKilling .= $mkKillingTable;
						$mkKilling .= "</table></body></html>";
					} else {
						$mkKilling = "<html><head></head><body>";
						$mkKilling .= 
						"
							<table style='border-collapse:collapse;'>
								<tr>
									<td rowspan=2 width=10 style='background-color:$randomColor;'></td>
									<td width=300 height=50 style='text-align:center; font-size:40px; border-right:3px dotted $randomColor; border-bottom:3px solid $randomColor;'>KILL</td>
									<td width=260 height=50 style='text-align:center; font-size:40px; border-bottom:3px solid $randomColor;'>DIE</td>
									<td rowspan=2 width=10 style='background-color:$randomColor;'></td>
								</tr>
						";
						$mkKilling .= 
						"
							<tr>
								<td width=300 height=320 style='font-size:19; text-align:center; padding:10; border-right:3px dotted $randomColor;'>킬을 못했어요 ㅠ_ㅠ</td>
								<td width=260 height=320 style='font-size:14; padding-left:12;'>{$die_text}</td>
							</tr>
						";
						$mkKilling .= "</table></body></html>";
					}
					
					// HTML -> PDF
					$pdfName_killing = $mkLatestMatchPath.'/killing_'.$imageNumber.'.pdf';
					$html2pdf = new HTML2PDF('P', 'A4');
					$html2pdf->setDefaultFont('malgun');
					$html2pdf->writeHTML($mkKilling);
					$html2pdfOutput = $html2pdf->Output($pdfName_killing, 'F');
					
					// PDF -> JPG
					$imageName_killing = $mkLatestMatchPath.'/killing_'.$imageNumber.'.jpg';
					$im = new Imagick();
					$im->setResolution(150,150);
					$im->readImage($pdfName_killing);
					$im->flattenImages();
					$im->cropImage(1100, 660, 0, 0);
					$im->thumbnailImage(512, 320, 1, 1);
					$im->writeImage($imageName_killing);
					$im->clear();
					$im->destroy();
					
					if($imageName_killing) {
						unlink($pdfName_killing);
						
						$mkLatestMatchURL_killing = $mkLatestMatchURL.'/killing_'.$imageNumber.'.jpg';
						$send['title'][] = "킬 & 데스";
						$send['imgUrl'][] = $mkLatestMatchURL_killing;
					}
				}
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// 팀원 데이터
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
						$td_stats_name_style = "height=11; style='font-size:8; $td_name_padding_1 padding-top:5px;'";
						$td_stats_data_style = "font-size:10; $td_name_padding_2 padding-top:5px;'";
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
						$pdfName_members = $mkLatestMatchPath.'/members_'.$imageNumber.'.pdf';
						$html2pdf = new HTML2PDF('P', 'A4');
						$html2pdf->setDefaultFont('malgun');
						$html2pdf->writeHTML($mkMembers);
						$html2pdfOutput = $html2pdf->Output($pdfName_members, 'F');
						
						// PDF -> JPG
						$imageName_members = $mkLatestMatchPath.'/members_'.$imageNumber.'.jpg';
						$im = new Imagick();
						$im->setResolution(150,150);
						$im->readImage($pdfName_members);
						$im->flattenImages();
						$im->cropImage(850, 520, 0, 0);
						$im->thumbnailImage(512, 320, 1, 1);
						$im->writeImage($imageName_members);
						$im->clear();
						$im->destroy();
						
						if($imageName_members) {
							unlink($pdfName_members);
							
							$mkLatestMatchURL_members = $mkLatestMatchURL.'/members_'.$imageNumber.'.jpg'; 
							$send['title'][] = "팀원 전적";
							$send['imgUrl'][] = $mkLatestMatchURL_members;
						}
					}
				}
				messageImageList($send, $users[$i]['userkey'], "UPDATE");
	
				// 10번째 전 이미지파일 삭제
				if(file_exists($mkLatestMatchPath.'/stats_'.($imageNumber-10).'.jpg')) {
					unlink($mkLatestMatchPath.'/stats_'.($imageNumber-10).'.jpg');
				}
				if(file_exists($mkLatestMatchPath.'/killing_'.($imageNumber-10).'.jpg')) {
					unlink($mkLatestMatchPath.'/killing_'.($imageNumber-10).'.jpg');
				}			
				if(file_exists($mkLatestMatchPath.'/members_'.($imageNumber-10).'.jpg')) {
					unlink($mkLatestMatchPath.'/members_'.($imageNumber-10).'.jpg');
				}
				
				$send['text'] = "👁‍🗨: 【{$users[$i]['name']}】의 {$time_s} ~ {$time_f} 플레이 결과를 알려드립니다.‼️";
				if($data_be_killed) {
					$query = "INSERT INTO logging (userkey, inProgress, inputTime) VALUE('{$users[$i]['userkey']}', 'SEARCH_KILLER', '{$inputTime}')";
					$conn->query($query);
					
					$query = "SELECT season_ko FROM season WHERE server='{$users[$i]['mainServer']}' AND season_current='1'";
					$sql4season = $conn->query($query)->fetch_assoc();
					$season_ko = $sql4season['season_ko'];
					$die_payload = $die_name."/".$die_account_id."/".$users[$i]['mainServer']."/".$season_ko;
					
					$send['title'] = array('초기화면', $die_name.'의 전적 보기', '실시간 전적 알림 끄기', '나의 시즌 전적 보기', '전적 검색');
					$send['payload'] = array('초기화면', $die_payload, '실시간 전적 알림 끄기', '나의 시즌 전적 보기', '전적 검색');
				} else {
					$send['payload'] = $send['title'] = array('초기화면', '실시간 전적 알림 끄기', '나의 시즌 전적 보기', '전적 검색');
				}
				messageQR($send, $users[$i]['userkey'], "UPDATE");
	
				$query = "INSERT INTO alarm (userkey, name, accountID, server, matchID, inputTime)
														VALUE('{$users[$i]['userkey']}', '{$users[$i]['name']}', '{$users[$i]['accountID']}',
																	'{$users[$i]['mainServer']}', '{$latest_match_id}', '{$inputTime}')";
				$conn->query($query);
	
				$query = "INSERT INTO stats (userkey, name, accountID, matchID, timeS, timeF, allPartyCount, rank, mode, map, mapKo, server, serverKo, damage,
																DBNOs, assists, boosts, heals, headshot, `kill`, longestKill, revives, timeSurvived, distanceTravelled,
																ratingDelta, prevRating, currentRating, evaluation, inputTime)
													VALUE('{$users[$i]['userkey']}', '{$users[$i]['name']}', '{$users[$i]['accountID']}', '{$latest_match_id}', '{$data['start']['time']}', '{$data['finish']['time']}',
																'{$all_party_count}', '{$rank}', '{$mode}', '{$data['map']}', '{$map_ko}', '{$users[$i]['mainServer']}', '{$server_ko}', '{$damage}',
																'{$DBNOs}', '{$assists}', '{$boosts}', '{$heals}', '{$headshot}', '{$kill}', '{$longestKill}', '{$revives}', '{$timeSurvived}', '{$distanceTravelled}',
																'{$ratingDelta}', '{$pRating}', '{$cRating}', '{$stats_evaluation_user_sum}', '{$inputTime}')";
				$conn->query($query);
				
				TypingOff($users[$i]['userkey']);
			}
		}
	}
}
//$die_name