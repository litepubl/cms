<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\plugins\faqmenu;
use litepubl\core\Str;

class Menu extends \litepubl\pages\Menu
 {

    public function setContent($s) {
        $this->rawcontent = $s;
$filter = new Filter();
        $this->data['content'] = $filter->convert($s);
    }

}
