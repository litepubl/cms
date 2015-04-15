<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tprofile extends tevents_itemplate implements itemplate {
  
  public static function i($id = 0) {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'profile';
    $this->data = $this->data + array(
    'url' => '/profile.htm',
    'template' => '',
    'nick' => 'admin',
    'dateOfBirth' => date('Y-m-d'),
    'gender' => 'male',
    'img' => '',
    
    'skype' => '',
    'icqChatID' => '',
    'aimChatID' => '',
    'jabberID' => '',
    'msnChatID' => '',
    'yahooChatID' => '',
    'mbox' => '',
    
    'country' => litepublisher::$options->language,
    'region' => '',
    'city' => '',
    'geourl' => 'http://beta-maps.yandex.ru/?text=',
    'bio' => '',
    'interests' => '',
    'interesturl' => '  http://www.livejournal.com/interests.bml?int=',
    'googleprofile' => ''
    );
  }
  
  public function getfoaf() {
    $options = litepublisher::$options;
    $posts = tposts::i();
    $postscount = $posts->archivescount;
    $manager = litepublisher::$classes->commentmanager;
    
    $result = tfoaf::getparam('name', $this->nick);
    foreach (array(
    'nick',
    'dateOfBirth',
    'gender',
    'icqChatID',
    'aimChatID',
    'jabberID',
    'msnChatID',
    'yahooChatID',
    'mbox'
    ) as $name) {
      $result .= tfoaf::getparam($name, $this->data[$name]);
    }
    
    $result .= '<foaf:img rdf:resource="' . tfoaf::escape($this->img) . '" />';
    $result .= tfoaf::getparam('homepage', litepublisher::$site->url . '/');
    
    $result .= '<foaf:weblog ' .
    'dc:title="'. tfoaf::escape(litepublisher::$site->name) . '" ' .
    'rdf:resource="' . tfoaf::escape(litepublisher::$site->url) . '/" />' .
    
    '<foaf:page>' .
    '<foaf:Document rdf:about="' . tfoaf::escape(litepublisher::$site->url . $this->url) . '">' .
    '<dc:title>' . tfoaf::escape(litepublisher::$site->name) . ' Profile</dc:title>' .
    '<dc:description>Full profile, including information such as interests and bio.</dc:description>' .
    '</foaf:Document>' .
    '</foaf:page>' .
    
    '<lj:journaltitle>' . tfoaf::escape(litepublisher::$site->name) . '</lj:journaltitle>' .
    '<lj:journalsubtitle>' . tfoaf::escape(litepublisher::$site->description) . '</lj:journalsubtitle>' .
    
    '<ya:blogActivity>' .
    '<ya:Posts>' .
    '<ya:feed ' .
    'dc:type="application/rss+xml" ' .
    'rdf:resource="' . tfoaf::escape(litepublisher::$site->url) . '/rss.xml" />' .
    "<ya:posted>$postscount</ya:posted>" .
    '</ya:Posts>' .
    '</ya:blogActivity>' .
    
    '<ya:blogActivity>' .
    '<ya:Comments>' .
    '<ya:feed ' .
    'dc:type="application/rss+xml" '.
    'rdf:resource="' . tfoaf::escape(litepublisher::$site->url) . '/comments.xml"/>' .
    "<ya:posted>$postscount</ya:posted>" .
    "<ya:received>$manager->count</ya:received>" .
    '</ya:Comments>' .
    '</ya:blogActivity>';
    
    if ($this->bio != '') $result .= '<ya:bio>'. tfoaf::escape($this->bio) . '</ya:bio>';
    
    $result .= $this->GetFoafOpenid();
    $result .= $this->GetFoafCountry();
    $result .= $this->GetFoafInterests();
    return $result;
  }
  
  public function GetFoafInterests() {
    $result = '';
    $list = explode(',', $this->interests);
    foreach ($list as $name) {
      $name = trim($name);
      if (empty($name)) continue;
      $result .= '<foaf:interest dc:title="' . tfoaf::escape($name) . '" rdf:resource="' . tfoaf::escape($this->interesturl) . urlencode($name) . '" />';
    }
    return $result;
  }
  
  public function GetFoafOpenid() {
    return '<foaf:openid rdf:resource="'. tfoaf::escape(litepublisher::$site->url) . '/" />';
  }
  
  public function GetFoafCountry() {
    $result = '';
    if ($this->country != '') $result .= '<ya:country dc:title="' . tfoaf::escape($this->country) . '" '.
    'rdf:resource="' . tfoaf::escape($this->geourl) . urlencode($this->country) . '"/>';
    
    if ($this->region != '') $result .='<ya:region dc:title="' . tfoaf::escape($this->region) . '" '.
    'rdf:resource="' . tfoaf::escape($this->geourl) . urlencode($this->region) . '"/>';
    
    if ($this->city != '') $result .= '<ya:city dc:title="' . tfoaf::escape($this->city) . '" ' .
    'rdf:resource="' . tfoaf::escape($this->geourl) . urlencode("$this->country, $this->city") . '" />';
    
    return $result;
  }
  
  public function request($arg) {
    $lang = tlocal::i('foaf');
  }
  
  public function gettitle() {
    return tlocal::get('foaf', 'profile');
  }
  
public function gethead() { }
  
  public function getkeywords() {
    return $this->interests;
  }
  
  public function getdescription() {
    return tcontentfilter::getexcerpt($this->bio, 128);
  }
  
  public function getcont() {
    ttheme::$vars['profile'] = $this;
    $theme = ttheme::i();
    $tml = $this->template;
    if ($tml == '') {
      $html = tadminhtml::i();
      $html->section = 'foaf';
      $tml = $html->profile;
    }
    return $theme->parse($tml);
  }
  
  protected function getstat() {
    $posts = tposts::i();
    $manager = tcommentmanager::i();
    $lang = tlocal::i('foaf');
    return sprintf($lang->statistic, $posts->archivescount, $manager->count);
  }
  
  protected function getmyself() {
    $lang = tlocal::i('foaf');
    $result = array();
    if ($this->img != '') {
      $i = strrpos($this->img, '.');
      $preview = substr($this->img, 0, $i) . '.preview' . substr($this->img, $i);
      $result[] = sprintf('<a rel="prettyPhoto" href="%s"><img src="%s" alt="profile" /></a>', $this->img, $preview);
    }
    if ($this->nick != '') $result[] = "$lang->nick $this->nick";
    if (($this->dateOfBirth != '')  && @sscanf($this->dateOfBirth , '%d-%d-%d', $y, $m, $d)) {
      $date = mktime(0,0,0, $m, $d, $y);
      $ldate = tlocal::date($date);
      $result[] = sprintf($lang->birthday, $ldate);
    }
    
    $result[] = $this->gender == 'female' ? $lang->female : $lang->male;
    
    if (!$this->country != '') $result[] = $this->country;
    if (!$this->region != '') $result[] = $this->region;
    if (!$this->city != '') $result[] = $this->city;
    $result[] = sprintf('<a rel="me" href="%s">Google profile</a>', $this->googleprofile);
    return implode("</li>\n<li>", $result);
  }
  
  protected function getcontacts() {
    $contacts = array(
    'skype' => 'Skype',
    'icqChatID' => 'ICQ',
    'aimChatID' => 'AIM',
    'jabberID' => 'Jabber',
    'msnChatID' => 'MSN',
    'yahooChatID' => 'Yahoo',
    'mbox' => 'E-Mail'
    );
    $lang = tlocal::i('foaf');
    $theme = ttheme::i();
    $result = "<div class=\"table-responsive\">
    <table class=\"' . $theme->templates['content.admin.tableclass'] . '\">
    <thead>
    <tr>
    <th align=\"left\">$lang->contactname</th>
    <th align=\"left\">$lang->value</th>
    </tr>
    </thead>
    <tbody>\n";
    
    foreach ($contacts as $contact => $name) {
      $value = $this->data[$contact];
      if ($value == '') continue;
      $result .= "<tr>
      <td align=\"left\">$name</td>
      <td align=\"left\">$value</td>
      </tr>\n";
    }
    
    $result .= "</tbody >
    </table>
    </div>";
    return $result;
  }
  
  protected function getmyinterests() {
    $result = "<p>\n";
    $list = explode(',', $this->interests);
    foreach ($list as $name) {
      $name = trim($name);
      if (empty($name)) continue;
      $result .= "<a href=\"$this->interesturl". urlencode($name). "\">$name</a>,\n";
    }
    $result .= "</p>\n";
    return $result;
  }
  
  protected function getfriendslist() {
    $result = "<p>\n";
    $foaf = tfoaf::i();
    $widget = tfriendswidget::i();
    $foaf->loadall();
    foreach ($foaf->items As $id => $item) {
    $url = $widget->redir ?"litepublisher::$site->url$widget->redirlink{litepublisher::$site->q}friend=$id" : $item['url'];
    $result .= "<a href=\"$url\" rel=\"friend\">{$item['nick']}</a>,\n";
    }
    $result .= "</p>\n";
    return $result;
  }
  
}//class