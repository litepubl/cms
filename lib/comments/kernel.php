<?php
//Comment.php
namespace litepubl\comments;

use litepubl\core\Str;
use litepubl\core\Users;
use litepubl\post\Post;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\Theme;

class Comment extends \litepubl\core\Data
{
    private static $md5 = [];
    private $_posted;

    public function __construct($id = 0)
    {
        if (!isset($id)) {
            return false;
        }

        parent::__construct();
        $this->table = 'comments';
        $id = (int)$id;
        if ($id > 0) {
            $this->setid($id);
        }
    }

    public function setId($id)
    {
        $comments = Comments::i();
        $this->data = $comments->getItem($id);
        if (!isset($this->data['name'])) {
            $this->data = $this->data + Users::i()->getitem($this->data['author']);
        }

        $this->_posted = false;
    }

    public function save()
    {
        extract($this->data, EXTR_SKIP);
        $this->db->UpdateAssoc(compact('id', 'post', 'author', 'parent', 'posted', 'status', 'content'));

        $this->getdb($this->rawtable)->UpdateAssoc(
            [
            'id' => $id,
            'modified' => Str::sqlDate() ,
            'rawcontent' => $rawcontent,
            'hash' => Str::baseMd5($rawcontent)
            ]
        );
    }

    public function getAuthorlink()
    {
        $name = $this->data['name'];
        $website = $this->data['website'];
        if ($website == '') {
            return $name;
        }

        $manager = Manager::i();
        if ($manager->hidelink || ($this->trust <= $manager->trustlevel)) {
            return $name;
        }

        $rel = $manager->nofollow ? 'rel="nofollow"' : '';
        if ($manager->redir) {
            return sprintf('<a %s href="%s/comusers.htm%sid=%d">%s</a>', $rel, $this->getApp()->site->url, $this->getApp()->site->q, $this->author, $name);
        } else {
            if (!Str::begin($website, 'http://')) {
                $website = 'http://' . $website;
            }
            return sprintf('<a class="url fn" %s href="%s" itemprop="url">%s</a>', $rel, $website, $name);
        }
    }

    public function getDate()
    {
        $theme = Theme::i();
        return Lang::date($this->posted, $theme->templates['content.post.templatecomments.comments.comment.date']);
    }

    public function getLocalStatus()
    {
        return Lang::get('commentstatus', $this->status);
    }

    public function getPosted()
    {
        if ($this->_posted) {
            return $this->_posted;
        }

        return $this->_posted = strtotime($this->data['posted']);
    }

    public function setPosted($date)
    {
        $this->data['posted'] = Str::sqlDate($date);
        $this->_posted = $date;
    }

    public function getTime()
    {
        return date('H:i', $this->posted);
    }

    public function getIso()
    {
        return date('c', $this->posted);
    }

    public function getRfc()
    {
        return date('r', $this->posted);
    }

    public function getUrl()
    {
        $post = Post::i($this->post);
        return $post->link . "#comment-$this->id";
    }

    public function getPosttitle()
    {
        $post = Post::i($this->post);
        return $post->title;
    }

    public function getRawcontent()
    {
        if (isset($this->data['rawcontent'])) {
            return $this->data['rawcontent'];
        }

        $comments = Comments::i($this->post);
        return $comments->raw->getvalue($this->id, 'rawcontent');
    }

    public function setRawcontent($s)
    {
        $this->data['rawcontent'] = $s;
        $filter = Filter::i();
        $this->data['content'] = $filter->filtercomment($s);
    }

    public function getIp()
    {
        if (isset($this->data['ip'])) {
            return $this->data['ip'];
        }

        $comments = Comments::i($this->post);
        return $comments->raw->getvalue($this->id, 'ip');
    }

    public function getMd5email()
    {
        $email = $this->data['email'];
        if ($email) {
            if (isset(static ::$md5[$email])) {
                return static ::$md5[$email];
            }

            $md5 = md5($email);
            static ::$md5[$email] = $md5;
            return $md5;
        }
        return '';
    }

    public function getGravatar()
    {
        if ($md5email = $this->getmd5email()) {
            return sprintf('<img class="avatar photo" src="http://www.gravatar.com/avatar/%s?s=90&amp;r=g&amp;d=wavatar" title="%2$s" alt="%2$s"/>', $md5email, $this->name);
        } else {
            return '';
        }
    }
}

//Comments.php
namespace litepubl\comments;

use litepubl\core\Event;
use litepubl\core\Str;
use litepubl\post\Post;
use litepubl\post\View as PostView;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\Vars;

/**
 * Comment items
 *
 * @property-write callable $edited
 * @property-write callable $onStatus
 * @property-write callable $changed
 * @property-write callable $onApproved
 * @method         array edited(array $params)
 * @method         array onStatus(array $params)
 * @method         array changed(array $params)
 * @method         array onApproved(array $params)
 */

class Comments extends \litepubl\core\Items
{
    public $rawtable;
    private $pid;

