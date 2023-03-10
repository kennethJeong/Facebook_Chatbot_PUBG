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
	$send['text'] = "πβπ¨: μ΄μ  λ¨κ³λ‘ λμκ°λ €λ©΄ μλ λ²νΌμ λλ¬μ£ΌμΈμ.";
	$send['payload'] = $send['title'] = array('μ΄μ μΌλ‘', 'μ΄κΈ°νλ©΄');

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
			$query = "SELECT DISTINCT $column FROM $thisCourse WHERE $column LIKE '%μμ­'";
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
    // λ³΄μμ μ΅λννκΈ° μν΄ λΉλ°λ²νΈλ₯Ό ν΄μ±νλ€.
    
    $password = hash('sha256', $password, true);
    
    // μ©λ μ κ°κ³Ό λ³΄μ ν₯μμ μν΄ νλ¬Έμ μμΆνλ€.
    
    $plaintext = gzcompress($plaintext);
    
    // μ΄κΈ°ν λ²‘ν°λ₯Ό μμ±νλ€.
    
    $iv_source = defined('MCRYPT_DEV_URANDOM') ? MCRYPT_DEV_URANDOM : MCRYPT_RAND;
    $iv = mcrypt_create_iv(32, $iv_source);
    
    // μνΈννλ€.
    
    $ciphertext = mcrypt_encrypt('rijndael-256', $password, $plaintext, 'cbc', $iv);
    
    // μλ³μ‘° λ°©μ§λ₯Ό μν HMAC μ½λλ₯Ό μμ±νλ€. (encrypt-then-MAC)
    
    $hmac = hash_hmac('sha256', $ciphertext, $password, true);
    
    // μνΈλ¬Έ, μ΄κΈ°ν λ²‘ν°, HMAC μ½λλ₯Ό ν©νμ¬ λ°ννλ€.
    
    return base64_encode($ciphertext . $iv . $hmac);
}

function aes_decrypt($ciphertext, $password)
{
    // μ΄κΈ°ν λ²‘ν°μ HMAC μ½λλ₯Ό μνΈλ¬Έμμ λΆλ¦¬νκ³  κ°κ°μ κΈΈμ΄λ₯Ό μ²΄ν¬νλ€.
    
    $ciphertext = @base64_decode($ciphertext, true);
    if ($ciphertext === false) return false;
    $len = strlen($ciphertext);
    if ($len < 64) return false;
    $iv = substr($ciphertext, $len - 64, 32);
    $hmac = substr($ciphertext, $len - 32, 32);
    $ciphertext = substr($ciphertext, 0, $len - 64);
    
    // μνΈν ν¨μμ κ°μ΄ λΉλ°λ²νΈλ₯Ό ν΄μ±νλ€.
    
    $password = hash('sha256', $password, true);
    
    // HMAC μ½λλ₯Ό μ¬μ©νμ¬ μλ³μ‘° μ¬λΆλ₯Ό μ²΄ν¬νλ€.
    
    $hmac_check = hash_hmac('sha256', $ciphertext, $password, true);
    if ($hmac !== $hmac_check) return false;
    
    // λ³΅νΈννλ€.
    
    $plaintext = @mcrypt_decrypt('rijndael-256', $password, $ciphertext, 'cbc', $iv);
    if ($plaintext === false) return false;
    
    // μμΆμ ν΄μ νμ¬ νλ¬Έμ μ»λλ€.
    
    $plaintext = @gzuncompress($plaintext);
    if ($plaintext === false) return false;
    
    // μ΄μμ΄ μλ κ²½μ° νλ¬Έμ λ°ννλ€.
    
    return $plaintext;
}