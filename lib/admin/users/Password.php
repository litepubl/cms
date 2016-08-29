<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace litepubl\admin\users;

class Password extends Perm
{

    public function getForm()
    {
        $this->args->password = '';
        return '[password=password]';
    }

    public function processForm()
    {
        $this->perm->password = $_POST['password'];
        parent::processForm();
    }
}
