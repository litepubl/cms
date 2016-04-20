<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\xmlrpc;
use litepubl\Config;

class Server extends \litepubl\core\Items
{
    public $parser;

    protected function create() {
        parent::create();
        $this->basename = 'xmlrpc';
        $this->dbversion = false;
        $this->cache = false;
        $this->addevents('beforecall', 'aftercall', 'getmethods');
    }

    public function request($param) {
        global $HTTP_RAW_POST_DATA;
        if (!isset($HTTP_RAW_POST_DATA)) {
            $HTTP_RAW_POST_DATA = file_get_contents('php://input');
        }
        if (isset($HTTP_RAW_POST_DATA)) {
            $HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);
        }

        if (Config::$debug) {
            \litepubl\utils\Filer::log("request:\n" . $HTTP_RAW_POST_DATA, 'xmlrpc.txt');
            $reqname =  $this->getApp()->paths->data . 'logs' . DIRECTORY_SEPARATOR . 'request.xml';
            file_put_contents($reqname, $HTTP_RAW_POST_DATA);
            @chmod($reqname, 0666);
                    }



        $this->getmethods();
require_once(__DIR__ . '/IXR.php');
        $this->parser = new Parser();
        $this->parser->owner = $this;
        $this->parser->IXR_Server($this->items);
        $Result = $this->parser->XMLResult;

        $this->aftercall();
        if (Config::$debug) tfiler::log("responnse:\n" . $Result, 'xmlrpc.txt');
        return $Result;
    }

    public function call($method, $args) {
        $this->callevent('beforecall', array(
            $method, &$args
        ));
        if (!isset($this->items[$method])) {
            return new IXR_Error(-32601, "server error. requested method $method does not exist.");
        }

        $class = $this->items[$method]['class'];
        $func = $this->items[$method]['func'];
            if (!class_exists($class)) {
                $this->delete($method);
                return new IXR_Error(-32601, "server error. requested class \"$class\" does not exist.");
            }

            $obj = getinstance($class);
            try {
                return call_user_func_array(array(
                    $obj,
                    $func
                ) , $args);
            }
            catch(\Exception $e) {
                // $this->getApp()->options->handexception($e);
                //echo ( $this->getApp()->options->errorlog);
                return new IXR_Error($e->getCode() , $e->getMessage());
            }
        }
    }

    public function add($method, $Function, $ClassName) {
        $this->items[$method] = array(
            'class' => $ClassName,
            'func' => $Function
        );
        $this->save();
    }

    public function deleteclass($class) {
        foreach ($this->items as $method => $Item) {
            if ($class == $Item['class']) {
                unset($this->items[$method]);
            }
        }
        $this->save();
    }

}