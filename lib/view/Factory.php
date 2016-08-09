<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\view;

trait Factory
{

    public function newArgs(): Args
    {
        return new Args();
    }

    public function newVars(): Vars
    {
        return new Vars();
    }

    public function getLang(string $section = ''): Lang
    {
        return Lang::i($section);
    }

    public function getSchema(): Schema
    {
        return Schema::getSchema($this);
    }

    public function getTheme(): Theme
    {
        return $this->getSchema()->theme;
    }

}
