<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\perms;

use litepubl\core\Response;

class Groups extends Perm
{

    protected function create()
    {
        parent::create();
        $this->adminclass = '\litepubl\admin\users\PermGroups';
        $this->data['author'] = false;
        $this->data['groups'] = [];
    }

    public function setResponse(Response $response, $obj)
    {
        $g = $this->groups;
        if (!$this->author && !count($g)) {
            return;
        }

        $author = '';
        if ($this->author && isset($obj->author) && ($obj->author > 1)) {
            $author = sprintf('  || (\ $this->getApp()->options->user != %d)', $obj->author);
        }

        $idgroups = implode(',', $g);
        $response->body.= "<?php
 if (!\\litepubl\\core\\litepubl::\$app->options->ingroups([$idgroups]) $author) {
return \\litepubl\\core\\litepubl::\$app->context->response->forbidden();
}
 ?>";
    }

    public function hasPerm($obj): bool
    {
        $g = $this->groups;
        if (!$this->author && !count($g)) {
            return true;
        }

        if ($this->getApp()->options->ingroups($g)) {
            return true;
        }

        return $this->author && isset($obj->author) && ($obj->author > 1) && ($this->getApp()->options->user == $obj->author);
    }
}
