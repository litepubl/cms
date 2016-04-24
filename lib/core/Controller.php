<?php

namespace litepubl\core;
use litepubl\pages\Redirector;
use litepubl\pages\Notfound404;
use litepubl\pages\Forbidden;

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
if ($this->cached($context)) {
return;
}

if ($context->itemRoute) {
if (class_exists($context->itemRoute['class'])) {
$context->model = $this->getModel($context->itemRoute['class'], $context->itemRoute['arg']);
$this->render($context);
} else {
$this->getApp()->getLogger()->warning('Class for requested item not found', $context->itemRoute);
$this->notfound($context);
}
} elseif ($context->model) {
$this->render($context);
} elseif ($context->response->body) {
$context->response->send();
} else {
$this->nodfound($context);
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
return md5($context->reqest->url) .'.php';
} else {
        switch ($context->itemRoute['type']) {
            case 'usernormal':
            case 'userget':
                return sprintf('%s-%d.php', md5($context->request->url), $this->getApp()->options->user);

            default:
                return md5($context->request->url) . '.php';
        }
}
}

public function getModel($class, $arg)
{
if (is_a($class, '\litepubl\core\Item')) {
return $class::i($arg);
}else {
return $this->getApp()->classes->getInstance($class);
}
}

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

switch ($response->status) {
case 404:

case 403:

default:
}
}