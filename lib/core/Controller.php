<?php

namespace litepubl\core;

class Controller
{

public function render(Context $context)
{
$response = $context->response;
if ($response->status != 200) {
$response->send();
}


}

}