    public static function i($pid = 0)
    {
        $result = static ::iGet(get_called_class());
        if ($pid) {
            $result->pid = $pid;
        }
        return $result;
    }

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->table = 'comments';
        $this->rawtable = 'rawcomments';
        $this->basename = 'comments';
        $this->addEvents('edited', 'onstatus', 'changed', 'onapproved');
        $this->pid = 0;
    }

    public function add($idpost, $idauthor, $content, $status, $ip)
    {
        if ($idauthor == 0) {
            $this->error('Author id = 0');
        }
        $filter = Filter::i();
        $filtered = $filter->filtercomment($content);

        $item = [
            'post' => $idpost,
            'parent' => 0,
            'author' => (int)$idauthor,
            'posted' => Str::sqlDate() ,
            'content' => $filtered,
            'status' => $status
        ];

        $id = (int)$this->db->add($item);
        $item['id'] = $id;
        $item['rawcontent'] = $content;
        $this->items[$id] = $item;

        $this->getdb($this->rawtable)->add(
            [
            'id' => $id,
            'created' => Str::sqlDate() ,
            'modified' => Str::sqlDate() ,
            'ip' => $ip,
            'rawcontent' => $content,
            'hash' => Str::baseMd5($content)
            ]
        );

        $this->added(['id' => $id]);
        $this->changed(['id' => $id]);
        return $id;
    }

    public function edit($id, $content)
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        $filtered = Filter::i()->filtercomment($content);
        $this->db->setvalue($id, 'content', $filtered);
        $this->getdb($this->rawtable)->updateassoc(
            [
            'id' => $id,
            'modified' => Str::sqlDate() ,
            'rawcontent' => $content,
            'hash' => Str::baseMd5($content)
            ]
        );

        if (isset($this->items[$id])) {
            $this->items[$id]['content'] = $filtered;
            $this->items[$id]['rawcontent'] = $content;
        }

        $this->edited(['id' => $id]);
        $this->changed(['id' => $id]);
        return true;
    }

    public function delete($id)
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        $this->db->setvalue($id, 'status', 'deleted');
        $this->deleted(['id' => $id]);
        $this->changed(['id' => $id]);
        return true;
    }

    public function setStatus($id, $status)
    {
        if (!in_array(
            $status,
            [
            'approved',
            'hold',
            'spam'
            ]
        )) {
            return false;
        }
        if (!$this->itemExists($id)) {
            return false;
        }

        $old = $this->getValue($id, 'status');
        if ($old != $status) {
            $this->setValue($id, 'status', $status);
            $this->onstatus(
                [
                'id' => $id,
                'old' => $old,
                'status' =>  $status
                ]
            );

            $this->changed(['id' => $id]);
            if (($old == 'hold') && ($status == 'approved')) {
                $this->onapproved(['id' => $id]);
            }
            return true;
        }
        return false;
    }

    public function postDeleted(Event $event)
    {
        $this->db->update("status = 'deleted'", "post = $event->id");
    }

    public function getComment($id)
    {
        return new Comment($id);
    }

    public function getCount($where = '')
    {
        return $this->db->getcount($where);
    }

    public function select(string $where, string $limit): array
    {
        if ($where) {
            $where.= ' and ';
        }

        $table = $this->thistable;
        $db = $this->getApp()->db;
        $authors = $db->users;
        $res = $db->query(
            "select $table.*, $authors.name, $authors.email, $authors.website, $authors.trust from $table, $authors
    where $where $authors.id = $table.author $limit"
        );

        return $this->res2items($res);
    }

    public function getRaw()
    {
        return $this->getdb($this->rawtable);
    }

    public function getApprovedCount()
    {
        return $this->db->getcount("post = $this->pid and status = 'approved'");
    }

    //uses in import functions
    public function insert($idauthor, $content, $ip, $posted, $status)
    {
        $filtered = Filter::i()->filtercomment($content);
        $item = [
            'post' => $this->pid,
            'parent' => 0,
            'author' => $idauthor,
            'posted' => Str::sqlDate($posted) ,
            'content' => $filtered,
            'status' => $status
        ];

        $id = $this->db->add($item);
        $item['rawcontent'] = $content;
        $this->items[$id] = $item;

        $this->getdb($this->rawtable)->add(
            [
            'id' => $id,
            'created' => Str::sqlDate($posted) ,
            'modified' => Str::sqlDate() ,
            'ip' => $ip,
            'rawcontent' => $content,
            'hash' => Str::baseMd5($content)
            ]
        );

        return $id;
    }

    public function getContent(PostView $view)
    {
        return $this->getcontentWhere($view, 'approved', '');
    }

    public function getHoldContent($idauthor)
    {
        return $this->getcontentWhere('hold', "and $this->thistable.author = $idauthor");
    }

    public function getContentWhere(PostView $view, $status, $where)
    {
        $result = '';
        $theme = $view->theme;
        $options = $this->getApp()->options;
        if ($status == 'approved') {
            if ($options->commentpages) {
                $page = $view->page;
                if ($options->comments_invert_order) {
                    $page = max(0, $view->commentpages - $page) + 1;
                }

                $count = $options->commentsperpage;
                $from = ($page - 1) * $count;
            } else {
                $from = 0;
                $count = $vew->commentscount;
            }
        } else {
            $from = 0;
            $count = $options->commentsperpage;
        }

        $table = $this->thistable;
        $items = $this->select("$table.post = $view->id $where and $table.status = '$status'", "order by $table.posted asc limit $from, $count");

        $args = new Args();
        $args->from = $from;
        $comment = new Comment(0);
        $vars = new Vars();
        $vars->comment = $comment;
        $lang = Lang::i('comment');

        $tml = strtr(
            $theme->templates['content.post.templatecomments.comments.comment'],
            [
            '$quotebuttons' => $view->comstatus != 'closed' ? $theme->templates['content.post.templatecomments.comments.comment.quotebuttons'] : ''
            ]
        );

        $index = $from;
        $class1 = $theme->templates['content.post.templatecomments.comments.comment.class1'];
        $class2 = $theme->templates['content.post.templatecomments.comments.comment.class2'];

        foreach ($items as $id) {
            $comment->id = $id;
            $args->index = ++$index;
            $args->indexplus = $index + 1;
            $args->class = ($index % 2) == 0 ? $class1 : $class2;
            $result.= $theme->parseArg($tml, $args);
        }

        if (!$result) {
            return '';
        }

        if ($status == 'hold') {
            $tml = $theme->templates['content.post.templatecomments.holdcomments'];
        } else {
            $tml = $theme->templates['content.post.templatecomments.comments'];
        }

        $args->from = $from + 1;
        $args->comment = $result;
        return $theme->parseArg($tml, $args);
    }
}

