<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\admin\pages;

use litepubl\core\Context;
use litepubl\view\Lang;
use litepubl\view\Schema;
use litepubl\view\Schemes;

class Form extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
    protected $formresult;
    protected $title;
    protected $section;

    public function getTitle()
    {
        return Lang::get($this->section, 'title');
    }

    public function getHead()
    {
    }

    public function getKeywords()
    {
    }

    public function getDescription()
    {
    }

    public function getIdSchema()
    {
        return 1;
    }

    public function setIdSchema($id)
    {
    }

    public function getTheme()
    {
        return Schema::getSchema($this)->theme;
    }

    public function getAdmintheme()
    {
        return Schema::getSchema($this)->admintheme;
    }

    public function request(Context $context)
    {
        $context->response->cache = false;
        Lang::usefile('admin');
        $this->formresult = '';
        if (isset($_POST) && count($_POST)) {
            $this->formresult = $this->processForm();
        }
    }

    public function processForm()
    {
        return '';
    }

    public function getCont()
    {
        Lang::admin($this->section);
        $result = $this->formresult;
        $result.= $this->getcontent();
        $theme = $this->theme;
        return $theme->simple($result);
    }

    public function getForm()
    {
        if ($result = $this->getApp()->cache->getString($this->getbasename())) {
            return $result;
        }

        $result = $this->createForm();
        $this->getApp()->cache->setString($this->getbasename() , $result);
        return $result;
    }

    public function createForm()
    {
        return '';
    }

public function setWidget(string $widget)
{
$login = Login::i();
$login->widget = $widget;
$login->save();

$reg = RegUser::i();
$reg->widget = $widget;
$reg->save();
}

}

