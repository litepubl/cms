<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\core;

class Response
{
    use AppTrait;
use Callbacks;

    public $body;
    public $cacheFile;
    public $cacheHeader;
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
        $this->cacheFile = true;
        $this->cacheHeader = true;
        $this->protocol = '1.1';
        $this->status = 200;
        $this->headers = [
        'Content-type' => 'text/html;charset=utf-8',
        'Last-Modified' => date('r') ,
        //'X-Pingback' => $this->getApp()->site->url . '/rpc.xml',
        ];
    }

    public function __get(string $name)
    {
        if (method_exists($this, $get = 'get' . $name)) {
            return $this->$get();
        } else {
            throw new PropException(get_class($this), $name);
        }
    }

    public function __set($name, $value)
    {
        if (method_exists($this, $set = 'set' . $name)) {
            $this->$set($value);
        } else {
            throw new PropException(get_class($this), $name);
        }
    }

    public function getCache(): bool
    {
        return $this->cacheFile;
    }

    public function setCache(bool $cache)
    {
        $this->cacheFile = $cache;
        $this->cacheHeader = $cache;
    }

    public function setCacheHeaders(bool $cache)
    {
        if ($cache) {
            unset($this->headers['Cache-Control']);
            unset($this->headers['Pragma']);
        } else {
            $this->headers['Cache-Control'] = 'no-cache, must-revalidate';
            $this->headers['Pragma'] = 'no-cache';
        }
    }

    public function send()
    {
        if (!isset($this->phrases[$this->status])) {
            $this->getApp()->getLogger()->warning(sprintf('Phrase for status %s not exists', $this->status));
        }

        header(sprintf('HTTP/%s %s %s', $this->protocol, $this->status, $this->phrases[$this->status]), true, $this->status);

        $this->setCacheHeaders($this->cacheHeader);
        if (isset($this->headers['Date'])) {
            unset($this->headers['Last-Modified']);
        }

            $this->triggerCallback('onheaders');
        foreach ($this->headers as $k => $v) {
            header(sprintf('%s: %s', $k, $v));
        }

        if (is_string($this->body)) {
            eval('?>' . $this->body);
        } elseif (is_callable($this->body)) {
            call_user_func_array($this->body, [$this]);
            //free resource in callable
            $this->body = null;
        }
    }

    public function getString(): string
    {
        return $this->__tostring();
    }

    public function __tostring()
    {
        $phrase =  $this->phrases[$this->status];
        $result = "<?php\nheader('HTTP/$this->protocol $this->status $phrase', true, $this->status);\n";

        foreach ($this->headers as $k => $v) {
            $result .= "header('$k: $v');\n";
        }

        $result .= '?>';
        if ($this->body) {
            $result.= $this->body;
        }

        return $result;
    }

    public function setXml()
    {
        $this->headers['Content-Type'] = 'text/xml; charset=utf-8';
        $this->body.= '<?php echo \'<?xml version="1.0" encoding="utf-8" ?>\'; ?>';
    }

    public function setJson(string $js = '')
    {
        $this->headers['Content-Type'] = 'application/json;charset=utf-8';
        if ($js) {
            $this->cache = false;
            $this->body = $js;
            $this->headers['Connection'] = 'close';
            $this->headers['Content-Length'] = strlen($js);
            $this->headers['Date'] = date('r');
        }
    }

    public function redir(string $url, int $status = 301)
    {
        $this->status = $status;

        //check if relative path
        if (!strpos($url, '://')) {
            $url = $this->getApp()->site->url . $url;
        }

        $this->headers['Location'] = $url;
    }

    public function isRedir(): bool
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

    public function onHeaders(callable $callback)
    {
        $this->addCallback('onheaders', $callback);
    }
}