//Form.php
namespace litepubl\comments;

use litepubl\core\Context;
use litepubl\core\Session;
use litepubl\core\Str;
use litepubl\core\UserOptions;
use litepubl\core\Users;
use litepubl\pages\Simple;
use litepubl\perms\Perm;
use litepubl\post\Post;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\Vars;

/**
 * Manage comment form data
 *
 * @property-write callable $onComuser
 * @method         array onComuser(array $params)
 */

class Form extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
{
    use \litepubl\utils\TempProps;

    public $helper;

    protected function create()
    {
        parent::create();
        $this->basename = 'commentform';
        $this->addEvents('oncomuser');
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;

        if ($this->getApp()->options->commentsdisabled) {
            $response->status = 404;
            return;
        }

        if ('POST' != $_SERVER['REQUEST_METHOD']) {
            $response->status = 405;
            $response->headers['Allow'] = 'POST';
            $response->headers['Content-Type'] = 'text/plain';
            return;
        }

        $temp = $this->newProps();
        $temp->context = $context;
        $response->body .= $this->doRequest($context->request->getPost());
    }

    public function doRequest(array $args)
    {
        if (isset($args['confirmid'])) {
            return $this->confirmRecevied($args['confirmid']);
        }

        return $this->processForm($args, false);
    }

    public function getShortPost(int $id)
    {
        if ($id == 0) {
            return false;
        }

        $db = $this->getApp()->db;
        return $db->selectAssoc("select id, idurl, idperm, status, comstatus, commentscount from $db->posts where id = $id");
    }

    public function invalidate(array $shortpost): string
    {
        $lang = Lang::i('comment');
        if (!$shortpost) {
            return $this->getErrorContent($lang->postnotfound);
        }

        if ($shortpost['status'] != 'published') {
            return $this->getErrorContent($lang->commentondraft);
        }

        if ($shortpost['comstatus'] == 'closed') {
            return $this->getErrorContent($lang->commentsdisabled);
        }

        return '';
    }

    protected function checkEmpty(array $values): string
    {
        $lang = Lang::i('comment');
        if (!trim($values['content'])) {
            return $this->getErrorContent($lang->emptycontent);
        }

        if (!$this->checkspam(isset($values['antispam']) ? $values['antispam'] : '')) {
            return $this->getErrorContent($lang->spamdetected);
        }

        return '';
    }

