<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

class Response
{
use AppTrait;

public $body;
public $cache;
public $headers;
public $protocol;
public $status;
    protected $phrases = [
        200 => 'OK',
        206 => 'Partial Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
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

public function __construct()
{
$this->body = '';
$this->cache = true;
$this->protocol = '1.1';
$this->status = 200;
$this->headers = [
'Content-type' => 'text/html;charset=utf-8',
'Last-Modified' => date('r'),
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
if (!isset($this->phrases[$this->status])) {
$this->getApp()->getLogger()->warning(sprintf('Phrase for status %s not exists', $this->status));
}

header(sprintf('HTTP/%s %s %s', $this->protocol, $this->status, $this->phrases[$this->status]), true, $this->status);

$this->setCacheHeaders($this->cache);
if (isset($this->headers['Date'])) {
unset($this->headers['Last-Modified']);
}

foreach ($this->headers as $k => $v) {
header(sprintf('%s: %s', $k, $v));
}

if (is_string($this->body)) {
eval('?>' . $this->body);
/*
return;
$f = $this->getApp()->paths->cache . 'temp.php';
file_put_contents($f, $this->body);
require ($f);
*/
} elseif (is_callable($this->body)) {
call_user_func_array($this->body, [$this]);
}
}

public function getString()
{
return $this->__tostring();
}

public function __tostring()
 {
$headers =sprintf('header(\'HTTP/%s %d %s\', true, %d);',
 $this->protocol, $this->status, $this->phrases[$this->status], $this->status);

foreach ($this->headers as $k => $v) {
$headers .= sprintf('header(\'%s: %s\');', $k, $v);
}

$result = sprintf('<?php %s ?>', $headers);
if ($this->body) {
$result .= $this->body;
}

return $result;
}

public function setXml()
{
        $this->headers['Content-Type'] = 'text/xml; charset=utf-8';
        $this->body .= '<?php echo \'<?xml version="1.0" encoding="utf-8" ?>\'; ?>';
}

public function setJson($js = '')
{
        $this->headers['Content-Type'] = 'application/json';
if ($js) {
$this->cache = false;
$this->body = $js;
    $this->headers['Connection'] = 'close';
    $this->headers['Content-Length'] = strlen($js);
    $this->headers['Date'] = date('r');
}
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

public function isRedir()
{
return in_array($this->status, [301, 302, 303, 307]);
}

public function forbidden()
{
$this->status = 403;
$this->cache = false;
}

    public function closeConnection()
 {
        $len = ob_get_length();
        header('Connection: close');
        header('Content-Length: ' . $len);
        header('Content-Encoding: none');
    }

    public function getReasonPhrase()
{
return $this->phrases[$this->status];
}

}