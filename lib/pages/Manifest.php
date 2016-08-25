<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\pages;

use litepubl\core\Context;
use litepubl\post\Node;

class Manifest extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
{

    public function request(Context $context)
    {
        $response = $context->response;
        $response->setXml();
        $site = $this->getApp()->site;

        switch ($context->itemRoute['arg']) {
        case 'manifest':
            $response->body.= '<manifest xmlns="http://schemas.microsoft.com/wlw/manifest/weblog">' . '<options>' . '<clientType>WordPress</clientType>' . '<supportsKeywords>Yes</supportsKeywords>' . '<supportsGetTags>Yes</supportsGetTags>' . '<supportsNewCategories>Yes</supportsNewCategories>' . '</options>' . '<weblog>' . '<serviceName>Lite Publisher</serviceName>' . "<homepageLinkText>$site->name</homepageLinkText>" . "<adminLinkText>$site->name</adminLinkText>" . "<adminUrl>$site->url/admin/</adminUrl>" . '<postEditingUrl>' . "<![CDATA[$site->url/admin/posts/editor/{$site->q}id={post-id}]]>" . '</postEditingUrl>' . '</weblog>' . '<buttons>' . '<button>' . '<id>0</id>' . '<text>Manage Comments</text>' .
            //'<imageUrl>images/wlw/wp-comments.png</imageUrl>' .
            '<imageUrl>/favicon.ico</imageUrl>' . '<clickUrl>' . "<![CDATA[$site->url/admin/comments/]]>" . '</clickUrl>' . '</button>' .

            '</buttons>' . '</manifest>';
            break;


        case 'rsd':
            /*
            $s .= '<rsd version="1.0" xmlns="http://archipelago.phrasewise.com/rsd">' .
            '<service>' .
            '<engineName>Lite Publisher</engineName>' .
            '<engineLink>http://litepublisher.com/</engineLink>' .
            "<homePageLink>$site->url/</homePageLink>" .
            '<apis>' .
            '<api name="WordPress" blogID="1" preferred="true" apiLink="' . $site->url . '/rpc.xml" />' .
            '<api name="Movable Type" blogID="1" preferred="false" apiLink="' . $site->url . '/rpc.xml" />' .
            '<api name="MetaWeblog" blogID="1" preferred="false" apiLink="' . $site->url . '/rpc.xml" />' .
            '<api name="Blogger" blogID="1" preferred="false" apiLink="' . $site->url . '/rpc.xml" />' .
            '</apis>' .
            '</service>' .
            '</rsd>';
            */

            $dom = new \domDocument();
            $dom->encoding = 'utf-8';
            $rsd = $dom->createElement('rsd');
            $dom->appendChild($rsd);
            Node::attr($rsd, 'version', '1.0');
            Node::attr($rsd, 'xmlns', 'http://archipelago.phrasewise.com/rsd');
            $service = Node::add($rsd, 'service');
            Node::addvalue($service, 'engineName', 'LitePublisher');
            Node::addvalue($service, 'engineLink', 'http://litepublisher.com/');
            Node::addvalue($service, 'homePageLink', $site->url . '/');
            $apis = Node::add($service, 'apis');
            $api = Node::add($apis, 'api');
            Node::attr($api, 'name', 'WordPress');
            Node::attr($api, 'blogID', '1');
            Node::attr($api, 'preferred', 'true');
            Node::attr($api, 'apiLink', $this->getApp()->site->url . '/rpc.xml');

            $api = Node::add($apis, 'api');
            Node::attr($api, 'name', 'Movable Type');
            Node::attr($api, 'blogID', '1');
            Node::attr($api, 'preferred', 'false');
            Node::attr($api, 'apiLink', $this->getApp()->site->url . '/rpc.xml');

            $api = Node::add($apis, 'api');
            Node::attr($api, 'name', 'MetaWeblog');
            Node::attr($api, 'blogID', '1');
            Node::attr($api, 'preferred', 'false');
            Node::attr($api, 'apiLink', $this->getApp()->site->url . '/rpc.xml');

            $api = Node::add($apis, 'api');
            Node::attr($api, 'name', 'Blogger');
            Node::attr($api, 'blogID', '1');
            Node::attr($api, 'preferred', 'false');
            Node::attr($api, 'apiLink', $this->getApp()->site->url . '/rpc.xml');

            $xml = $dom->saveXML();
            $response->body.= substr($xml, strpos($xml, '?>') + 2);
            break;
        }

        return $s;
    }
}
