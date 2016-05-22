<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\core;

class DbOptimizer extends Events
{
    public $childTables;

    protected function create()
    {
        parent::create();
        $this->basename = 'db.optimizer';
        $this->addmap('childTables', array());
        $this->addevents('postsdeleted');
    }

    public function garbagePosts(string $table)
    {
        $db = $this->getApp()->db;
/*
        $deleted = $db->res2id($db->query(
"select id from $db->prefix$table where id not in
    (select $db->posts.id from $db->posts)"
));
*/

        $deleted = $db->res2id($db->query(
"select $db->prefix$table.id FROM $db->prefix$table
    LEFT JOIN $db->posts ON $db->prefix$table.id = $db->posts.id
    WHERE $db->posts.id IS NULL"
));

        if (count($deleted)) {
            $db->table = $table;
            $db->deleteItems($deleted);
        }
    }

    public function deleteDeleted()
    {
        //posts
        $db = $this->getApp()->db;
        $db->table = 'posts';

        $items = $db->idSelect("status = 'deleted'");
        if (count($items)) {
            $this->postsdeleted($items);
            $deleted = sprintf('id in (%s)', implode(',', $items));
            $db->exec("delete from $db->urlmap where id in
      (select idurl from $db->posts where $deleted)");

                $db->table = 'posts';
                $db->delete($deleted);

            foreach (array(
                'rawposts',
                'pages',
                'postsmeta'
            ) as $table) {
                $db->table = $table;
                $db->delete($deleted);
$this->garbagePosts($table);
            }

            foreach ($this->childTables as $table) {
                $db->table = $table;
                $db->delete($deleted);
$this->garbagePosts($table);
            }
        }

        //comments
$items = $db->res2id($db->query(
"select $db->comments.id FROM $db->comments
    LEFT JOIN $db->posts ON $db->comments.post = $db->posts.id
    WHERE $db->posts.id IS NULL"
));

if (count($items)) {
$db->query("update $db->comments set $db->comments.status = 'deleted' where $db->comments.id in ("
 . implode(',', $items) . ')');
}

        $db->table = 'comments';
        $items = $db->idSelect("status = 'deleted'");
        if (count($items)) {
            $deleted = sprintf('id in (%s)', implode(',', $items));
            $db->delete($deleted);
            $db->table = 'rawcomments';
            $db->delete($deleted);
        }

        $items = $db->res2id($db->query(
"select $db->users.id FROM $db->users
    LEFT JOIN $db->comments ON $db->users.id=$db->comments.author
    WHERE $db->users.status = 'comuser' and $db->comments.author IS NULL"
));

        if (count($items)) {
            $db->table = 'users';
            $db->delete(sprintf('id in(%s)', implode(',', $items)));
        }

        $items = $db->res2id($db->query(
"select $db->subscribers.post FROM $db->subscribers
    LEFT JOIN $db->posts ON $db->subscribers.post = $db->posts.id
    WHERE $db->posts.id IS NULL"
));

        if (count($items)) {
            $db->table = 'subscribers';
            $db->delete(sprintf('post in(%s)', implode(',', $items)));
        }

        $items = $db->res2id($db->query(
"select $db->subscribers.item FROM $db->subscribers
    LEFT JOIN $db->users ON $db->subscribers.item = $db->users.id
    WHERE $db->users.id IS NULL"
));

        if (count($items)) {
            $db->table = 'subscribers';
            $db->delete(sprintf('item in(%s)', implode(',', $items)));
        }

    }

    public function optimize()
    {
        $this->deleteDeleted();
        sleep(2);
        $man = DBManager::i();
        $man->optimize();
    }

}

