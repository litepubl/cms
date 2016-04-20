<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\pages;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\utils\Mailer;

class Notfound404 extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
use \litepubl\view\EmptyViewTrait;

    protected function create() {
        parent::create();
        $this->basename = 'notfound';
        $this->data['text'] = '';
        $this->data['notify'] = false;
    }

    public function httpheader() {
        return "<?php Header( 'HTTP/1.0 404 Not Found'); ?>" . \litepubl\core\Router::htmlheader(false);
    }

public function gettitle() {
return Lang::i()->notfound;
}

    public function getCont() {
        if ($this->notify) {
$this->sendmail();
}

        $schema = $this->getSchema();
        $theme = $schema->theme;
        if ($this->text) {
return $theme->simple($this->text);
}

            return $theme->notfound;
    }

    private function sendmail() {
        $args = new Args();
        $args->url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $args->ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        Lang::usefile('mail');
        $lang = Lang::i('notfound');
        $theme = $this->getSchema()->theme;

        $subject = $theme->parsearg($lang->subject, $args);
        $body = $theme->parsearg($lang->body, $args);

        Mailer::sendtoadmin($subject, $body, true);
    }

} //class