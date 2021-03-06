<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\widget;

use litepubl\core\Context;
use litepubl\core\Event;
use litepubl\core\Response;
use litepubl\core\litepubl;
use litepubl\view\Schema;
use litepubl\view\Theme;

class Ajax implements \litepubl\core\ResponsiveInterface
{
    public $url = '/getwidget.htm';

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;
        $id = (int)$context->request->getArg('id');
        $sidebar = (int)$context->request->getArg('sidebar');
        $idurl = (int)$context->request->getArg('idurl');

        $widgets = Widgets::i();
        if (!$id || !$widgets->itemExists($id)) {
            return $this->errorRequest('Invalid params');
        }

        $themename = $context->request->getArg('themename', Schema::i(1)->themename);
        if (!preg_match('/^\w[\w\.\-_]*+$/', $themename) || !Theme::exists($themename)) {
            $themename = Schema::i(1)->themename;
        }

        try {
            Theme::getTheme($themename);
            $widgets->onFindContext = function (Event $event) use ($idurl) {
                $class =$Event->classname;
                if (($item = litepubl::$app->router->getItem($idurl)) && is_a($class, $item['class'], true)) {
                    if (is_a($item['class'], 'litepubl\core\Item', true)) {
                        $event->result = ($item['class'])::i($item['arg']);
                    } else {
                        $event->result = litepubl::$app->classes->getInstance($item['class']);
                    }

                    $event->stopPropagation(true);
                }
            };

            $response->body = $widgets->getWidgetContent($id, $sidebar);
        } catch (\Exception $e) {
            return $this->errorRequest('Cant get widget content');
        }
    }

    private function errorRequest(Response $response, $mesg)
    {
        $response->status = 400;
        $response->body = $mesg;
    }
}