    public function processForm(array $values, $confirmed)
    {
        if ($error = $this->checkEmpty($values)) {
                return $error;
        }

        $app = $this->getApp();
        $lang = Lang::i('comment');
        $shortpost = $this->getshortpost(isset($values['postid']) ? (int)$values['postid'] : 0);
        if ($err = $this->invalidate($shortpost)) {
            return $err;
        }

        if (!$this->hasPerm($shortpost)) {
                return $this->context->response->forbidden();
        }

        $cm = Manager::i();
        if ($cm->checkduplicate && $cm->is_duplicate($shortpost['id'], $values['content'])) {
            return $this->getErrorContent($lang->duplicate);
        }

        unset($values['submitbutton']);

        if (!$confirmed) {
            $values['ip'] = preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR']);
        }

        if ($app->options->ingroups($cm->idgroups)) {
            if (!$confirmed && $cm->confirmlogged) {
                return $this->requestConfirm($values, $shortpost);
            }

            $iduser = $app->options->user;
        } else {
            switch ($shortpost['comstatus']) {
                case 'reg':
                    return $this->getErrorContent($lang->reg);

                case 'guest':
                    if (!$confirmed && $cm->confirmguest) {
                        return $this->requestConfirm($values, $shortpost);
                    }

                    $iduser = $cm->idguest;
                    break;


                case 'comuser':
                    //hook in regservices social plugin
                    $r = $this->oncomuser(
                        [
                        'values' => $values,
                        'confirmid' => $confirmed,
                        'result' => false
                        ]
                    );
                    if ($r['result']) {
                        return $r['result'];
                    }

                    if (!$confirmed && $cm->confirmcomuser) {
                        return $this->requestConfirm($values, $shortpost);
                    }

                    if ($err = $this->processcomuser($values)) {
                        return $err;
                    }

                        $users = Users::i();
                    if ($iduser = $users->emailExists($values['email'])) {
                        if ('comuser' != $users->getValue($iduser, 'status')) {
                            return $this->getErrorContent($lang->emailregistered);
                        }
                    } else {
                        $iduser = $cm->addcomUser($values['name'], $values['email'], $values['url'], $values['ip']);
                    }

                        $cookies = [];
                    foreach ([
                        'name',
                        'email',
                        'url'
                        ] as $field) {
                        $cookies["comuser_$field"] = $values[$field];
                    }
                    break;
            }
        }

        $user = Users::i()->getItem($iduser);
        if ('hold' == $user['status']) {
            return $this->getErrorContent($lang->holduser);
        }

        if (!$cm->canAdd($iduser)) {
            return $this->getErrorContent($lang->toomany);
        }

        if (!$cm->add($shortpost['id'], $iduser, $values['content'], $values['ip'])) {
            return $this->getErrorContent($lang->spamdetected);
        }

        //subscribe by email
        switch ($user[Users::STATUS]) {
            case Users::APPROVED:
                if ($user['email'] != '') {
                    // subscribe if its first comment
                    if (1 == Comments::i()->db->getcount("post = {$shortpost['id']} and author = $iduser")) {
                        if ('enabled' == UserOptions::i()->getvalue($iduser, 'subscribe')) {
                            Subscribers::i()->update($shortpost['id'], $iduser, true);
                        }
                    }
                }
                break;


            case Users::COMUSER:
                if (('comuser' == $shortpost['comstatus']) && $cm->comuser_subscribe) {
                    Subscribers::i()->update($shortpost['id'], $iduser, $values['subscribe']);
                }
                break;
        }

        //$post->lastcommenturl;
        $shortpost['commentscount']++;
        if (!$app->options->commentpages || ($shortpost['commentscount'] <= $app->options->commentsperpage)) {
            $c = 1;
        } else {
            $c = ceil($shortpost['commentscount'] / $app->options->commentsperpage);
        }

        $url = $app->router->getvalue($shortpost['idurl'], 'url');
        if (($c > 1) && !$app->options->comments_invert_order) {
            $url = rtrim($url, '/') . "/page/$c/";
        }

        $app->cache->clearUrl($url);
        return $this->sendResult($app->site->url . $url, isset($cookies) ? $cookies : []);
    }

    public function confirmRecevied($confirmid)
    {
        $lang = Lang::i('comment');
        Session::start(md5($confirmid));
        if (!isset($_SESSION['confirmid']) || ($confirmid != $_SESSION['confirmid'])) {
            session_destroy();
            return $this->getErrorContent($lang->notfound);
        }

        $values = $_SESSION['values'];
        session_destroy();
        return $this->processForm($values, true);
    }

    protected function requestConfirm(array $values, array $shortpost)
    {
        $values['date'] = time();
        $values['ip'] = preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR']);

        $confirmid = Str::md5Uniq();
        if ($sess = Session::start(md5($confirmid))) {
            $sess->lifetime = 900;
        }
        $_SESSION['confirmid'] = $confirmid;
        $_SESSION['values'] = $values;
        session_write_close();

        if ((int)$shortpost['idperm']) {
            $this->getPermHeader($shortpost);
        }

        return $this->confirm($confirmid);
    }

    protected function getPermheader(array $shortpost)
    {
        $post = Post::i((int)$shortpost['id']);
        $perm = Perm::i($post->idperm);
        $perm->setResponse($this->context->response, $post);
    }

    protected function hasPerm(array $shortpost): bool
    {
        if ((int)$shortpost['idperm']) {
            $post = Post::i((int)$shortpost['id']);
            $perm = Perm::i($post->idperm);
            return $perm->hasperm($post);
        }

        return true;
    }

    private function getConfirmform($confirmid)
    {
        $vars = new Vars();
        $vars->lang = Lang::i('comment');
        $args = new Args();
        $args->confirmid = $confirmid;
        $theme = Simple::i()->getSchema()->theme;
        return $theme->parseArg($theme->templates['content.post.templatecomments.confirmform'], $args);
    }

    //htmlhelper
    public function confirm($confirmid)
    {
        if (isset($this->helper) && ($this != $this->helper)) {
            return $this->helper->confirm($confirmid);
        }

        return Simple::html($this->getconfirmform($confirmid));
    }

    public function getErrorContent($s)
    {
        if (isset($this->helper) && ($this != $this->helper)) {
            return $this->helper->getErrorContent($s);
        }

        return Simple::content($s);
    }

    private function checkspam($s)
    {
        if (!($s = @base64_decode($s))) {
            return false;
        }

        $sign = 'superspamer';
        if (!Str::begin($s, $sign)) {
            return false;
        }

        $TimeKey = (int)substr($s, strlen($sign));
        return time() < $TimeKey;
    }

    public function processcomuser(array & $values)
    {
        $lang = Lang::i('comment');
        if (empty($values['name'])) {
            return $this->getErrorContent($lang->emptyname);
        }

        $values['name'] = Filter::escape($values['name']);
        $values['email'] = isset($values['email']) ? strtolower(trim($values['email'])) : '';
        if (!Filter::ValidateEmail($values['email'])) {
            return $this->getErrorContent($lang->invalidemail);
        }

        $values['url'] = isset($values['url']) ? Filter::escape(Filter::clean_website($values['url'])) : '';
        $values['subscribe'] = isset($values['subscribe']);
    }

