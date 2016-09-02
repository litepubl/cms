<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\admin\views;

use litepubl\admin\GetSchema;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Schemes as SchemaItems;

class Spec extends \litepubl\admin\Menu
{

    public static function getSpecclasses()
    {
        return [
            'litepubl\pages\Home',
            'litepubl\post\Archives',
            'litepubl\pages\Notfound404',
            'litepubl\pages\Sitemap'
        ];
    }

    public function getContent(): string
    {
        $result = '';
        $schemes = SchemaItems::i();
        $theme = $this->theme;
        $lang = Lang::i('schemes');
        $args = new Args();

        $tabs = $this->newTabs();
        $inputs = '';
        foreach (static ::getspecclasses() as $classname) {
            $obj = static ::iGet($classname);
            $name = $classname = str_replace('\\', '-', $classname);
            $args->classname = $classname;
            $args->title = $lang->{$name};
            $inputs = GetSchema::combo($obj->idschema, "idschema-$classname");
            if (isset($obj->data['keywords'])) {
                $inputs.= $theme->getinput('text', "keywords-$classname", $obj->data['keywords'], $lang->keywords);
            }

            if (isset($obj->data['description'])) {
                $inputs.= $theme->getinput('text', "description-$classname", $obj->data['description'], $lang->description);
            }

            if (isset($obj->data['head'])) {
                $inputs.= $theme->getinput('editor', "head-$classname", $theme->quote($obj->data['head']), $lang->head);
            }

            $tabs->add($lang->{$name}, $inputs);
        }

        $args->formtitle = $lang->defaults;
        $result.= $this->admintheme->form($tabs->get(), $args);
        return $result;
    }

    public function processForm()
    {
        foreach (static ::getspecclasses() as $classname) {
            $obj = static ::iGet($classname);
            $obj->lock();
            $classname = str_replace('\\', '-', $classname);
            $obj->setidschema($_POST["idschema-$classname"]);
            if (isset($obj->data['keywords'])) {
                $obj->keywords = $_POST["keywords-$classname"];
            }
            if (isset($obj->data['description '])) {
                $obj->description = $_POST["description-$classname"];
            }
            if (isset($obj->data['head'])) {
                $obj->head = $_POST["head-$classname"];
            }
            $obj->unlock();
        }
    }
}
