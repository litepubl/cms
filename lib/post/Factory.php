<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\post;

use litepubl\comments\Comments;
use litepubl\comments\Pingbacks;
use litepubl\core\Users;
use litepubl\pages\Users as UserPages;
use litepubl\tag\Cats;
use litepubl\tag\Tags;

class Factory
{
    use \litepubl\core\Singleton;

    public function __get($name)
    {
        return $this->{'get' . $name}();
    }

    public function getPosts()
    {
        return Posts::i();
    }

    public function getFiles()
    {
        return Files::i();
    }

    public function getTags()
    {
        return Tags::i();
    }

    public function getCats()
    {
        return Cats::i();
    }

    public function getCategories()
    {
        return $this->getcats();
    }

    public function getTemplatecomments()
    {
        return Templates::i();
    }

    public function getComments($id)
    {
        return Comments::i($id);
    }

    public function getPingbacks($id)
    {
        return Pingbacks::i($id);
    }

    public function getMeta($id)
    {
        return Meta::i($id);
    }

    public function getUsers()
    {
        return Users::i();
    }

    public function getUserpages()
    {
        return UserPages::i();
    }

    public function getView()
    {
        return View::i();
    }

/*
* sample code 
*        $posts = $this->posts;
*        foreach ($posts->itemcoclasses as $class) {
*            $post->coinstances[] = new $class($post);
*        }
*/

    public function createCoInstances(Post $post)
    {
    }
}
