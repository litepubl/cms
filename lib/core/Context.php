<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

class Context
{
public $request;
public $response;
public $model;
public $view;
public $itemRoute;
public $abtest;

public function __construct(Request $request, Response $response)
{
$this->request = $request;
$this->response = $response;
}

public function __get($name)
{
if (strtolower($name) == 'id') {
return (int) $this->itemRoute['arg'];
}

throw new PropException(get_class($this), $name);
}

    public function checkAttack() {
if ($this->request->checkAttack()) {
$errorPages = new ErrorPages();
$this->response->cache = false;
$this->response->body = $errorPages->attack($this->request->url);
return true;
}

return false;
}

}