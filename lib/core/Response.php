<?php

namespace litepubl\core;

class Response
{
use AppTrait;

    protected $phrases = [
        200 => 'OK',
        206 => 'Partial Content',
        301 => 'Moved Permanently',
        302 => 'Found',
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

public function setCacheHeaders($mode)
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
if (!isset($this->phrases]$this->status])) {
$this->getApp()->getLogger()->warning(sprintf('Phrase for status %s not exists', $this->status));
}

header(sprintf('HTTP/%s %s %s', $this->protocol, $this->status, $this->phrases[$this->status]), true, $this->status);

$this->setCacheHeaders($this->cache);
foreach ($this->headers as $k => $v) {
header(sprintf('%s: %s', $k, $v));
}

if (is_string($this->body)) {
echo $this->body;
} elseif (is_callable($this->body)) {
call_user_func_array($this->body, [$this]);
}
}

public function __tostring()
 {
$headers =sprintf('header(\'HTTP/%s %s %s\', true, %s);', $this->protocol, $this->status, $this->phrases[$this->status], $this->status));

foreach ($this->headers as $k => $v) {
$headers .= sprintf('header(\'%s: %s\');', $k, $v);
}

$result = sprintf('<?php %s ?>', $headers);
if ($this->body) {
$result .= $body;
}

return $result;
}

public function redir($url, $status = 301)
 {
$this->status = $status;

//check if relative path
if (!strpos($url, '://')) {
$url = $this->getApp()->site->url . $url;
}

$this->headers['Location'] = $url;
}

    public function getReasonPhrase()
{
return $this->phrases[$this->status];
}

}