<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\xmlrpc;

class Parser extends IXR_Server
{
    public $XMLResult;
    public $owner;

    function call($methodname, $args)
    {
        return $this->owner->call($methodname, $args);
    }

    function output($xml)
    {
        $head = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $length = strlen($head) + strlen($xml);
        $this->XMLResult = "<?php
    header('Connection: close');
    header('Content-Length: $length');
    header('Content-Type: text/xml; charset=utf-8');
    header('Date: " . date('r') . "');
    Header( 'Cache-Control: no-cache, must-revalidate');
    Header( 'Pragma: no-cache');
    header('X-Pingback: " . $this->getApp()->site->url . "/rpc.xml');
    echo'$head';
    ?>" . $xml;
    }
}
