<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\install;

use litepubl\Config;
use litepubl\comments\Comments;
use litepubl\comments\Manager;
use litepubl\core;
use litepubl\core\Options;
use litepubl\core\Plugins;
use litepubl\core\Router;
use litepubl\core\Users;
use litepubl\core\litepubl;
use litepubl\post\Post;
use litepubl\post\Posts;
use litepubl\utils\Filer;
use litepubl\utils\Mailer;
use litepubl\view\Args;
use litepubl\view\Css;
use litepubl\view\Js;
use litepubl\view\Lang;
use litepubl\view\MainView;
use litepubl\view\Schema;
use litepubl\view\Theme;
use litepubl\view\Vars;
use litepubl\xmlrpc;

class Installer
{
    public $app;
    public $language;
    public $mode;
    public $lite;
public $plugins;
    public $resulttype;
    public $installed;

    public function __construct()
    {
        $this->app = litepubl::$app;
$this->plugins = [];
    }

    public function DefineMode()
    {
        $this->mode = 'form';
        $this->language = $this->GetBrowserLang();
        $this->lite = false;
        $this->installed = false;

        if (isset($_GET) && (count($_GET) > 0)) {
            $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
        }

        $sub = rtrim($_SERVER['REQUEST_URI'], '/');
        if ('/admin' == substr($sub, -6)) {
            $sub = substr($sub, 0, strlen($sub) - 5);
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $sub);
            exit();
        }

        if (!empty($_GET['lang'])) {
            if ($this->langExists($_GET['lang'])) {
                $this->language = $_GET['lang'];
            }
        }

        if (!empty($_GET['mode'])) {
            $this->mode = $_GET['mode'];
        }

        if (!empty($_GET['lite'])) {
            $this->lite = $_GET['lite'] == 1;
        }

        if (!empty($_GET['plugins'])) {
            $this->plugins = explode(',', $_GET['plugins']);
        }

