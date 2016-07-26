<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\plugins\shortcode;

use litepubl\core\Event;

class Plugin extends \litepubl\core\Items
{

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'shortcodes';
    }

    public function filter(Event $event)
    {
        foreach ($this->items as $code => $tml) {
            $event->content = str_replace("[$code]", $tml, $event->content);
            if (preg_match_all("/\[$code\=(.*?)\]/", $event->content, $m, PREG_SET_ORDER)) {
                foreach ($m as $item) {
                    $value = str_replace('$value', $item[1], $tml);
                    $event->content = str_replace($item[0], $value, $event->content);
                }
            }
        }
    }
}
