<?php

function long($b) {
  $bytes = array_merge(unpack('C*', $b));
  $n = 0;
  foreach ($bytes as $byte) {
    $n = bmmul($n, bmpow(2,8));
    $n = bmadd($n, $byte);
  }
  return $n;
}

function random ( $max ) {
  if (strlen($max) < 4) return mt_rand(1, $max - 1);
  $r = '';
  for($i=1; $i<strlen($max) - 1; $i++) $r .= mt_rand(0,9);
  $r .= mt_rand(1,9);
  return $r;
}

function str_diff_at ($a, $b) {
  if ($a == $b) return -1;
  $n = min(strlen($a), strlen($b));
  for ($i = 0; $i < $n; $i++) {
    if ($a[$i] != $b[$i]) return $i;
  }
  return $n;
}

function x_or ($a, $b) {
  $r = '';
  for ($i = 0; $i < strlen($b); $i++) $r .= $a[$i] ^ $b[$i];
  return $r;
}

function bmadd($l, $r) {
  if (function_exists('bcadd')) return bcadd($l, $r);
  if (function_exists('gmp_strval')) return gmp_strval(gmp_add($l, $r));
  
  $l = strval($l); $r = strval($r);
  $ll = strlen($l); $rl = strlen($r);
  if ($ll < $rl) {
    $l = str_repeat("0", $rl-$ll) . $l;
    $o = $rl;
  } elseif ( $ll > $rl ) {
    $r = str_repeat("0", $ll-$rl) . $r;
    $o = $ll;
  } else {
    $o = $ll;
  }
  
  $v = '';
  $carry = 0;
  for ($i = $o-1; $i >= 0; $i--) {
    $d = (int)$l[$i] + (int)$r[$i] + $carry;
    if ($d <= 9) {
      $carry = 0;
    } else {
      $carry = 1;
      $d -= 10;
    }
    $v = (string) $d . $v;
  }
  
  if ($carry > 0) $v = "1" . $v;
  return $v;
}

function bmcomp($l, $r) {
  if (function_exists('bccomp')) return bccomp($l, $r);
  if (function_exists('gmp_strval')) return gmp_strval(gmp_cmp($l, $r));
  
  $l = strval($l); $r = strval($r);
  $ll = strlen($l); $lr = strlen($r);
  if ($ll != $lr) return ($ll > $lr) ? 1 : -1;
  return strcmp($l, $r);
}

function bmdiv($l, $r, $z = 0) {
  if (function_exists('bcdiv')) return ($z == 0) ? bcdiv($l, $r) : bcmod($l, $r);
  if (function_exists('gmp_div_q')) return gmp_strval(($z == 0) ? gmp_div_q($l, $r) : gmp_mod($l, $r));
  
  $l = strval($l); $r = strval($r);
  $v = '0';
  
  while (true) {
    if( bmcomp($l, $r) < 0 ) break;
    $delta = strlen($l) - strlen($r);
    if ($delta >= 1) {
      $zeroes = str_repeat("0", $delta);
      $r2 = $r . $zeroes;
      
      if (strcmp($l, $r2) >= 0) {
        $v = bmadd($v, "1" . $zeroes);
        $l = bmsub($l, $r2);
        
      } else {
        $zeroes = str_repeat("0", $delta - 1);
        $v = bmadd($v, "1" . $zeroes);
        $l = bmsub($l, $r . $zeroes);
      }
      
    } else {
      $l = bmsub($l, $r);
      $v = bmadd($v, "1");
    }
  }
  
  return ($z == 0) ? $v : $l;
}

function bmmul($l, $r) {
  if (function_exists('bcmul')) return bcmul($l, $r);
  if (function_exists('gmp_mul')) return gmp_strval(gmp_mul($l, $r));
  
  $l = strval($l); $r = strval($r);
  
  $v = '0';
  $z = '';
  
  for( $i = strlen($r)-1; $i >= 0; $i-- ){
    $bd = (int) $r[$i];
    $carry = 0;
    $p = "";
    for( $j = strlen($l)-1; $j >= 0; $j-- ){
      $ad = (int) $l[$j];
      $pd = $ad * $bd + $carry;
      if( $pd <= 9 ){
        $carry = 0;
      } else {
        $carry = (int) ($pd / 10);
        $pd = $pd % 10;
      }
      $p = (string) $pd . $p;
    }
    if( $carry > 0 )
    $p = (string) $carry . $p;
    $p = $p . $z;
    $z .= "0";
    $v = bmadd($v, $p);
  }
  
  return $v;
}

