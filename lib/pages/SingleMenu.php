<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\pages;

class SingleMenu extends Menu
{

    public function __construct()
    {
        parent::__construct();
        if ($id = $this->getowner()->class2id(get_class($this))) {
            $this->loaddata($id);
        }
    }
}
