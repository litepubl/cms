<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\plugins\blackip;

class BlackIP extends \litepubl\core\Plugin
{
    public $ip;
    public $words;

    protected function create()
    {
        parent::create();
        $this->addmap('ip', array());
        $this->addmap('words', array());
        $this->data['ipstatus'] = 'hold';
        $this->data['wordstatus'] = 'hold';
    }

    public function filter($idpost, $idauthor, $content, $ip)
    {
        if (in_array($ip, $this->ip)) {
            return $this->ipstatus;
        }

        $ip = substr($ip, 0, strrpos($ip, '.') + 1);
        if (in_array($ip, $this->ip)) {
            return $this->ipstatus;
        }

        foreach ($this->words as $word) {
            if (false !== strpos($content, $word)) {
                return $this->wordstatus;
            }
        }
    }
}
