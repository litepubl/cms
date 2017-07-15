<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\core;

use litepubl\pages\Redirector;
use litepubl\view\MainView;

class Controller
{
    use AppTrait;

    public $cache;

    public function __construct()
    {
        $options = $this->getApp()->options;
        $this->cache = isset($options->cache) && $options->cache && !$options->adminFlag;
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
                $this->renderStatus($context);
            }
        } elseif ($context->model) {
            $this->render($context);
        } elseif ($context->response->body) {
            $context->response->send();
        } else {
            $this->renderStatus($context);
        }
    }

    public function render(Context $context)
    {
        if (!$context->view && !($context->view = $this->findView($context))) {
            throw new \RuntimeException('View not found form model');
        }

        $context->view->request($context);
        $response = $context->response;
        if (!$response->body && $response->status == 200) {
            MainView::i()->render($context);
        }

        $response->send();
        if ($this->cache && $response->cache) {
            $this->getApp()->cache->savePhp($this->getCacheFileName($context), $response->getString());
        }
    }

    public function findView(Context $context)
    {
        $model = $context->model;
        if ($model instanceof ResponsiveInterface) {
            return $model;
        } elseif (isset($model->view) && ($view = $model->view) && ($view instanceof ResponsiveInterface)) {
            return $view;
        }

        return false;
    }

    public function cached(Context $context)
    {
        if (!$this->cache) {
            return false;
        }

        $filename = $this->getCacheFileName($context);
        $cache = $this->getApp()->cache;
        if ($context->request->isPostMethod()) {
                $cache->delete($filename);
                return false;
        } else {
                return $cache->includePhp($filename);
        }
    }

    public function getCacheFileName(Context $context)
    {
        $ext = $context->abtest ? sprintf('.%s.php', $context->abtest) : '.php';

        if (!$context->itemRoute) {
            return md5($context->request->url) . $ext;
        } else {
            switch ($context->itemRoute['type']) {
                case 'usernormal':
                case 'userget':
                    return sprintf('%s-%d%s', md5($context->request->url), $this->getApp()->options->user, $ext);

                default:
                    return md5($context->request->url) . $ext;
            }
        }
    }

    public function url2cacheFile(string $url): string
    {
        return md5($url) . '.php';
    }

    public function getModel($class, $arg)
    {
        if (is_a($class, __NAMESPACE__ . '\Item', true)) {
            return $class::i($arg);
        } else {
            return $this->getApp()->classes->getInstance($class);
        }
    }

    public function renderStatus(Context $context)
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
                $errorPages = new ErrorPages();
                $content = $errorPages->notfound();
                if ($this->cache && $response->cache) {
                    $cache->savePhp($this->getCacheFileName($context), $content);
                }
                break;


            case 403:
                $errorPages = new ErrorPages();
                $content = $errorPages->forbidden();
                if ($this->cache && $response->cache) {
                    $cache->savePhp($this->getCacheFileName($context), $content);
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
