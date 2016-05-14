<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

use litepubl\core\Str;
use litepubl\view\Filter;

class tregservice extends \litepubl\core\Plugin
{
    public $sessdata;
    public $session_id;

    public static function i()
    {
        return static ::iGet(__class__);
    }

    protected function create()
    {
        parent::create();
        $this->addevents('onadd');
        $this->data['name'] = 'service';
        $this->data['title'] = 'service';
        $this->data['icon'] = '';
        $this->data['url'] = '';
        $this->data['client_id'] = '';
        $this->data['client_secret'] = '';
        $this->sessdata = array();
        $this->session_id = '';
    }

    public function getBasename()
    {
        return 'regservices' . DIRECTORY_SEPARATOR . $this->name;
    }

    public function valid()
    {
        return $this->client_id && $this->client_secret;
    }

    public function install()
    {
        if ($this->url) $this->getApp()->router->addget($this->url, get_class($this));
    }

    public function uninstall()
    {
        $this->getApp()->router->unbind($this);
    }

    public function start_session()
    {
        tsession::init(1);
        session_start();
        $this->session_id = session_id();
    }

    //handle callback
    public function request($arg)
    {
        $this->cache = false;
        Header('Cache-Control: no-cache, must-revalidate');
        Header('Pragma: no-cache');

        if (empty($_REQUEST['code'])) {
            return 403;
        }

        $this->start_session();

        if (empty($_REQUEST['state']) || empty($_SESSION['state']) || ($_REQUEST['state'] != $_SESSION['state'])) {
            session_destroy();
            return 403;
        }
        $this->sessdata = isset($_SESSION['sessdata']) ? $_SESSION['sessdata'] : array();
        session_destroy();
    }

    public function newstate()
    {
        $this->start_session();
        $state = Str::md5Rand();
        $_SESSION['state'] = $state;
        $_SESSION['sessdata'] = $this->sessdata;
        session_write_close();
        return $state;
    }

    public function getAuthurl()
    {
        $url = 'response_type=code';
        $url.= '&redirect_uri=' . urlencode($this->getApp()->site->url . $this->url);
        $url.= '&client_id=' . $this->client_id;
        $url.= '&state=' . $this->newstate();
        return $url;
    }

    protected function getAdmininfo($lang)
    {
        return array(
            'regurl' => '',
            'client_id' => $lang->client_id,
            'client_secret' => $lang->client_secret
        );
    }

    public function getTab($html, $args, $lang)
    {
        $a = $this->getadmininfo($lang);
        $result = $html->p(sprintf($lang->reg, $a['regurl'], $this->getApp()->site->url . $this->url));
        $result.= $html->getinput('text', "client_id_$this->name", tadminhtml::specchars($this->client_id) , $a['client_id']);
        $result.= $html->getinput('text', "client_secret_$this->name", tadminhtml::specchars($this->client_secret) , $a['client_secret']);
        return $result;
    }

    public function processForm()
    {
        if (isset($_POST["client_id_$this->name"])) $this->client_id = $_POST["client_id_$this->name"];
        if (isset($_POST["client_secret_$this->name"])) $this->client_secret = $_POST["client_secret_$this->name"];
        $this->save();
    }

    public function errorauth()
    {
        return 403;
    }

    public function adduser(array $item, $rawdata)
    {
        $users = tusers::i();
        $reguser = tregserviceuser::i();
        if (!empty($item['email'])) {
            if ($id = $users->emailexists($item['email'])) {
                $user = $users->getitem($id);
                if ($user['status'] == 'comuser') $users->approve($id);
            } elseif ($this->getApp()->options->reguser) {
                $id = $users->add(array(
                    'email' => $item['email'],
                    'name' => $item['name'],
                    'website' => isset($item['website']) ? Filter::clean_website($item['website']) : ''
                ));
                if (isset($item['uid'])) {
                    $uid = $item['uid'];
                    if (strlen($uid) >= 22) $uid = Str::baseMd5($uid);
                    $reguser->add($id, $this->name, $uid);
                }
            } else {
                //registration disabled
                return 403;
            }
        } else {
            $uid = !empty($item['uid']) ? $item['uid'] : (!empty($item['website']) ? $item['website'] : '');
            if ($uid) {
                if (strlen($uid) >= 22) $uid = Str::baseMd5($uid);
                if ($id = $reguser->find($this->name, $uid)) {
                    //nothing
                    
                } elseif ($this->getApp()->options->reguser) {
                    $id = $users->add(array(
                        'email' => '',
                        'name' => $item['name'],
                        'website' => isset($item['website']) ? Filter::clean_website($item['website']) : ''
                    ));
                    $users->approve($id);
                    $reguser->add($id, $this->name, $uid);
                } else {
                    //registration disabled
                    return 403;
                }
            } else {
                //nothing found and hasnt email or uid
                return 403;
            }
        }

        $expired = time() + 31536000;
        $cookie = Str::md5Uniq();
        $this->getApp()->options->user = $id;
        $this->getApp()->options->updategroup();
        $this->getApp()->options->setcookies($cookie, $expired);
        if ($this->getApp()->options->ingroup('admin')) setcookie('litepubl_user_flag', 'true', $expired, $this->getApp()->site->subdir . '/', false);

        setcookie('litepubl_regservice', $this->name, $expired, $this->getApp()->site->subdir . '/', false);

        $this->onadd($id, $rawdata);

        if (isset($this->sessdata['comuser'])) {
            return tcommentform::i()->processForm($this->sessdata['comuser'], true);
        }

        if (!empty($_COOKIE['backurl'])) {
            $backurl = $_COOKIE['backurl'];
        } else {
            $user = $users->getitem($id);
            $backurl = tusergroups::i()->gethome($user['idgroups'][0]);
        }

        return $this->getApp()->router->redir($backurl);
    }

} //class
class tregserviceuser extends titems
{

    public static function i()
    {
        return static ::iGet(__class__);
    }

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'regservices' . DIRECTORY_SEPARATOR . 'users';
        $this->table = 'regservices';
    }

    public function add($id, $service, $uid)
    {
        if (($id == 0) || ($service == '') || ($uid == '')) {
            return;
        }

        $this->db->insert(array(
            'id' => $id,
            'service' => $service,
            'uid' => $uid
        ));

        $this->added($id, $service);
    }

    public function find($service, $uid)
    {
        return $this->db->findid('service = ' . Str::quote($service) . ' and uid = ' . Str::quote($uid));
    }

}

