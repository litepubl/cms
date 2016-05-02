<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;
use litepubl\pages\Notfound404;
use litepubl\pages\Forbidden;
use litepubl\view\MainView;
use litepubl\view\Lang;

class ErrorPages
{
use AppTrait;

public $cache;

public function __construct()
{
$options = $this->getApp()->options;
$this->cache = $options->cache && ! $options->admincookie;
}

public function notfound()
{
$filename = '404.php';
if ($this->cache && ($result = $this->getApp()->cache->getString($filename))) {
eval('?>' . $result);
return $result;
}

$instance  = Notfound404::i();
$context = new Context(new Request('', ''), new Response());
$context->model = $instance;
$context->view = $instance;
$instance->request($context);
MainView::i()->render($context);
$context->response->send();

if ($this->cache) {
$result = $context->response->getString();
$this->getApp()->cache->savePhp($filename, $result);
return $result;
}
}

public function forbidden()
{
$filename = '403.php';
if ($this->cache && ($result = $this->getApp()->cache->getString($filename))) {
eval('?>' . $result);
return $result;
}

$instance  = Forbidden::i();
$context = new Context(new Reqest(), new Response());
$context->model = $instance;
$instance->request($context);
MainView::i()->render($context);
$context->response->send();

if ($this->cache) {
$result = $context->response->getString();
$this->getApp()->cache->savePhp($filename, $result);
return $result;
}
}

public function attack($url) {
Lang::usefile('admin');
            if ($_POST) {
                return Lang::get('login', 'xxxattack');
            }

                return Lang::get('login', 'confirmxxxattack')
 . sprintf(' <a href="%1$s">%1$s</a>', $url);
        }

}