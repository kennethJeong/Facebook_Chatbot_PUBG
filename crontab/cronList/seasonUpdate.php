<?php
if(date("H:i") == "12:00") {
	$pbg_servers = pbg_server();
	$pbg_seasons = pbg_seasons($pbg_servers);
	$pbg_seasons_count = count($pbg_seasons);
	for($i=0; $i<$pbg_seasons_count; $i++) {
		$server = $pbg_seasons[$i]['server'];
		$server_ko = $pbg_seasons[$i]['server_ko'];
		$season_list = $pbg_seasons[$i]['season'];
		$season_list_count = count($season_list);
		for($j=0; $j<$season_list_count; $j++) {
			$season_id = $season_list[$j]['id'];
			$season_ko = $season_list[$j]['ko'];
			$season_current = $season_list[$j]['season_current'];
			$season_off = $season_list[$j]['season_off'];
			$query = "SELECT * FROM season WHERE server='$server' AND server_ko='$server_ko' AND season='$season_id' AND season_ko='$season_ko'";
			$sql4season = $conn->query($query)->fetch_assoc();
			if(!$sql4season) {
				if($season_current == TRUE || $season_off == TRUE) {
					$query = "UPDATE season SET season_current='', season_off='' WHERE server='$server' AND server_ko='$server_ko'";				
					$conn->query($query);				
				}
				$query = "INSERT INTO season (server, server_ko, season, season_ko, season_current, season_off, inputTime)
														VALUE('$server', '$server_ko', '$season_id', '$season_ko', '$season_current', '$season_off', '$inputTime')";
				$conn->query($query);
			} else {
				$old_season_current = $sql4season['season_current'];
				$old_season_off = $sql4season['season_off'];
				if($old_season_current != $season_current) {
					$query = "UPDATE season SET season_current='' WHERE server='$server' AND server_ko='$server_ko'";				
					$conn->query($query);
	
					$query = "UPDATE season SET season_current='$season_current', inputTime='$inputTime'
																WHERE server='$server' AND server_ko='$server_ko' AND season='$season_id' AND season_ko='$season_ko'";
					$conn->query($query);				
				}
				if($old_season_off != $season_off) {
					$query = "UPDATE season SET season_off='' WHERE server='$server' AND server_ko='$server_ko'";				
					$conn->query($query);					
	
					$query = "UPDATE season SET season_off='$season_off', inputTime='$inputTime'
																WHERE server='$server' AND server_ko='$server_ko' AND season='$season_id' AND season_ko='$season_ko'";
					$conn->query($query);				
				}		
			}
		}
	}
}