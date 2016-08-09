<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\view;

function MainViewInstall($self)
{
    $self->heads = '<link type="text/css" href="$site.files$template.cssmerger_default" rel="stylesheet" />
  <script type="text/javascript" src="$site.files$template.jsmerger_default"></script>
  <link rel="alternate" type="application/rss+xml" title="$site.name RSS Feed" href="$site.url/rss.xml" />
  <link rel="pingback" href="$site.url/rpc.xml" />
  <link rel="EditURI" type="application/rsd+xml" title="RSD" href="$site.url/rsd.xml" />
  <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="$site.url/wlwmanifest.xml" />
  <meta name="generator" content="Lite Publisher $site.version" /> <!-- leave this for stats -->
  <meta name="keywords" content="$template.keywords" />
  <meta name="description" content="$template.description" />
  <link rel="sitemap" href="$site.url/sitemap.htm" />
  <link rel="apple-touch-icon" sizes="57x57" href="$site.files/js/litepubl/logo/apple-touch-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="$site.files/js/litepubl/logo/apple-touch-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="$site.files/js/litepubl/logo/apple-touch-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="$site.files/js/litepubl/logo/apple-touch-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="$site.files/js/litepubl/logo/apple-touch-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="$site.files/js/litepubl/logo/apple-touch-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="$site.files/js/litepubl/logo/apple-touch-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="$site.files/js/litepubl/logo/apple-touch-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="$site.files/apple-touch-icon.png">
  <link rel="icon" type="image/png" href="$site.files/js/litepubl/logo/favicon-32x32.png" sizes="32x32">
  <link rel="icon" type="image/png" href="$site.files/js/litepubl/logo/android-chrome-192x192.png" sizes="192x192">
  <link rel="icon" type="image/png" href="$site.files/js/litepubl/logo/favicon-96x96.png" sizes="96x96">
  <link rel="icon" type="image/png" href="$site.files/js/litepubl/logo/favicon-16x16.png" sizes="16x16">
  <link rel="manifest" href="$site.files/manifest.json">
  <link rel="shortcut icon" href="$site.files/favicon.ico">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-TileImage" content="$site.files/js/litepubl/logo/mstile-144x144.png">
  <meta name="msapplication-config" content="$site.files/browserconfig.xml">
  <meta name="theme-color" content="#ffffff">
  ';

    $lang = Lang::i('installation');
    $self->footer = "$lang->poweredby $lang->copyright";
}
