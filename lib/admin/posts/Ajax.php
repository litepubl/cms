<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\admin\posts;

use litepubl\admin\GetPerm;
use litepubl\admin\GetSchema;
use litepubl\core\Arr;
use litepubl\core\Context;
use litepubl\post\Post;
use litepubl\post\Posts as PostItems;
use litepubl\view\Admin;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\MainView;
use litepubl\view\Schema;
use litepubl\view\Schemes;
use litepubl\view\Vars;

class Ajax extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
{
    public $idpost;
    private $isauthor;

    protected function create()
    {
        parent::create();
        $this->basename = 'ajaxposteditor';
        $this->data['eventnames'] = & $this->eventnames;
        $this->map['eventnames'] = 'eventnames';

        $this->data['head'] = '';
        $this->data['visual'] = '';
        //'/plugins/ckeditor/init.js';
        $this->data['ajaxvisual'] = true;
    }

    public function addEvent(string $name, $callable, $method = null)
    {
        $name = strtolower($name);
        if (!in_array($name, $this->eventnames)) {
            $this->eventnames[] = $name;
        }

        return parent::addEvent($name, $callable, $method);
    }

    public function deleteEvent(string $name)
    {
        $name = strtolower($name);
        if (isset($this->data['events'][$name])) {
            unset($this->data['events'][$name]);
            Arr::deleteValue($this->eventnames, $name);
            $this->save();
        }
    }

    public function auth(Context $context)
    {
        $response = $context->response;
        $options = $this->getApp()->options;
        if (!$options->user) {
            return $response->forbidden();
        }

        if (!$options->hasgroup('editor')) {
            if (!$options->hasgroup('author')) {
                return $response->forbidden();
            }
        }
    }

    public function idparam()
    {
        return !empty($_GET['id']) ? (int)$_GET['id'] : (!empty($_POST['id']) ? (int)$_POST['id'] : 0);
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;
        $this->auth($context);
        if ($response->status != 200) {
            return;
        }

        $options = $this->getApp()->options;
        $this->idpost = $this->idparam();
        $this->isauthor = $options->inGroup('author');
        if ($this->idpost > 0) {
            $posts = PostItems::i();
            if (!$posts->itemExists($this->idpost)) {
                return $response->forbidden();
            }

            if (!$options->hasGroup('editor')) {
                if ($options->hasGroup('author')) {
                    $this->isauthor = true;
                    $post = Post::i($this->idpost);
                    if ($options->user != $post->author) {
                        return $response->forbidden();
                    }
                }
            }
        }

        $response->body = $this->getContent();
    }

    public function getContent(): string
    {
        $theme = Schema::i(Schemes::i()->defaults['admin'])->theme;
        $lang = Lang::admin('editor');
        $post = Post::i($this->idpost);
        $vars = new Vars();
        $vars->post = $post;

        switch ($_GET['get']) {
        case 'tags':
            $result = $theme->getInput('text', 'tags', $post->tagnames, $lang->tags);
            $result.= $theme->h($lang->addtags);
            $items = [];
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
            $args->comstatus = $theme->comboItems(
                [
                'closed' => $lang->closed,
                'reg' => $lang->reg,
                'guest' => $lang->guest,
                'comuser' => $lang->comuser
                ], $post->comstatus
            );

            $args->pingenabled = $post->pingenabled;
            $args->status = $theme->comboItems(
                [
                'published' => $lang->published,
                'draft' => $lang->draft
                ], $post->status
            );

            $args->perms = GetPerm::combo($post->idperm);
            $args->password = $post->password;
            $admin = Admin::admin();
            $result = $admin->parseArg(
                '
[combo=comstatus]
      [checkbox=pingenabled]
      [combo=status]
      $perms
      [password=password]
' . $admin->help($lang->notepassword), $args
            );
            break;


        case 'view':
            $result = GetSchema::combo($post->idschema);
            break;


        default:
            $name = trim($_GET['get']);
            if (isset($this->data['events'][$name])) {
                $r = $this->callEvent($name, ['post'  => $post, 'result' => '']);
                $result = $r['result'];
            } else {
                $result = var_export($_GET, true);
            }
        }

        return $result;
    }

    public function getText(string $text, $admintheme = null): string
    {
        if (!$admintheme) {
            $admintheme = Admin::admin();
        }

        $args = new Args();
        if ($this->visual) {
            if ($this->ajaxvisual) {
                $args->scripturl = $this->visual;
                $args->visual = $admintheme->parseArg($admintheme->templates['posteditor.text.visual'], $args);
            } else {
                $args->visual = MainView::i()->getjavascript($this->visual);
            }
        } else {
            $args->visual = '';
        }

        $args->raw = $text;
        Lang::admin('editor');
        return $admintheme->parseArg($admintheme->templates['posteditor.text'], $args);
    }
}
