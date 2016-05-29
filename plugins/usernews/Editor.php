<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\usernews;

use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\post\Post;
use litepubl\post\Posts;

class Editor extends \litepubl\admin\Editor
{

    public function getPostEditor(Post $post, Args $args)
    {
        $args->data['$lang.sourceurl'] = Lang::admin()->get('usernews', 'sourceurl');
        if ($this->insertsource) $args->sourceurl = isset($post->meta->sourceurl) ? $post->meta->sourceurl : '';

        $form = file_get_contents($this->getApp()->paths->plugins . $this->dir . DIRECTORY_SEPARATOR . $this->editorfile);
        $args->raw = $post->rawcontent;
        $result = $post->id == 0 ? '' : $html->h2->formhead . $post->bookmark;
        $result.= $html->parseArg($form, $args);
        unset(Theme::$vars['post']);
        return $html->fixquote($result);
    }

    public function editPost(Post $post)
    {
        extract($_POST, EXTR_SKIP);
        $posts = Posts::i();
        $html = tadminhtml::i();

        if ($this->checkspam && ($id == 0)) {
            $post->status = 'published';
            $hold = $posts->db->getcount('status = \'draft\' and author = ' . $this->getApp()->options->user);
            $approved = $posts->db->getcount('status = \'published\' and author = ' . $this->getApp()->options->user);
            if ($approved < 3) {
                if ($hold - $approved >= 2) {
                    return $this->norights;
                }

                $post->status = 'draft';
            }
        }

        if ($this->insertsource) $post->meta->sourceurl = $sourceurl;
        $post->title = $title;
        $post->categories = admintheme::i()->processcategories();
        if ($this->getApp()->options->user > 1) {
            $post->author = $this->getApp()->options->user;
        }

        if (isset($files)) {
            $files = trim($files);
            $post->files = $files == '' ? array() : explode(',', $files);
        }

        $post->content = Filter::remove_scripts($raw);
        if ($this->insertsource) $post->filtered = sprintf($this->sourcetml, $post->meta->sourceurl) . $post->filtered;
        if ($id == 0) {
            $id = $posts->add($post);
            $_GET['id'] = $id;
            $_POST['id'] = $id;
        } else {
            $posts->edit($post);
        }

        return $html->h4->successedit;
    }

}

