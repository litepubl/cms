<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

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
'tdbmanager' => 'litepubl\core\DBManager',
'tdboptimizer' => 'litepubl\core\DBOptimizer',
'tarray2prop' => 'litepubl\core\Array2prop',

//theme
'Lang' => 'litepubl\view\Lang',
'Langmerger' => 'theme\LangMerger',
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
'tjsmerger' => 'litepubl\view\Js',
'tcssmerger' => 'litepubl\view\Css',
'tadminjsmerger' => 'litepubl\admin\Js',
'tadminviewsgroup' => 'litepubl\admin\views\Group',
'tadminheaders' => 'litepubl\admin\views\Head',
'tadminviewsspec' => 'litepubl\admin\views\Spec',



//posts
'tpost' => 'litepubl\post\Post',
'tposts' => 'litepubl\post\Posts',
'tmetapost' => 'litepubl\post\Meta',
'tposttransform' => 'litepubl\post\Transform',
'tpostfactory' => 'litepubl\post\Factory',
'tfiles' => 'litepubl\post\Files',
'tfileitems' => 'litepubl\post\FileItems',
'tjsonfiles' => 'litepubl\post\JsonFiles',
'tdomrss' => 'litepubl\post\DomRss',
'trss' => 'litepubl\post\Rss',
'tnode' => 'litepubl\post\Node',
'trssMultimedia' => 'litepubl\post\RssFiles',
'tmediaparser' => 'litepubl\post\MediaParser',
'tarchives' => 'litepubl\post\Archives',
'tpinger' => 'litepubl\post\Pinger',

//cats
'tcategories' => 'litepubl\tag\Cats',
'tcommontags' => 'litepubl\tag\Common',
'ttags' => 'litepubl\tag\Tags',
'ttagfactory' => 'litepubl\tag\Factory',
'ttagcontent' => 'litepubl\tag\Content',


//pages
'trobotstxt' => 'litepubl\pages\RobotsTxt',
'tsimplecontent' => 'litepubl\pages\Simple',
'tstaticpages' => 'litepubl\pages\StaticPages',
'tredirector' => 'litepubl\pages\Redirector',
'tsitemap' => 'litepubl\pages\Sitemap',
'tmenus' => 'litepubl\pages\Menus',
'tmenu' => 'litepubl\pages\Menu',
'tfakemenu' => 'litepubl\pages\FakeMenu',
'tsinglemenu' => 'litepubl\pages\SingleMenu',
'thomepage' => 'litepubl\pages\Home',
'tcontactform' => 'litepubl\pages\Contacts',
'tuserpages' => 'litepubl\pages\Users',
'tforbidden' => 'litepubl\pages\Forbidden',
'tnotfound404' => 'litepubl\pages\Notfound404',
'appcache_manifest' => 'litepubl\pages\Appcache',
'tmanifest' => 'litepubl\pages\Manifest',
'tjsonserver' => 'litepubl\pages\Json',

//utils
'tfiler' => 'litepubl\utils\Filer',
'http' => 'litepubl\utils\Http',
'tmailer' => 'litepubl\utils\Mailer',
'tsmtp' => 'litepubl\utils\Smtp',
'TSMTPMailer' => 'litepubl\utils\Smtp',
'tlinkgenerator' => 'litepubl\utils\LinkGenerator',

//perms
'tperm' => 'litepubl\perms\Perm.php',
'tpermgroups' => 'litepubl\perms\Groups',
//'tadminpermgroups' => 'litepubl\admin\PermGroups',
'tperms' => 'litepubl\perms\Perms',
'tsinglepassword' => 'litepubl\perms\Single',
'tpermpassword' => 'litepubl\perms\Password',
'tpasswordpage' => 'litepubl\perms\Page',
'tprivatefiles' => 'litepubl\perms\Files',




//widgets
'twidget' => 'litepubl\widget\Widget',
'twidgets' => 'litepubl\widget\Widgets',
'twidgetscache' => 'litepubl\widget\Cache',
'tcategorieswidget' => 'litepubl\widget\Cats',
'ttagswidget' => 'litepubl\widget\Tags',
'tcommontagswidget' => 'litepubl\widget\CommonTags',
'tarchiveswidget' => 'litepubl\widget\Archives',
'tcommentswidget' => 'litepubl\widget\Comments',
'tcustomwidget' => 'litepubl\widget\Custom',
'tlinkswidget' => 'litepubl\widget\Links',
'tmetawidget' => 'litepubl\widget\Meta',
'torderwidget' => 'litepubl\widget\Order',
'tpostswidget' => 'litepubl\wdiget\Posts',
'tclasswidget' => 'litepubl\widget\Depended',
'tsidebars' => 'litepubl\widget\Sidebars',

//admin
'iadmin' => 'litepubl\admin\AdminInterface',
'tauthor_rights' => 'litepubl\admin\AuthorRights',
'adminform' => 'litepubl\admin\Form',
'tadminhtml' => 'litepubl\admin\Html',
'Table' => 'litepubl\admin\Table',
'tabs' => 'litepubl\admin\Tabs',
'ulist' => 'litepubl\admin\UList',
'thtmltag' => 'litepubl\admin\Tag',
'tadminmenu'=> 'litepubl\admin\Menu',
'tadminmenus'=> 'litepubl\admin\Menus',
'tadminplugins' => 'litepubl\admin\Plugins',