    public function sendResult($link, $cookies)
    {
        if (isset($this->helper) && ($this != $this->helper)) {
            return $this->helper->sendresult($link, $cookies);
        }

        foreach ($cookies as $name => $value) {
            setcookie($name, $value, time() + 30000000, '/', false);
        }

        return $this->context->response->redir($link);
    }
}

//Json.php
namespace litepubl\comments;

use litepubl\view\Lang;
use litepubl\view\Theme;

class Json extends \litepubl\core\Events
{

    public function auth($id, $action)
    {
        if (!$this->getApp()->options->user) {
            return false;
        }

        $comments = Comments::i();
        if (!$comments->itemExists($id)) {
            return false;
        }

        if ($this->getApp()->options->ingroup('moderator')) {
            return true;
        }

        $cm = Manager::i();
        switch ($action) {
            case 'edit':
                if (!$cm->canedit) {
                    return false;
                }

                if ('closed' == $this->getApp()->db->getval('posts', $comments->getvalue($id, 'post'), 'comstatus')) {
                    return false;
                }

                return $comments->getvalue($id, 'author') == $this->getApp()->options->user;

            case 'delete':
                if (!$cm->candelete) {
                    return false;
                }

                if ('closed' == $this->getApp()->db->getval('posts', $comments->getvalue($id, 'post'), 'comstatus')) {
                    return false;
                }

                return $comments->getvalue($id, 'author') == $this->getApp()->options->user;
        }

        return false;
    }

    public function forbidden()
    {
        $this->error('Forbidden', 403);
    }

    public function comment_delete(array $args)
    {
        $id = (int)$args['id'];
        if (!$this->auth($id, 'delete')) {
            return $this->forbidden();
        }

        return Comments::i()->delete($id);
    }

    public function comment_setstatus($args)
    {
        $id = (int)$args['id'];
        if (!$this->auth($id, 'status')) {
            return $this->forbidden();
        }

        return Comments::i()->setstatus($id, $args['status']);
    }

    public function comment_edit(array $args)
    {
        $id = (int)$args['id'];
        if (!$this->auth($id, 'edit')) {
            return $this->forbidden();
        }

        $content = trim($args['content']);
        if (empty($content)) {
            return false;
        }

        $comments = Comments::i();
        if ($comments->edit($id, $content)) {
            return [
                'id' => $id,
                'content' => $comments->getvalue($id, 'content')
            ];
        } else {
            return false;
        }
    }

    public function comment_getraw(array $args)
    {
        $id = (int)$args['id'];
        if (!$this->auth($id, 'edit')) {
            return $this->forbidden();
        }

        $comments = Comments::i();
        $raw = $comments->raw->getvalue($id, 'rawcontent');
        return [
            'id' => $id,
            'rawcontent' => $raw
        ];
    }

    public function comments_get_hold(array $args)
    {
        if (!$this->getApp()->options->user) {
            return $this->forbidden();
        }

        $idpost = (int)$args['idpost'];
        $comments = Comments::i($idpost);

        if ($this->getApp()->options->ingroup('moderator')) {
            $where = '';
        } else {
            $where = "and $comments->thistable.author = " . $this->getApp()->options->user;
        }

        return [
            'items' => $comments->getcontentwhere('hold', $where)
        ];
    }

    public function comment_add(array $args)
    {
        if ($this->getApp()->options->commentsdisabled) {
            return [
                'error' => [
                    'message' => 'Comments disabled',
                    'code' => 403
                ]
            ];
        }

        $commentform = Form::i();
        $commentform->helper = $this;
        return $commentform->dorequest($args);
    }

    public function comment_confirm(array $args)
    {
        return $this->comment_add($args);
    }

    //commentform helper
    public function confirm($confirmid)
    {
        return [
            'confirmid' => $confirmid,
            'code' => 'confirm',
        ];
    }

    public function getErrorcontent($s)
    {
        return [
            'error' => [
                'message' => $s,
                'code' => 'error'
            ]
        ];
    }

    public function sendresult($url, $cookies)
    {
        return [
            'cookies' => $cookies,
            'posturl' => $url,
            'code' => 'success'
        ];
    }

    public function comments_get_logged(array $args)
    {
        if (!$this->getApp()->options->user) {
            return $this->forbidden();
        }

        $theme = Theme::context();
        $mesg = $theme->templates['content.post.templatecomments.form.mesg.logged'];
        $mesg = str_replace('$site.liveuser', $this->getApp()->site->getuserlink(), $mesg);

        $lang = Lang::i('comment');
        return $theme->parse($mesg);
    }
}

//Manager.php
namespace litepubl\comments;

use litepubl\Config;
use litepubl\core\Context;
use litepubl\core\Event;
use litepubl\core\Str;
use litepubl\core\Users;
use litepubl\utils\Mailer;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Vars;

/**
 * Comment manager included several options and rules
 *
 * @property       bool $
 * @property       bool $filterstatus
 * @property       bool $checkduplicate
 * @property       string $defstatus
 * @property       bool $sendnotification
 * @property       int $trustlevel
 * @property       bool $hidelink
 * @property       bool $redir
 * @property       bool $nofollow
 * @property       bool $canedit
 * @property       bool $candelete
 * @property       bool $confirmlogged
 * @property       bool $confirmguest
 * @property       bool $confirmcomuser
 * @property       bool $confirmemail
 * @property       bool $comuser_subscribe
 * @property       int $idguest
 * @property       array $idgroups
 * @property-write callable $onChanged
 * @property-write callable $comuserAdded
 * @property-write callable $isSpamer
 * @property-write callable $onCreateStatus
 * @method         array onChanged(array $params)
 * @method         array comuserAdded(array $params)
 * @method         array isSpamer(array $params)
 * @method         array onComuser(array $params)
 * @method         array onCreateStatus(array $params)
 */