function bmmod( $value, $mod ) {
  if (function_exists('bcmod')) return bcmod($value, $mod);
  if (function_exists('gmp_mod')) return gmp_strval(gmp_mod($value, $mod));
  return bmdiv($value, $mod, 1);
}

function bmpow ($value, $exponent) {
  if (function_exists('bcpow')) return bcpow($value, $exponent);
  if (function_exists('gmp_pow')) return gmp_strval(gmp_pow($value, $exponent));
  
  $r = '1';
  while ($exponent) {
    $r = bmmul($r, $value, 100);
    $exponent--;
  }
  return (string)rtrim($r, '0.');
}

function bmpowmod ($value, $exponent, $mod) {
  if (function_exists('bcpowmod')) return bcpowmod($value, $exponent, $mod);
  if (function_exists('gmp_powm')) return gmp_strval(gmp_powm($value, $exponent, $mod));
  
  $r = '';
  while ($exponent != '0') {
    $t = bmmod($exponent, '4096');
    $r = substr("000000000000" . decbin(intval($t)), -12) . $r;
    $exponent = bmdiv($exponent, '4096');
  }
  
  $r = preg_replace("!^0+!","",$r);
  
  if ($r == '')
  $r = '0';
  $value = bmmod($value, $mod);
  $erb = strrev($r);
  $q = '1';
  $a[0] = $value;
  
  for ($i = 1; $i < strlen($erb); $i++) {
    $a[$i] = bmmod( bmmul($a[$i-1], $a[$i-1]), $mod );
  }
  
  for ($i = 0; $i < strlen($erb); $i++) {
    if ($erb[$i] == "1") {
      $q = bmmod( bmmul($q, $a[$i]), $mod );
    }
  }
  
  return($q);
}

function bmsub($l, $r) {
  if (function_exists('bcsub')) return bcsub($l, $r);
  if (function_exists('gmp_sub')) return gmp_strval(gmp_sub($l, $r));
  
  $l = strval($l); $r = strval($r);
  $ll = strlen($l); $rl = strlen($r);
  
  if ($ll < $rl) {
    $l = str_repeat("0", $rl-$ll) . $l;
    $o = $rl;
  } elseif ( $ll > $rl ) {
    $r = str_repeat("0", $ll-$rl) . (string)$r;
    $o = $ll;
  } else {
    $o = $ll;
  }
  
  if (strcmp($l, $r) >= 0) {
    $sign = '';
  } else {
    $x = $l; $l = $r; $r = $x;
    $sign = '-';
  }
  
  $v = '';
  $carry = 0;
  
  for ($i = $o-1; $i >= 0; $i--) {
    $d = ($l[$i] - $r[$i]) - $carry;
    if ($d < 0) {
      $carry = 1;
      $d += 10;
    } else {
      $carry = 0;
    }
    $v = (string) $d . $v;
  }
  
  return $sign . ltrim($v, '0');
}

function bin ($n) {
  $bytes = array();
  while (bmcomp($n, 0) > 0) {
    array_unshift($bytes, bmmod($n, 256));
    $n = bmdiv($n, bmpow(2,8));
  }
  
  if ($bytes && ($bytes[0] > 127))
  array_unshift($bytes, 0);
  
  $b = '';
  foreach ($bytes as $byte)
  $b .= pack('C', $byte);
  
  return $b;
}

function hmac($key, $data) {
  $blocksize=64;
  if (strlen($key) > $blocksize) $key = sha1($key, true);
  $key = str_pad($key, $blocksize,chr(0x00));
  $ipad = str_repeat(chr(0x36),$blocksize);
  $opad = str_repeat(chr(0x5c),$blocksize);
  $h1 = sha1(($key ^ $ipad) . $data, true);
  $hmac = sha1(($key ^ $opad) . $h1, true);
  return $hmac;
}

function new_secret () {
  $r = '';
  for($i=0; $i<20; $i++) $r .= chr(mt_rand(0, 255));
  return $r;
}