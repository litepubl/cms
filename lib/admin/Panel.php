<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\admin;

class Panel implements AdminInterface
{
            use PanelTrait;
    use Params;
    use \litepubl\core\AppTrait;

    public function __construct()
    {
        $this->createInstances($this->getSchema());
    }

    public function getContent(): string
    {
    }

    public function processForm()
    {
    }
}
