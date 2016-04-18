<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\widget;
use litepubl\view\Theme;

class CommonTags extends Widget
 {

    protected function create() {
        parent::create();
        $this->adminclass = '\litepubl\admin\widget\Tags';
        $this->data['sortname'] = 'count';
        $this->data['showcount'] = true;
        $this->data['showsubitems'] = true;
        $this->data['maxcount'] = 0;
    }

    public function getowner() {
        return false;
    }

    public function getcontent($id, $sidebar) {
        $theme = Theme::i();
        $items = $this->owner->getsortedcontent(array(
            'item' => $theme->getwidgetitem($this->template, $sidebar) ,
            'subcount' => $theme->getwidgettml($sidebar, $this->template, 'subcount') ,
            'subitems' => $this->showsubitems ? $theme->getwidgettml($sidebar, $this->template, 'subitems') : ''
        ) , 0, $this->sortname, $this->maxcount, $this->showcount);

        return str_replace('$parent', 0, $theme->getwidgetcontent($items, $this->template, $sidebar));
    }

}