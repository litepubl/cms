<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\widget;
use litepubl\posts\Archives as Arch;
use litepubl\view\Theme;
use litepubl\view\Lang;
use litepubl\view\Args;

class Archives extends Widget 
{

    protected function create() {
        parent::create();
        $this->basename = 'widget.archives';
        $this->template = 'archives';
        $this->adminclass = '\litepubl\admin\widget\ShowCount';
        $this->data['showcount'] = false;
    }

    public function getDeftitle() {
        return Lang::get('default', 'archives');
    }

    protected function setShowcount($value) {
        if ($value != $this->showcount) {
            $this->data['showcount'] = $value;
            $this->Save();
        }
    }

    public function getContent($id, $sidebar) {
        $arch = Arch::i();
        if (count($arch->items) == 0) {
 return '';
}


        $result = '';
        $theme = Theme::i();
        $tml = $theme->getwidgetitem('archives', $sidebar);
        if ($this->showcount) $counttml = $theme->getwidgettml($sidebar, 'archives', 'subcount');
        $args = new Args();
        $args->icon = '';
        $args->subcount = '';
        $args->subitems = '';
        $args->rel = 'archives';
        foreach ($arch->items as $date => $item) {
            $args->add($item);
            $args->text = $item['title'];
            if ($this->showcount) $args->subcount = str_replace($counttml, '$itemscount', $item['count']);
            $result.= $theme->parsearg($tml, $args);
        }

        return $theme->getwidgetcontent($result, 'archives', $sidebar);
    }

}