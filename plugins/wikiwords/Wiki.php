<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\plugins\wikiwords;

use litepubl\core\Event;
use litepubl\core\ItemsPosts;
use litepubl\core\Str;
use litepubl\post\Post;
use litepubl\post\Posts;
use litepubl\view\Theme;

class Wiki extends \litepubl\core\Items
{
    public $itemsposts;
    private $fix;
    private $words;
    private $links;

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->table = 'wikiwords';
        $this->itemsposts = new ItemsPosts();
        $this->itemsposts->table = $this->table . 'items';

        $this->fix = [];
        $this->words = [];
        $this->links = [];
    }

    public function __get($name)
    {
        if (Str::begin($name, 'word_')) {
            $id = (int)substr($name, strlen('word_'));
            if (($id > 0) && $this->itemExists($id)) {
                return $this->getLink($id);
            }

            return '';
        }

        return parent::__get($name);
    }

    public function getPost(string $word)
    {
        if ($id = $this->add($word, 0)) {
            $items = $this->itemsposts->getposts($id);
            if (count($items)) {
                return $items[0];
            }
        }

        return false;
    }

    public function getLink(int $id): string
    {
        $item = $this->getItem($id);
        $word = $item['word'];
        if (isset($this->links[$word])) {
            return $this->links[$word];
        }

        $items = $this->itemsposts->getposts($id);
        $theme = Theme::context();

        $c = count($items);
        if ($c == 0) {
            $result = str_replace('$word', $word, $theme->templates['wiki.word']);
        } elseif ($c == 1) {
            $post = Post::i($items[0]);
            $result = strtr(
                $theme->templates['wiki.link'], [
                '$id' => $id,
                '$word' => $word,
                '$post.link' => $post->link,
                ]
            );
        } else {
            $links = '';
            $posts = Posts::i();
            $posts->loadItems($items);
            foreach ($items as $idpost) {
                $post = Post::i($idpost);
                $links.= strtr(
                    $theme->templates['wiki.links.item'], [
                    '$id' => $id,
                    '$word' => $word,
                    '$post.link' => $post->link,
                    '$post.title' => $post->title,
                    ]
                );
            }

            $result = strtr(
                $theme->templates['wiki.links'], [
                '$id' => $id,
                '$word' => $word,
                '$item' => $links,
                ]
            );
        }

        $result = str_replace(["\n", "\r"], ' ', $result);
        $this->links[$word] = $result;
        return $result;
    }

    public function add(string $word, int $idpost): int
    {
        $word = trim(strip_tags($word));
        if (!$word) {
            return 0;
        }

        if (isset($this->words[$word])) {
            $id = $this->words[$word];
        } else {
            $id = $this->indexOf('word', $word);
            if (!$id) {
                $id = $this->addItem(['word' => $word]);
                $this->words[$word] = $id;
            }
        }

        if (($idpost > 0) && !$this->itemsposts->exists($idpost, $id)) {
            $this->itemsposts->add($idpost, $id);
            if (isset($this->links[$word])) {
                unset($this->links[$word]);
            }

            Posts::i()->addRevision();
        }

        return $id;
    }

    public function edit(int $id, string $word)
    {
        return $this->setValue($id, 'word', $word);
    }

    public function delete($id)
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        $this->itemsposts->deleteItem($id);
        return parent::delete($id);
    }

    public function deleteWord(string $word)
    {
        if ($id = $this->indexof('word', $word)) {
            return $this->delete($id);
        }
    }

    public function getWord(string $word): string
    {
        if ($id = $this->add($word, 0)) {
            return '$wikiwords.word_' . $id;
        }

        return '';
    }

    public function getWordLink(string $word): string
    {
        $word = trim($word);
        if (isset($this->links[$word])) {
            return $this->links[$word];
        }

        if ($id = $this->add($word, 0)) {
            return $this->getLink($id);
        }

        return $word;
    }

    public function fixPost(Post $post)
    {
        if (!count($this->fix)) {
            return;
        }

        foreach ($this->fix as $id => $wikipost) {
            if ($post == $wikipost) {
                $this->itemsposts->add($post->id, $id);
                unset($this->fix[$id]);
            }
        }

        Posts::i()->addrevision();
    }

    public function postDeleted(Event $event)
    {
        if (count($this->itemsposts->deletePost($event->id)) > 0) {
            Posts::i()->addRevision();
        }
    }

    public function beforeFilter(Event $event)
    {
        $event->content = $this->createWords($event->content, $event->post);
        $event->content = $this->replaceWords($event->content);
    }

    public function createWords(string $content, Post $post): string
    {
        if (preg_match_all('/\[wiki\:(.*?)\]/im', $content, $m, PREG_SET_ORDER)) {
            $theme = $post->view->theme;
            foreach ($m as $item) {
                $word = $item[1];
                if ($id = $this->add($word, $post->id)) {
                    if ($post->id == 0) {
                        $this->fix[$id] = $post;
                        $post->onId(
                            function ($event) {
                                $this->fixPost($event->getTarget());
                            }
                        );
                    }


                    $wikiWord = str_replace('$word', $word, $theme->templates['wiki.word']);
                    $content = str_replace($item[0], $wikiWord, $content);
                }
            }
        }

        return $content;
    }

    public function replaceWords(string $content): string
    {
        if (preg_match_all('/\[\[(.*?)\]\]/i', $content, $m, PREG_SET_ORDER)) {
            foreach ($m as $item) {
                $word = $item[1];
                if ($id = $this->add($word, 0)) {
                    $content = str_replace($item[0], $this->getLink($id), $content);
                }
            }
        }

        return $content;
    }
}
