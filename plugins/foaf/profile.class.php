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

use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\Theme;

class tprofile extends tevents_itemplate implements itemplate
{

    public static function i($id = 0)
    {
        return static ::iGet(__class__);
    }

    protected function create()
    {
        parent::create();
        $this->basename = 'profile';
        $this->data = $this->data + array(
            'url' => '/profile.htm',
            'template' => '',
            'nick' => 'admin',
            'dateOfBirth' => date('Y-m-d') ,
            'gender' => 'male',
            'img' => '',

            'skype' => '',
            'icqChatID' => '',
            'aimChatID' => '',
            'jabberID' => '',
            'msnChatID' => '',
            'yahooChatID' => '',
            'mbox' => '',

            'country' => $this->getApp()->options->language,
            'region' => '',
            'city' => '',
            'geourl' => 'http://beta-maps.yandex.ru/?text=',
            'bio' => '',
            'interests' => '',
            'interesturl' => '  http://www.livejournal.com/interests.bml?int=',
            'googleprofile' => ''
        );
    }

    public function getFoaf()
    {
        $options = $this->getApp()->options;
        $posts = tposts::i();
        $postscount = $posts->archivescount;
        $manager = $this->getApp()->classes->commentmanager;

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
            $result.= tfoaf::getparam($name, $this->data[$name]);
        }

        $result.= '<foaf:img rdf:resource="' . tfoaf::escape($this->img) . '" />';
        $result.= tfoaf::getparam('homepage', $this->getApp()->site->url . '/');

        $result.= '<foaf:weblog ' . 'dc:title="' . tfoaf::escape($this->getApp()->site->name) . '" ' . 'rdf:resource="' . tfoaf::escape($this->getApp()->site->url) . '/" />' .

        '<foaf:page>' . '<foaf:Document rdf:about="' . tfoaf::escape($this->getApp()->site->url . $this->url) . '">' . '<dc:title>' . tfoaf::escape($this->getApp()->site->name) . ' Profile</dc:title>' . '<dc:description>Full profile, including information such as interests and bio.</dc:description>' . '</foaf:Document>' . '</foaf:page>' .

        '<lj:journaltitle>' . tfoaf::escape($this->getApp()->site->name) . '</lj:journaltitle>' . '<lj:journalsubtitle>' . tfoaf::escape($this->getApp()->site->description) . '</lj:journalsubtitle>' .

        '<ya:blogActivity>' . '<ya:Posts>' . '<ya:feed ' . 'dc:type="application/rss+xml" ' . 'rdf:resource="' . tfoaf::escape($this->getApp()->site->url) . '/rss.xml" />' . "<ya:posted>$postscount</ya:posted>" . '</ya:Posts>' . '</ya:blogActivity>' .

        '<ya:blogActivity>' . '<ya:Comments>' . '<ya:feed ' . 'dc:type="application/rss+xml" ' . 'rdf:resource="' . tfoaf::escape($this->getApp()->site->url) . '/comments.xml"/>' . "<ya:posted>$postscount</ya:posted>" . "<ya:received>$manager->count</ya:received>" . '</ya:Comments>' . '</ya:blogActivity>';

        if ($this->bio != '') $result.= '<ya:bio>' . tfoaf::escape($this->bio) . '</ya:bio>';

