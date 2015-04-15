<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tbookmarkswidgetInstall($self) {
  $self->lock();
  $self->items = array();
  
  $self->add(
  'http://www.google.com/reader/link?url=$url&title=$title',
  'Google Buzz',
  'buzz.png'
  );
  
  $self->add(
  'http://www.google.com/bookmarks/mark?op=add&bkmk=$url&title=$title',
  'Google Bookmarks',
  'google.png'
  );
  
  $self->add(
  'http://www.facebook.com/sharer.php?u=$url&t=$title',
  'Facebook',
  'facebook.png'
  );
  
  $self->add(
  'http://twitter.com/home/?status=$url?p=801+$title',
  'Twitter',
  'twitter.png'
  );
  
  if (litepublisher::$options->language == 'ru') {
    $self->add(
    'http://zakladki.yandex.ru/addlink.xml?name=$title&url=$url',
    'Закладки Yandex',
    'ya.png'
    );
    
    $self->add(
    'http://vkontakte.ru/share.php?url=$url',
    'Vkontakte',
    'vkontakte.png'
    );
    
    $self->add(
    'http://www.livejournal.com/update.bml?event=$url&subject=$title',
    'Livejournal',
    'livejournal.png'
    );
    
    $self->add(
    'http://connect.mail.ru/share?share_url=$url',
    'Мой мир',
    'myworld.png'
    );
    
    $self->add(
    'http://news2.ru/add_story.php?url=$url',
    'News2.ru',
    'news2ru.png'
    );
    $self->add(
    'http://smi2.ru/add/?action=step2&url=$url',
    'SMI2',
    'smi2.png'
    );
    
    $self->add(
    'http://www.bobrdobr.ru/addext.html?url=$url&title=$title',
    'БобрДобр.ru',
    'bobrdobr.png'
    );
    
    $self->add(
    'http://memori.ru/link/?sm=1&u_data[url]=$url&u_data[name]=$title',
    'Memori.ru',
    'memori.png'
    );
    
    $self->add(
    'http://moemesto.ru/post.php?url=$url&title=$title',
    'Мое место',
    'moemesto.png'
    );
    
    $self->add(
    'http://www.mister-wong.ru/index.php?action=addurl&bm_url=$url&bm_description=$title',
    'Mister Wong',
    'mrwong.png'
    );
    
  } else {
    $self->add(
    'http://digg.com/submit?url=$url',
    'Digg',
    'digg.png'
    );
    
    $self->add(
    'http://reddit.com/submit?url=$url&title=$title',
    'Reddit',
    'reddit.png'
    );
    
    $self->add(
    'http://delicious.com/post?url=$url&title=$title',
    'delicious',
    'delicious.png'
    );
    
    $self->add(
    'http://www.technorati.com/faves?add=$url',
    'Technorati',
    'technorati.png'
    );
    
    $self->add(
    'http://www.slashdot.org/bookmark.pl?url=$url&title=$title',
    'Slashdot',
    'slashdot.png'
    );
    
    $self->add(
    'http://myweb2.search.yahoo.com/myresults/bookmarklet?u=$url&t=$title',
    'Yahoo My Web',
    'yahoo.png'
    );
    
    $self->add(
    'http://www.stumbleupon.com/submit?url=$url&title=$title',
    'Stumble Upon',
    'stumbleupon.png'
    );
    
  }
  $self->addtosidebar(0);
  $self->unlock();
  //trick: lock data to prevent add link from parent class tlinkswidget
  $self->lock();
}