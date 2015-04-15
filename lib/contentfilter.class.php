<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcontentfilter extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'contentfilter';
    $this->addevents('oncomment', 'onaftercomment', 'beforecontent', 'aftercontent', 'beforefilter', 'afterfilter', 'onsimplefilter', 'onaftersimple');
    $this->data['automore'] = true;
    $this->data['automorelength'] = 250;
    $this->data['phpcode'] = true;
    $this->data['usefilter'] = true;
    $this->data['autolinks'] = true;
    $this->data['commentautolinks'] = true;
  }
  
  public function filtercomment($content) {
    $result = trim($content);
    $result = str_replace(array("\r\n", "\r"), "\n", $result);
    $result = self::quote(htmlspecialchars($result));
    
    if ($this->callevent('oncomment', array(&$result))) {
      $this->callevent('onaftercomment', array(&$result));
      return $result;
    }
    
    $result = self::simplebbcode($result);
    if ($this->commentautolinks) $result = self::createlinks($result);
    $result = $this->replacecode($result);
    $result = self::auto_p($result);
    if ((strlen($result) > 4) && !strpos($result, '<p>', 4)) {
      if (strbegin($result, '<p>')) $result = substr($result, 3);
      if(strend($result, '</p>')) $result = substr($result, 0, strlen($result) - 4);
      $result = trim($result);
    }
    $this->callevent('onaftercomment', array(&$result));
    return $result;
  }
  
  public function filterpost(tpost $post, $s) {
    $cancel = false;
    $this->callevent('beforecontent', array($post, &$s, &$cancel));
    if ($cancel) return     $this->aftercontent($post);
    
    $moretag = ' <!--more-->';
    if ( preg_match('/<!--more(.*?)?-->/', $s, $matches)  ||
    preg_match('/\[more(.*?)?\]/', $s, $matches)  ||
    preg_match('/\[cut(.*?)?\]/', $s, $matches)
    ) {
      $parts = explode($matches[0], $s, 2);
      $excerpt = $this->filter(trim($parts[0]) . $moretag);
      $post->filtered = $excerpt . $this->extract_pages($post,trim($parts[1]));
      $this->setexcerpt($post, $excerpt, self::gettitle($matches[1]));
      if ($post->moretitle == '')  $post->moretitle = tlocal::get('default', 'more');
    } else {
      if ($this->automore) {
        $post->filtered = $this->extract_pages($post, $s);
        $this->setexcerpt($post, $this->filter(trim(self::GetExcerpt($post->pagescount == 1 ? $s : $post->filtered, $this->automorelength)) . $moretag), tlocal::get('default', 'more'));
      } else {
        $post->filtered = $this->extract_pages($post, $s);
        $this->setexcerpt($post, $post->filtered, '');
      }
    }
    
    $post->description = self::getpostdescription($post->excerpt);
    $this->aftercontent($post);
  }
  
  public function setexcerpt(tpost $post, $excerpt, $more) {
    $post->excerpt = $excerpt;
    $post->rss =  $excerpt;
    $post->description = self::getpostdescription($excerpt);
    $post->moretitle =  $more;
  }
  
  public static function getpostdescription($description) {
    if (litepublisher::$options->parsepost) {
      $theme = ttheme::i();
      $description = $theme->parse($description);
    }
    $description = self::gettitle($description);
    $description = str_replace(
    array("\r", "\n", '  ', '"', "'", '$'),
    array(' ', ' ', ' ', '&quot;', '&#39;', '&#36;'),
    $description);
    $description =str_replace('  ', ' ', $description);
    return $description;
  }
  
  public function extract_pages(tpost $post, $s) {
    $post->deletepages();
    $pages = explode('<!--nextpage-->', $s);
    $firstpage = $this->filter(array_shift($pages));
    foreach ($pages as $page) {
      $page = trim($page);
      if ($page) $post->addpage($this->filter($page));
    }
    return $firstpage;
  }
  
  public static function gettitle($s) {
    $s = trim($s);
    $s = preg_replace('/\0+/', '', $s);
    $s = preg_replace('/(\\\\0)+/', '', $s);
    $s = strip_tags($s);
    return trim($s);
  }
  
  public function filterpages($content) {
    $result = array();
    $pages = explode('<!--nextpage-->', $content);
    foreach ($pages as $page) {
      if ($page = trim($page)) {
        $result[] = $this->filter($page);
      }
    }
    
    return implode('<!--nextpage-->', $result);
  }
  
  public function filter($content) {
    if ($this->callevent('beforefilter', array(&$content))) {
      $this->callevent('afterfilter', array(&$content));
      return $content;
    }
    $result = str_replace(array("\r\n", "\r"), "\n", trim($content));
    if ($this->usefilter) {
      if (strpos($result, '[html]') !== false) {
        $result = $this->splitfilter($result);
      } else {
        $result = $this->simplefilter($result);
      }
    }
    $this->callevent('afterfilter', array(&$result));
    return $result;
  }
  
  public function simplefilter($s) {
    $s = trim($s);
    if ($s == '') return '';
    $this->callevent('onsimplefilter', array(&$s));
    if ($this->autolinks) $s = self::createlinks($s);
    $s = $this->replacecode($s);
    $s = self::auto_p($s);
    $this->callevent('onaftersimple', array(&$s));
    return $s;
  }
  
  public function splitfilter($s) {
    $result = '';
    $openlen = strlen('[html]');
    $closelen = strlen('[/html]');
    while(false !== ($i = strpos($s, '[html]'))) {
      if ($i > 0) $result = $this->simplefilter(substr($s, 0, $i));
      if ($j = strpos($s, '[/html]', $i)) {
        $result .= substr($s, $i + $openlen, $j - $i - $openlen);
        $s = substr($s, $j + $closelen);
      } else {
        //no close tag, no filter to end
        $result .= substr($s, $i + $openlen);
        $s = '';
        break;
      }
    }
    $result .= $this->simplefilter($s);
    return $result;
  }
  
  public function replacecode($s) {
    $s =preg_replace_callback('/<code>(.*?)<\/code>/ims', array($this, 'callback_replace_code'), $s);
    if ($this->phpcode) {
      $s = preg_replace_callback('/\<\?(.*?)\?\>/ims', array(&$this, 'callback_replace_php'), $s);
    } else {
      $s = preg_replace_callback('/\<\?(.*?)\?\>/ims', array(&$this, 'callback_fix_php'), $s);
    }
    return $s;
  }
  
  public static function replace_code($s) {
    $s = strtr(    htmlspecialchars($s), array(
    '"' =>'&quot;',
    "'" =>  '&#39;',
    '$' => '&#36;',
    '  ' => '&nbsp;&nbsp;'
    ));
    //double space for prevent auto_p
    $s = str_replace("\n", '<br  />', $s);
    return sprintf('<code>%s</code>', $s);
  }
  
  public function callback_replace_code($found) {
    return self::replace_code($found[1]);
  }
  
  public function callback_replace_php($found) {
    return self::replace_code($found[0]);
  }
  
  public function callback_fix_php($m) {
    return str_replace("\n", ' ', $m[0]);
  }
  
  public static function getexcerpt($content, $len) {
    $result = strip_tags($content);
    if (strlen($result) <= $len) return $result;
    $chars = "\n ,.;!?:(";
    $p = strlen($result);
    for ($i = strlen($chars) - 1; $i >= 0; $i--) {
      if($pos = strpos($result, $chars[$i], $len)) {
        $p = min($p, $pos + 1);
      }
    }
    return substr($result, 0, $p);
  }
  
  public static function ValidateEmail($email) {
  return  preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email);
  }
  
  public static function quote($s) {
    return strtr ($s, array('"'=> '&quot;', "'" => '&#039;', '\\'=> '&#092;', '$' => '&#36;', '%' =>  '&#37;', '_' => '&#95;'));
  }
  
  public static function escape($s) {
    return strtr(trim(strip_tags($s)), array(
    '"' => '&quot;',
    "'" => '&#039;',
    '\\'=> '&#092;',
    '$' => '&#36;',
    '%' => '&#37;',
    '_' => '&#95;',
    '<' => '&lt;',
    '>' => '&gt;',
    ));
  }
  
  public static function unescape($s) {
    return strtr($s, array(
    '&quot;' => '"',
    '&#039;' =>  "'",
    '&#092;' => '\\',
    '&#36;' => '$',
    '&#37;' => '%',
    '&#95;' =>'_',
    '&lt;' => '<',
    '&gt;' => '>'
    ));
  }
  
  public static function remove_scripts($s) {
    $s = preg_replace('/\<\?.*?\?\>/ims', '', $s);
    foreach (array('script', 'style', 'iframe', 'frame', 'object') as $tag) {
      $s = preg_replace("/\\<$tag(.*?)$tag(\\s*)\\>/ims", '', $s);
      $s = preg_replace("/\\<$tag(.*?)\\>/ims", '', $s);
    }
    $s = preg_replace('/\[html(.*?)html\]/ims', '', $s);
    $s = preg_replace('/\[html(.*?)/ims', '', $s);
    return $s;
  }
  
  // uset in tthemeparser
  public static function getidtag($tag, $s) {
    if (preg_match("/<$tag\\s*.*?id\\s*=\\s*['\"]([^\"'>]*)/i", $s, $m)) {
      return $m[1];
    }
    return false;
  }
  
  public static function bbcode2tag($s, $code, $tag) {
    if (strpos($s, "[/$code]") !== false) {
      $low = strtolower($s);
      if (substr_count($low, "[$code]") == substr_count($low, "[/$code]")) {
        $s = str_replace("[$code]", "<$tag>", $s);
        $s = str_replace("[/$code]", "</$tag>", $s);
      }
    }
    RETURN $s;
  }
  
  public static function simplebbcode($s){
    $s = self::bbcode2tag($s, 'b', 'cite');
    $s = self::bbcode2tag($s, 'i', 'em');
    $s = self::bbcode2tag($s, 'code', 'code');
    //$s = self::bbcode2tag($s, 'quote', 'blockquote');
    if (strpos($s, '[/quote]') !== false) {
      $low = strtolower($s);
      if (substr_count($low, '[quote]') == substr_count($low, '[/quote]')) {
        $s = str_replace('[quote]', '<blockquote><p>', $s);
        $s = str_replace('[/quote]', '</p></blockquote>', $s);
      }
    }
    return$s;
  }
  
  public static function auto_p($str) {
    // Trim whitespace
    if (($str = trim($str)) === '') return '';
    // Standardize newlines
    $str = str_replace(array("\r\n", "\r"), "\n", $str);
    
    //remove br
    $str = str_replace(array("</br>\n", "<br />\N", "<br>\n", "<br/>\n"), "\n", $str);
    $str = str_replace(array('</br>', '<br />', '<br>', '<br/>'), "\n", $str);
    
    // Trim whitespace on each line
    $str = preg_replace('~^[ \t]+~m', '', $str);
    $str = preg_replace('~[ \t]+$~m', '', $str);
    
    // The following regexes only need to be executed if the string contains html
    if ($html_found = (strpos($str, '<') !== FALSE)) {
      // Elements that should not be surrounded by p tags
      $no_p = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th)|script|code|input|\?)';
      
      // Put at least two linebreaks before and after $no_p elements
      $str = preg_replace('~^<'.$no_p.'[^>]*+>~im', "\n$0", $str);
      $str = preg_replace('~</'.$no_p.'\s*+>$~im', "$0\n", $str);
    }
    
    // Do the <p> magic!
    $str = '<p>'.trim($str).'</p>';
  $str = preg_replace('~\n{2,}~', "</p>\n\n<p>", $str);
    
    // The following regexes only need to be executed if the string contains html
    if ($html_found !== FALSE) {
      // Remove p tags around $no_p elements
      $str = preg_replace('~<p>(?=</?'.$no_p.'[^>]*+>)~i', '', $str);
      $str = preg_replace('~(</?'.$no_p.'[^>]*+>)</p>~i', '$1', $str);
    }
    
    // Convert single linebreaks to <br />
    $str = preg_replace('~(?<!\n)\n(?!\n)~', "<br />\n", $str);
    //fix bug <li> ... </p>
    $str = preg_replace('~\n<li>(.*)</p>\n~', "\n<li>\$1\n", $str);
    return $str;
  }
  
  public static function clean_website($url) {
    $url = trim(strip_tags($url));
    if (strlen($url) <= 3) return '';
    if (!strbegin($url, 'http')) $url = 'http://' . $url;
    if ($parts = @parse_url($url)) {
      if (empty($parts['host'])) return '';
      if (!strpos($parts['host'], '.')) return '';
      $url = isset($parts['scheme']) ? $parts['scheme'] : 'http';
      $url .= '://';
      $url .= trim($parts['host']);
      $url .= isset($parts['path']) ? $parts['path'] : '/';
      if (isset($parts['query'])) $url .= '?' . $parts['query'];
      if (isset($parts['fragment'])) $url .= '#' . $parts['fragment'];
      return $url;
    }
    return '';
  }
  
  // imported code from wordpress
  public static function createlinks($s) {
    $s = ' ' . $s;
    $s = preg_replace_callback('#(?<=[\s>])(\()?([\w]+?://(?:[\w\\x80-\\xff\#$%&~/=?@\[\](+-]|[.,;:](?![\s<]|(\))?([\s]|$))|(?(1)\)(?![\s<.,;:]|$)|\)))+)#is',
    array(__class__, '_make_url_clickable_cb'), $s);
    
    $s = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]+)#is',
    array(__class__, '_make_web_ftp_clickable_cb'), $s);
    
  $s = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i',
    array(__class__, '_make_email_clickable_cb'), $s);
    
    $s = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $s);
    
    return trim($s);
  }
  
  public static function _make_url_clickable_cb($matches) {
    $url = $matches[2];
    if ( empty($url) ) 		return $matches[0];
    return $matches[1] . "<a href=\"$url\">$url</a>";
  }
  
  public static function _make_web_ftp_clickable_cb($matches) {
    $ret = '';
    $dest = $matches[2];
    $dest = 'http://' . $dest;
    if ( empty($dest) ) return $matches[0];
    if ( in_array( substr($dest, -1), array('.', ',', ';', ':', ')') ) === true ) {
      $ret = substr($dest, -1);
      $dest = substr($dest, 0, strlen($dest)-1);
    }
    return $matches[1] . "<a href=\"$dest\" rel=\"nofollow\">$dest</a>$ret";
  }
  
  public static function _make_email_clickable_cb($matches) {
    $email = $matches[2] . '@' . $matches[3];
    return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
  }
  
}//class