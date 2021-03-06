<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\ulogin;

use litepubl\core\Context;
use litepubl\core\DBManager;
use litepubl\core\Event;
use litepubl\core\Str;
use litepubl\core\UserGroups;
use litepubl\core\Users;
use litepubl\utils\Http;
use litepubl\view\Filter;

/**
 * ULogin plugin
 *
 * @property       string $url
 * @property       array $nets
 * @property-write callable $added
 * @property-write callable $onAdd
 * @property-write callable $onPhone
 * @method         array added(array $params)
 * @method         array onAdd(array $params)
 * @method         array onPhone(array $params)
 */

class Ulogin extends \litepubl\core\Plugin implements \litepubl\core\ResponsiveInterface
{

    protected function create()
    {
        parent::create();
        $this->addEvents('added', 'onadd', 'onphone');
        $this->table = 'ulogin';
        $this->data['url'] = '/admin/ulogin.php';
        $this->data['remember'] = true;
        $this->data['nets'] = [];
    }

    public function add($id, $service, $uid)
    {
        if (!$id || !$service || !$uid) {
            return;
        }

        if (!in_array($service, $this->data['nets'])) {
            $this->data['nets'][] = $service;
            $this->save();
            DBManager::i()->addEnum($this->table, 'service', $service);
        }

        $this->db->insert(
            [
            'id' => $id,
            'service' => $service,
            'uid' => $uid
            ]
        );

        $this->added(['id' => $id, 'service' => $service]);
        return $id;
    }

    public function find($service, $uid)
    {
        return $this->db->findId('service = ' . Str::quote($service) . ' and uid = ' . Str::quote($uid));
    }

    public function userDeleted(Event $event)
    {
        $this->db->delete("id = $event->id");
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;

        $token = isset($_POST['token']) ? $_POST['token'] : (isset($_GET['token']) ? $_GET['token'] : '');
        if (!$token) {
            //try fix ulogin bug double symbol ?
            $uri = $_SERVER['REQUEST_URI'];
            if (substr_count($uri, '?') <= 1) {
                return $response->forbidden();
            }

            $q = substr($uri, strpos($uri, '?') + 1);
            $q = str_replace('?', '&', $q);
            parse_str($q, $_GET);
            $token = isset($_GET['token']) ? $_GET['token'] : '';
            if (!$token) {
                return $response->forbidden();
            }
        }

        if (!($cookies = $this->auth($token))) {
            return $response->forbidden();
        }

        if (!empty($_GET['backurl'])) {
            $backurl = $_GET['backurl'];
        } elseif (!empty($_COOKIE['backurl'])) {
            $backurl = $_COOKIE['backurl'];
        } else {
            $backurl = false;
        }

        if (!$backurl || (strpos($backurl, '/') === false)) {
            $user = Users::i()->getItem($cookies['id']);
            $backurl = UserGroups::i()->getHome($user['idgroups'][0]);
        }

        if (!(int)Users::i()->db->getValue((int) $cookies['id'], 'phone')) {
            $r = $this->onPhone(['backurl' => $backurl, 'redir' => false]);
            if ($r['redir']) {
                return $response->redir($r['redir']);
            }
        }

        $this->getApp()->options->setCookie('backurl', '', 0);
        return $response->redir($backurl);
    }

    public function auth($token)
    {
        if (!($s = http::get('http://ulogin.ru/token.php?token=' . $token . '&host=' . $_SERVER['HTTP_HOST']))) {
            return false;
        }

        if (!($info = json_decode($s, true))) {
            return false;
        }

        if (isset($info['error']) || !isset($info['network'])) {
            return false;
        }

        $name = !empty($info['first_name']) ? $info['first_name'] : '';
        $name.= !empty($info['last_name']) ? ' ' . $info['last_name'] : '';
        if (!$name && !empty($info['nickname'])) {
            $name = $info['nickname'];
        }

        $uid = !empty($info['uid']) ? $info['uid'] : (!empty($info['id']) ? $info['id'] : (!empty($info['identity']) ? $info['identity'] : (!empty($info['profile']) ? $info['profile'] : '')));
        if (strlen($uid) >= 22) {
            $uid = Str::baseMd5($uid);
        }

        $phone = !empty($info['phone']) ? static ::filterPhone($info['phone']) : false;

        $newreg = false;
        $users = Users::i();
        if (!empty($info['email'])) {
            if ($id = $users->emailExists($info['email'])) {
                $user = $users->getItem($id);
                if ($user['status'] == 'comuser') {
                    $users->approve($id);
                }

                if ($phone && empty($user['phone'])) {
                    $users->setValue($id, 'phone', $phone);
                }
            } elseif ($this->getApp()->options->reguser) {
                $newreg = true;
                $id = $users->add(
                    [
                    'email' => $info['email'],
                    'name' => $name,
                    'website' => empty($info['profile']) ? '' : Filter::clean_website($info['profile']) ,
                    ]
                );

                if ($phone) {
                    $users->db->setValue($id, 'phone', $phone);
                }
                if ($uid) {
                    $this->add($id, $info['network'], $uid);
                }
            } else {
                //registration disabled
                return false;
            }
        } else {
            if ($uid) {
                if ($id = $this->find($info['network'], $uid)) {
                    //nothing
                } elseif ($this->getApp()->options->reguser) {
                    $newreg = true;
                    $id = $users->add(
                        [
                        'email' => '',
                        'name' => $name,
                        'website' => empty($info['profile']) ? '' : Filter::clean_website($info['profile']) ,
                        ]
                    );
                    $users->approve($id);
                    if ($phone) {
                        $users->db->setvalue($id, 'phone', $phone);
                    }
                    $this->add($id, $info['network'], $uid);
                } else {
                    //registration disabled
                    return false;
                }
            } else {
                //nothing found and hasnt email or uid
                return false;
            }
        }

        $expired = $this->remember ? time() + 31536000  : time() + 8 * 3600;
        $cookie = Str::md5Uniq();
        $options = $this->getApp()->options;
        $options->user = $id;
        $options->updateGroup();
        $options->setCookies($cookie, $expired);
        $options->setCookie('litepubl_regservice', $info['network'], $expired);
        $this->onAdd(
            [
            'id' => $id,
            'info' =>  $info,
            'newreg' =>  $newreg
            ]
        );

        return [
            'id' => $id,
            'pass' => $cookie,
            'regservice' => $info['network']
        ];
    }

    public function ulogin_auth(array $args)
    {
        if (!isset($args['token']) || (!($token = $args['token']))) {
            return $this->error('Invalid token', 403);
        }

        $result = $this->auth($token);
        if (!$result) {
            $this->error('Not authorized', 403);
        }

        return $result;
    }

    public function check_logged(array $args)
    {
        if ($this->getApp()->options->authCookies($args['litepubl_user_id'], $args['litepubl_user'])) {
            return [
                'logged' => true
            ];
        } else {
            return [
                'error' => [
                    'message' => 'Not logged',
                    'code' => 403
                ]
            ];
        }
    }

    public static function filterPhone($phone)
    {
        $phone = trim(
            str_replace(
                [
                ' ',
                '+',
                '=',
                '-',
                '_',
                '(',
                ')',
                '.'
                ],
                '',
                trim($phone)
            )
        );
        if (strlen($phone) && ($phone[0] == '9')) {
            $phone = '7' . $phone;
        }
        return (float)$phone;
    }
}
