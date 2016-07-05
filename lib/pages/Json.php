<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\pages;

use litepubl\Config;
use litepubl\core\Arr;
use litepubl\core\Context;
use litepubl\core\Request;
use litepubl\core\Response;
use litepubl\core\Str;

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

        $this->beforerequest($context);
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
            $params = isset($args['params']) ? $args['params'] : array();
        } else {
            $params = $args;
        }

        if (isset($params['litepubl_user'])) {
            $_COOKIE['litepubl_user'] = $params['litepubl_user'];
        }

        if (isset($params['litepubl_user_id'])) {
            $_COOKIE['litepubl_user_id'] = $params['litepubl_user_id'];
        }

        $a = [&$params];
        $this->callevent('beforecall', $a);

        try {
            $result = $this->callevent($args['method'], $a);
        } catch (\Exception $e) {
            if (Config::$debug) {
                $this->getApp()->logException($e);
            }

            return $this->jsonError($response, $id, $e->getCode(), $e->getMessage());
        }

        $this->callevent(
            'aftercall', array(&$result,
            $args
            )
        );

        $resp = array(
            'jsonrpc' => '2.0'
        );

        if (is_array($result) && isset($result['error'])) {
            $resp['error'] = $result['error'];
        } else {
            $resp['result'] = $result;
            if (isset($params['slave']) && is_array($params['slave'])) {
                try {
                    $slave_result = $this->callevent(
                        $params['slave']['method'], array(
                        $params['slave']['params']
                        )
                    );
                } catch (\Exception $e) {
                    $slave_result = array(
                        'error' => array(
                            'message' => $e->getMessage() ,
                            'code' => $e->getCode()
                        )
                    );
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
        $result = array(
            'jsonrpc' => '2.0',
            'error' => array(
                'code' => $code,
                'message' => $message,
            )
        );

        if ($id) {
            $result['id'] = $id;
        }

        $response->setJson(Str::toJson($result));
    }

    public function addevent($name, $class, $func, $once = false)
    {
        if (!in_array($name, $this->eventnames)) {
            $this->eventnames[] = $name;
        }

        return parent::addevent($name, $class, $func, $once);
    }

    public function delete_event($name)
    {
        if (isset($this->events[$name])) {
            unset($this->events[$name]);
            Arr::deleteValue($this->eventnames, $name);
            $this->save();
        }
    }
}
