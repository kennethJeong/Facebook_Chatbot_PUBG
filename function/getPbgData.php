<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// curl GET with pubg_api_key
//	return json decoded data
//
function pbg_curl_get($url)
{
	global $pbg_api_key;
	
	$header = array(
		"Authorization: Bearer $pbg_api_key",
	    "Accept: application/json"
	);
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");  
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                      
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                                                                                               
	$result = json_decode(curl_exec($ch), true);
	if(!$result) {
		for($i=1; $i<=2; $i++) {
			if($GLOBALS['pbg_api_key_'.$i]) {
				$header = array(
					"Authorization: Bearer ".$GLOBALS['pbg_api_key_'.$i],
				    "Accept: application/json"
				);
				
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");  
				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                      
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$result = json_decode(curl_exec($ch), true);
			}
			
			if(!$result) {
				continue;
			} else {
				break;
			}
		}
	}
	return $result;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// get all of server from database
// return server, server_ko
//
function pbg_server()
{
	global $conn;
	
	$query = "SELECT * FROM server";
	$sql4server = $conn->query($query);
	$server_i=0;
	$server = array();
	while($row4server = $sql4server->fetch_assoc()) {
		$server[$server_i]['server'] = $row4server['server'];
		$server[$server_i]['server_ko'] = $row4server['server_ko'];
		$server_i++;
	}
	
	return $server;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// get map list
// return en and ko name of each map
//
function pbg_maps($map_official_name)
{
	$map_list = array
	(
		"Desert_Main"=> array
		(
			"en" => "Miramar",
			"ko" => "미라마"
		),
		"Erangel_Main"=> array
		(
			"en" => "Erangel",
			"ko" => "에란겔"	
		),
		"Savage_Main" => array
		(
			"en" => "Sanhok",
			"ko" => "사녹"
		)
	);
	foreach($map_list as $key=>$value) {
		if($key == $map_official_name) {
			$map['official'] = $map_official_name;
			$map['en'] = $value['en'];
			$map['ko'] = $value['ko'];
		}
	}
	return $map;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// get all of name cases for searching real id from inserted id
// return all of name cases (mixed upper case and lower case)
//
function num_of_name_cases($name)
{
	$name_upper = strtoupper($name);
	$name_lower = strtolower($name);
	
	$name_string_length = mb_strlen($name, "UTF-8");
	
	for($i=0; $i<$name_string_length; $i++) {
		$name_cut[$i][] = substr($name_upper, $i, 1);
		$name_cut[$i][] = substr($name_lower, $i, 1);
	}
	
	$results = array();
	foreach ($name_cut as $values) {
	    // Loop over the available sets of options.
	    if (count($results) == 0) {
	        // If this is the first set, the values form our initial results.
	        $results = $values;
	    } else {
	        // Otherwise append each of the values onto each of our existing results.
	        $new_results = array();
	        foreach ($results as $result) {
	            foreach ($values as $value) {
	                $new_results[] = "$result$value";
	            }
	        }
	        $results = $new_results;
	    }
	}
	$results = array_keys(array_flip($results));
	$results_last_key = count($results)-1;
	$results[($results_last_key+1)] = $results[1];
	$results[1] = $results[$results_last_key];
	$results[($results_last_key)] = $results[($results_last_key+1)];
	unset($results[($results_last_key+1)]);
	
	return $results;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// get pbg real id from entered pbg id
// return search id, result(real) id and account id
//
function pbg_find_real_id($ex_pbg_id, $pbg_servers)
{
	$pbg_id_matching = array();
	for($sv=0; $sv<count($pbg_servers); $sv++) {
		if(is_array($pbg_servers[$sv])) {
			$pbg_server = $pbg_servers[$sv]['server'];
		} else {
			$pbg_server = $pbg_servers[0];
		}
		$pbg_id_check_url = "https://api.playbattlegrounds.com/shards/$pbg_server/players?filter[playerNames]={$ex_pbg_id}";
		$pbg_id_check_data = pbg_curl_get($pbg_id_check_url);
		if($pbg_id_check_data['errors']) {
			// 영문 대소문자의 모든 조합
			$name_of_name_cases = num_of_name_cases($ex_pbg_id);
			$name_of_name_cases_chunk = array_chunk($name_of_name_cases, 6);
			for($i=0; $i<count($name_of_name_cases_chunk); $i++) {
				$name_of_name_cases_chunk_imp = implode(",",$name_of_name_cases_chunk[$i]);
				$pbg_id_check_another_id_url = "https://api.playbattlegrounds.com/shards/$pbg_server/players?filter[playerNames]={$name_of_name_cases_chunk_imp}";
				$pbg_id_check_another_id_data = pbg_curl_get($pbg_id_check_another_id_url);
				if($pbg_id_check_another_id_data['errors']) {
					continue;
				} else {
					if($pbg_id_check_another_id_data['data']) {
						$pbg_id_matching['search'] = $ex_pbg_id;
						$pbg_id_matching['result']['id'] = $pbg_id_check_another_id_data['data'][0]['attributes']['name'];
						$pbg_id_matching['result']['account_id'] = $pbg_id_check_another_id_data['data'][0]['id'];
						break 2;
					}			
				}					
			}
		} else {
			if($pbg_id_check_data['data']) {
				$pbg_id_matching['search'] = $pbg_id_matching['result']['id'] = $ex_pbg_id;
				$pbg_id_matching['result']['account_id'] = $pbg_id_check_data['data'][0]['id'];
				break;
			}
		}
	}

	return $pbg_id_matching;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// get pbg real id from entered pbg id (2nd version)
// return search id, result(real) id, main playing server, account id
//
function pbg_find_real_id_v2($ex_pbg_id)
{
	$pbg_id_matching['search'] = $ex_pbg_id;
	
	// 출처 - https://pubg.op.gg/
	$url = "https://pubg.op.gg/user/" . $ex_pbg_id;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                             
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                                                                                               
	$result = curl_exec($ch);
	
	preg_match_all("'<title>(.*?)</title>'si", $result, $match);
	if(preg_match("/Redirecting to/", $match[1][0])) {
		preg_match_all("/pubg\.op\.gg\/user\/(.*?)\?/", $match[1][0], $match_id);
		$matched_id = $match_id[1][0];
		$pbg_id_matching['result']['id'] = $matched_id;
		
		preg_match_all("/".$matched_id."\?server\=(.*?)$/", $match[1][0], $match_server);
		$matched_server = $match_server[1][0];
		if($matched_server == "as") {
			$pbg_id_matching['result']['server'] = "";
		} else {
			$pbg_id_matching['result']['server'] = $matched_server;
		}
		
		$pbg_find_account_id_url= "https://api.playbattlegrounds.com/shards/{$matched_server}/players?filter[playerNames]={$matched_id}";
		$pbg_find_account_id_data = pbg_curl_get($pbg_find_account_id_url);
		$matched_account_id = $pbg_find_account_id_data['data'][0]['id'];
		$pbg_id_matching['result']['account_id'] = $matched_account_id;
	}
	return $pbg_id_matching;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// get all of season from pbg season url using each of a server
// return season, season_ko, current_season, season_off
//
function pbg_seasons($pbg_servers)
{
	$seasons = array();
	for($sv=0; $sv<count($pbg_servers); $sv++) {
		$pbg_server = $pbg_servers[$sv]['server'];
		$pbg_server_ko = $pbg_servers[$sv]['server_ko'];
		
		$pbg_seasons_url = "https://api.playbattlegrounds.com/shards/$pbg_server/seasons";
		$pbg_seasons_all_data = pbg_curl_get($pbg_seasons_url);
		$pbg_seasons_data = $pbg_seasons_all_data['data'];
		$pbg_seasons_data_count = count($pbg_seasons_data);
		
		$seasons[$sv]['server'] = $pbg_server;
		$seasons[$sv]['server_ko'] = $pbg_server_ko;
		
		for($j=0; $j<$pbg_seasons_data_count; $j++) {
			// 시즌 ID
			$seasons[$sv]['season'][$j]['id'] = $pbg_seasons_data[$j]['id'];
			
			// 시즌 한국어
			if(preg_match("/beta/", $pbg_seasons_data[$j]['id'])) {
				if(preg_match("/beta$/", $pbg_seasons_data[$j]['id'])) {
					$seasons[$sv]['season'][$j]['ko'] = "베타";
				} else {
					if(preg_match("/beta[0-9]/", $pbg_seasons_data[$j]['id'])) {
						preg_match_all("/beta(.*?)$/", $pbg_seasons_data[$j]['id'], $seasonNum);
						$seasons[$sv]['season'][$j]['ko'] = "베타 ".$seasonNum[1][0];
					}			
				}
			}
			else if(preg_match("/pre/", $pbg_seasons_data[$j]['id'])) {
				if(preg_match("/pre$/", $pbg_seasons_data[$j]['id'])) {
					$seasons[$sv]['season'][$j]['ko'] = "프리";
				} else {
					if(preg_match("/pre[0-9]/", $pbg_seasons_data[$j]['id'])) {
						preg_match_all("/pre(.*?)$/", $pbg_seasons_data[$j]['id'], $seasonNum);
						$seasons[$sv]['season'][$j]['ko'] = "프리 ".$seasonNum[1][0];
					}			
				}
			} else {
				$seasonNum = substr($pbg_seasons_data[$j]['id'], -2, 2);
				if(substr($seasonNum, 0, 1) == 0) {
					$seasons[$sv]['season'][$j]['ko'] = "정규 ".substr($seasonNum, 1, 1);
				} else {
					$seasons[$sv]['season'][$j]['ko'] = "정규 ".$seasonNum;
				}
			}
			
			// 현재 시즌
			if($pbg_seasons_data[$j]['attributes']['isCurrentSeason'] == TRUE) {
				$seasons[$sv]['season'][$j]['season_current'] = TRUE;
			} else {
				$seasons[$sv]['season'][$j]['season_current'] = FALSE;
			}
			
			// 시즌 오프 기간
			if($pbg_seasons_data[$j]['attributes']['isOffseason'] == TRUE) {
				$seasons[$sv]['season'][$j]['season_off'] = TRUE;
			} else {
				$seasons[$sv]['season'][$j]['season_off'] = FALSE;
			}
		}
	}

	return $seasons;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// get latest match data
// return latest match data
//
function pbg_latest_match($pbg_user_accountID, $pbg_server)
{
	global $conn;
	
	$this_game = array();
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 유저 검색
	$pbg_user_match_url = "https://api.playbattlegrounds.com/shards/{$pbg_server}/players/{$pbg_user_accountID}";
	$pbg_user_match_data = pbg_curl_get($pbg_user_match_url);
	if($pbg_user_match_data['data']) {
		$this_game['user']['id'] = $pbg_user_match_data['data']['attributes']['name'];
		$this_game['user']['account_id'] = $pbg_user_match_data['data']['id'];
		$this_game['latest_match']['match_id'] = $pbg_user_match_data['data']['relationships']['matches']['data'][0]['id'];
		$this_game['latest_match']['server'] = $pbg_user_match_data['data']['attributes']['shardId'];
		
		// 가장 최근 매치
		$pbg_user_latest_match_url = "https://api.playbattlegrounds.com/shards/$pbg_server/matches/{$pbg_user_match_data['data']['relationships']['matches']['data'][0]['id']}";
		$pbg_user_latest_match_data = pbg_curl_get($pbg_user_latest_match_url);
		if($pbg_user_latest_match_data['data']) {
			$this_game['latest_match']['mode'] = $pbg_user_latest_match_data['data']['attributes']['gameMode'];
			$this_game['latest_match']['map'] = $pbg_user_latest_match_data['data']['attributes']['mapName'];
			$this_game['latest_match']['start']['timestamp'] = strtotime($pbg_user_latest_match_data['data']['attributes']['createdAt']);
			$this_game['latest_match']['start']['time'] = date("Y-m-d H:i", $this_game['latest_match']['start']['timestamp']);
			
			$pbg_user_latest_match_rosters_data = $pbg_user_latest_match_data['data']['relationships']['rosters']['data'];
			$pbg_user_latest_match_rosters_data_count = count($pbg_user_latest_match_rosters_data);
			$this_game['latest_match']['all_party_count'] = $pbg_user_latest_match_rosters_data_count;
			
			$pbg_user_latest_match_included_data = $pbg_user_latest_match_data['included'];
			$pbg_user_latest_match_included_data_count = count($pbg_user_latest_match_included_data);
			for($i=0; $i<$pbg_user_latest_match_included_data_count; $i++) {
				if($pbg_user_latest_match_included_data[$i]['attributes']['stats']['playerId'] == $this_game['user']['account_id']) {
					$this_game['latest_match']['partyID'] = $pbg_user_latest_match_included_data[$i]['id'];
					$this_game['latest_match']['stats']['DBNOs'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['DBNOs'];
					$this_game['latest_match']['stats']['assists'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['assists'];
					$this_game['latest_match']['stats']['boosts'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['boosts'];
					$this_game['latest_match']['stats']['damage'] = round($pbg_user_latest_match_included_data[$i]['attributes']['stats']['damageDealt']);
					$this_game['latest_match']['stats']['heals'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['heals'];
					$this_game['latest_match']['stats']['headshot'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['headshotKills'];
					$this_game['latest_match']['stats']['kill'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['kills'];
					$this_game['latest_match']['stats']['killStreaks'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['killStreaks'];
					$this_game['latest_match']['stats']['longestKill'] = number_format($pbg_user_latest_match_included_data[$i]['attributes']['stats']['longestKill'], 1);
					$this_game['latest_match']['stats']['revives'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['revives'];
					$this_game['latest_match']['stats']['timeSurvived'] = date("i:s",$pbg_user_latest_match_included_data[$i]['attributes']['stats']['timeSurvived']);
					$this_game['latest_match']['stats']['distanceTravelled'] = round($pbg_user_latest_match_included_data[$i]['attributes']['stats']['rideDistance'] + $pbg_user_latest_match_included_data[$i]['attributes']['stats']['walkDistance']);
					$this_game['latest_match']['stats']['winPointsDelta'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['winPointsDelta'];
					$this_game['latest_match']['stats']['killPointsDelta'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['killPointsDelta'];
					$this_game['latest_match']['stats']['ratingDelta'] = round($this_game['latest_match']['stats']['winPointsDelta'] + $this_game['latest_match']['stats']['killPointsDelta'] * 0.2);
				}
				
				if($pbg_user_latest_match_included_data[$i]['type'] == "asset") {
					$this_game['latest_match']['finish']['timestamp'] = strtotime($pbg_user_latest_match_included_data[$i]['attributes']['createdAt']);
					$this_game['latest_match']['finish']['time'] = date("Y-m-d H:i", $this_game['latest_match']['finish']['timestamp']);
					$this_game['latest_match']['telemetryURL'] = $pbg_user_latest_match_included_data[$i]['attributes']['URL'];
				}
			}

			for($i=0; $i<$pbg_user_latest_match_included_data_count; $i++) {
				if($pbg_user_latest_match_included_data[$i]['type'] == "roster") {
					$pbg_user_latest_match_roster = $pbg_user_latest_match_included_data[$i]['relationships']['participants']['data'];
					$pbg_user_latest_match_roster_count = count($pbg_user_latest_match_roster);
					for($j=0; $j<$pbg_user_latest_match_roster_count; $j++) {
						if($pbg_user_latest_match_roster[$j]['id'] == $this_game['latest_match']['partyID']) {
							$this_game['latest_match']['rank'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['rank'];
							for($k=0, $m=0; $k<$pbg_user_latest_match_roster_count; $k++) {
								if($pbg_user_latest_match_roster[$k]['id'] != $this_game['latest_match']['partyID']) {
									$this_game['latest_match']['members'][$m]['rosterID'] = $pbg_user_latest_match_roster[$k]['id'];
									$m++;
								}
							}
						}
					}
				}
			}

			for($i=0; $i<$pbg_user_latest_match_included_data_count; $i++) {
				for($j=0; $j<count($this_game['latest_match']['members']); $j++) {
					if($pbg_user_latest_match_included_data[$i]['id'] == $this_game['latest_match']['members'][$j]['rosterID']) {
						$this_game['latest_match']['members'][$j]['id'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['name'];
						$this_game['latest_match']['members'][$j]['accountID'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['playerId'];
						$this_game['latest_match']['members'][$j]['stats']['DBNOs'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['DBNOs'];
						$this_game['latest_match']['members'][$j]['stats']['assists'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['assists'];
						$this_game['latest_match']['members'][$j]['stats']['boosts'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['boosts'];
						$this_game['latest_match']['members'][$j]['stats']['damage'] = round($pbg_user_latest_match_included_data[$i]['attributes']['stats']['damageDealt']);
						$this_game['latest_match']['members'][$j]['stats']['heals'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['heals'];
						$this_game['latest_match']['members'][$j]['stats']['headshot'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['headshotKills'];
						$this_game['latest_match']['members'][$j]['stats']['kill'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['kills'];
						$this_game['latest_match']['members'][$j]['stats']['killStreaks'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['killStreaks'];
						$this_game['latest_match']['members'][$j]['stats']['longestKill'] = number_format($pbg_user_latest_match_included_data[$i]['attributes']['stats']['longestKill'], 1);
						$this_game['latest_match']['members'][$j]['stats']['revives'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['revives'];
						$this_game['latest_match']['members'][$j]['stats']['timeSurvived'] = date("i:s",$pbg_user_latest_match_included_data[$i]['attributes']['stats']['timeSurvived']);
						$this_game['latest_match']['members'][$j]['stats']['distanceTravelled'] = round($pbg_user_latest_match_included_data[$i]['attributes']['stats']['rideDistance'] + $pbg_user_latest_match_included_data[$i]['attributes']['stats']['walkDistance']);
						$this_game['latest_match']['members'][$j]['stats']['winPointsDelta'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['winPointsDelta'];
						$this_game['latest_match']['members'][$j]['stats']['killPointsDelta'] = $pbg_user_latest_match_included_data[$i]['attributes']['stats']['killPointsDelta'];
						$this_game['latest_match']['members'][$j]['stats']['ratingDelta'] = round($this_game['latest_match']['members'][$j]['stats']['winPointsDelta'] + $this_game['latest_match']['members'][$j]['stats']['killPointsDelta'] * 0.2);						
					}
				}
			}			

			$pbg_each_game_detail_url = $this_game['latest_match']['telemetryURL'];
			$pbg_each_game_detail_data = pbg_curl_get($pbg_each_game_detail_url);
			$pbg_each_game_detail_data_count = count($pbg_each_game_detail_data);
			for($i=0, $j=0, $k=0; $i<$pbg_each_game_detail_data_count; $i++) {
				if($pbg_each_game_detail_data[$i]['_T'] == "LogPlayerKill") {
					if($pbg_each_game_detail_data[$i]['killer']['name'] == $this_game['user']['id']) {
						$this_game['latest_match']['kill'][$j]['name'] = $pbg_each_game_detail_data[$i]['victim']['name'];
						$this_game['latest_match']['kill'][$j]['accountID'] = $pbg_each_game_detail_data[$i]['victim']['accountId'];
						$this_game['latest_match']['kill'][$j]['timestamp'] = strtotime($pbg_each_game_detail_data[$i]['_D']);
						$this_game['latest_match']['kill'][$j]['time'] = date("i:s", ($this_game['latest_match']['kill'][$j]['timestamp'] - $this_game['latest_match']['start']['timestamp']));
						$this_game['latest_match']['kill'][$j]['distance'] = round($pbg_each_game_detail_data[$i]['distance']/100) . "m";
						$this_game['latest_match']['kill'][$j]['damageCategory'] = $pbg_each_game_detail_data[$i]['damageTypeCategory'];
						
						$query = "SELECT translation FROM damageCategory WHERE category='{$this_game['latest_match']['kill'][$j]['damageCategory']}'";
						$sql4damageCategory = $conn->query($query)->fetch_assoc();
						$this_game['latest_match']['kill'][$j]['damageCategory_translation'] = $sql4damageCategory['translation'];		
						
						if($this_game['latest_match']['kill'][$j]['damageCategory'] == "Damage_Gun" || $this_game['latest_match']['kill'][$j]['damageCategory'] == "Damage_Melee") {
							$this_game['latest_match']['kill'][$j]['weapon'] = $pbg_each_game_detail_data[$i]['damageCauserName'];
							
							$query = "SELECT nickname FROM weapon WHERE name='{$this_game['latest_match']['kill'][$j]['weapon']}'";
							$sql4weapon = $conn->query($query)->fetch_assoc();
							$this_game['latest_match']['kill'][$j]['weapon_nickname'] = $sql4weapon['nickname'];
						}
						
						$j++;
					}
					
					if($pbg_each_game_detail_data[$i]['victim']['name'] == $this_game['user']['id']) {
						$this_game['latest_match']['be_killed'][$k]['name'] = $pbg_each_game_detail_data[$i]['killer']['name'];
						$this_game['latest_match']['be_killed'][$k]['accountID'] = $pbg_each_game_detail_data[$i]['killer']['accountId'];
						$this_game['latest_match']['be_killed'][$k]['timestamp'] = strtotime($pbg_each_game_detail_data[$i]['_D']);
						$this_game['latest_match']['be_killed'][$k]['time'] = date("i:s", ($this_game['latest_match']['be_killed'][$k]['timestamp'] - $this_game['latest_match']['start']['timestamp']));
						$this_game['latest_match']['be_killed'][$k]['distance'] = round($pbg_each_game_detail_data[$i]['distance']/100) . "m";
						$this_game['latest_match']['be_killed'][$k]['damageCategory'] = $pbg_each_game_detail_data[$i]['damageTypeCategory'];

						$query = "SELECT translation FROM damageCategory WHERE category='{$this_game['latest_match']['be_killed'][$k]['damageCategory']}'";
						$sql4damageCategory = $conn->query($query)->fetch_assoc();
						$this_game['latest_match']['be_killed'][$k]['damageCategory_translation'] = $sql4damageCategory['translation'];		
						
						if($this_game['latest_match']['be_killed'][$k]['damageCategory'] == "Damage_Gun" || $this_game['latest_match']['be_killed'][$k]['damageCategory'] == "Damage_Melee") {
							$this_game['latest_match']['be_killed'][$k]['weapon'] = $pbg_each_game_detail_data[$i]['damageCauserName'];

							$query = "SELECT nickname FROM weapon WHERE name='{$this_game['latest_match']['be_killed'][$k]['weapon']}'";
							$sql4weapon = $conn->query($query)->fetch_assoc();
							$this_game['latest_match']['be_killed'][$k]['weapon_nickname'] = $sql4weapon['nickname'];	
						}
						
						$k++;
					}
				}
			}
		} else {
			unset($this_game);
		}
	} else {
		unset($this_game);
	}
	
	return $this_game;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// get user's season stats about selected season(ko)
// return user's season stats about selected season(ko)
//
function pbg_season_stats($pbg_userID, $pbg_user_accountID, $pbg_server, $season_ko)
{
	global $conn;
			
	$query = "SELECT season, server_ko FROM season WHERE server='$pbg_server' AND season_ko='$season_ko'";
	$sql4season = $conn->query($query)->fetch_assoc();
	$server_ko = $sql4season['server_ko'];
	$season = $sql4season['season'];
	$pbg_user_season_stat_url = "https://api.playbattlegrounds.com/shards/$pbg_server/players/$pbg_user_accountID/seasons/$season";
	$pbg_user_season_stat_data = pbg_curl_get($pbg_user_season_stat_url);
	
	$this_season = array();
	if($pbg_user_season_stat_data['data']) {
		$this_season['user']['id'] = $pbg_userID;
		$this_season['user']['accountID'] = $pbg_user_accountID;
		$this_season['user']['server'] = $pbg_server;
		$this_season['user']['server_ko'] = $server_ko;
		$this_season['user']['currentSeason'] = $season;
		$this_season['user']['currentSeason_ko'] = $season_ko;
		
		$game_mode_stats = $pbg_user_season_stat_data['data']['attributes']['gameModeStats'];
		$solo_tpp = $game_mode_stats['solo'];
		$duo_tpp = $game_mode_stats['duo'];
		$squad_tpp = $game_mode_stats['squad'];
		$solo_fpp = $game_mode_stats['solo-fpp'];
		$duo_fpp = $game_mode_stats['duo-fpp'];
		$squad_fpp = $game_mode_stats['squad-fpp'];
		
		if($solo_tpp['roundsPlayed'] > 0 || $duo_tpp['roundsPlayed'] > 0 || $squad_tpp['roundsPlayed'] > 0) {
			if($solo_tpp['roundsPlayed'] > 0) {
				$this_season['tpp']['solo']['roundsPlayed'] = $solo_tpp['roundsPlayed'];
				$this_season['tpp']['solo']['wins'] = $solo_tpp['wins'];
				$this_season['tpp']['solo']['winPer'] = number_format(($solo_tpp['wins'] / $solo_tpp['roundsPlayed']) * 100, 1) . "%";
				$this_season['tpp']['solo']['top10s'] = $solo_tpp['top10s'];
				$this_season['tpp']['solo']['top10Per'] = number_format(($solo_tpp['top10s'] / $solo_tpp['roundsPlayed']) * 100, 1) . "%";
				$this_season['tpp']['solo']['DBNOsPerRound'] = number_format(($solo_tpp['dBNOs'] / $solo_tpp['roundsPlayed']), 2);
				$this_season['tpp']['solo']['KD'] = number_format($solo_tpp['kills'] / ($solo_tpp['roundsPlayed'] - $solo_tpp['wins']), 2);
				$this_season['tpp']['solo']['KDA'] = number_format(($solo_tpp['kills'] + $solo_tpp['assists']) / ($solo_tpp['roundsPlayed'] - $solo_tpp['wins']), 2);
				$this_season['tpp']['solo']['longestKill'] = floor($solo_tpp['longestKill'])."m";
				$this_season['tpp']['solo']['timeSurvivedAvg'] = date("i분 s초", floor($solo_tpp['timeSurvived'] / $solo_tpp['roundsPlayed']));
				$this_season['tpp']['solo']['damageAvg'] = floor($solo_tpp['damageDealt'] / $solo_tpp['roundsPlayed']);
				$this_season['tpp']['solo']['mostKills'] = $solo_tpp['roundMostKills'];
				if($solo_tpp['kills'] > 0) {
					$this_season['tpp']['solo']['headshotPer'] = number_format(($solo_tpp['headshotKills'] / $solo_tpp['kills']) * 100, 1) . "%";
				} else {
					$this_season['tpp']['solo']['headshotPer'] = "0.0%";
				}
				$this_season['tpp']['solo']['winPoints'] = $solo_tpp['winPoints'];
				$this_season['tpp']['solo']['killPoints'] = $solo_tpp['killPoints'];
				$this_season['tpp']['solo']['rating'] = floor($solo_tpp['winPoints'] + $solo_tpp['killPoints'] * 0.2);
			} else {
				$this_season['tpp']['solo'] = array();
			}
			
			if($duo_tpp['roundsPlayed'] > 0) {
				$this_season['tpp']['duo']['roundsPlayed'] = $duo_tpp['roundsPlayed'];
				$this_season['tpp']['duo']['wins'] = $duo_tpp['wins'];
				$this_season['tpp']['duo']['winPer'] = number_format(($duo_tpp['wins'] / $duo_tpp['roundsPlayed']) * 100 , 1) . "%";
				$this_season['tpp']['duo']['top10s'] = $duo_tpp['top10s'];
				$this_season['tpp']['duo']['top10Per'] = number_format(($duo_tpp['top10s'] / $duo_tpp['roundsPlayed']) * 100 , 1) . "%";
				$this_season['tpp']['duo']['DBNOsPerRound'] = number_format(($duo_tpp['dBNOs'] / $duo_tpp['roundsPlayed']), 2);
				$this_season['tpp']['duo']['KD'] = number_format($duo_tpp['kills'] / ($duo_tpp['roundsPlayed'] - $duo_tpp['wins']), 2);
				$this_season['tpp']['duo']['KDA'] = number_format(($duo_tpp['kills'] + $duo_tpp['assists']) / ($duo_tpp['roundsPlayed'] - $duo_tpp['wins']), 2);
				$this_season['tpp']['duo']['longestKill'] = floor($duo_tpp['longestKill'])."m";
				$this_season['tpp']['duo']['timeSurvivedAvg'] = date("i분 s초", floor($duo_tpp['timeSurvived'] / $duo_tpp['roundsPlayed']));
				$this_season['tpp']['duo']['damageAvg'] = floor($duo_tpp['damageDealt'] / $duo_tpp['roundsPlayed']);
				$this_season['tpp']['duo']['mostKills'] = $duo_tpp['roundMostKills'];
				if($duo_tpp['kills'] > 0) {
					$this_season['tpp']['duo']['headshotPer'] = number_format(($duo_tpp['headshotKills'] / $duo_tpp['kills']) * 100, 1) . "%";
				} else {
					$this_season['tpp']['duo']['headshotPer'] = "0.0%";
				}
				$this_season['tpp']['duo']['winPoints'] = $duo_tpp['winPoints'];
				$this_season['tpp']['duo']['killPoints'] = $duo_tpp['killPoints'];
				$this_season['tpp']['duo']['rating'] = floor($duo_tpp['winPoints'] + $duo_tpp['killPoints'] * 0.2);
			} else {
				$this_season['tpp']['duo'] = array();
			}	
			
			if($squad_tpp['roundsPlayed'] > 0) {
				$this_season['tpp']['squad']['roundsPlayed'] = $squad_tpp['roundsPlayed'];
				$this_season['tpp']['squad']['wins'] = $squad_tpp['wins'];
				$this_season['tpp']['squad']['winPer'] = number_format(($squad_tpp['wins'] / $squad_tpp['roundsPlayed']) * 100 , 1) . "%";
				$this_season['tpp']['squad']['top10s'] = $squad_tpp['top10s'];
				$this_season['tpp']['squad']['top10Per'] = number_format(($squad_tpp['top10s'] / $squad_tpp['roundsPlayed']) * 100 , 1) . "%";
				$this_season['tpp']['squad']['DBNOsPerRound'] = number_format(($squad_tpp['dBNOs'] / $squad_tpp['roundsPlayed']), 2);
				$this_season['tpp']['squad']['KD'] = number_format($squad_tpp['kills'] / ($squad_tpp['roundsPlayed'] - $squad_tpp['wins']), 2);
				$this_season['tpp']['squad']['KDA'] = number_format(($squad_tpp['kills'] + $squad_tpp['assists']) / ($squad_tpp['roundsPlayed'] - $squad_tpp['wins']), 2);
				$this_season['tpp']['squad']['longestKill'] = floor($squad_tpp['longestKill'])."m";
				$this_season['tpp']['squad']['timeSurvivedAvg'] = date("i분 s초", floor($squad_tpp['timeSurvived'] / $squad_tpp['roundsPlayed']));
				$this_season['tpp']['squad']['damageAvg'] = floor($squad_tpp['damageDealt'] / $squad_tpp['roundsPlayed']);
				$this_season['tpp']['squad']['mostKills'] = $squad_tpp['roundMostKills'];
				if($squad_tpp['kills'] > 0) {
					$this_season['tpp']['squad']['headshotPer'] = number_format(($squad_tpp['headshotKills'] / $squad_tpp['kills']) * 100, 1) . "%";
				} else {
					$this_season['tpp']['squad']['headshotPer'] = "0.0%";
				}
				$this_season['tpp']['squad']['winPoints'] = $squad_tpp['winPoints'];
				$this_season['tpp']['squad']['killPoints'] = $squad_tpp['killPoints'];
				$this_season['tpp']['squad']['rating'] = floor($squad_tpp['winPoints'] + $squad_tpp['killPoints'] * 0.2);	
			} else {
				$this_season['tpp']['squad'] = array();
			}	
		} else {
			$this_season['tpp'] = array();
		}
	
	
		if($solo_fpp['roundsPlayed'] > 0 || $duo_fpp['roundsPlayed'] > 0 || $squad_fpp['roundsPlayed'] > 0) {
			if($solo_fpp['roundsPlayed'] > 0) {
				$this_season['fpp']['solo']['roundsPlayed'] = $solo_fpp['roundsPlayed'];
				$this_season['fpp']['solo']['wins'] = $solo_fpp['wins'];
				$this_season['fpp']['solo']['winPer'] = number_format(($solo_fpp['wins'] / $solo_fpp['roundsPlayed']) * 100 , 1) . "%";
				$this_season['fpp']['solo']['top10s'] = $solo_fpp['top10s'];
				$this_season['fpp']['solo']['top10Per'] = number_format(($solo_fpp['top10s'] / $solo_fpp['roundsPlayed']) * 100 , 1) . "%";
				$this_season['fpp']['solo']['DBNOsPerRound'] = number_format(($solo_fpp['dBNOs'] / $solo_fpp['roundsPlayed']), 2);
				$this_season['fpp']['solo']['KD'] = number_format($solo_fpp['kills'] / ($solo_fpp['roundsPlayed'] - $solo_fpp['wins']), 2);
				$this_season['fpp']['solo']['KDA'] = number_format(($solo_fpp['kills'] + $solo_fpp['assists']) / ($solo_fpp['roundsPlayed'] - $solo_fpp['wins']), 2);
				$this_season['fpp']['solo']['longestKill'] = floor($solo_fpp['longestKill'])."m";
				$this_season['fpp']['solo']['timeSurvivedAvg'] = date("i분 s초", floor($solo_fpp['timeSurvived'] / $solo_fpp['roundsPlayed']));
				$this_season['fpp']['solo']['damageAvg'] = floor($solo_fpp['damageDealt'] / $solo_fpp['roundsPlayed']);
				$this_season['fpp']['solo']['mostKills'] = $solo_fpp['roundMostKills'];
				if($solo_fpp['kills'] > 0) {
					$this_season['fpp']['solo']['headshotPer'] = number_format(($solo_fpp['headshotKills'] / $solo_fpp['kills']) * 100, 1) . "%";
				} else {
					$this_season['fpp']['solo']['headshotPer'] = "0.0%";
				}
				$this_season['fpp']['solo']['winPoints'] = $solo_fpp['winPoints'];
				$this_season['fpp']['solo']['killPoints'] = $solo_fpp['killPoints'];
				$this_season['fpp']['solo']['rating'] = floor($solo_fpp['winPoints'] + $solo_fpp['killPoints'] * 0.2);			
			} else {
				$this_season['fpp']['solo'] = array();
			}
			
			if($duo_fpp['roundsPlayed'] > 0) {
				$this_season['fpp']['duo']['roundsPlayed'] = $duo_fpp['roundsPlayed'];
				$this_season['fpp']['duo']['wins'] = $duo_fpp['wins'];
				$this_season['fpp']['duo']['winPer'] = number_format(($duo_fpp['wins'] / $duo_fpp['roundsPlayed']) * 100 , 1) . "%";
				$this_season['fpp']['duo']['top10s'] = $duo_fpp['top10s'];
				$this_season['fpp']['duo']['top10Per'] = number_format(($duo_fpp['top10s'] / $duo_fpp['roundsPlayed']) * 100 , 1) . "%";
				$this_season['fpp']['duo']['DBNOsPerRound'] = number_format(($duo_fpp['dBNOs'] / $duo_fpp['roundsPlayed']), 2);
				$this_season['fpp']['duo']['KD'] = number_format($duo_fpp['kills'] / ($duo_fpp['roundsPlayed'] - $duo_fpp['wins']), 2);
				$this_season['fpp']['duo']['KDA'] = number_format(($duo_fpp['kills'] + $duo_fpp['assists']) / ($duo_fpp['roundsPlayed'] - $duo_fpp['wins']), 2);
				$this_season['fpp']['duo']['longestKill'] = floor($duo_fpp['longestKill'])."m";
				$this_season['fpp']['duo']['timeSurvivedAvg'] = date("i분 s초", floor($duo_fpp['timeSurvived'] / $duo_fpp['roundsPlayed']));
				$this_season['fpp']['duo']['damageAvg'] = floor($duo_fpp['damageDealt'] / $duo_fpp['roundsPlayed']);
				$this_season['fpp']['duo']['mostKills'] = $duo_fpp['roundMostKills'];
				if($duo_fpp['kills']) {
					$this_season['fpp']['duo']['headshotPer'] = number_format(($duo_fpp['headshotKills'] / $duo_fpp['kills']) * 100, 1) . "%";
				} else {
					$this_season['fpp']['duo']['headshotPer'] = "0.0%";
				}
				$this_season['fpp']['duo']['winPoints'] = $duo_fpp['winPoints'];
				$this_season['fpp']['duo']['killPoints'] = $duo_fpp['killPoints'];
				$this_season['fpp']['duo']['rating'] = floor($duo_fpp['winPoints'] + $duo_fpp['killPoints'] * 0.2);			
			} else {
				$this_season['fpp']['duo'] = array();
			}
			
			if($squad_fpp['roundsPlayed'] > 0) {
				$this_season['fpp']['squad']['roundsPlayed'] = $squad_fpp['roundsPlayed'];
				$this_season['fpp']['squad']['wins'] = $squad_fpp['wins'];
				$this_season['fpp']['squad']['winPer'] = number_format(($squad_fpp['wins'] / $squad_fpp['roundsPlayed']) * 100 , 1) . "%";
				$this_season['fpp']['squad']['top10s'] = $squad_fpp['top10s'];
				$this_season['fpp']['squad']['top10Per'] = number_format(($squad_fpp['top10s'] / $squad_fpp['roundsPlayed']) * 100 , 1) . "%";
				$this_season['fpp']['squad']['DBNOsPerRound'] = number_format(($squad_fpp['dBNOs'] / $squad_fpp['roundsPlayed']), 2);
				$this_season['fpp']['squad']['KD'] = number_format($squad_fpp['kills'] / ($squad_fpp['roundsPlayed'] - $squad_fpp['wins']), 2);
				$this_season['fpp']['squad']['KDA'] = number_format(($squad_fpp['kills'] + $squad_fpp['assists']) / ($squad_fpp['roundsPlayed'] - $squad_fpp['wins']), 2);
				$this_season['fpp']['squad']['longestKill'] = floor($squad_fpp['longestKill'])."m";
				$this_season['fpp']['squad']['timeSurvivedAvg'] = date("i분 s초", floor($squad_fpp['timeSurvived'] / $squad_fpp['roundsPlayed']));
				$this_season['fpp']['squad']['damageAvg'] = floor($squad_fpp['damageDealt'] / $squad_fpp['roundsPlayed']);
				$this_season['fpp']['squad']['mostKills'] = $squad_fpp['roundMostKills'];
				if($squad_fpp['kills'] > 0) {
					$this_season['fpp']['squad']['headshotPer'] = number_format(($squad_fpp['headshotKills'] / $squad_fpp['kills']) * 100, 1) . "%";
				} else {
					$this_season['fpp']['squad']['headshotPer'] = "0.0%";
				}
				$this_season['fpp']['squad']['winPoints'] = $squad_fpp['winPoints'];
				$this_season['fpp']['squad']['killPoints'] = $squad_fpp['killPoints'];
				$this_season['fpp']['squad']['rating'] = floor($squad_fpp['winPoints'] + $squad_fpp['killPoints'] * 0.2);			
			} else {
				$this_season['fpp']['squad'] = array();
			}	
		} else {
			$this_season['fpp'] = array();
		}
	} else {
		unset($this_season);
	}
	return $this_season;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// get image for user's season stats about selected season(ko)
// make images about season stats
//
function mkSeasonStats($senderID, $name, $accountID, $mainServer, $season_ko, $search=NULL)
{
	include_once '/usr/share/nginx/html/pbg/class/html2pdf/html2pdf.class.php';
	
	if($search == TRUE) {
		$userSeasonStatsDir = "/usr/share/nginx/html/pbg/stats/season/{$senderID}/{$mainServer}/search/{$name}";
	} else {
		$userSeasonStatsDir = "/usr/share/nginx/html/pbg/stats/season/{$senderID}/{$mainServer}/{$name}";
	}
	if(!is_dir($userSeasonStatsDir)) {
		umask(0);
		if(@mkdir($userSeasonStatsDir, 0777, true)) {
			if(is_dir($userSeasonStatsDir)) {
				@chmod($userSeasonStatsDir, 0777);
			}
		}
		$userSeasonStatsImages = array();
	} else {
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
	}
	
	$old_imageNumbers = array();
	if(count($userSeasonStatsImages) > 0) {
		foreach($userSeasonStatsImages as $images) {
			preg_match_all("/[\d]{1,5}/", $images, $old_imageNumber);
			$old_imageNumbers[] = $old_imageNumber[0][0];
		}
		$imageNumber = max($old_imageNumbers) + 1;
	} else {
		$imageNumber = 1;
	}
	
	$seasonStats = pbg_season_stats($name, $accountID, $mainServer, $season_ko);
	$mainServer_ko = $seasonStats['user']['server_ko'];
	$currentSeason_ko = $seasonStats['user']['currentSeason_ko'];
	$seasonStatsTPP = $seasonStats['tpp'];
	if($seasonStatsTPP) {
		foreach($seasonStatsTPP as $mode=>$stats) {
			if($seasonStatsTPP[$mode]) {
				$rounds = $stats['roundsPlayed'];
				$wins = $stats['wins'];
				$winPer = $stats['winPer'];
				$top10s = $stats['top10s'];
				$top10Per = $stats['top10Per'];
				$mode == "solo" ? $DBNOsPerRound = 0 : $DBNOsPerRound = $stats['DBNOsPerRound'];
				$KD = $stats['KD'];
				$KDA = $stats['KDA'];
				$longestKill = $stats['longestKill'];
				$timeSurvivedAvg = $stats['timeSurvivedAvg'];
				$damageAvg = $stats['damageAvg'];
				$mostKills = $stats['mostKills'];
				$headshotPer = $stats['headshotPer'];
				$rating = $stats['rating'];
				
				if($mode == "solo") {
					$backgroundColor = "green";
				}
				else if($mode == "duo") {
					$backgroundColor = "red";
				}
				else if($mode == "squad") {
					$backgroundColor = "blue";
				}
				
				if(mb_strlen($name) > 11) {
					$nameHtml = "<span style='color:white; font-size:25;'>$name</span>";
				} else {
					$nameHtml = "<span style='color:white; font-size:40;'>$name</span>";
				}
				$modeText = ucwords(strtolower($mode));
				
				$mkStatsTPP = "<html><head></head><body>";
				$mkStatsTPP .= 
				"
					<table style='border-collapse:collapse;'>
						<tr>
							<td colspan=2 width=250 height=30 style='border-bottom:3px solid $backgroundColor; border-right:3px solid $backgroundColor; padding-left:10px; background-color:$backgroundColor;'><span style='color:white; font-size:20;'>{$currentSeason_ko} 시즌 {$modeText}</span></td>
							<td colspan=2 rowspan=2 width=250 height=30 align=right style='border-left:7px solid $backgroundColor; padding-right:30px; background-color:$backgroundColor;'>$nameHtml</td>
						</tr>
						<tr>
							<td colspan=2 width=250 height=30 style='border-top:3px solid $backgroundColor; border-right:3px solid $backgroundColor; padding-left:10px; background-color:$backgroundColor;'><span style='color:white; font-size:20;'>{$mainServer_ko} 서버 TPP</span></td>
						</tr>
						<tr>
							<td colspan=2 height=60 style='padding-left:10px; text-align:center;'><span style='font-size:34;'>총 {$rounds}게임</span></td>
							<td colspan=2 rowspan=2 align=center style='border-bottom: 3px solid $backgroundColor;'><span style='font-size:70;'>$rating</span></td>
						</tr>
						<tr>
							<td height=40 style='padding-left:10px; padding-bottom:5px; border-bottom:3px solid $backgroundColor;'><img src='https://bhandy.kr/pbg/image/win.png' align='center'><span style='margin-left:5px; font-size:20;'>$winPer</span><span style='margin-left:2px; font-size:14;'>($wins)</span></td>
							<td height=40 style='padding-left:10px; padding-bottom:5px; border-bottom:3px solid $backgroundColor;'><img src='https://bhandy.kr/pbg/image/Top10.png' align='center'><span style='margin-left:5px; font-size:20;'>$top10Per</span><span style='margin-left:2px; font-size:14;'>($top10s)</span></td>
						</tr>
						<tr>
							<td height=40 style='padding-left:10px;'>KD</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$KD</span></td>
							<td height=40 style='padding-left:40px;'>평균딜량</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$damageAvg</span></td>
						</tr>	
						<tr>
							<td height=40 style='padding-left:10px;'>KDA</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$KDA</span></td>
							<td height=40 style='padding-left:40px;'>헤드샷%</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$headshotPer</span></td>
						</tr>	
						<tr>
							<td height=40 style='padding-left:10px;'>평균 기절시킴</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$DBNOsPerRound</span></td>
							<td height=40 style='padding-left:40px;'>평균 생존시간</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$timeSurvivedAvg</span></td>
						</tr>	
						<tr>
							<td height=40 style='padding-left:10px;'>최다 킬</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$mostKills</span></td>
							<td height=40 style='padding-left:40px;'>최장거리 킬</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$longestKill</span></td>
						</tr>	
					</table>
				";
				$mkStatsTPP .= "</body></html>";
				
				if($search == TRUE) {
					$mkStatsPath = "/usr/share/nginx/html/pbg/stats/season/{$senderID}/{$mainServer}/search/{$name}";
				} else {
					$mkStatsPath = "/usr/share/nginx/html/pbg/stats/season/{$senderID}/{$mainServer}/{$name}";
				}
				umask(0);
				if(!is_dir($mkStatsPath)) {
					if(@mkdir($mkStatsPath, 0777, true)) {
						if(is_dir($mkStatsPath)) {
							@chmod($mkStatsPath, 0777);
						}
					}
				}
		
				// HTML -> PDF
				$pdfName_TPP = $mkStatsPath.'/'.$mode.'_'.$imageNumber.'.pdf';
				$html2pdf = new HTML2PDF('P', 'A4');
				$html2pdf->setDefaultFont('malgun');
				$html2pdf->writeHTML($mkStatsTPP);
				$html2pdfOutput = $html2pdf->Output($pdfName_TPP, 'F');
				
				// PDF -> JPG
				$imageName_TPP = $mkStatsPath.'/'.$mode.'_'.$imageNumber.'.jpg';
				$im = new Imagick();
				$im->setResolution(150,150);
				$im->readImage($pdfName_TPP);
				$im->flattenImages();
				$im->cropImage(1000, 640, 0, 0);
				$im->thumbnailImage(500, 320, 1, 1);
				$im->writeImage($imageName_TPP);
				$im->clear();
				$im->destroy();
				
				unlink($pdfName_TPP);
				
				$old_imageName_TPP = $mkStatsPath.'/'.$mode.'_'.($imageNumber-5).'.jpg';
				if(file_exists($old_imageName_TPP)) {
					unlink($old_imageName_TPP);
				}
			}
		}
	}
	
	$seasonStatsFPP = $seasonStats['fpp'];
	if($seasonStatsFPP) {
		foreach($seasonStatsFPP as $mode=>$stats) {
			if($seasonStatsFPP[$mode]) {
				$rounds = $stats['roundsPlayed'];
				$wins = $stats['wins'];
				$winPer = $stats['winPer'];
				$top10s = $stats['top10s'];
				$top10Per = $stats['top10Per'];
				$mode == "solo" ? $DBNOsPerRound = 0 : $DBNOsPerRound = $stats['DBNOsPerRound'];
				$KD = $stats['KD'];
				$KDA = $stats['KDA'];
				$longestKill = $stats['longestKill'];
				$timeSurvivedAvg = $stats['timeSurvivedAvg'];
				$damageAvg = $stats['damageAvg'];
				$mostKills = $stats['mostKills'];
				$headshotPer = $stats['headshotPer'];
				$rating = $stats['rating'];
				
				if($mode == "solo") {
					$backgroundColor = "green";
				}
				else if($mode == "duo") {
					$backgroundColor = "red";
				}
				else if($mode == "squad") {
					$backgroundColor = "blue";
				}
				
				if(mb_strlen($name) > 11) {
					$nameHtml = "<span style='color:white; font-size:25;'>$name</span>";
				} else {
					$nameHtml = "<span style='color:white; font-size:40;'>$name</span>";
				}
				$modeText = ucwords(strtolower($mode));
				
				$mkStatsFPP = "<html><head></head><body>";
				$mkStatsFPP .= 
				"
					<table style='border-collapse:collapse;'>
						<tr>
							<td colspan=2 width=250 height=30 style='border-bottom:3px solid $backgroundColor; border-right:3px solid $backgroundColor; padding-left:10px; background-color:$backgroundColor;'><span style='color:white; font-size:20;'>{$currentSeason_ko} 시즌 {$modeText}</span></td>
							<td colspan=2 rowspan=2 width=250 height=30 align=right style='border-left:7px solid $backgroundColor; padding-right:30px; background-color:$backgroundColor;'>$nameHtml</td>
						</tr>
						<tr>
							<td colspan=2 width=250 height=30 style='border-top:3px solid $backgroundColor; border-right:3px solid $backgroundColor; padding-left:10px; background-color:$backgroundColor;'><span style='color:white; font-size:20;'>{$mainServer_ko} 서버 FPP</span></td>
						</tr>
						<tr>
							<td colspan=2 height=60 style='padding-left:10px; text-align:center;'><span style='font-size:35;'>총 {$rounds}게임</span></td>
							<td colspan=2 rowspan=2 align=center style='border-bottom: 3px solid $backgroundColor;'><span style='font-size:70;'>$rating</span></td>
						</tr>
						<tr>
							<td height=40 style='padding-left:10px; padding-bottom:5px; border-bottom:3px solid $backgroundColor;'><img src='https://bhandy.kr/pbg/image/win.png' align='center'><span style='margin-left:5px; font-size:20;'>$winPer</span><span style='margin-left:2px; font-size:14;'>($wins)</span></td>
							<td height=40 style='padding-left:10px; padding-bottom:5px; border-bottom:3px solid $backgroundColor;'><img src='https://bhandy.kr/pbg/image/Top10.png' align='center'><span style='margin-left:5px; font-size:20;'>$top10Per</span><span style='margin-left:2px; font-size:14;'>($top10s)</span></td>
						</tr>
						<tr>
							<td height=40 style='padding-left:10px;'>KD</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$KD</span></td>
							<td height=40 style='padding-left:40px;'>평균딜량</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$damageAvg</span></td>
						</tr>	
						<tr>
							<td height=40 style='padding-left:10px;'>KDA</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$KDA</span></td>
							<td height=40 style='padding-left:40px;'>헤드샷%</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$headshotPer</span></td>
						</tr>	
						<tr>
							<td height=40 style='padding-left:10px;'>평균 기절시킴</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$DBNOsPerRound</span></td>
							<td height=40 style='padding-left:40px;'>평균 생존시간</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$timeSurvivedAvg</span></td>
						</tr>	
						<tr>
							<td height=40 style='padding-left:10px;'>최다 킬</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$mostKills</span></td>
							<td height=40 style='padding-left:40px;'>최장거리 킬</td>
							<td style='padding-right:10px; text-align:right;'><span style='font-size:25;'>$longestKill</span></td>
						</tr>	
					</table>
				";
				$mkStatsFPP .= "</body></html>";
				
				if($search == TRUE) {
					$mkStatsPath = "/usr/share/nginx/html/pbg/stats/season/{$senderID}/{$mainServer}/search/{$name}";
				} else {
					$mkStatsPath = "/usr/share/nginx/html/pbg/stats/season/{$senderID}/{$mainServer}/{$name}";
				}
				umask(0);
				if(!is_dir($mkStatsPath)) {
					if(@mkdir($mkStatsPath, 0777, true)) {
						if(is_dir($mkStatsPath)) {
							@chmod($mkStatsPath, 0777);
						}
					}
				}
				
				// HTML -> PDF
				$pdfName_FPP = $mkStatsPath.'/'.$mode.'-fpp_'.$imageNumber.'.pdf';
				$html2pdf = new HTML2PDF('P', 'A4');
				$html2pdf->setDefaultFont('malgun');
				$html2pdf->writeHTML($mkStatsFPP);
				$html2pdfOutput = $html2pdf->Output($pdfName_FPP, 'F');
				
				// PDF -> JPG
				$imageName_FPP = $mkStatsPath.'/'.$mode.'-fpp_'.$imageNumber.'.jpg';
				$im = new Imagick();
				$im->setResolution(150,150);
				$im->readImage($pdfName_FPP);
				$im->flattenImages();
				$im->cropImage(1000, 640, 0, 0);
				$im->thumbnailImage(500, 320, 1, 1);
				$im->writeImage($imageName_FPP);
				$im->clear();
				$im->destroy();
				
				unlink($pdfName_FPP);
				
				$old_imageName_FPP = $mkStatsPath.'/'.$mode.'-fpp_'.($imageNumber-5).'.jpg';
				if(file_exists($old_imageName_FPP)) {
					unlink($old_imageName_FPP);
				}
			}
		}
	}
	if(file_exists($imageName_TPP) || file_exists($imageName_FPP)) {
		return TRUE;
	} else {
		return FALSE;
	}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// get evaluation score data from latest match data
// each of evaluation score and sum of score
//
function stats_evaluation($pbg_latest_match)
{
	$mode = $pbg_latest_match['latest_match']['mode'];
	$total_time = $pbg_latest_match['latest_match']['finish']['timestamp'] - $pbg_latest_match['latest_match']['start']['timestamp'];
	$timeSurvived = $pbg_latest_match['latest_match']['stats']['timeSurvived'];		// 20점
	$kill = $pbg_latest_match['latest_match']['stats']['kill'];		// 15점
	$killStreaks = $pbg_latest_match['latest_match']['stats']['killStreaks'];			// 10점
	$damage = $pbg_latest_match['latest_match']['stats']['damage'];			// 15점
	$revives = $pbg_latest_match['latest_match']['stats']['revives'];			// 10점
	$DBNOs = $pbg_latest_match['latest_match']['stats']['DBNOs'];			// 10점
	$assists = $pbg_latest_match['latest_match']['stats']['assists'];			// 10점
	$headshot = $pbg_latest_match['latest_match']['stats']['headshot'];			// 10점	
	
	
	$members = $pbg_latest_match['latest_match']['members'];
	$member = array();
	for($i=0; $i<count($members); $i++) {
		$member[$i]['id'] = $members[$i]['id'];
		$member[$i]['accountID'] = $members[$i]['accountID'];
		$member[$i]['timeSurvived'] = $members[$i]['stats']['timeSurvived'];
		$member[$i]['kill'] = $members[$i]['stats']['kill'];
		$member[$i]['killStreaks'] = $members[$i]['stats']['killStreaks'];
		$member[$i]['revives'] = $members[$i]['stats']['revives'];
		$member[$i]['damage'] = $members[$i]['stats']['damage'];
		$member[$i]['DBNOs'] = $members[$i]['stats']['DBNOs'];
		$member[$i]['assists'] = $members[$i]['stats']['assists'];
		$member[$i]['headshot'] = $members[$i]['stats']['headshot'];
	}
	
	if(isset($mode) && isset($total_time) && isset($timeSurvived) && isset($damage) && isset($kill) && isset($killStreaks) && isset($revives) && isset($DBNOs) && isset($assists) && isset($headshot)) {
		$stats_evaluation = array();
		
		if(preg_match("/solo/", $mode)) {
			//
			// 생존시간40 킬25 데미지25 헤드샷5 연속킬5 => 100
			//
			
			// 생존시간
			$ev_timeSurvived[0] = 0;
			for($i=1; $i<=10; $i++) {
				$ev_timeSurvived[$i] = round((date("i", $total_time) / 10) * $i);
			}
			$timeSurvived_min = substr($timeSurvived, 0, 2);
			foreach($ev_timeSurvived as $ev_timeSurvived_k => $ev_timeSurvived_v) {
				if($timeSurvived_min >= $ev_timeSurvived_v && $timeSurvived_min <= $ev_timeSurvived_v + round((date("i", $total_time) / 10))) {
					$stats_evaluation['user']['timeSurvived'] = ($ev_timeSurvived_k + 1) * 4;
				}
			}
			// 킬
			if($kill < 9) {
				$stats_evaluation['user']['kill'] = $kill * 3;
			} else {
				$stats_evaluation['user']['kill'] = 25;
			}
			// 데미지
			if($damage < 800) {
				$stats_evaluation['user']['damage'] = sprintf('%d',25/800*$damage);
			} else {
				$stats_evaluation['user']['damage'] = 25;
			}
			// 연속킬
			if($killStreaks < 3) {
				if($killStreaks == 0) {
					$stats_evaluation['user']['killStreaks'] = 0;
				} else {
					if($killStreaks == 1) {
						$stats_evaluation['user']['killStreaks'] = 2;
					} else {
						$stats_evaluation['user']['killStreaks'] = 4;
					}
				}
			} else {
				$stats_evaluation['user']['killStreaks'] = 5;
			}
			// 헤드샷
			if($headshot >= $kill*0.3) {
				$stats_evaluation['user']['headshot'] = 5;
			}
			else if($headshot < $kill*0.3 && $headshot >= $kill*0.2) {
				$stats_evaluation['user']['headshot'] = 4;
			}
			else if($headshot < $kill*0.2 && $headshot >= $kill*0.1) {
				$stats_evaluation['user']['headshot'] = 2;
			} else {
				$stats_evaluation['user']['headshot'] = 0;
			}
		} else {
			//
			// 생존시간30 킬25 데미지20 부활시킴5 기절시킴5 어시스트5 헤드샷5 연속킬5 => 100
			//
			
			// 생존시간
			$ev_timeSurvived[0] = 0;
			for($i=1; $i<=10; $i++) {
				$ev_timeSurvived[$i] = round((date("i", $total_time) / 10) * $i);
			}
			$timeSurvived_min = substr($timeSurvived, 0, 2);
			foreach($ev_timeSurvived as $ev_timeSurvived_k => $ev_timeSurvived_v) {
				if($timeSurvived_min >= $ev_timeSurvived_v && $timeSurvived_min <= $ev_timeSurvived_v + round((date("i", $total_time) / 10))) {
					$stats_evaluation['user']['timeSurvived'] = ($ev_timeSurvived_k + 1) * 3;
				}
			}
			// 킬
			if($kill < 9) {
				$stats_evaluation['user']['kill'] = $kill * 3;
			} else {
				$stats_evaluation['user']['kill'] = 25;
			}
			// 데미지
			if($damage < 500) {
				$stats_evaluation['user']['damage'] = sprintf('%d',20/500*$damage);
			} else {
				$stats_evaluation['user']['damage'] = 20;
			}
			// 연속킬
			if($killStreaks < 3) {
				if($killStreaks == 0) {
					$stats_evaluation['user']['killStreaks'] = 0;
				} else {
					if($killStreaks == 1) {
						$stats_evaluation['user']['killStreaks'] = 2;
					} else {
						$stats_evaluation['user']['killStreaks'] = 4;
					}
				}
			} else {
				$stats_evaluation['user']['killStreaks'] = 5;
			}
			// 기절시킴
			if($DBNOs < 4) {
				$stats_evaluation['user']['DBNOs'] = $DBNOs + 1;
			} else {
				$stats_evaluation['user']['DBNOs'] = 5;
			}
			
			// 부활시킴
			if($revives >= 0) {
				if($members) {
					$members_timeSurvived = array();
					$members_revives = array();
					foreach($member as $memb) {
						$members_timeSurvived[] = $memb['timeSurvived'];
						$members_revives[] = $memb['revives'];
					}
					$members_timeSurvived_acv = array_count_values($members_timeSurvived);
					$members_revives_max = max($members_revives);
					if(count($members_timeSurvived_acv) == 1) {
						if($members_revives_max == $revives) {
							$stats_evaluation['user']['revives'] = 5;
						} else {
							if($revives == 0) {
								$stats_evaluation['user']['revives'] = 2;
							} else {
								$stats_evaluation['user']['revives'] = 4;
							}							
						}
					} else {
						if($revives == 0 || $revives == 1) {
							if($revives == 0) {
								$stats_evaluation['user']['revives'] = 1;
							}
							else if($revives == 1) {
								$stats_evaluation['user']['revives'] = 3;
							}
						} else {
							$stats_evaluation['user']['revives'] = 5;
						}							
					}
				} else {
					$stats_evaluation['user']['revives'] = 5;
				}
			}
			// 어시스트
			if($assists >= 0) {
				if($members) {
					$members_kill = array();
					foreach($member as $memb) {
						$members_kill[] = $memb['kill'];
					}
					$members_kill_sum = array_sum($members_kill);
					if($members_kill_sum == 0) {
						if($kill == 0) {
							$stats_evaluation['user']['assists'] = 0;
						} else {
							$stats_evaluation['user']['assists'] = 3;
						}
					} else {
						if($assists >= $members_kill_sum*0.3) {
							$stats_evaluation['user']['assists'] = 5;
						}
						else if($assists < $members_kill_sum*0.3 && $assists >= $members_kill_sum*0.2) {
							$stats_evaluation['user']['assists'] = 4;
						}
						else if($assists < $members_kill_sum*0.2 && $assists >= $members_kill_sum*0.1) {
							$stats_evaluation['user']['assists'] = 3;
						} else {
							$stats_evaluation['user']['assists'] = 1;
						}
					}
				} else {
					$stats_evaluation['user']['assists'] = 5;
				}
			}
			// 헤드샷
			if($headshot >0) {
				if($headshot >= $kill*0.3) {
					$stats_evaluation['user']['headshot'] = 5;
				}
				else if($headshot < $kill*0.3 && $headshot >= $kill*0.2) {
					$stats_evaluation['user']['headshot'] = 4;
				}
				else if($headshot < $kill*0.2 && $headshot >= $kill*0.1) {
					$stats_evaluation['user']['headshot'] = 2;
				} else {
					$stats_evaluation['user']['headshot'] = 0;
				}
			} else {
				$stats_evaluation['user']['headshot'] = 0;
			}

			//// 멤버 스텟
			if($members && $member) {
				for($i=0; $i<count($member); $i++) {
					// 멤버 생존시간
					$member_ev_timeSurvived[0] = 0;
					for($j=1; $j<=10; $j++) {
						$member_ev_timeSurvived[$j] = round((date("i", $total_time) / 10) * $j);
					}
					$member_timeSurvived_min = substr($member[$i]['timeSurvived'], 0, 2);
					foreach($member_ev_timeSurvived as $member_ev_timeSurvived_k => $member_ev_timeSurvived_v) {
						if($member_timeSurvived_min >= $member_ev_timeSurvived_v && $member_timeSurvived_min <= $member_ev_timeSurvived_v + round((date("i", $total_time) / 10))) {
							$stats_evaluation['member'][$i]['timeSurvived'] = ($member_ev_timeSurvived_k + 1) * 3;
						}
					}
					// 멤버 킬
					if($member[$i]['kill'] < 9) {
						$stats_evaluation['member'][$i]['kill'] = $member[$i]['kill'] * 3;
					} else {
						$stats_evaluation['member'][$i]['kill'] = 25;
					}
					// 멤버 데미지
					if($member[$i]['damage'] < 500) {
						$stats_evaluation['member'][$i]['damage'] = sprintf('%d',20/500*$member[$i]['damage']);
					} else {
						$stats_evaluation['member'][$i]['damage'] = 20;
					}
					// 멤버 연속킬
					if($member[$i]['killStreaks'] < 3) {
						if($member[$i]['killStreaks'] == 0) {
							$stats_evaluation['member'][$i]['killStreaks'] = 0;
						} else {
							if($member[$i]['killStreaks'] == 1) {
								$stats_evaluation['member'][$i]['killStreaks'] = 2;
							} else {
								$stats_evaluation['member'][$i]['killStreaks'] = 4;
							}
						}
					} else {
						$stats_evaluation['member'][$i]['killStreaks'] = 5;
					}
					// 멤버 기절시킴
					if($member[$i]['DBNOs'] < 4) {
						$stats_evaluation['member'][$i]['DBNOs'] = $member[$i]['DBNOs'] + 1;
					} else {
						$stats_evaluation['member'][$i]['DBNOs'] = 5;
					}
					
					// 멤버 부활시킴
					if($member[$i]['revives'] >= 0) {
						$members_timeSurvived = array();
						$members_revives = array();
						foreach($member as $memb) {
							$members_timeSurvived[] = $memb['timeSurvived'];
							$members_revives[] = $memb['revives'];
						}
						$members_timeSurvived_acv = array_count_values($members_timeSurvived);
						$members_revives_max = max($members_revives);
						if(count($members_timeSurvived_acv) == 1) {
							if($members_revives_max == $member[$i]['revives']) {
								$stats_evaluation['member'][$i]['revives'] = 5;
							} else {
								if($member[$i]['revives'] == 0) {
									$stats_evaluation['member'][$i]['revives'] = 2;
								} else {
									$stats_evaluation['member'][$i]['revives'] = 4;
								}							
							}
						} else {
							if($member[$i]['revives'] == 0 || $member[$i]['revives'] == 1) {
								if($member[$i]['revives'] == 0) {
									$stats_evaluation['member'][$i]['revives'] = 1;
								}
								else if($member[$i]['revives'] == 1) {
									$stats_evaluation['member'][$i]['revives'] = 3;
								}
							} else {
								$stats_evaluation['member'][$i]['revives'] = 5;
							}							
						}
					}
					// 멤버 어시스트
					if($member[$i]['assists'] >= 0) {
						$members_kill = array();
						foreach($member as $memb) {
							$members_kill[] = $memb['kill'];
						}
						$members_kill_sum = array_sum($members_kill);
						if($members_kill_sum == 0) {
							if($member[$i]['kill'] == 0) {
								$stats_evaluation['member'][$i]['assists'] = 0;
							} else {
								$stats_evaluation['member'][$i]['assists'] = 3;
							}
						} else {
							if($member[$i]['assists'] >= $members_kill_sum*0.3) {
								$stats_evaluation['member'][$i]['assists'] = 5;
							}
							else if($member[$i]['assists'] < $members_kill_sum*0.3 && $member[$i]['assists'] >= $members_kill_sum*0.2) {
								$stats_evaluation['member'][$i]['assists'] = 4;
							}
							else if($member[$i]['assists'] < $members_kill_sum*0.2 && $member[$i]['assists'] >= $members_kill_sum*0.1) {
								$stats_evaluation['member'][$i]['assists'] = 3;
							} else {
								$stats_evaluation['member'][$i]['assists'] = 1;
							}
						}
					}
					// 멤버 헤드샷
					if($member[$i]['headshot'] > 0) {
						if($member[$i]['headshot'] >= $member[$i]['kill']*0.3) {
							$stats_evaluation['member'][$i]['headshot'] = 5;
						}
						else if($member[$i]['headshot'] < $member[$i]['kill']*0.3 && $member[$i]['headshot'] >= $member[$i]['kill']*0.2) {
							$stats_evaluation['member'][$i]['headshot'] = 4;
						}
						else if($member[$i]['headshot'] < $member[$i]['kill']*0.2 && $member[$i]['headshot'] >= $member[$i]['kill']*0.1) {
							$stats_evaluation['member'][$i]['headshot'] = 2;
						} else {
							$stats_evaluation['member'][$i]['headshot'] = 0;
						}						
					} else {
						$stats_evaluation['member'][$i]['headshot'] = 0;
					}

					$stats_evaluation['member'][$i]['sum'] = array_sum($stats_evaluation['member'][$i]);
				}

			}
		}

		$stats_evaluation['user']['sum'] = array_sum($stats_evaluation['user']);
		
		return $stats_evaluation;
	}
}