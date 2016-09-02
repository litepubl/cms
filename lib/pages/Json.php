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
 * @method         array beforeRequest(array $params)
 * @method         array beforeCall(array $params)
 * @method         array afterCall(array $params)
 */

class Json extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
{

    protected function create()
    {
        parent::create();
        $this->basename = 'jsonserver';
        $this->addevents('beforerequest', 'beforecall', 'aftercall');
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

        $this->beforeRequest(['context' => $context]);
        $args = $this->getArgs($context->request);
        if (!$args || !isset($args['method'])) {
            return $this->jsonError($response, false, 403, 'Method not found in arguments');
        }

        $rpc = isset($args['jsonrpc']) ? ($args['jsonrpc'] == '2.0') : false;
        $id = $rpc && isset($args['id']) ? $args['id'] : false;

        if (!isset($this->events[$args['method']])) {
            return $this->jsonError($response, $id, 404, sprintf('Method "%s" not found', $args['method']));
        }

        if ($rpc) {
            $params = isset($args['params']) ? $args['params'] : [];
        } else {
            $params = $args;
        }

        if (isset($params['litepubl_user'])) {
            $_COOKIE['litepubl_user'] = $params['litepubl_user'];
        }

        if (isset($params['litepubl_user_id'])) {
            $_COOKIE['litepubl_user_id'] = $params['litepubl_user_id'];
        }

        $a = $this->beforeCall(['params' => $params]);
        $params = $a['params'];
        try {
            $result = $this->callMethod($args['method'], $params);
        } catch (\Exception $e) {
            if (Config::$debug) {
                $this->getApp()->logException($e);
            }

            return $this->jsonError($response, $id, $e->getCode(), $e->getMessage());
        }

        $r = $this->afterCall(['result' => $result, 'args' => $args]);
        $result = $r['result'];

        $resp = [
            'jsonrpc' => '2.0'
        ];

        if (is_array($result) && isset($result['error'])) {
            $resp['error'] = $result['error'];
        } else {
            $resp['result'] = $result;
            if (isset($params['slave']) && is_array($params['slave'])) {
                try {
                    $slave_result = $this->callMethod(
                        $params['slave']['method'], 
                        $params['slave']['params']
                    );
                } catch (\Exception $e) {
                    $slave_result = [
                        'error' => [
                            'message' => $e->getMessage() ,
                            'code' => $e->getCode()
                        ]
                    ];
                }

                $resp['result']['slave'] = $slave_result;
            }
        }

        if ($id) {
            $resp['id'] = $id;
        }

        return $response->setJson(Str::toJson($resp));
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

    public function delete_event($name)
    {
        if (isset($this->events[$name])) {
            unset($this->events[$name]);
            Arr::deleteValue($this->eventnames, $name);
            $this->save();
        }
    }

    public function callMethod(string $method, array $params)
    {
        foreach ($this->data['events'][$method] as $item) {
            if (class_exists($item[0])) {
                $callback = [$this->getApp()->classes->getInstance($item[0]), $item[1]];
                return call_user_func_array($callback, [$params]);
            } else {
                $mesg = sprintf('Class "%s" not found for method "%s"', $item[0], $method);
                $this->getApp()->getLogger()->warning($mesg);
                return ['error' => [
                'message' => $mesg,
                'code' => 500
                ]];
            }
        }
    }
}
