<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\xmlrpc;

use litepubl\post\Post;
use litepubl\post\Posts;
use litepubl\tag\Cats;

class MovableType extends Common
{

    // on success, array of structs containing ISO.8601 dateCreated, String userid, String postid, String title; on failure, fault
    public function getRecentPostTitles($blogid, $username, $password, $count)
    {
        $this->auth($username, $password, 'author');
        $count = (int)$count;
        $posts = Posts::i();
        $list = $posts->getrecent($this->getApp()->options->user, $count);
        $result = [];
        foreach ($list as $id) {
            $post = Post::i($id);
            $result[] = [
                'dateCreated' => new IXR_Date($post->posted) ,
                'userid' => (string)$post->author,
                'postid' => (string)$post->id,
                'title' => $post->title
            ];
        }

        return $result;
    }

    // On success, an array of structs containing String categoryId and String categoryName; on failure, fault.
    public function getCategoryList($blogid, $username, $password)
    {
        $this->auth($username, $password, 'author');
        $categories = Cats::i();
        $categories->loadall();
        $result = [];
        foreach ($categories->items as $id => $item) {
            $result[] = [
                'categoryId' => (string)$id,
                'categoryName' => $item['title']
            ];
        }
        return $result;
    }
    // on success, an array of structs containing String categoryName, String categoryId, and boolean isPrimary; on failure, fault.
    public function getPostCategories($id, $username, $password)
    {
        $id = (int)$id;
        $this->canedit($username, $password, $id);
        $posts = Posts::i();
        if (!$posts->itemExists($id)) {
            return $this->xerror(404, "Invalid post id.");
        }

        $post = Post::i($id);
        $categories = Cats::i();
        $categories->loaditems($post->categories);
        $isPrimary = true;
        $result = [];
        foreach ($post->categories as $idcat) {
            $item = $categories->getitem($idcat);
            $result[] = [
                'categoryName' => $item['title'],
                'categoryId' => (string)$idcat,
                'isPrimary' => $isPrimary
            ];
            $isPrimary = false;
        }
        return $result;
    }

    // on success, boolean true value; on failure, fault
    public function setPostCategories($id, $username, $password, $catlist)
    {
        $id = (int)$id;
        $this->canedit($username, $password, $id);
        $posts = Posts::i();
        if (!$posts->itemExists($id)) {
            return $this->xerror(404, "Invalid post id.");
        }

        $post = Post::i($id);

        $list = [];
        foreach ($catlist as $Cat) {
            $list[] = $Cat['categoryId'];
        }
        $post->categories = $list;
        $posts->edit($post);
        return true;
    }

    public function supportedTextFilters()
    {
        return [];
    }

    public function getTrackbackPings($id)
    {
        $id = (int)$id;
        $posts = Posts::i();
        if (!$posts->itemExists($id)) {
            return $this->xerror(404, "Invalid post id.");
        }

        $post = Post::i($id);
        if ($post->status != 'published') {
            return $this->xerror(403, 'Target post not published');
        }

        $result = [];
        $pingbacks = PingbacksItems::i($id);
        $items = $pingbacks->db->getItems("post = $id and status = 'approved' order by posted");
        foreach ($items as $item) {
            $result[] = [
                'pingIP' => $item['ip'],
                'pingURL' => $item['url'],
                'pingTitle' => $item['title']
            ];
        }
        return $result;
    }

    public function publishPost($id, $username, $password)
    {
        $id = (int)$id;
        $this->canedit($username, $password, $id);
        $posts = Posts::i();
        if (!$posts->itemExists($id)) {
            return $this->xerror(404, "Invalid post id.");
        }

        $post = Post::i($id);
        $post->status = 'published';
        $posts->edit($post);
        return true;
    }
}
