<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\widget;

class CommonTags extends Widget
{

    protected function create()
    {
        parent::create();
        $this->adminclass = '\litepubl\admin\widget\Tags';
        $this->data['sortname'] = 'count';
        $this->data['showcount'] = true;
        $this->data['showsubitems'] = true;
        $this->data['maxcount'] = 0;
    }

    public function getOwner()
    {
        return false;
    }

    public function getContent($id, $sidebar)
    {
        $view = new View();
        $items = $this->owner->getView()->getSorted(array(
            'item' => $view->getItem($this->template, $sidebar) ,
            'subcount' => $view->getTml($sidebar, $this->template, 'subcount') ,
            'subitems' => $this->showsubitems ? $view->getTml($sidebar, $this->template, 'subitems') : ''
        ) , 0, $this->sortname, $this->maxcount, $this->showcount);

        return str_replace('$parent', 0, $view->getContent($items, $this->template, $sidebar));
    }

}

