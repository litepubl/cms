<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\admin;

use litepubl\core\Context;
use litepubl\core\UserGroups;
use litepubl\view\Lang;
use litepubl\view\Schemes;

class Menu extends \litepubl\pages\Menu
{
    use Factory;
    use Params;

    public static $adminownerprops = array(
        'title',
        'url',
        'idurl',
        'parent',
        'order',
        'status',
        'name',
        'group'
    );

    public static function getInstanceName()
    {
        return 'adminmenu';
    }

    public static function getOwner()
    {
        return Menus::i();
    }

    public function get_owner_props()
    {
        return static ::$adminownerprops;
    }

    public function load()
    {
        return true;
    }

    public function save()
    {
        return true;
    }

    public function getHead(): string
    {
        return Menus::i()->heads;
    }

    public function getIdSchema(): int
    {
        return Schemes::i()->defaults['admin'];
    }

    public function auth(Context $context, $group)
    {
        if ($context->checkAttack()) {
            return;
        }

        $response = $context->response;
        $options = $this->getApp()->options;
        if (!$options->user) {
            $response->cache = false;
            $response->redir('/admin/login/' . $this->getApp()->site->q . 'backurl=' . urlencode($context->request->url));
            return;
        }

        if (!$options->hasGroup($group)) {
            $url = UserGroups::i()->gethome($options->group);
            $response->cache = false;
            $response->redir($url);
            return;
        }
    }

    public function request(Context $context)
    {
        error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING);
        ini_set('display_errors', 1);
        $id = $context->id;
        if (is_null($id)) {
            $id = $this->owner->class2id(get_class($this));
        }

        $this->data['id'] = (int)$id;
        if ($id > 0) {
            $this->basename = $this->parent == 0 ? $this->name : $this->owner->items[$this->parent]['name'];
        }

        $this->auth($context, $this->group);
        if ($context->response->status != 200) {
            return;
        }

        Lang::usefile('admin');
        if ($status = $this->canRequest()) {
            $context->response->status = $status;
            return;
        }

        $this->doProcessForm();
    }

    public function canRequest()
    {
        return false;
    }

    protected function doProcessForm()
    {
        if (isset($_POST) && count($_POST)) {
            $this->getApp()->cache->clear();
        }

        return parent::doProcessForm();
    }

    public function getCont(): string
    {
        $app = $this->getApp();
        if ($app->options->admincache) {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $filename = 'adminmenu.' . $app->options->user . '.' . md5($_SERVER['REQUEST_URI'] . '&id=' . $id) . '.php';
            if ($result = $app->cache->getString($filename)) {
                return $result;
            }

            $result = parent::getcont();
            $app->cache->setString($filename, $result);
            return $result;
        } else {
            return parent::getCont();
        }
    }

    public function getAdminurl(): string
    {
        return $this->getApp()->site->url . $this->url . $this->getApp()->site->q . 'id';
    }

    public function getLang(): Lang
    {
        return Lang::i($this->name);
    }
}
