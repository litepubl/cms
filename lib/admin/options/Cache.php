<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\admin\options;

use litepubl\view\Base;
use litepubl\view\Lang;

class Cache extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $options = $this->getApp()->options;
        $lang = Lang::admin('options');
        $admin = $this->admintheme;
        $args = $this->newArgs();

        $args->enabledcache = $options->cache;
        $args->expiredcache = $options->expiredcache;
        $args->admincache = $options->admincache;
        $args->ob_cache = $options->ob_cache;
        $args->compress = $options->compress;
        $args->commentspool = $options->commentspool;

        $args->formtitle = $lang->optionscache;
        $result = $admin->form(
            '
      [checkbox=enabledcache]
      [text=expiredcache]
      [checkbox=ob_cache]
      [checkbox=admincache]
      [checkbox=commentspool]
      ', $args
        );

        $form = $this->newForm($args);
        $form->submit = 'clearcache';
        $result.= $form->get();
        return $result;
    }

    public function processForm()
    {
        extract($_POST, EXTR_SKIP);
        $options = $this->getApp()->options;
        if (isset($clearcache)) {
            Base::clearCache();
        } else {
            $options->lock();
            $options->cache = isset($enabledcache);
            $options->admincache = isset($admincache);
            if (!empty($expiredcache)) {
                $options->expiredcache = (int)$expiredcache;
                $options->filetime_offset = Filer::getFiletimeOffset();
            }

            $options->ob_cache = isset($ob_cache);
            $options->commentspool = isset($commentspool);
            $options->unlock();
        }
    }
}
