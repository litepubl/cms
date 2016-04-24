<?php

namespace litepubl\core;
use litepubl\pages\Redirector;

class Controller
{
use AppTrait;

public $cacheEnabled;

public function __construct()
{
$options = $this->getApp()->options;
$this->cacheEnabled = $options->cache && ! $options->admincookie;
}

public function request(Context $context)
{
if ($context->itemRoute) {
if ($this->cached($context)) {
return;
}

if (class_exists($context->itemRoute['class'])) {
$context->model = $this->getModel($context->itemRoute['class'], $context->itemRoute['arg']);
$this->render($context);
} else {
$this->notfound($context);
}
} elseif ($context->model) {
$this->render($context);
} elseif ($context->response->body) {
$context->response->send();
} else {
} elseif($context->response->status == 200) {
$context->response->status = 404;
}

$this->renderStatus($context);
}
}

public function cached(Context $context)
{
if (!$this->cacheEnabled) {
return false;
}

$filename = $this->getCacheFileName($context);
return $this->getApp()->cache->includePhp($filename);
}

public function getCacheFileName(Context $context)
{
if (!$context->itemRoute) {
return md5($context->reqest->url);
} else {
}
}

public function getModel($class, $arg)
{
if (is_a($class, '\litepubl\core\Item')) {
return $class::i($arg);
}else {
return $this->getApp()->classes->getInstance($class);
}
} else {

public function notfound(Context $context)
{
$response = $context->response;
if (!$response->isRedir()) {
        $redir = Redirector::i();
        if ($url = $redir->get($context->request->url)) {
$response->redir($url);
}
}

if ($response->status == 200) {
$response->status = 404;
}



}