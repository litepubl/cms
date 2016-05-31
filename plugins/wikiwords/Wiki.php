<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\wikiwords;

use litepubl\core\Str;
use litepubl\post\Post;
use litepubl\post\Posts;
use litepubl\core\ItemsPosts;

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
        $this->addevents('edited');
        $this->table = 'wikiwords';
        $this->itemsposts = new ItemsPosts();
        $this->itemsposts->table = $this->table . 'items';

        $this->fix = array();
        $this->words = array();
        $this->links = array();
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

    public function getPost($word)
    {
        if ($id = $this->add($word, 0)) {
            $items = $this->itemsposts->getposts($id);
            if (count($items)) {
                return $items[0];
            }
        }

        return false;
    }

    public function getLink($id)
    {
        $item = $this->getItem($id);
        $word = $item['word'];
        if (isset($this->links[$word])) {
            return $this->links[$word];
        }

        $items = $this->itemsposts->getposts($id);
        $theme = $this->getTheme();

        $c = count($items);
        if ($c == 0) {
            $result = str_replace('$word', $word, $theme->templates['wiki.word']);
        } elseif ($c == 1) {
            $post = Post::i($items[0]);
            $result = strtr($theme->templates['wiki.link'], ['$id' => $id, '$word' => $word, '$post.link' => $post->link, ]);
        } else {
            $links = '';
            $posts = Posts::i();
            $posts->loadItems($items);
            foreach ($items as $idpost) {
                $post = Post::i($idpost);
                $links.= strtr($theme->templates['wiki.links.item'], ['$id' => $id, '$word' => $word, '$post.link' => $post->link, '$post.title' => $post->title, ]);
            }

            $result = strtr($theme->templates['wiki.links'], ['$id' => $id, '$word' => $word, '$item' => $links, ]);
        }

        $this->links[$word] = $result;
        return $result;
    }

    public function add($word, $idpost)
    {
        $word = trim(strip_tags($word));
        if (!$word) {
            return false;
        }

        if (isset($this->words[$word])) {
            $id = $this->words[$word];
        } else {
            $id = $this->indexOf('word', $word);
            if (!$id) {
                $id = $this->addItem(array(
                    'word' => $word
                ));

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

    public function edit($id, $word)
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

    public function deleteWord($word)
    {
        if ($id = $this->indexof('word', $word)) {
            return $this->delete($id);
        }
    }

    public function getWord($word)
    {
        if ($id = $this->add($word, 0)) {
            return '$wikiwords.word_' . $id;
        }

        return '';
    }

    public function getWordLink($word)
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

    public function postdeleted($idpost)
    {
        if (count($this->itemsposts->deletepost($idpost)) > 0) {
            Posts::i()->addRevision();
        }
    }

    public function beforeFilter($post, &$content, &$cancel)
    {
        $this->createWords($post, $content);
        $this->replaceWords($content);
    }

    public function createWords($post, &$content)
    {
        $result = array();
        if (preg_match_all('/\[wiki\:(.*?)\]/im', $content, $m, PREG_SET_ORDER)) {
            foreach ($m as $item) {
                $word = $item[1];
                if ($id = $this->add($word, $post->id)) {
                    $result[] = $id;
                    if ($post->id == 0) {
                        $this->fix[$id] = $post;
                        $post->onId = array(
                            $this,
                            'fixpost'
                        );
                    }
                    $content = str_replace($item[0], "<span class=\"wiki\" id=\"wikiword-$id\">$word</span>", $content);
                }
            }
        }

        return $result;
    }

    public function replaceWords(&$content)
    {
        $result = array();
        if (preg_match_all('/\[\[(.*?)\]\]/i', $content, $m, PREG_SET_ORDER)) {
            foreach ($m as $item) {
                $word = $item[1];
                if ($id = $this->add($word, 0)) {
                    $result[] = $id;
                    //$content = str_replace($item[0], "\$wikiwords.word_$id", $content);
                    $content = str_replace($item[0], $this->getlink($id) , $content);
                }
            }
        }

        return $result;
    }

}

