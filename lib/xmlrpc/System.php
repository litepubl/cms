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

class System extends Common
{

    public function listMethods()
    {
        $caller = Server::i();
        return array_keys($caller->items);
    }

    public function methodSignature($name)
    {
        $caller = Server::i();
        if (!$caller->itemExists($name)) {
            return new IXR_Error(-32601, "server error. requested method $name does not exist.");
        }

        $item = $caller->getitem($name);
        if ($item['class'] != __class__) {
            $obj = static ::iGet($item['class']);
            if (method_exists($obj, 'getsignature')) {
                return $obj->getsignature($name);
            } else {
                return new IXR_Error(-32601, "server error. requested signature of $name method does not exist.");
            }
        }

        switch ($name) {
        case 'system.listMethods':
        case 'mt.listMethods':
            return array(
                    'array'
                );

        case 'system.methodSignature':
            return array(
                    'array',
                    'string'
                );

        case 'system.methodHelp':
            return array(
                    'string',
                    'string'
                );

        case 'system.multicall':
            return array(
                    'array',
                    'array'
                );

        case 'methodExist':
            return array(
                    'boolean',
                    'string'
                );

        case 'demo.addTwoNumbers':
        case 'sample.add':
            return array(
                    'int',
                    'int',
                    'int'
                );

        case 'demo.sayHello':
            return array(
                    'string'
                );

        default:
            return new IXR_Error(-32601, "server error. requested signature of $name method does not exist.");
        }
    }

    public function methodHelp($name)
    {
        return "I know nothing.";
    }

    public function multicall(array $items)
    {
        $result = array();
        $caller = Server::i();
        foreach ($items as $item) {
            $r = $caller->call($item['methodName'], isset($item['params']) ? $item['params'] : null);
            if ($r instanceof IXR_Error) {
                $result[] = array(
                    'faultCode' => $r->code,
                    'faultName' => $r->message
                );
            } else {
                $result[] = $r;
            }
        }
        return $result;
    }

    public function methodExist($name)
    {
        $caller = Server::i();
        return $caller->itemExists($name);
    }

    public function sayHello()
    {
        return 'Hello!';
    }

    public function addTwoNumbers($number1, $number2)
    {
        return $number1 + $number2;
    }
}
