<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\core;

use litepubl\Config;

/**
 * This is the class to storage base tags in templates
 *
 * @property      string $files
 * @property      string $language
 * @property      string $liveUser
 * @property      string $q
 * @property      string $subdir
 * @property      string $url
 * @property      string $userlink
 * @property      string $version
 * @property-read string $domain
 */

class Site extends Events
{
    use PoolStorageTrait;

    public $mapoptions;
    private $users;

    protected function create()
    {
        parent::create();
        $this->basename = 'site';
        $this->addmap(
            'mapoptions',
            [
            'version' => 'version',
            'language' => 'language',
            ]
        );
    }

    protected function getProp(string $name)
    {
        if (isset($this->mapoptions[$name])) {
            $prop = $this->mapoptions[$name];
            if (is_array($prop)) {
                list($classname, $method) = $prop;
                return call_user_func_array([static ::iGet($classname),                      $method], [$name]);
            } else {
                return $this->getApp()->options->data[$prop];
            }
        }

        return parent::getProp($name);
    }

    protected function setProp(string $name, $value)
    {
        if (isset($this->mapoptions[$name])) {
            $prop = $this->mapoptions[$name];
            if (is_string($prop)) {
                $this->getApp()->options->{$prop} = $value;
                return;
            }
        }

        try {
                parent::setProp($name, $value);
        } catch (PropException $e) {
            $this->data[$name] = $value;
        }

            $this->save();
    }

    public function getUrl(): string
    {
        if ($this->fixedurl) {
            return $this->data['url'];
        }

        return 'http://' . $this->getApp()->context->request->host;
    }

    public function getFiles(): string
    {
        if ($this->fixedurl) {
            return $this->data['files'];
        }

        return 'http://' . $this->getApp()->context->request->host;
    }

    public function setUrl(string $url)
    {
        $url = rtrim($url, '/');
        $this->data['url'] = $url;
        $this->data['files'] = $url;
        $this->subdir = '';
        if ($i = strpos($url, '/', 10)) {
            $this->subdir = substr($url, $i);
        }
        $this->save();
    }

    public function getDomain(): string
    {
        if (Config::$host) {
            return Config::$host;
        } else {
            $url = $this->url;
            return substr($url, strpos($url, '//') + 2);
        }
    }

    public function getUserlink(): string
    {
        if ($id = $this->getApp()->options->user) {
            if (!isset($this->users)) {
                $this->users = [];
            }

            if (isset($this->users[$id])) {
                return $this->users[$id];
            }

            $item = Users::i()->getitem($id);
            if ($item['website']) {
                $result = sprintf('<a href="%s">%s</a>', $item['website'], $item['name']);
            } else {
                $page = $this->getdb('userpage')->getitem($id);
                if ((int)$page['idurl']) {
                    $result = sprintf('<a href="%s%s">%s</a>', $this->url, $this->getApp()->router->getvalue($page['idurl'], 'url'), $item['name']);
                } else {
                    $result = $item['name'];
                }
            }
            $this->users[$id] = $result;
            return $result;
        }
        return '';
    }

    public function getLiveUser(): string
    {
        return '<?php echo  litepubl::$app->site->getUserLink(); ?>';
    }
}
