<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');

    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);
echo "<pre>\n";
$url = $_GET['url'];
            $bits = parse_url($url);
//var_dump($bits );
//flush();
            $host = $bits['host'];
            $path = isset($bits['path']) ? $bits['path'] : '/';
            $port = isset($bits['port']) ? $bits['port'] : 80;

//var_dump($bits);
        $r = "\r\n";
        $request  = "GET {$path} HTTP/1.0$r";

        $headers = array('Host' =>  $host);
//        $headers['Content-Type']  = 'text/xml';
        $headers['User-Agent']    = 'tester';
        //$this->headers['Content-Length']= $length;

        foreach( $headers as $header => $value ) {
//litepublisher replace to sprintf
            $request .= sprintf('%s: %s%s', $header, $value, $r);
        }
        $request .= $r;

echo "request:\n$request\n";;
if ($fp = fsockopen($host, $port, $errno, $errstr)) {
        fputs($fp, $request);
echo "response:\n";
        while (!feof($fp)) {
            $s = fgets($fp, 4096);
if (!trim($s)) exit();
echo htmlspecialchars($s);
flush();
//echo "\n";
}
fclose($fp);
}
 else echo "not connected";
