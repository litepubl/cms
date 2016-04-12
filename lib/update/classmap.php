<?php

return [
'tdata' => 'litepubl\core\Data',
'tevents' => 'litepubl\core\Events',
'tcoevents' => 'litepubl\core\CoEvents',
'titem' => 'litepubl\core\Item',
'titems' => 'litepubl\core\Items',
'titemsposts' => 'litepubl\core\ItemsPosts',
'tpoolitems' => 'litepubl\core\Pool',
'tsingleitems' => 'litepubl\core\SingleItems',
'tclasses' => 'litepubl\core\Classes',
'tcron' => 'litepubl\core\Cron',
'tdatabase' => 'litepubl\core\DB',
'toptions' => 'litepubl\core\Options',
'tplugin' => 'litepubl\core\Plugin',
'tplugins' => 'litepubl\core\Plugins',
'tsession' => 'litepubl\core\Session',
'tsite' => 'litepubl\core\Site',
'turlmap' => 'litepubl\core\Router',
'tusers' => 'litepubl\core\Users',
'tusergroups' => 'litepubl\core\UserGroups',
'tuseroptions' => 'litepubl\core\UserOptions',
'tusersman' => 'litepubl\core\Usersman',
'cachestorage_file' => 'litepubl\core\CacheFile',
'cachestorage_memcache ' => 'litepubl\core\CacheMemcache',
'tdbmanager' => 'litepubl\core\DBManager
'tdboptimizer' => 'litepubl\core\DBOptimizer',
'tarray2prop' => 'litepubl\core\Array2prop',

//theme
'tlocal' => 'litepubl\view\Lang',
'tlocalmerger' => 'theme\LangMerger',
'ttemplate' => 'litepubl\view\MainControler',
'itemplate' => 'litepubl\view\ControlerInterface',
'ttheme' => 'litepubl\view\Theme',
'basetheme' => 'litepubl\view\BaseTheme',
'admintheme' => 'litepubl\view\Admin',
'targs' => 'litepubl\view\Args',
'themevars' => 'litepubl\view\Vars',
'tview' => 'litepubl\view\Schema',
'tviews' => 'litepubl\view\Schemes',
'tguard' => 'litepubl\view\Guard',
'tcontentfilter' => 'litepubl\view\Filter',
'tjsmerger' => 'litepubl\view\JsMerger',
'tcssmerger' => 'litepubl\view\CssMerger',

//posts
'tpost' => 'litepubl\post\Post',
'tposts' => 'litepubl\post\Posts',
'tmetapost' => 'litepubl\post\Meta',
'tposttransform' => 'litepubl\post\Transform',
'tpostfactory' => 'litepubl\post\Factory',
'tfiles' => 'litepubl\post\Files',
'tfileitems' => 'litepubl\post\FileItems',
'tprivatefiles' => 'litepubl\post\PrivateFiles',
'tdomrss' => 'litepubl\post\DomRss',
'trss' => 'litepubl\post\Rss',
'tnode' => 'litepubl\post\Node',
'trssMultimedia' => 'litepubl\post\RssFiles',
'tmediaparser' => 'litepubl\post\MediaParser',
'tarchives' => 'litepubl\post\Archives',
'tarchiveswidget' => 'litepubl\widget\Archives',
'tpinger' => 'litepubl\post\Pinger",

//pages
'trobotstxt' => 'litepubl\pages\RobotsTxt',
'tsimplecontent' => 'litepubl\pages\Simple',
'tstaticpages' => 'litepubl\pages\StaticPages',
'tredirector' => 'litepubl\pages\Redirector',
'tsitemap' => 'litepubl\pages\Sitemap',
'tmenus' => 'litepubl\pages\Menus';
'tmenu' => 'litepubl\pages\Menu',
'tfakemenu' => 'litepubl\pages\FakeMenu',
'tsinglemenu' => 'litepubl\pages\SingleMenu',
'thomepage' => 'litepubl\pages\Home',
'tcontactform' => 'litepubl\pages\Contacts',
'tuserpages' => 'litepubl\pages\Users',
'tforbidden' => 'litepubl\pages\Forbidden',
'tnotfound404' => 'litepubl\pages\Notfound404',
'appcache_manifest' => 'litepubl\pages\Appcache',

//utils
'tfiler' => 'litepubl\utils\Filer',
'http' => 'litepubl\utils\Http',
'tmailer' => 'litepubl\utils\Mailer',
'tsmtp' => 'litepubl\utils\Smtp',
'tlinkgenerator' => 'litepubl\utils\LinkGenerator',

//widgets
'tsidebars' => 'litepubl\widget\Sidebars',

//admin
'iadmin' => 'litepubl\admin\AdminInterface',


];