class Manager extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
{
    use \litepubl\core\PoolStorageTrait;

    protected function create()
    {
        parent::create();
        $this->basename = 'commentmanager';
        $this->addEvents('onchanged', 'comuseradded', 'isspamer', 'oncreatestatus');
    }

    public function getCount()
    {
        $this->getApp()->db->table = 'comments';
        return $this->getApp()->db->getcount();
    }

    public function addcomuser($name, $email, $website, $ip)
    {
        $users = Users::i();
        $id = $users->add(
            [
            'email' => strtolower(trim($email)) ,
            'name' => $name,
            'website' => Filter::clean_website($website) ,
            'status' => 'comuser',
            'idgroups' => 'commentator'
            ]
        );

        if ($id) {
            $this->comuseradded(['id' => $id]);
        }
        return $id;
    }

    public function add($idpost, $idauthor, $content, $ip)
    {
        $status = $this->createStatus($idpost, $idauthor, $content, $ip);
        if (!$status) {
            return false;
        }

        $comments = Comments::i();
        return $comments->add($idpost, $idauthor, $content, $status, $ip);
    }

    public function reply($idparent, $content)
    {
        $idauthor = 1; //admin
        $comments = Comments::i();
        $idpost = $comments->getvalue($idparent, 'post');
        $id = $comments->add($idpost, $idauthor, $content, 'approved', '');
        $comments->setvalue($id, 'parent', $idparent);
        return $id;
    }

    public function changed(Event $event)
    {
        $comments = Comments::i();
        $idpost = $comments->getValue($event->id, 'post');
        $count = $comments->db->getcount("post = $idpost and status = 'approved'");
        $comments->getDB('posts')->setValue($idpost, 'commentscount', $count);
        if ($this->getApp()->options->commentspool) {
            Pool::i()->set($idpost, $count);
        }

        //update trust
        try {
            $idauthor = $comments->getvalue($event->id, 'author');
            $users = Users::i();
            if ($this->trustlevel > (int)$users->getvalue($idauthor, 'trust')) {
                $trust = $comments->db->getcount("author = $idauthor and status = 'approved' limit " . ($this->trustlevel + 1));
                $users->setvalue($idauthor, 'trust', $trust);
            }
        } catch (\Exception $e) {
        }

        $this->onChanged(['id' => $event->id]);
    }

    public function commentAdded(Event $event)
    {
        $this->sendMail($event->id);
    }

    public function sendMail(int $id)
    {
        if ($this->sendnotification) {
            $this->getApp()->onClose(
                function ($event) use ($id) {
                    $this->send_mail($id);
                    $event->once = true;
                }
            );
        }
    }

    public function send_mail($id)
    {
        $comments = Comments::i();
        $comment = $comments->getcomment($id);
        //ignore admin comments
        if ($comment->author == 1) {
            return;
        }
        $vars = new Vars();
        $vars->comment = $comment;
        $args = new Args();
        $adminurl = $this->getApp()->site->url . '/admin/comments/' . $this->getApp()->site->q . "id=$id";
        $ref = md5(Config::$secret . $adminurl . $this->getApp()->options->solt);
        $adminurl.= "&ref=$ref&action";
        $args->adminurl = $adminurl;

        Lang::usefile('mail');
        $lang = Lang::i('mailcomments');
        $theme = Theme::i();

        $subject = $theme->parseArg($lang->subject, $args);
        $body = $theme->parseArg($lang->body, $args);
        return Mailer::sendtoadmin($subject, $body, false);
    }

    public function createStatus(int $idpost, int $idauthor, string $content, string $ip): string
    {
        $r = $this->onCreateStatus(
            [
            'idpost' => $idpost,
            'status' => '',
            'idauthor' =>  $idauthor,
            'content' =>  $content,
            'ip' =>  $ip
            ]
        );

        $status = $r['status'];
        if ($status === false || $status == 'spam') {
            return '';
        }

        if (($status == 'hold') || ($status == 'approved')) {
            return $status;
        }

        if (!$this->filterstatus) {
            return $this->defstatus;
        }

        if ($this->defstatus == 'approved') {
            return 'approved';
        }

        return 'hold';
    }

    public function canAdd(int $idauthor): bool
    {
        $r = $this->isSpamer(['author' => $idauthor, 'spamer' => false]);
        return !$r['spamer'];
    }

    public function is_duplicate($idpost, $content)
    {
        $comments = Comments::i($idpost);
        $content = trim($content);
        $hash = Str::baseMd5($content);
        return $comments->raw->findid("hash = '$hash'");
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
        $users = Users::i();
        if (!$users->itemExists($id)) {
            return $response->redir('/');
        }

        $item = $users->getitem($id);
        $url = $item['website'];
        if (!strpos($url, '.')) {
            $url = $this->getApp()->site->url . '/';
        }

        if (!Str::begin($url, 'http')) {
            $url = 'http://' . $url;
        }

        return $response->redir($url);
    }
}

