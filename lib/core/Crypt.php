<?php

namespace litepubl\core;

class Crypt
{
const METHOD = 'AES-256-CTR';
const LENGTH = 32;
const NONCELENGTH = 16;

    public static function encode(string $s, string $password): string
    {
$nonce = static::getNonce();
$result = openssl_encrypt($s, static::METHOD, static::getPassword($password, $nonce), OPENSSL_RAW_DATA, $nonce);
return base64_encode($nonce . $result);
    }

    public static function decode(string $s, string $password): string
    {
$s = base64_decode($s);
$nonce = substr($s, 0, static::NONCELENGTH);
return openssl_decrypt(substr($s, static::NONCELENGTH), static::METHOD, static::getPassword($password, $nonce), OPENSSL_RAW_DATA, $nonce);
    }

public static function getNonce()
{
return openssl_random_pseudo_bytes (static::NONCELENGTH);
}

public static function getPassword(string $password, string $solt): string
{
return openssl_pbkdf2($password , $solt, static::LENGTH, 2, 'MD5');
}

}