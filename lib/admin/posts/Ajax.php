<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\posts;
    use litepubl\core\Context;
use litepubl\post\Posts as PostItems;
use litepubl\post\Post;
use litepubl\view\Schemes;
use litepubl\view\Schema;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\view\Vars;
use litepubl\view\Admin;
use litepubl\view\MainView;
use litepubl\admin\GetSchema;
use litepubl\admin\GetPerm;
use litepubl\core\Arr;

class Ajax extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
 {
    public $idpost;
    private $isauthor;

    protected function create() {
        parent::create();
        $this->basename = 'ajaxposteditor';
        $this->data['eventnames'] = & $this->eventnames;
        $this->map['eventnames'] = 'eventnames';

        $this->data['head'] = '';
        $this->data['visual'] = '';
        //'/plugins/ckeditor/init.js';
        $this->data['ajaxvisual'] = true;
    }

    public function addevent($name, $class, $func, $once = false) {
        if (!in_array($name, $this->eventnames)) {
            $this->eventnames[] = $name;
        }

        return parent::addevent($name, $class, $func, $once);
    }

    public function delete_event($name) {
        if (isset($this->events[$name])) {
            unset($this->events[$name]);
            Arr::deleteValue($this->eventnames, $name);
            $this->save();
        }
    }


    public function auth(Context $context) {
$response = $context->response;
        $options =  $this->getApp()->options;
        if (!$options->user) {
return $response->forbidden();
}

        if (!$options->hasgroup('editor')) {
            if (!$options->hasgroup('author')) {
return $response->forbidden();
}
        }
    }

    public function idparam() {
        return !empty($_GET['id']) ? (int) $_GET['id'] : (!empty($_POST['id']) ? (int) $_POST['id'] : 0);
    }

    public function request(Context $context)
    {
    $response = $context->response;
        $response->cache = false;
$this->auth($context);
if ($response->status != 200) {
return;
}

        $this->idpost = $this->idparam();
        $this->isauthor =  $this->getApp()->options->ingroup('author');
        if ($this->idpost > 0) {
            $posts = PostItems::i();
            if (!$posts->itemexists($this->idpost)) {
 return $response->forbidden();
}

            if (! $this->getApp()->options->hasgroup('editor')) {
                if ( $this->getApp()->options->hasgroup('author')) {
                    $this->isauthor = true;
                    $post = Post::i($this->idpost);
                    if ( $this->getApp()->options->user != $post->author) {
 return $response->forbidden();
}
                }
            }
        }

        $response->body = $this->getcontent();
    }

    public function getContent() {
        $theme = Schemes::i(Schemes::i()->defaults['admin'])->theme;
        $lang = Lang::i('editor');
        $post = Post::i($this->idpost);
        $vars = new Vars();
        $vars->post = $post;

        switch ($_GET['get']) {
            case 'tags':
                $result = $theme->getinput('text', 'tags', $post->tagnames, $lang->tags);
                $lang->section = 'editor';
                $result.= $theme->h($lang->addtags);
                $items = array();
                $tags = $post->factory->tags;
                $list = $tags->getsorted(-1, 'name', 0);
                foreach ($list as $id) {
                    $items[] = '<a href="" class="posteditor-tag">' . $tags->items[$id]['title'] . "</a>";
                }
                $result.= sprintf('<p>%s</p>', implode(', ', $items));
                break;


            case 'status':
            case 'access':
                $args = new Args();
                $args->comstatus = $theme->comboItems(array(
                    'closed' => $lang->closed,
                    'reg' => $lang->reg,
                    'guest' => $lang->guest,
                    'comuser' => $lang->comuser
                ) , $post->comstatus);

                $args->pingenabled = $post->pingenabled;
                $args->status = $theme->comboItems(array(
                    'published' => $lang->published,
                    'draft' => $lang->draft
                ) , $post->status);

                $args->perms = Perms::getcombo($post->idperm);
                $args->password = $post->password;
                $result = Admin::admin()->parsearg('[combo=comstatus]
      [checkbox=pingenabled]
      [combo=status]
      $perms
      [password=password]
      <p>$lang.notepassword</p>', $args);

                break;


            case 'view':
                $result = GetSchema::combo($post->idschema);
                break;


            default:
                $name = trim($_GET['get']);
                if (isset($this->events[$name])) {
                    $result = $this->callevent($name, array(
                        $post
                    ));
                } else {
                    $result = var_export($_GET, true);
                }
        }

        return \litepubl\core\Router::htmlheader(false) . $result;
    }

    public function getText($text, $admintheme = null) {
        if (!$admintheme) {
            $admintheme = Admin::admin();
        }

        $args = new Args();
        if ($this->visual) {
            if ($this->ajaxvisual) {
                $args->scripturl = $this->visual;
                $args->visual = $admintheme->parsearg($admintheme->templates['posteditor.text.visual'], $args);
            } else {
                $args->visual = MainView::i()->getjavascript($this->visual);
            }
        } else {
            $args->visual = '';
        }

        $args->raw = $text;
        return $admintheme->parsearg($admintheme->templates['posteditor.text'], $args);
    }

}