<?php

namespace litepubl\core;

class Response
{
public $protocol;
public $status;
public $headers;
public $body;

public function __construct()
{
$this->protocol = '1.1';
$this->status = 200;
$this->headers = [
'Content-type' => 'text/html;charset=utf-8',
//'Last-Modified' => date('r'),
//'X-Pingback' => $this->getApp()->site->url . '/rpc.xml',
];

$this->body = '';
}

//psr7 interface methods
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    public function getHeaders()
    {
$result = [];
foreach ($this->headers as $k => $v) {
$result[$k] = [$v];
}

return $result;
    }



