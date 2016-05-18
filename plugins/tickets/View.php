<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\tickets;

use litepubl\plugins\polls\Polls;

class View extends \litepubl\post\View
{
    public $context;

    protected function getContentpage(int $page): string
    {
        $result = parent::getcontentpage($page);
        $result.= Polls::i()->getObjectPoll($this->id, 'post');
        return $result;
    }

}
