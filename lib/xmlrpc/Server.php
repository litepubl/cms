<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\xmlrpc;

use litepubl\core\Context;

class Server extends \litepubl\core\Items implements \litepubl\core\ResponsiveInterface
{
    public $parser;

    protected function create()
    {
        parent::create();
        $this->basename = 'xmlrpc';
        $this->dbversion = false;
        $this->addevents('beforecall', 'aftercall', 'getmethods');
    }

    public function request(Context $context)
    {
        $this->getmethods();
        include_once __DIR__ . '/IXR.php';
        $this->parser = new Parser();
        $this->parser->owner = $this;
        $this->parser->IXR_Server($this->items);

        $response = $context->response;
        $response->cache = false;
        $response->setXml();
        $response->body.= $this->parser->XMLResult;

        $this->aftercall();
    }

    public function call($method, $args)
    {
        $this->callevent(
            'beforecall', array(
            $method, &$args
            )
        );
        if (!isset($this->items[$method])) {
            return new IXR_Error(-32601, "server error. requested method $method does not exist.");
        }

        $class = $this->items[$method]['class'];
        $func = $this->items[$method]['func'];
        if (!class_exists($class)) {
            $this->delete($method);
            return new IXR_Error(-32601, "server error. requested class \"$class\" does not exist.");
        }

        $obj = static ::iGet($class);
        try {
            return call_user_func_array(
                array(
                $obj,
                $func
                ), $args
            );
        } catch (\Exception $e) {
            return new IXR_Error($e->getCode(), $e->getMessage());
        }
    }

    public function add($method, $Function, $ClassName)
    {
        $this->items[$method] = array(
            'class' => $ClassName,
            'func' => $Function
        );
        $this->save();
    }

    public function deleteclass($class)
    {
        foreach ($this->items as $method => $Item) {
            if ($class == $Item['class']) {
                unset($this->items[$method]);
            }
        }
        $this->save();
    }
}
