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

}
