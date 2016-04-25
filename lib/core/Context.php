<?php

namespace litepubl\core;

class Context
{
public $request;
public $response;
public $model;
public $view;
public $itemRoute;

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
