<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\pages;

use litepubl\Config;
use litepubl\core\Arr;
use litepubl\core\Context;
use litepubl\core\Request;
use litepubl\core\Response;
use litepubl\core\Str;

/**
 * JSON-RPC server
 *
 * @property-write callable $beforeRequest
 * @property-write callable $beforeCall
 * @property-write callable $afterCall
 * @property-write callable $onGetMethod
 * @method         array beforeRequest(array $params)
 * @method         array beforeCall(array $params)
 * @method         array afterCall(array $params)
 * @method         array onGetMethod(array $params)
 */

class Json extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
{

    protected function create()
    {
        parent::create();
        $this->basename = 'jsonserver';
        $this->addevents('beforeRequest', 'beforeCall', 'afterCall', 'onGetMethod');
        $this->data['eventnames'] = & $this->eventnames;
        $this->map['eventnames'] = 'eventnames';
        $this->data['url'] = '/admin/jsonserver.php';
    }

    public function getArgs(Request $request)
    {
        $get = $request->getGet();
        if (isset($get['method'])) {
            return $get;
        }

        $post = $request->getPost();
        if (isset($post['method'])) {
            return $post;
        }

        if (isset($post['json']) && ($s = trim($post['json'])) && ($args = json_decode($s, true)) && isset($args['method'])) {
            return $args;
        }

        $args = false;
        if ($s = trim($request->getInput())) {
            $args = json_decode($s, true);
        }

        if ($args && isset($args['method'])) {
            return $args;
        }

        return false;
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;
try {
        $this->beforeRequest(['context' => $context]);
$id = false;
        $args = $this->getArgs($context->request);
        if (!$args || !isset($args['method'])) {
throw new \UnexpectedValueException('Method not found in arguments', 403);
        }

$callback = $this->getMethod($args['method']);
        $rpc = isset($args['jsonrpc']) ? ($args['jsonrpc'] == '2.0') : false;
        if ($rpc) {
        $id = $args['id'] ?? false;
            $params = $args['params'] ?? [];
        } else {
            $params = $args;
        }

$this->setCookie($params);
        $a = $this->beforeCall(['params' => $params]);
        $params = $a['params'];
            $result = call_user_func_array($callback, [$params]);

            if (isset($params['slave']) && is_array($params['slave'])) {
                try {
$slaveCallback = $this->getMethod(                        $params['slave']['method']);
                    $slaveResult = call_user_func_array($slaveCallback,                         [$params['slave']['params']]);
                } catch (\Exception $e) {
                    $slaveResult  = [
                        'error' => [
                            'message' => $e->getMessage() ,
                            'code' => $e->getCode()
                        ]
                    ];
                }
            }

} catch (\Throwable $e) {
                    $result = [
                        'error' => [
                            'message' => $e->getMessage() ,
                            'code' => $e->getCode(),
                        ]
                    ];

            if (Config::$debug) {
                $this->getApp()->logException($e);
            }
}

        $jsonResult = ['jsonrpc' => '2.0'];
        if ($id) {
            $jsonResult['id'] = $id;
        }

        if (is_array($result) && isset($result['error'])) {
            $jsonResult['error'] = $result['error'];
        } else {
            $jsonResult['result'] = $result;
            if (isset($params['slave']) && is_array($params['slave'])) {
                $jsonResult['result']['slave'] = $slaveResult;
}
}

        $r = $this->afterCall(['result' => $jsonResult, 'args' => $args]);
$response->setJson(Str::toJson($r['result']));
    }

    public function jsonError(Response $response, $id, $code, $message)
    {
        $result = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $code,
                'message' => $message,
            ]
        ];

        if ($id) {
            $result['id'] = $id;
        }

        $response->setJson(Str::toJson($result));
    }

    public function addEvent(string $name, $callable, $method = null)
    {
        $name = strtolower($name);
        if (!in_array($name, $this->eventnames)) {
            $this->eventnames[] = $name;
        }

        return parent::addEvent($name, $callable, $method);
    }

    public function delete(string $name)
    {
        if (isset($this->events[$name])) {
            unset($this->events[$name]);
            Arr::deleteValue($this->eventnames, $name);
            $this->save();
        }
    }

public function getMethod(string $name): callable
{
        if (isset($this->events[$args['method']])) {
        foreach ($this->data['events'][$name] as $item) {
            if (class_exists($item[0])) {
return [$this->getApp()->classes->getInstance($item[0]), $item[1]];
            } else {
                $mesg = sprintf('Class "%s" not found for method "%s"', $item[0], $name);
                $this->getApp()->getLogger()->warning($mesg);
throw new \UnexpectedValueException($mesg);
            }
}
}

$r = $this->onGetMethod(['name' => $name, 'result' => false]);
if (is_callable($r['result'])) {
return $r['result'];
}

throw new \UnexpectedValueException(sprintf('JSON-RPC %s method not found', $name));
}

public function setCookie(array $params)
{
        if (isset($params['litepubl_user'])) {
            $_COOKIE['litepubl_user'] = $params['litepubl_user'];
        }

        if (isset($params['litepubl_user_id'])) {
            $_COOKIE['litepubl_user_id'] = $params['litepubl_user_id'];
        }
}
}