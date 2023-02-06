<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$conn = new mysqli($dbhost, $dbuser, $dbpass);
$conn -> select_db($db);
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$hubVerifyToken = 'BHandy_PBG';
$accessToken = "EAAa9pM2AuqkBAI3M7eZAmH5eiKX9PZBZCFTa8WLXJFu8sf5P6kI9bbV9TalH7RA49JijFQ4BtbKxm2ZC9fvil1QwTyhZBgK3KkUVN5u3FCGTiEFpZAa10X5eSDwcNb0IHKYWqetLxEvnCRwfJrtCmZAe3hjvbRDqCWD5UC5CdmVWUyX1rg7W2Yu";
if($_REQUEST['hub_verify_token'] === $hubVerifyToken) {
	//echo $_REQUEST['hub_challenge'];
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$input = json_decode(file_get_contents('php://input'), true);
$senderID = $input['entry'][0]['messaging'][0]['sender']['id'];
$recipientID = $input['entry'][0]['messaging'][0]['recipient']['id'];
$messageText = $input['entry'][0]['messaging'][0]['message']['text'];
$payload = $input['entry'][0]['messaging'][0]['postback']['payload'];
$payloadQR = $input['entry'][0]['messaging'][0]['message']['quick_reply']['payload'];
///////////////////////////////////////////////////
$pageServiceID = "";
$appServiceID = "2000094200061648";
///////////////////////////////////////////////////
$pbg_api_key = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJiODdmYjY3MC00MjFiLTAxMzYtNDEwOS0yNTE1Y2RhMTQzMzYiLCJpc3MiOiJnYW1lbG9ja2VyIiwiaWF0IjoxNTI3MjMzNTU2LCJwdWIiOiJibHVlaG9sZSIsInRpdGxlIjoicHViZyIsImFwcCI6InBiZ19ib3QifQ.PIAUY9wARH1kymcHLDsNsQ4xcn7b3N0YtJKh0tTRSNc";
$pbg_api_key_1 = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI1MWFiYzAyMC03MWUzLTAxMzYtZmFiMS0wMWQ3M2ZiNGU4NzUiLCJpc3MiOiJnYW1lbG9ja2VyIiwiaWF0IjoxNTMyNDg2OTg4LCJwdWIiOiJibHVlaG9sZSIsInRpdGxlIjoicHViZyIsImFwcCI6InBiZ19ib3RfMiJ9.wJ3DB6tZUVIaHM14-JzjV7wjtnie092xx4CdRYpE-b8";
$pbg_api_key_2 = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI0MTFlOTYyMC03MWU2LTAxMzYtZjFmNy01Nzc3ZmRiNmJmOTEiLCJpc3MiOiJnYW1lbG9ja2VyIiwiaWF0IjoxNTMyNDg4MjQ5LCJwdWIiOiJibHVlaG9sZSIsInRpdGxlIjoicHViZyIsImFwcCI6InBiZ19ib3RfMyJ9.7RVkFdsLimQnZjXM5zVML9AYxWYGJ7a0Lyk6HSatOi8";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$thisYear = date("Y");
$inputTime = date("Y-m-d H:i:s",time());
// timestamp type
$now = strtotime($inputTime);
// date type (ex. 2018-01-01)
$today = date("Y-m-d");
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////