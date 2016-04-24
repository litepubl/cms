<?php

namespace litepubl\core;

class Controller
{
use AppTrait;

public $cacheEnabled;
public $obEnabled;

public function __construct()
{
$options = $this->getApp()->options;
$this->cacheEnabled = $options->cache && ! $options->admincookie;
        $this->obEnabled = !Config::$debug &&  $options->ob_cache;
}

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

}