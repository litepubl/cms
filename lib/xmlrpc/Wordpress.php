<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\xmlrpc;

use litepubl\core\Str;
use litepubl\pages\Menu;
use litepubl\pages\Menus;
use litepubl\tag\Cats;
use litepubl\tag\Tags;

class Wordpress extends MetaWeblog
{

    private function menutostruct($id)
    {
        if (Str::begin($id, 'menu_')) {
            $id = substr($id, strlen('menu_'));
        }
        $id = (int)$id;
        $menus = Menus::i();
        if (!$menus->itemExists($id)) {
            return xerror(404, "Sorry, no such page.");
        }

        $menu = Menu::i($id);

        if ($menu->parent > 0) {
            $parent = Menu::i($menu->parent);
            $ParentTitle = $parent->title;
        } else {
            $ParentTitle = "";
        }

        $Result = [
            "dateCreated" => new IXR_Date(time()) ,
            "userid" => $menu->author,
            "page_id" => "menu_" . $menu->id,
            "page_status" => $menu->status == 'published' ? 'publish' : 'draft',
            "description" => $menu->content,
            "title" => $menu->title,
            "link" => $menu->url,
            "permaLink" => $menu->url,
            "categories" => [] ,
            "excerpt" => '',
            "text_more" => '',
            "mt_allow_comments" => 0,
            //"mt_allow_pings"		=> $menu->pingenabled ? 1 : 0,
            "mt_allow_pings" => 0,

            "wp_slug" => $menu->url,
            "wp_password" => $menu->password,
            "wp_author" => 'ADMIN',
            "wp_page_parent_id" => "menu_" . $menu->parent,
            "wp_page_ParentTitle" => $ParentTitle,
            "wp_page_order" => $menu->order,
            "wp_author_id" => $menu->author,
            "wp_author_display_name" => 'ADMIN',
            "date_created_gmt" => new IXR_Date(time() - $this->getApp()->options->gmt)
        ];

        return $Result;
    }

    // return struct
    public function wp_getPage($blogid, $id, $username, $password)
    {
        $this->auth($username, $password, 'editor');
        return $this->menutostruct($id);
    }

    public function wp_getPages($blogid, $username, $password)
    {
        $this->auth($username, $password, 'editor');
        $result = [];
        $menus = Menus::i();
        foreach ($menus->items as $id => $item) {
            $result[] = $this->menutostruct($id);
        }
        return $result;
    }

    public function wp_getPageList($blogid, $username, $password)
    {
        $this->auth($username, $password, 'editor');
        $result = [];
        $menus = Menus::i();
        foreach ($menus->items as $id => $item) {
            $result[] = [
                'page_id' => "menu_" . $id,
                'page_title' => $item['title'],
                'page_parent_id' => "menu_" . $item['parent'],
                'dateCreated' => new IXR_Date(time()) ,
            ];
        }

        return $result;
    }

    public function wp_deletePage($blogid, $username, $password, $id)
    {
        $this->auth($username, $password, 'editor');
        if (Str::begin($id, 'menu_')) {
            $id = substr($id, strlen('menu_'));
        }
        $id = (int)$id;
        $menus = Menus::i();
        if (!$menus->itemExists($id)) {
            return xerror(404, "Sorry, no such page.");
        }

        $menus->delete($id);
        return true;
    }

    public function wp_newCategory($blogid, $username, $password, $struct)
    {
        $this->auth($username, $password, 'editor');
        $categories = Cats::i();
        return (int) $categories->add($struct["name"], $struct["slug"]);
    }

    public function deleteCategory($blogid, $username, $password, $id)
    {
        $this->auth($username, $password, 'editor');
        $id = (int)$id;
        $categories = Cats::i();
        if (!$categories->itemExists($id)) {
            return xerror(404, "Sorry, no such page.");
        }

        $categories->delete($id);
        return true;
    }

    public function getTags($blogid, $username, $password)
    {
        $this->auth($username, $password, 'editor');
        $tags = Tags::i();
        $result = [];
        $tags->loadall();
        foreach ($tags->items as $id => $item) {
            $result[] = [
                'tag_id' => (string)$id,
                'name' => $item['title'],
                'count' => $item['itemscount'],
                'slug' => '',
                'html_url' => $this->getApp()->site->url . $item['url'],
                'rss_url' => $this->getApp()->site->url . $item['url']
            ];
        }
        return $result;
    }
}
