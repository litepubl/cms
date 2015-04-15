<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmanifest extends tevents {
  
  static function i() {
    return getinstance(__class__);
  }
  
  public function request($arg) {
    $site = litepublisher::$site;
    $s = '<?php turlmap::sendxml(); ?>';
    switch ($arg) {
      case 'manifest':
      $s .= '<manifest xmlns="http://schemas.microsoft.com/wlw/manifest/weblog">' .
      '<options>' .
      '<clientType>WordPress</clientType>' .
      '<supportsKeywords>Yes</supportsKeywords>' .
      '<supportsGetTags>Yes</supportsGetTags>' .
      '<supportsNewCategories>Yes</supportsNewCategories>' .
      '</options>' .
      
      '<weblog>' .
      '<serviceName>Lite Publisher</serviceName>' .
      
      "<homepageLinkText>$site->name</homepageLinkText>" .
      "<adminLinkText>$site->name</adminLinkText>" .
      "<adminUrl>$site->url/admin/</adminUrl>" .
      '<postEditingUrl>' .
  "<![CDATA[$site->url/admin/posts/editor/{$site->q}id={post-id}]]>" .
      '</postEditingUrl>' .
      '</weblog>' .
      
      '<buttons>' .
      '<button>' .
      '<id>0</id>' .
      '<text>Manage Comments</text>' .
      //'<imageUrl>images/wlw/wp-comments.png</imageUrl>' .
      '<imageUrl>/favicon.ico</imageUrl>' .
      '<clickUrl>' .
      "<![CDATA[$site->url/admin/comments/]]>" .
      '</clickUrl>' .
      '</button>' .
      
      '</buttons>' .
      '</manifest>';
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
      
      $dom = new domDocument();
      $dom->encoding = 'utf-8';
      $rsd = $dom->createElement('rsd');
      $dom->appendChild($rsd);
      tnode::attr($rsd, 'version', '1.0');
      tnode::attr($rsd, 'xmlns', 'http://archipelago.phrasewise.com/rsd');
      $service = tnode::add($rsd, 'service');
      tnode::addvalue($service , 'engineName', 'LitePublisher');
      tnode::addvalue($service , 'engineLink', 'http://litepublisher.com/');
      tnode::addvalue($service , 'homePageLink', litepublisher::$site->url . '/');
      $apis = tnode::add($service, 'apis');
      $api = tnode::add($apis, 'api');
      tnode::attr($api, 'name', 'WordPress');
      tnode::attr($api, 'blogID', '1');
      tnode::attr($api, 'preferred', 'true');
      tnode::attr($api, 'apiLink', litepublisher::$site->url . '/rpc.xml');
      
      $api = tnode::add($apis, 'api');
      tnode::attr($api, 'name', 'Movable Type');
      tnode::attr($api, 'blogID', '1');
      tnode::attr($api, 'preferred', 'false');
      tnode::attr($api, 'apiLink', litepublisher::$site->url . '/rpc.xml');
      
      $api = tnode::add($apis, 'api');
      tnode::attr($api, 'name', 'MetaWeblog');
      tnode::attr($api, 'blogID', '1');
      tnode::attr($api, 'preferred', 'false');
      tnode::attr($api, 'apiLink', litepublisher::$site->url . '/rpc.xml');
      
      $api = tnode::add($apis, 'api');
      tnode::attr($api, 'name', 'Blogger');
      tnode::attr($api, 'blogID', '1');
      tnode::attr($api, 'preferred', 'false');
      tnode::attr($api, 'apiLink', litepublisher::$site->url . '/rpc.xml');
      
      $xml = $dom->saveXML();
      $s .=substr($xml, strpos($xml, '?>') + 2);
      break;
    }
    
    return  $s;
  }
  
}//class