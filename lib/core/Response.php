<?php

namespace litepubl\core;

class Response
{
    private $phrases = [
        200 => 'OK',
        301 => 'Moved Permanently',        206 => 'Partial Content',
        304 => 'Not Modified',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
];

public $body;
public $cache;
public $headers;
public $protocol;
public $status;

public function __construct()
{
$this->body = '';
$this->cache = true;
$this->protocol = '1.1';
$this->status = 200;
$this->headers = [
'Content-type' => 'text/html;charset=utf-8',
//'Last-Modified' => date('r'),
//'X-Pingback' => $this->getApp()->site->url . '/rpc.xml',
];
}

public function setCache($mode)
{
if ($mode) {
unset($this->headers['Cache-Control']);
unset($this->headers['Pragma']);
} else {
$this->headers['Cache-Control'] = 'no-cache, must-revalidate';
$this->headers['Pragma'] = 'no-cache';
}
}



public function send() {
header(sprintf('HTTP/%s %s %s', $this->protocol, $this->status, $this->phrases[$this->status]), true, $this->status);

foreach ($this->headers as $k => $v) {
header(sprintf('%s: %s', $k, $v));
}

echo $this->body;
}

public function redir($url)
 {
$this->headers['Location'] = $url;
$this->status = 301;
}