'tadminboard' => 'litepubl\admin\pages\Board',
'tadminform' => 'litepubl\admin\pages\Form',
'tadminlogin' => 'litepubl\admin\pages\Login',
'tadminpassword' => 'litepubl\admin\pages\Password',
'tadminreguser' => 'litepubl\admin\pages\RegUser',
'tadminsubscribers' => 'litepubl\admin\pages\Subscribers',

'tadminoptions' => 'litepubl\admin\options\Options',
'adminhomeoptions' => 'litepubl\admin\options\Home',
'tadminlocalmerger' => 'litepubl\admin\options\LangMerger',
'tadminredirector' => 'litepubl\admin\options\Redirector',
'adminsecure' => 'litepubl\admin\options\Secure',
'adminthemeparser' => 'litepubl\admin\options\ThemeParser',

'tadmincssmerger' => 'litepubl\admin\Css',
'tadmincommentmanager' => 'litepubl\admin\comments\Options',
'tadmincomusers' => 'litepubl\admin\comments\Authors',
'tadminmoderator' => 'litepubl\admin\comments\Moderator',
'tadminpingbacks' => 'litepubl\admin\comments\Pingbacks',

'tadminfiles' => 'litepubl\admin\Files',
'tadminfilethumbnails' => 'litepubl\admin\files\Thumbnails',

'tadminmenumanager' => 'litepubl\admin\menu\Manager',
'tajaxmenueditor' => 'litepubl\admin\menu\Ajax',

'tadminservice' => 'litepubl\admin\service\Service',
'tadminposts' => 'litepubl\admin\posts\Posts',
'tposteditor' => 'litepubl\admin\posts\Editor',
'tajaxposteditor' => 'litepubl\admin\posts\Ajax',
'tadmintags' => 'litepubl\admin\posts\Tags',
'tajaxtageditor' => 'litepubl\admin\posts\TagAjax',
'tadminstaticpages' => 'litepubl\admin\posts\StaticPages',

//admin users
'tadmingroups' => 'litepubl\admin\users\Groups',
'tadminuseroptions' => 'litepubl\admin\users\Options',
'tadminuserpages' => 'litepubl\admin\users\Pages',
'tadminperms' => 'litepubl\admin\users\Perms',
'tadminperm' => 'litepubl\admin\users\Perm',
'tadminpermpassword' => 'litepubl\admin\users\Password',
'tadminpermgroups' => 'litepubl\admin\users\PermGroups',
'tadminusers' => 'litepubl\admin\users\Users',

'tadminviews' => 'litepubl\admin\view\Schemes',

'tadminwidgets' => 'litepubl\admin\widget\Widgets',
'tadminwidget' => 'litepubl\admin\widget\Widget',
'tadminmaxcount' => 'litepubl\admin\widget\MaxCount',
'tadminshowcount' => 'litepubl\admin\widget\ShowCount',
'tadminorderwidget' => 'litepubl\admin\widget\Order',
'addcustomwidget' => 'liteubl\admin\widget\AddCustom',
'tadmincustomwidget' => 'litepubl\admin\widget\Custom',
'tadminlinkswidget' => 'litepubl\admin\widget\Links',
'tadminmetawidget' => 'litepubl\admin\widget\Meta',
'tadmintagswidget' => 'litepubl\admin\widget\Tags',

//comments
'tcomments' => 'litepubl\comments\Comments',
'tcomment' => 'litepubl\comments\Comment',
'tcommentform' => 'litepubl\comments\Form',
'tcommentmanager' => 'litepubl\comments\Manager',
'tjsoncomments' => 'litepubl\comments\Json',
'tsubscribers' => 'litepubl\comments\Subscribers',
'tcommentspool' => 'litepubl\comments\Pool',
'trssholdcomments' => 'litepubl\comments\RssHold',
'tpingbacks' => 'litepubl\comments\Pingbacks',
'ttemplatecomments' => 'litepubl\comments\Templates',

//xmlrpc
'TXMLRPCParser' => 'litepubl\xmlrpc\Parser',
'TXMLRPC' => 'litepubl\xmlrpc\Server',
'TXMLRPCAbstract' => 'litepubl\xmlrpc\Common',
'TXMLRPCAction' => 'litepubl\xmlrpc\Action',
'TXMLRPCBlogger' => 'lib\xmlrpc\Blogger',
'TXMLRPCComments' => 'lib\xmlrpc\Comments.php',
'TXMLRPCLivejournal' => 'litepubl\xmlrpc\Livejournal',
'TXMLRPCMetaWeblog' => 'litepubl\xmlrpc\MetaWeblog',
'TXMLRPCMovableType' => 'litepubl\xmlrpc\MovableType',
'TXMLRPCPingback' => 'litepubl\xmlrpc\Pingback',
'TXMLRPCSystem' => 'litepubl\xmlrpc\System',
'TXMLRPCWordpress' => 'litepubl\xmlrpc\Wordpress',

//updater
'tupdater' => 'litepubl\updater\Updater',
'tbackuper' => 'litepubl\updater\Backuper',
'tftpfiler' => 'litepubl\updater\Ftp',
'tremotefiler' => 'litepubl\updater\Remote',
'tftpsocketfiler' => 'litepubl\updater\FtpSocket',
'Langfiler' => 'litepubl\updater\Local',
'tssh2filer' => 'litepubl\updater\Ssh2',

//plugins
'tblackip' => 'litepubl\plugins\blackip\BlackIP',
'tadminblackip' => 'litepubl\plugins\blackip\AdminBlackIP',

];