//Pool.php
namespace litepubl\comments;

use litepubl\view\Lang;

class Pool extends \litepubl\core\Pool
{

    protected function create()
    {
        parent::create();
        $this->basename = 'commentspool';
        $this->perpool = 50;
    }

    public function getItem($id)
    {
        return $this->getdb('posts')->getvalue($id, 'commentscount');
    }

    public function getLangcount($count)
    {
        $l = Lang::i()->ini['comment'];
        switch ($count) {
            case 0:
                return $l[0];

            case 1:
                return $l[1];

            default:
                return sprintf($l[2], count);
        }
    }

    public function getLink($idpost, $tml)
    {
        return sprintf($tml, $this->getlangcount($this->get($idpost)));
    }
}

//Subscribers.php
namespace litepubl\comments;

use litepubl\core\Arr;
use litepubl\core\Cron;
use litepubl\core\Event;
use litepubl\core\Str;
use litepubl\core\UserOptions;
use litepubl\core\Users;
use litepubl\post\Post;
use litepubl\post\Posts;
use litepubl\utils\Mailer;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Vars;

class Subscribers extends \litepubl\core\ItemsPosts
{
    public $blacklist;

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->table = 'subscribers';
        $this->basename = 'subscribers';
        $this->data['fromemail'] = '';
        $this->data['enabled'] = true;
        $this->addmap('blacklist', []);
    }

    public function getStorage()
    {
        return $this->getApp()->storage;
    }

    public function update($pid, $uid, $subscribed)
    {
        if ($subscribed == $this->exists($pid, $uid)) {
            return;
        }

        $this->remove($pid, $uid);
        $user = Users::i()->getitem($uid);
        if (in_array($user['email'], $this->blacklist)) {
            return;
        }

        if ($subscribed) {
            $this->add($pid, $uid);
        }
    }

    public function setEnabled($value)
    {
        if ($this->enabled != $value) {
            $this->data['enabled'] = $value;
            $this->save();

            $comments = Comments::i();
            if ($value) {
                Posts::i()->added = $this->postAdded;

                $comments->lock();
                $comments->added = $this->commentAdded;
                $comments->onapproved = $this->commentAdded;
                $comments->unlock();
            } else {
                $comments->unbind($this);
                Posts::i()->detach('added', $this->postAdded);
            }
        }
    }

    public function postAdded(Event $event)
    {
        $post = Post::i($event->id);
        if ($post->author <= 1) {
            return;
        }

        $useroptions = UserOptions::i();
        if ('enabled' == $useroptions->getValue($post->author, 'authorpost_subscribe')) {
            $this->add($post->id, $post->author);
        }
    }

    public function getLocklist()
    {
        return implode("\n", $this->blacklist);
    }

    public function setLocklist($s)
    {
        $this->setblacklist(explode("\n", strtolower(trim($s))));
    }

    public function setBlacklist(array $a)
    {
        $a = array_unique($a);
        Arr::deleteValue($a, '');
        $this->data['blacklist'] = $a;
        $this->save();

        $dblist = [];
        foreach ($a as $s) {
            if ($s == '') {
                continue;
            }

            $dblist[] = Str::quote($s);
        }
        if (count($dblist) > 0) {
            $db = $this->db;
            $db->delete("item in (select id from $db->users where email in (" . implode(',', $dblist) . '))');
        }
    }

    public function commentAdded(Event $event)
    {
        $this->sendMail($event->id);
    }

    public function sendMail(int $id)
    {
        if (!$this->enabled) {
            return;
        }

        $comments = Comments::i();
        if (!$comments->itemExists($id)) {
            return;
        }

        $item = $comments->getitem($id);
        if (($item['status'] != 'approved')) {
            return;
        }

        if ($this->getApp()->options->mailer == 'smtp') {
            Cron::i()->add('single', get_class($this), 'cronsendmail', (int)$id);
        } else {
            $this->cronsendmail($id);
        }
    }

    public function cronsendmail($id)
    {
        $comments = Comments::i();
        try {
            $item = $comments->getitem($id);
        } catch (\Exception $e) {
            return;
        }

        $subscribers = $this->getitems($item['post']);
        if (!$subscribers || (count($subscribers) == 0)) {
            return;
        }

        $comment = $comments->getcomment($id);
        $vars = new Vars();
        $vars->comment = $comment;
        Lang::usefile('mail');
        $lang = Lang::i('mailcomments');
        $theme = Theme::i();
        $args = new Args();

        $subject = $theme->parseArg($lang->subscribesubj, $args);
        $body = $theme->parseArg($lang->subscribebody, $args);

        $body.= "\n";
        $adminurl = $this->getApp()->site->url . '/admin/subscribers/';

        $users = Users::i();
        $users->loaditems($subscribers);
        $list = [];
        foreach ($subscribers as $uid) {
            $user = $users->getitem($uid);
            if ($user['status'] == 'hold') {
                continue;
            }

            $email = $user['email'];
            if (empty($email)) {
                continue;
            }

            if ($email == $comment->email) {
                continue;
            }

            if (in_array($email, $this->blacklist)) {
                continue;
            }

            $admin = $adminurl;
            if ('comuser' == $user['status']) {
                $admin.= $this->getApp()->site->q . 'auth=';
                if (empty($user['cookie'])) {
                    $user['cookie'] = Str::md5Uniq();
                    $users->setvalue($user['id'], 'cookie', $user['cookie']);
                }
                $admin.= rawurlencode($user['cookie']);
            }

            $list[] = [
                'fromname' => $this->getApp()->site->name,
                'fromemail' => $this->fromemail,
                'toname' => $user['name'],
                'toemail' => $email,
                'subject' => $subject,
                'body' => $body . $admin
            ];
        }

        if (count($list)) {
            Mailer::sendlist($list);
        }
    }
}

