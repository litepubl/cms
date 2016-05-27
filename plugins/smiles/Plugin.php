<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\smiles;

class Plugin extends \litepubl\core\Plugin
{

    public function filter(&$content)
    {
        $content = strtr($content, array(
            ':)' => $this->smile,
            ';)' => $this->smile,
':(' => $this->sad,
';(' => $this->sad,
));
    }

}
