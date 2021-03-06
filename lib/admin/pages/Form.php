<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\admin\pages;

use litepubl\core\Context;
use litepubl\view\Admin;
use litepubl\view\Lang;
use litepubl\view\Schema;
use litepubl\view\Theme;

class Form extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
    protected $formresult;
    protected $title;
    protected $section;

    public function getTitle(): string
    {
        return Lang::get($this->section, 'title');
    }

    public function getHead(): string
    {
        return '';
    }

    public function getKeywords(): string
    {
        return '';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getIdSchema(): int
    {
        return 1;
    }

    public function setIdSchema(int $id)
    {
    }

    public function getTheme(): Theme
    {
        return Schema::getSchema($this)->theme;
    }

    public function getAdmintheme(): Admin
    {
        return Schema::getSchema($this)->admintheme;
    }

    public function request(Context $context)
    {
        $context->response->cache = false;
        Lang::useFile('admin');
        $this->formresult = '';
        if (isset($_POST) && count($_POST)) {
            $this->formresult = $this->processForm();
        }
    }

    public function processForm()
    {
        return '';
    }

    public function getCont(): string
    {
        Lang::admin($this->section);
        $result = $this->formresult;
        $result.= $this->getcontent();
        $theme = $this->theme;
        return $theme->simple($result);
    }

    public function getForm(): string
    {
        if ($result = $this->getApp()->cache->getString($this->getBaseName())) {
            return $result;
        }

        $result = $this->createForm();
        $this->getApp()->cache->setString($this->getbasename(), $result);
        return $result;
    }

    public function createForm(): string
    {
        return '';
    }
}
