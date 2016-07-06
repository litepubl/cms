<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\admin;

use litepubl\core\Plugins as PluginItems;
use litepubl\utils\Filer;
use litepubl\view\Lang;

class Plugins extends Menu
{
    private $names;

    protected function create()
    {
        parent::create();
        $this->names = Filer::getdir($this->getApp()->paths->plugins);
        sort($this->names);
    }

    public function getPluginsmenu()
    {
        $result = '';
        $link = Link::url($this->url, 'plugin=');
        $plugins = PluginItems::i();
        foreach ($this->names as $name) {
            $about = PluginItems::getabout($name);
            if (isset($plugins->items[$name]) && !empty($about['adminclassname'])) {
                $result.= sprintf('<li><a href="%s%s">%s</a></li>', $link, $name, $about['name']);
            }
        }

        return sprintf('<ul>%s</ul>', $result);
    }

    public function getHead(): string
    {
        $result = parent::gethead();
        if (!empty($_GET['plugin'])) {
            $name = $_GET['plugin'];
            if (in_array($name, $this->names)) {
                if ($admin = $this->getadminplugin($name)) {
                    if (method_exists($admin, 'gethead')) {
                        $result.= $admin->gethead();
                    }
                }
            }
        }
        return $result;
    }

    public function getContent(): string
    {
        $result = $this->getPluginsmenu();
        $admintheme = $this->admintheme;
        $lang = $this->lang;
        $plugins = PluginItems::i();

        if (empty($_GET['plugin'])) {
            $result.= $admintheme->parse($admintheme->templates['help.plugins']);

            $tb = new Table();
            $tb->setStruct(
                array(
                $tb->nameCheck() ,

                array(
                    $lang->name,
                    '$short'
                ) ,

                array(
                    'right',
                    $lang->version,
                    '$version'
                ) ,

                array(
                    $lang->description,
                    '$description'
                ) ,
                )
            );

            $body = '';
            $args = $tb->args;
            foreach ($this->names as $name) {
                if (in_array($name, $plugins->deprecated)) {
                    continue;
                }

                $about = PluginItems::getabout($name);
                $args->add($about);
                $args->name = $name;
                $args->checked = isset($plugins->items[$name]);
                $args->short = $about['name'];
                $body.= $admintheme->parseArg($tb->body, $args);
            }

            $form = new Form();
            $form->title = $lang->formhead;
            $form->body = $admintheme->gettable($tb->head, $body);
            $form->submit = 'update';

            //no need to parse form
            $result.= $form->gettml();
        } else {
            $name = $_GET['plugin'];
            if (!in_array($name, $this->names)) {
                return $this->notfound;
            }

            if ($admin = $this->getadminplugin($name)) {
                $result.= $admin->getcontent();
            }
        }

        return $result;
    }

    public function processForm()
    {
        if (!isset($_GET['plugin'])) {
            $list = array_keys($_POST);
            array_pop($list);
            $plugins = PluginItems::i();
            try {
                $plugins->update($list);
            } catch (\Exception $e) {
                $this->getApp()->logException($e);
            }
            $result = $this->theme->h(Lang::i()->updated);
        } else {
            $name = $_GET['plugin'];
            if (!in_array($name, $this->names)) {
                return $this->notfound;
            }

            if ($admin = $this->getadminplugin($name)) {
                $result = $admin->processForm();
            }
        }

        $this->getApp()->cache->clear();
        return $result;
    }

    private function getAdminplugin(string $name)
    {
        $about = PluginItems::getabout($name);
        if (empty($about['adminclassname'])) {
            return false;
        }

        $class = $about['adminclassname'];
        if (!class_exists($class)) {
            $this->getApp()->classes->include_file($this->getApp()->paths->plugins . $name . DIRECTORY_SEPARATOR . $about['adminfilename']);
        }

        return static ::iGet($class);
    }
}
