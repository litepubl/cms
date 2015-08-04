<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function ttemplateInstall($self) {
  $self->heads =
  '<link rel="alternate" type="application/rss+xml" title="$site.name RSS Feed" href="$site.url/rss.xml" />
  <link rel="pingback" href="$site.url/rpc.xml" />
  <link rel="EditURI" type="application/rsd+xml" title="RSD" href="$site.url/rsd.xml" />
  <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="$site.url/wlwmanifest.xml" />
  <link rel="shortcut icon" type="image/x-icon" href="$site.files/favicon.ico" />
  <link rel="apple-touch-icon" href="$site.files/apple-touch-icon.png" />
  <meta name="generator" content="Lite Publisher $site.version" /> <!-- leave this for stats -->
  <meta name="keywords" content="$template.keywords" />
  <meta name="description" content="$template.description" />
  <link rel="sitemap" href="$site.url/sitemap.htm" />
  <link rel="alternate" type="application/rss+xml" title="$site.name RSS Feed" href="$site.url/rss.xml" />
  <link rel="pingback" href="$site.url/rpc.xml" />
  <link rel="EditURI" type="application/rsd+xml" title="RSD" href="$site.url/rsd.xml" />
  <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="$site.url/wlwmanifest.xml" />
  <link rel="shortcut icon" type="image/x-icon" href="$site.files/favicon.ico" />
  <link rel="apple-touch-icon" href="$site.files/apple-touch-icon.png" />
  <meta name="generator" content="Lite Publisher $site.version" /> <!-- leave this for stats -->
  <meta name="keywords" content="$template.keywords" />
  <meta name="description" content="$template.description" />
  <link rel="sitemap" href="$site.url/sitemap.htm" />
  <link type="text/css" href="$site.files$template.cssmerger_default" rel="stylesheet" />
  <script type="text/javascript" src="$site.files$template.jsmerger_default"></script>';
  
  //footer
  $html = tadminhtml::i();
  $html->section = 'installation';
  $lang = tlocal::i('installation');
  ttheme::$vars['lang'] = $lang;
  $theme = ttheme::i();
  $self->footer = $theme->parse($html->footer);
}