<?php
/* fix redirect from wordpress' /feed to lite publisher */
$mode = 'redir';
include('index.php'); $robots = &TRobotstxt ::Instance();

 $robots = &TRobotstxt ::Instance();
 $robots->AddDisallow('/feed/');

$redir = &TRedirector::Instance();
$redir->items['/feed/'] = '/rss/';
$redir->items['/feed'] = '/rss/';
$redir->Save();

?>