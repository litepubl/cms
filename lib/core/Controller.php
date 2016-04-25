<?php

namespace litepubl\core;
use litepubl\view\MainView;
use litepubl\pages\Redirector;
use litepubl\pages\Notfound404;
use litepubl\pages\Forbidden;

class Controller
{
use AppTrait;

public $cache;

public function __construct()
{
$options = $this->getApp()->options;
$this->cache = $options->cache && ! $options->admincookie;
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

public function render(Context $context)
{
if (!($context->model instanceof ResponsiveInterface)) {
trow new \RuntimeException('Model not implemented ResponsiveInterface');
}

$context->model->request($context);
$response = $context->response;
if (!$response->body && $response->status == 200) {
MainView::i()->render($context);
}

$response->send();
if ($this->cache && $response->cache) {
$this->getApp()->cache->savePhp($this->getCacheFileName($context), $response->getString());
}
}

public function cached(Context $context)
{
if (!$this->cache) {
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

$cache = $this->getApp()->cache;
switch ($response->status) {
case 404:
case 403:
if ($this->cache) {
//double cache
$filename = $response->status . '.php';
if ($content = $cache->getString($filename)) {
eval('?>' . $content);
if ($response->cache) {
$cache->savePhp($this->getCacheFileName($context), $content);
}

return;

}
}

$instance  = $response->code == 404 ? Notfound::i() : Forbidden::i();
$newContext = new Context($context->request, $context->response);
$newContext->model = $instance;
$instance->request($newContext);
MainView::i()->render($newContext);
$newContext->response->send();

if ($this->cache) {
$content = $newContext->response->getString();
$cache->savePhp($filename, $content);
if ($response->cache) {
$cache->savePhp($this->getCacheFileName($context), $content);
}
}
break;

default:
$response->send();
if ($this->cache && $response->cache) {
$cache->savePhp($this->getCacheFileName($context), $response->getString());
}
}
}

}