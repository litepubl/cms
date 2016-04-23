<?php

namespace litepubl\core;

class Controller
{

public function request(Context $context)
{
$response = $context->response;
if ($response->status != 200) {
$response->send();
        } elseif ($context->itemRoute) {
            return $this->render($context);
        } else {
$response->status = 404;
}


}

}