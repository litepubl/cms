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

use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;

class tfoafutil extends tevents
{

    public static function i()
    {
        return static ::iGet(__class__);
    }

    public function getFoafdom(&$foafurl)
    {
        $s = http::get($foafurl);
        if (!$s) {
            return false;
        }

        if (!$this->isfoaf($s)) {
            $foafurl = $this->discoverfoafurl($s);
            if (!$foafurl) {
                return false;
            }

            $s = http::get($foafurl);
            if (!$s) {
                return false;
            }

            if (!$this->isfoaf($s)) {
                return false;
            }

        }

        $dom = new domDocument;
        $dom->loadXML($s);
        return $dom;
    }

    public function getInfo($url)
    {
        $dom = $this->getfoafdom($url);
        if (!$dom) {
            return false;
        }

        $person = $dom->getElementsByTagName('RDF')->item(0)->getElementsByTagName('Person')->item(0);
        $result = array(
            'nick' => $person->getElementsByTagName('nick')->item(0)->nodeValue,
            'url' => $person->getElementsByTagName('weblog')->item(0)->attributes->getNamedItem('resource')->nodeValue,
            'foafurl' => $url
        );
        return $result;
    }

    private function isfoaf(&$s)
    {
        return strpos($s, '<rdf:RDF') > 0;
    }

    private function discoverfoafurl(&$s)
    {
        $tag = '<link rel="meta" type="application/rdf+xml" title="FOAF" href="';
        if ($i = strpos($s, $tag)) {
            $i = $i + strlen($tag);
            $i2 = strpos($s, '"', $i);
            return substr($s, $i, $i2 - $i);
        } else {
            $tag = str_replace('"', "'", $tag);
            if ($i = strpos($s, $tag)) {
                $i = $i + strlen($tag);
                $i2 = strpos($s, "'", $i);
                return substr($s, $i, $i2 - $i);
            }
        }
        return false;
    }

    public function checkfriend($foafurl)
    {
        $dom = $this->getfoafdom($foafurl);
        if (!$dom) {
            return false;
        }

        $knows = $dom->getElementsByTagName('knows');
        foreach ($knows as $node) {
            $blog = $node->getElementsByTagName('Person')->item(0)->getElementsByTagName('weblog')->item(0)->attributes->getNamedItem('resource')->nodeValue;
            $seealso = $node->getElementsByTagName('Person')->item(0)->getElementsByTagName('seeAlso')->item(0)->attributes->getNamedItem('resource')->nodeValue;
            if (($blog == $this->getApp()->site->url . '/') && ($seealso == $this->getApp()->site->url . '/foaf.xml')) {
                return true;
            }
        }
        return false;
    }

    public function check()
    {
        $result = '';
        $lang = Lang::i('foaf');
        $foaf = tfoaf::i();
        $items = $foaf->getapproved(0);
        foreach ($items as $id) {
            $item = $foaf->getitem($item);
            if (!$this->checkfriend($item['foafurl'])) {
                $result.= sprintf($lang->mailerror, $item['nick'], $item['blog'], $item['url']);
                $foaf->lock();
                $foaf->setvalue($id, 'errors', ++$item['errors']);
                if ($item['errors'] > 3) {
                    $foaf->setstatus($id, 'error');
                    $result.= sprintf($lang->manyerrors, $item['errors']);
                }
                $foaf->unlock();
            }
        }

        if ($result != '') {
            $result = $lang->founderrors . $result;
            $result = str_replace('\n', "\n", $result);
            $args = new Args();
            $args->errors = $result;

            Lang::usefile('mail');
            $lang = Lang::i('mailfoaf');
            $theme = Theme::i();

            $subject = $theme->parseArg($lang->errorsubj, $args);
            $body = $theme->parseArg($lang->errorbody, $args);

            tmailer::sendtoadmin($subject, $body);
        }
    }

}

