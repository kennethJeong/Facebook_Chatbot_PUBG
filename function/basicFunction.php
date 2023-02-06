<?php
function queryInsert($dbTable, $content1, $content2=NULL)
{
	global $senderID, $inputTime;	
	
	if(!$content2 && !is_array($content2)) {
		$query = "INSERT INTO $dbTable (userkey, inProgress, inputTime) VALUE ('$senderID', '$content1', '$inputTime')";
	}
	else 	if($content2 && is_array($content2)) {
		foreach($content2 as $key=>$value) {
			$keys[] = $key;
			$values[] = "'" . $value. "'";
		}
		count($keys) == 1 ? $keys = $keys[0] : $keys = implode(", ", $keys);
		count($values) == 1 ? $values = $values[0] : $values = implode(", ", $values);

		$query = "INSERT INTO $dbTable (userkey, inProgress, $keys, inputTime) VALUE ('$senderID', '$content1', $values, '$inputTime')";		
	}
	return $query;
}

function ReturningQR()
{
	$send['text'] = "👁‍🗨: 이전 단계로 돌아가려면 아래 버튼을 눌러주세요.";
	$send['payload'] = $send['title'] = array('이전으로', '초기화면');

	messageQR($send);
}

function findUserName($userkey)
{
	$getSenderFullName = json_decode(curlGet("https://graph.facebook.com/v2.6/" . $userkey . "?fields=first_name,last_name&access_token="), true);
	$senderFullName = $getSenderFullName['last_name'] . $getSenderFullName['first_name'];
	
	return $senderFullName;
}

function getCourseColumnData($thisCourse, $column)
{
	global $conn;
	
	if($column == 'divs' || $column == 'title' || $column == 'major' || $column == 'fields') {
		if($column == 'fields') {
			$query = "SELECT DISTINCT $column FROM $thisCourse WHERE $column LIKE '%영역'";
		} else {
			$query = "SELECT DISTINCT $column FROM $thisCourse";
		}
		$sql = $conn->query($query);
		while($row = $sql->fetch_assoc()) {
			$result[] = $row[$column];
		}
		
		return $result;
	}
}

function aes_encrypt($plaintext, $password)
{
    // 보안을 최대화하기 위해 비밀번호를 해싱한다.
    
    $password = hash('sha256', $password, true);
    
    // 용량 절감과 보안 향상을 위해 평문을 압축한다.
    
    $plaintext = gzcompress($plaintext);
    
    // 초기화 벡터를 생성한다.
    
    $iv_source = defined('MCRYPT_DEV_URANDOM') ? MCRYPT_DEV_URANDOM : MCRYPT_RAND;
    $iv = mcrypt_create_iv(32, $iv_source);
    
    // 암호화한다.
    
    $ciphertext = mcrypt_encrypt('rijndael-256', $password, $plaintext, 'cbc', $iv);
    
    // 위변조 방지를 위한 HMAC 코드를 생성한다. (encrypt-then-MAC)
    
    $hmac = hash_hmac('sha256', $ciphertext, $password, true);
    
    // 암호문, 초기화 벡터, HMAC 코드를 합하여 반환한다.
    
    return base64_encode($ciphertext . $iv . $hmac);
}

function aes_decrypt($ciphertext, $password)
{
    // 초기화 벡터와 HMAC 코드를 암호문에서 분리하고 각각의 길이를 체크한다.
    
    $ciphertext = @base64_decode($ciphertext, true);
    if ($ciphertext === false) return false;
    $len = strlen($ciphertext);
    if ($len < 64) return false;
    $iv = substr($ciphertext, $len - 64, 32);
    $hmac = substr($ciphertext, $len - 32, 32);
    $ciphertext = substr($ciphertext, 0, $len - 64);
    
    // 암호화 함수와 같이 비밀번호를 해싱한다.
    
    $password = hash('sha256', $password, true);
    
    // HMAC 코드를 사용하여 위변조 여부를 체크한다.
    
    $hmac_check = hash_hmac('sha256', $ciphertext, $password, true);
    if ($hmac !== $hmac_check) return false;
    
    // 복호화한다.
    
    $plaintext = @mcrypt_decrypt('rijndael-256', $password, $ciphertext, 'cbc', $iv);
    if ($plaintext === false) return false;
    
    // 압축을 해제하여 평문을 얻는다.
    
    $plaintext = @gzuncompress($plaintext);
    if ($plaintext === false) return false;
    
    // 이상이 없는 경우 평문을 반환한다.
    
    return $plaintext;
}