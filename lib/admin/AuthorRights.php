<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\admin;

/**
 *  Events for author rights on post, files
 *
 * @property-write callable $changePosts
 * @property-write callable $canUpload
 * @property-write callable $canDeleteFile
 * @method array changePosts(array $params)
 * @method array canUpload(array $params)
 * @method array canDeleteFile(array $params)
 */

class AuthorRights extends \litepubl\core\Events
{

    protected function create()
    {
        parent::create();
        $this->addEvents('changeposts', 'canupload', 'candeletefile');
        $this->basename = 'authorrights';
    }
}
