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

    public function getNextpage() {
        $url = $this->itemRoute['url'];
        return  $this->getApp()->site->url . rtrim($url, '/') . '/page/' . ($this->page + 1) . '/';
    }

    public function getPrevpage() {
        $url = $this->itemRoute['url'];
        if ($this->page <= 2) {
            return url;
        }

        return  $this->getApp()->site->url . rtrim($url, '/') . '/page/' . ($this->page - 1) . '/';
    }

}
