<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\post;
use litepubl\view\Theme;
use litepubl\view\MainView;

class Factory
{
use \litepubl\core\Singleton;

public function __get($name) {
return $this->{'get' . $name}();
}

    public function getPosts() {
        return Posts::i();
    }

    public function getFiles() {
        return Files::i();
    }

    public function getTags() {
        return \litepubl\tag\Tags::i();
    }

    public function getCats() {
        return \litepubl\tag\Cats::i();
    }

    public function getCategories() {
        return $this->getcats();
    }

    public function getTemplatecomments() {
        return ttemplatecomments::i();
    }

    public function getComments($id) {
        return \litepubl\comments\Comments::i($id);
    }

    public function getPingbacks($id) {
        return \litepubl\comments\Pingbacks::i($id);
    }

    public function getMeta($id) {
        return Meta::i($id);
    }

public function getMainView() {
return \litepubl\view\MainView::i();
}

public function getTheme() {
return \litepubl\view\Theme::i();
}

public function getUsers() {
return \litepubl\core\Users::i();
}

public function getUserpages() {
return \litepubl\pages\Users::i();
}

public function getView()
{
return View::i();
}

}