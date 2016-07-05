<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 */

namespace litepubl\widget;

use litepubl\post\Archives as Arch;
use litepubl\view\Args;
use litepubl\view\Lang;

class Archives extends Widget
{

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.archives';
        $this->template = 'archives';
        $this->adminclass = '\litepubl\admin\widget\ShowCount';
        $this->data['showcount'] = false;
    }

    public function getDeftitle(): string
    {
        return Lang::get('default', 'archives');
    }

    protected function setShowcount(bool $value)
    {
        if ($value != $this->showcount) {
            $this->data['showcount'] = $value;
            $this->Save();
        }
    }

    public function getContent(int $id, int $sidebar): string
    {
        $arch = Arch::i();
        if (!count($arch->items)) {
            return '';
        }

        $result = '';
        $view = $this->getView();
        $tml = $view->getItem('archives', $sidebar);
        if ($this->showcount) {
            $counttml = $view->getTml($sidebar, 'archives', 'subcount');
        }

        $args = new Args();
        $args->icon = '';
        $args->subcount = '';
        $args->subitems = '';
        $args->rel = 'archives';
        foreach ($arch->items as $date => $item) {
            $args->add($item);
            $args->text = $item['title'];
            if ($this->showcount) {
                $args->subcount = str_replace('$itemscount', $item['count'], $counttml);
            }

            $result.= $view->theme->parseArg($tml, $args);
        }

        return $view->getContent($result, 'archives', $sidebar);
    }
}
