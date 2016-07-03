<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\admin\options;

use litepubl\post\Archives;
use litepubl\utils\Filer;
use litepubl\view\Lang;
use litepubl\view\LangMerger as LngMerger;

class LangMerger extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $merger = LngMerger::i();
        $tabs = $this->newTabs();
        $lang = Lang::admin('options');
        $args = $this->newArgs();
        $theme = $this->theme;

        foreach ($merger->items as $section => $items) {
            $tab = $this->newTabs();
            $tab->add($lang->files, $theme->getinput('editor', $section . '_files', $theme->quote(implode("\n", $items['files'])), $lang->files));
            foreach ($items['texts'] as $key => $text) {
                $tab->add($key, $theme->getinput('editor', $section . '_text_' . $key, $theme->quote($text), $key));
            }

            $tabs->add($section, $tab->get());
        }

        $args->formtitle = $lang->optionslocal;
        $args->dateformat = $this->getApp()->options->dateformat;
        $dirs = Filer::getdir($this->getApp()->paths->languages);
        $args->language = $this->theme->comboItems(array_combine($dirs, $dirs), $this->getApp()->options->language);
        $zones = timezone_identifiers_list();
        $args->timezone = $this->theme->comboItems(array_combine($zones, $zones), $this->getApp()->options->timezone);

        return $this->admintheme->form(
            '[text=dateformat]
    [combo=language]
    [combo=timezone]' . $tabs->get(), $args
        );
    }

    public function processForm()
    {
        $this->getApp()->options->dateformat = $_POST['dateformat'];
        $this->getApp()->options->language = $_POST['language'];
        if ($this->getApp()->options->timezone != $_POST['timezone']) {
            $this->getApp()->options->timezone = $_POST['timezone'];
            $archives = Archives::i();
            $this->getApp()->router->unbind($archives);
            $archives->PostsChanged();
        }

        $merger = LngMerger::i();
        $merger->lock();
        //$merger->items = array();
        //$merger->install();
        foreach (array_keys($merger->items) as $name) {
            $keys = array_keys($merger->items[$name]['texts']);
            $merger->setfiles($name, $_POST[$name . '_files']);
            foreach ($keys as $key) {
                $merger->addtext($name, $key, $_POST[$name . '_text_' . $key]);
            }
        }

        $merger->unlock();
    }
}