//Templates.php
namespace litepubl\comments;

use litepubl\post\Post;
use litepubl\post\View as PostView;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;

class Templates extends \litepubl\core\Events
{
    use \litepubl\utils\TempProps;

    protected function create()
    {
        parent::create();
        $this->basename = 'comments.templates';
    }

    public function getComments(PostView $view): string
    {
        $result = '';
        $idpost = (int)$view->id;
        $props = $this->newProps();
        $props->view = $view;
        $lang = Lang::i('comment');
        $comments = Comments::i();
        $list = $comments->getContent($view);

        $theme = $view->theme;
        $args = new Args();
        $args->count = $view->cmtcount;
        $result.= $theme->parseArg($theme->templates['content.post.templatecomments.comments.count'], $args);
        $result.= $list;

        if (($view->page == 1) && ($view->pingbackscount > 0)) {
            $pingbacks = Pingbacks::i($view->id);
            $result.= $pingbacks->getcontent();
        }

        if ($this->getApp()->options->commentsdisabled || ($view->comstatus == 'closed')) {
            $result.= $theme->parse($theme->templates['content.post.templatecomments.closed']);
            return $result;
        }

        $args->postid = $view->id;
        $args->antispam = base64_encode('superspamer' . strtotime("+1 hour"));

        $cm = Manager::i();

        // if user can see hold comments
        $result.= sprintf('<?php if (litepubl::$app->options->user && litepubl::$app->options->inGroups([%s])) { ?>', implode(',', $cm->idgroups));

        $holdmesg = '<?php if ($ismoder = litepubl::$app->options->ingroup(\'moderator\')) { ?>' . $theme->templates['content.post.templatecomments.form.mesg.loadhold'] .
        //hide template hold comments in html comment
        '<!--' . $theme->templates['content.post.templatecomments.holdcomments'] . '-->' . '<?php } ?>';

        $args->comment = '';
        $mesg = $theme->parseArg($holdmesg, $args);
        $mesg.= $this->getmesg('logged', $cm->canedit || $cm->candelete ? 'adminpanel' : false);
        $args->mesg = $mesg;

        $result.= $theme->parseArg($theme->templates['content.post.templatecomments.regform'], $args);
        $result.= $this->getJS(($view->idperm == 0) && $cm->confirmlogged, 'logged');

        $result.= '<?php } else { ?>';

        switch ($view->comstatus) {
            case 'reg':
                $args->mesg = $this->getmesg('reqlogin', $this->getApp()->options->reguser ? 'regaccount' : false);
                $result.= $theme->parseArg($theme->templates['content.post.templatecomments.regform'], $args);
                break;


            case 'guest':
                $args->mesg = $this->getmesg('guest', $this->getApp()->options->reguser ? 'regaccount' : false);
                $result.= $theme->parseArg($theme->templates['content.post.templatecomments.regform'], $args);
                $result.= $this->getJS(($view->idperm == 0) && $cm->confirmguest, 'guest');
                break;


            case 'comuser':
                $args->mesg = $this->getmesg('comuser', $this->getApp()->options->reguser ? 'regaccount' : false);

                foreach ([
                'name',
                'email',
                'url'
                ] as $field) {
                        $args->$field = "<?php echo (isset(\$_COOKIE['comuser_$field']) ? \$_COOKIE['comuser_$field'] : ''); ?>";
                }

                $args->subscribe = false;
                $args->content = '';

                $result.= $theme->parseArg($theme->templates['content.post.templatecomments.form'], $args);
                $result.= $this->getJS(($view->idperm == 0) && $cm->confirmcomuser, 'comuser');
                break;
        }

        $result.= '<?php } ?>';

        return $result;
    }

    public function getMesg(string $k1, string $k2): string
    {
        $theme = Theme::i();
        $result = $theme->templates['content.post.templatecomments.form.mesg.' . $k1];
        if ($k2) {
            $result.= "\n" . $theme->templates['content.post.templatecomments.form.mesg.' . $k2];
        }

        //normalize uri
        $result = str_replace('&backurl=', '&amp;backurl=', $result);

        //insert back url
        $result = str_replace('backurl=', 'backurl=' . urlencode($this->view->context->request->url), $result);

        return $theme->parse($result);
    }

    public function getJS(bool $confirmcomment, string $authstatus): string
    {
        $cm = Manager::i();
        $params = [
            'confirmcomment' => $confirmcomment,
            'comuser' => 'comuser' == $authstatus,
            'canedit' => $cm->canedit,
            'candelete' => $cm->candelete,
            'ismoder' => $authstatus != 'logged' ? false : '<?php echo ($ismoder ? \'true\' : \'false\'); ?>'
        ];

        $args = new Args();
        $args->params = json_encode($params);

        $theme = Theme::i();
        return $theme->parseArg($theme->templates['content.post.templatecomments.form.js'], $args);
    }
}