        $result.= $this->GetFoafOpenid();
        $result.= $this->GetFoafCountry();
        $result.= $this->GetFoafInterests();
        return $result;
    }

    public function GetFoafInterests()
    {
        $result = '';
        $list = explode(',', $this->interests);
        foreach ($list as $name) {
            $name = trim($name);
            if (empty($name)) {
                continue;
            }

            $result.= '<foaf:interest dc:title="' . tfoaf::escape($name) . '" rdf:resource="' . tfoaf::escape($this->interesturl) . urlencode($name) . '" />';
        }
        return $result;
    }

    public function GetFoafOpenid()
    {
        return '<foaf:openid rdf:resource="' . tfoaf::escape($this->getApp()->site->url) . '/" />';
    }

    public function GetFoafCountry()
    {
        $result = '';
        if ($this->country != '') $result.= '<ya:country dc:title="' . tfoaf::escape($this->country) . '" ' . 'rdf:resource="' . tfoaf::escape($this->geourl) . urlencode($this->country) . '"/>';

        if ($this->region != '') $result.= '<ya:region dc:title="' . tfoaf::escape($this->region) . '" ' . 'rdf:resource="' . tfoaf::escape($this->geourl) . urlencode($this->region) . '"/>';

        if ($this->city != '') $result.= '<ya:city dc:title="' . tfoaf::escape($this->city) . '" ' . 'rdf:resource="' . tfoaf::escape($this->geourl) . urlencode("$this->country, $this->city") . '" />';

        return $result;
    }

    public function request($arg)
    {
        $lang = Lang::i('foaf');
    }

    public function getTitle()
    {
        return Lang::get('foaf', 'profile');
    }

    public function getHead()
    {
    }

    public function getKeywords()
    {
        return $this->interests;
    }

    public function getDescription()
    {
        return Filter::getexcerpt($this->bio, 128);
    }

    public function getCont()
    {
        Theme::$vars['profile'] = $this;
        $theme = Theme::i();
        $tml = $this->template;
        if (!$tml) {
            $tml = file_get_contents($this->getApp()->paths->plugins . 'foaf/resource/profile.tml');
        }

        return $theme->parse($tml);
    }

    protected function getStat()
    {
        $posts = tposts::i();
        $manager = tcommentmanager::i();
        $lang = Lang::i('foaf');
        return sprintf($lang->statistic, $posts->archivescount, $manager->count);
    }

    protected function getMyself()
    {
        $lang = Lang::i('foaf');
        $result = array();
        if ($this->img != '') {
            $i = strrpos($this->img, '.');
            $preview = substr($this->img, 0, $i) . '.preview' . substr($this->img, $i);
            $result[] = sprintf('<a rel="prettyPhoto" href="%s"><img src="%s" alt="profile" /></a>', $this->img, $preview);
        }
        if ($this->nick != '') $result[] = "$lang->nick $this->nick";
        if (($this->dateOfBirth != '') && @sscanf($this->dateOfBirth, '%d-%d-%d', $y, $m, $d)) {
            $date = mktime(0, 0, 0, $m, $d, $y);
            $ldate = Lang::date($date);
            $result[] = sprintf($lang->birthday, $ldate);
        }

        $result[] = $this->gender == 'female' ? $lang->female : $lang->male;

        if (!$this->country != '') $result[] = $this->country;
        if (!$this->region != '') $result[] = $this->region;
        if (!$this->city != '') $result[] = $this->city;
        $result[] = sprintf('<a rel="me" href="%s">Google profile</a>', $this->googleprofile);
        return implode("</li>\n<li>", $result);
    }

    protected function getContacts()
    {
        $contacts = array(
            'skype' => 'Skype',
            'icqChatID' => 'ICQ',
            'aimChatID' => 'AIM',
            'jabberID' => 'Jabber',
            'msnChatID' => 'MSN',
            'yahooChatID' => 'Yahoo',
            'mbox' => 'E-Mail'
        );
        $lang = Lang::i('foaf');
        $theme = Theme::i();
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
            if ($value == '') {
                continue;
            }

            $result.= "<tr>
      <td align=\"left\">$name</td>
      <td align=\"left\">$value</td>
      </tr>\n";
        }

        $result.= "</tbody >
    </table>
    </div>";
        return $result;
    }

    protected function getMyinterests()
    {
        $result = "<p>\n";
        $list = explode(',', $this->interests);
        foreach ($list as $name) {
            $name = trim($name);
            if (empty($name)) {
                continue;
            }

            $result.= "<a href=\"$this->interesturl" . urlencode($name) . "\">$name</a>,\n";
        }
        $result.= "</p>\n";
        return $result;
    }

    protected function getFriendslist()
    {
        $result = "<p>\n";
        $foaf = tfoaf::i();
        $widget = tfriendswidget::i();
        $foaf->loadall();
        foreach ($foaf->items As $id => $item) {
            $url = $widget->redir ? " $this->getApp()->site->url$widget->redirlink{ $this->getApp()->site->q}friend=$id" : $item['url'];
            $result.= "<a href=\"$url\" rel=\"friend\">{$item['nick']}</a>,\n";
        }
        $result.= "</p>\n";
        return $result;
    }

}

