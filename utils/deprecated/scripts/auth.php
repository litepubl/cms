<?php

function GetDigestHeader() {
	if (function_exists('apache_request_headers') && ini_get('safe_mode') == false) {
		$arh = apache_request_headers();
		return  isset($arh['Authorization']) ? $arh['Authorization'] : null;
	} elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
		return $_SERVER['PHP_AUTH_DIGEST'];
	} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
		return $_SERVER['HTTP_AUTHORIZATION'];
	} elseif (isset($_ENV['PHP_AUTH_DIGEST'])) {
		return $_ENV['PHP_AUTH_DIGEST'];
	} elseif (isset($_SERVER['Authorization'])) {
		return $_SERVER['Authorization'];
	} elseif (isset($_REQUEST['auth'])) {
		return stripslashes(urldecode($_REQUEST['auth']));
}
		return null;
}

function GetDigest() {
if ($hdr = GetDigestHeader()) {
	return  substr($hdr,0,7) == 'Digest ' ?  substr($hdr, strpos($hdr, ' ') + 1) : $hdr;
}
return false;
}

function CheckAuth() {
global $Options;
if ($digest = GetDigest()) {
		$hdr = array();
		preg_match_all('/(\w+)=(?:"([^"]+)"|([^\s,]+))/', $digest, $mtx, PREG_SET_ORDER);
		foreach ($mtx as $m) $hdr[$m[1]] = $m[2] ? $m[2] : $m[3];
if (md5(secret) != $hdr['nonce']) return false;
//		if ($Options->login != $hdr['username']) return false;
//		$a1 = strtolower($profile['auth_password']);
		$a1 = strtolower(md5(
'test:mytest:test'));
			$a2 = $hdr['qop'] == 'auth-int' 				? 
md5(implode(':', array($_SERVER['REQUEST_METHOD'], $hdr['uri'], md5($entity_body))))
				: md5(implode(':', array($_SERVER['REQUEST_METHOD'], $hdr['uri'])));
			$ok = md5(implode(':', array($a1, $hdr['nonce'], $hdr['nc'], $hdr['cnonce'], $hdr['qop'], $a2)));

return $ok == $hdr['response'];
}
return false;
}

define('secret', 'mysecret');
if (CheckAuth()) {
echo "succes\n";
}
//echo "not auth\n";
//return;


$s = sprintf('WWW-Authenticate: Digest qop="auth-int, auth", realm="%s", domain="%s", nonce="%s", opaque="%s", stale="%s", algorithm="MD5"', 
'mytest',  '/',  
md5(secret),  md5('mytest'),  'false');
	header($s);
	header('HTTP/1.0 401 Unauthorized');
echo "<pre>\n";
//echo $s, "\n";
var_dump($PHP_AUTH_DIGEST);
var_dump($_SERVER);

?>