        if (!empty($_GET['resulttype'])) {
            $this->resulttype = $_GET['resulttype'];
        }
    }

    public function autoInstall()
    {
        $this->canInstall();
        $password = $this->firstStep();

        $this->processForm($_GET['email'], $_GET['name'], $_GET['description'], isset($_GET['checkrewrite']));

        $this->createDefaultItems($password);
        if ($this->mode == 'remote') {
            $this->outputResult($password);
        }
    }

    public function outputResult($password)
    {
        if ($this->mode != 'remote') {
            return;
        }

        $this->app->poolStorage->commit();

        $result = [
            'url' => $this->app->site->url,
            'email' => $this->app->options->email,
            'password' => $password,
            'name' => $this->app->site->name,
            'description' => $this->app->site->description
        ];

        switch ($this->resulttype) {
        case 'json':
            $s = json_encode($result, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            header('Content-Type: text/javascript; charset=utf-8');
            break;


        case 'serialized':
            $s = serialize($result);
            header('Content-Type: text/plain; charset=utf-8');
            break;


        case 'xmlrpc':
            include $this->app->paths->lib . 'xmlrpc/IXR.php';
            $r = new \litepubl\xmlrpc\IXR_Value($result);
            $s = '<?xml version="1.0" encoding="utf-8" ?>
      <methodResponse><params><param><value>' . $r->getXml() . '</value></param></params></methodResponse>';

            header('Content-Type: text/xml; charset=utf-8');
            break;


        default:
            die('Unknown remote method');
        }

        header('Connection: close');
        header('Last-Modified: ' . date('r'));
        Header('Cache-Control: no-cache, must-revalidate');
        Header('Pragma: no-cache');
        header('Content-Length: ' . strlen($s));
        echo $s;
        exit();
    }

    public function createDefaultItems($password)
    {
        if ($this->mode != 'remote') {
            $this->congratulation($password);
        }

        if (!$this->lite) {
            $this->CreateFirstPost();
        }

        $this->sendEmail($password);
        return $password;
    }

    public function canInstall()
    {
        $this->checkSystem();
        $this->checkFolders();
    }

    public function firstStep()
    {
        $this->checkFolders();

        return $this->installEngine($_REQUEST['email'], $this->language);
    }

    public function installEngine($email, $language)
    {
        //forward create folders
        @mkdir($this->app->paths->data . 'themes', 0777);
        @chmod($this->app->paths->data . 'themes', 0777);

        $options = Options::i();
        $options->lock();
        include_once dirname(__DIR__) . '/core/install/Options.install.php';
        $password = core\installOptions($email, $language);
        $this->installClasses();
        $options->unlock();
        return $password;
    }

    private function getDefaultArgs(): array
    {
        return [
                '$site.url' => 'http://' . strtolower($_SERVER['HTTP_HOST']),
                '$site.files' => 'http://' . strtolower($_SERVER['HTTP_HOST']),
                '{$site.q}' => '?',
                '$site.q' => '?',
            ];
    }

    public function installClasses()
    {
        $classes = $this->app->classes;
        $this->app->router = Router::i();
        $posts = Posts::i();
        $posts->lock();
        $js = Js::i();
        $js->lock();

        $css = Css::i();
        $css->lock();

        $xmlrpc = xmlrpc\Server::i();
        $xmlrpc->lock();

        //to prevent get properties fron non installet core\Site
            Args::$defaultArgs = $this->getDefaultArgs();
        $theme = Theme::getTheme('default');
            Args::$defaultArgs = null;

        $items = explode("\n", file_get_contents(__DIR__ . '/classes.txt'));
        foreach ($items as $classname) {
            $classname = trim($classname);
            if (!$classname || ($classname[0] == ';')) {
                continue;
            }

            $obj = $classes->getInstance('litepubl\\' . $classname);
            if (method_exists($obj, 'install')) {
                $obj->install();
            }
        }

        //default installed plugins
        $plugins = Plugins::i();
        $plugins->lock();
        $plugins->add('jslogger');
        $plugins->add('likebuttons');
        $plugins->add('oldestposts');
        $plugins->add('photoswipe');
        $plugins->add('photoswipeThumbnail');
        $plugins->add('bootstrap');

foreach ($this->plugins as $name) {
$plugins->add($name);
}
        $plugins->unlock();

        $xmlrpc->unlock();
        $css->unlock();
        $js->unlock();
        $posts->unlock();
    }

    public function run()
    {
        $this->defineMode();
        if ($this->mode != 'form') {
            return $this->autoInstall();
        }

        if (!isset($_POST) || (count($_POST) <= 1)) {
            $this->canInstall();
            return $this->wizardform();
        }

        $password = $this->firstStep();
        $this->processForm($_POST['email'], $_POST['name'], $_POST['description'], isset($_POST['checkrewrite']));
        return $this->createDefaultItems($password);
    }

    public function processForm($email, $name, $description, $rewrite)
    {
        $this->app->options->lock();
        $this->app->options->email = $email;
        $this->app->site->name = $name;
        $this->app->site->description = $description;
        $this->app->options->fromemail = 'litepublisher@' . $_SERVER['SERVER_NAME'];
        $this->CheckApache($rewrite);
        if ($this->app->site->q == '&') {
            $this->app->site->data['url'].= '/index.php?url=';
        }
        $this->app->options->unlock();
    }

    public function CheckFolders()
    {
        $this->checkFolder($this->app->paths->data);
        $this->CheckFolder($this->app->paths->cache);
        $this->CheckFolder($this->app->paths->files);
        //$this->CheckFolder($this->app->paths->languages);
        //$this->CheckFolder($this->app->paths->plugins);
        //$this->CheckFolder($this->app->paths->themes);
        
    }

    public function CheckFolder($folder)
    {
        if (!file_exists($folder)) {
            $up = dirname($folder);
            if (!file_exists($up)) {
                @mkdir($up, 0777);
                @chmod($up, 0777);
            }
            @mkdir($folder, 0777);
        }
        @chmod($folder, 0777);
        if (!file_exists($folder) && !@is_dir($FolderName)) {
            echo "directory $folder is not exists. Please create directory and set permisions to 0777";
            exit();
        }
        $tmp = $folder . 'index.htm';
        if (!@file_put_contents($tmp, ' ')) {
            echo "Error write file to the $folder folder. Please change permisions to 0777";
            exit();
        }
        @chmod($tmp, 0666);
        //@unlink($tmp);
        
    }

    public function CheckSystem()
    {
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            echo 'LitePublisher requires PHP 7.0 or later. You are using PHP ' . PHP_VERSION;
            exit;
        }

        if (!class_exists('domDocument')) {
            echo 'LitePublisher requires "domDocument" class and domxml extension';
            exit;
        }

    }

    public function CheckApache($rewrite)
    {
        if ($rewrite || (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()))) {
            $this->app->site->q = '?';
        } else {
            $this->app->site->q = '&';
        }
    }

    public function wizardform()
    {
        $this->loadlang();
        $combobox = $this->getlangcombo();

        $lang = Lang::i('installation');

        if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
            $checkrewrite = '';
        } else {
            $checkrewrite = file_get_contents($this->app->paths->lib . 'install/templates/modrewrite.tml');
            $checkrewrite = str_replace('$checkrewrite', $lang->checkrewrite, $checkrewrite);
        }

        $domain = Config::$host;
        if (!$domain) {
            $domain = $_SERVER['HTTP_HOST'];
        }

        $domain = \strtolower(\trim($domain));
        if ($domain && \preg_match('/(www\.)?([\w\.\-]+)(:\d*)?/', $domain, $m)) {
            $domain = $m[2];
        }

        $dbprefix = strtolower(
            str_replace(
                [
                '.',
                '-'
                ], '', $domain
            )
        ) . '_';

        $langcode = $this->language;

        $dbaccount = Config::$db ? 'hidden' : '';
        $likeurl = urlencode($lang->homeurl);
        $liketitle = urlencode($lang->homename);

        $form = file_get_contents($this->app->paths->lib . 'install/templates/installform.tml');
        $form = str_replace('"', '\"', $form);
        eval('$form = "' . $form . '\n";');

        $this->echohtml($form);
    }

    private function getLangcombo()
    {
        $langs = [
            'en' => 'English',
            'ru' => 'Russian'
            //'ua' => 'Ukrain'
            
        ];

        $result = '';
        foreach ($langs as $lang => $value) {
            $selected = $lang == $this->language ? 'selected' : '';
            $result.= "<option value='$lang' $selected>$value</option>\n";
        }
        return $result;
    }

    public function CreateFirstPost()
    {
        $lang = Lang::usefile('install');
        $theme = Theme::i();

        $post = Post::i(0);
        $post->title = $lang->posttitle;
        $post->catnames = $lang->postcategories;
        $post->tagnames = $lang->posttags;
        $post->content = $theme->parse($lang->postcontent);
        $posts = Posts::i();
        $posts->add($post);

        $cm = Manager::i();
        $users = Users::i();
        $cm->idguest = $users->add(
            [
            'email' => '',
            'name' => Lang::get('default', 'guest') ,
            'status' => 'hold',
            'idgroups' => 'commentator'
            ]
        );

        $cm->save();
        $users->setvalue($cm->idguest, 'status', 'approved');

        Comments::i()->add($post->id, $cm->idguest, $lang->postcomment, 'approved', '127.0.0.1');
    }

    public function SendEmail($password)
    {
        define('mailpassword', $password);
        register_shutdown_function([$this, 'sendMail']);
    }

    public function sendMail()
    {
        $lang = Lang::$self->ini['installation'];
        $body = sprintf($lang['body'], $this->app->site->url, $this->app->options->email, mailpassword);

        Mailer::sendmail('', $this->app->options->fromemail, '', $this->app->options->email, $lang['subject'], $body);
    }

    public function congratulation($password)
    {
        global $lang;
        $tml = file_get_contents($this->app->paths->lib . 'install/templates/install.congratulation.tml');
        $theme = Theme::getTheme('default');
        $vars = new Vars;
        $vars->template = MainView::i();
        $vars->template->schema = Schema::i(1);
        $vars->template->view = new EmptyView();

        $lang = Lang::i('installation');
        $args = new Args();
        $args->title = $this->app->site->name;
        $args->url = $this->app->site->url . '/';
        $args->email = $this->app->options->email;
        $args->password = $password;
        $args->likeurl = $this->app->options->language == 'ru' ? 'litepublisher.ru' : 'litepublisher.com';
        $content = $theme->parseArg($tml, $args);
        $this->echohtml($content);
    }

    public function uninstall()
    {
        Filer::delete($this->app->paths->data, true);
        Filer::delete($this->app->paths->cache, true);
        Filer::delete($this->app->pathsfiles, true);
    }

    private function loadLang()
    {
        include_once $this->app->paths->lib . 'view/install/Lang.install.php';
        \litepubl\view\LangPreinstall($this->language);
    }

    private function GetBrowserLang()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $a = explode(',', str_replace(';', ',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
            if (in_array('ru', $a) || in_array('ru_ru', $a)) {
                return 'ru';
            }

            foreach ($a as $result) {
                $result = substr($result, 0, 2);
                if ($this->langexists($result)) {
                    return $result;
                }
            }
        }

        return 'en';
    }

    public function langexists($language)
    {
        return @file_exists($this->app->paths->languages . $language . DIRECTORY_SEPARATOR . 'default.ini');
    }

    public function echohtml($html)
    {
        @header('Content-Type: text/html; charset=utf-8');
        @Header('Cache-Control: no-cache, must-revalidate');
        @Header('Pragma: no-cache');
        echo $html;
        if (ob_get_level()) {
            ob_end_flush();
        }
    }
}
