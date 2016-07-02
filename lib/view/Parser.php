<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\view;

use litepubl\core\Str;

class Parser extends BaseParser
{
    private $sidebar_index;

    protected function create()
    {
        parent::create();
        $this->basename = 'themeparser';
        $this->tagfiles[] = 'lib/install/ini/themeparser.ini';
        $this->sidebar_index = 0;
    }

    public function loadPaths(): array
    {
        if (!count($this->tagfiles)) {
            $this->tagfiles[] = 'lib/install/ini/themeparser.ini';
        }

        return parent::loadPaths();
    }

    public function getFileList(string $name): array
    {
        if ($name == 'default') {
                $result = parent::getFileList($name);
        } else {
                $about = $this->getAbout($name);
                $result = [$this->getApp()->paths->themes . $name . '/' . $about['file']];
        }

        return $result;
    }

    public function doReplaceLang(Base $theme)
    {
        parent::doReplaceLang($theme);

        $sidebars = & $theme->templates['sidebars'];
        foreach ($sidebars as $i => $sidebar) {
            foreach ($sidebar as $j => $widget) {
                $sidebars[$i][$j] = $theme->replacelang($widget, $lang);
            }
        }

    }

    public function checkAbout(string $name): bool
    {
        $about = $this->getAbout($name);
        switch ($about['type']) {
        case 'litepublisher3':
        case 'litepublisher':
            $this->error('Litepublisher not supported old themes');
            break;


        default:
            return true;
        }
    }

    public function getParentName(string $name): string
    {
        if ($name == 'default') {
            return '';
        }

        if ($name == 'default-old') {
            return 'default';
        }

        $about = $this->getabout($name);
        return $about['parent'] ?? 'default-old';
    }

    public function getFile($filename)
    {
        if ($s = parent::getfile($filename)) {
            //fix some old tags
            $s = strtr(
                $s, array(
                '$options.url$url' => '$link',
                '$post.categorieslinks' => '$post.catlinks',
                '$post.tagslinks' => '$post.taglinks',
                '$post.subscriberss' => '$post.rsslink',
                '$post.excerptcategories' => '$post.excerptcatlinks',
                '$post.excerpttags' => '$post.excerpttaglinks',
                '$options' => '$site',
                '$template.sitebar' => '$template.sidebar',
                '<!--sitebar-->' => '<!--sidebar-->',
                '<!--/sitebar-->' => '<!--/sidebar-->'
                )
            );
        }

        return $s;
    }

    protected function preparetag($name)
    {
        $name = parent::preparetag($name);
        if (Str::begin($name, 'sidebar')) {
            if (preg_match('/^sidebar(\d)\.?/', $name, $m)) {
                $this->sidebar_index = (int)$m[1];
            } else {
                $this->sidebar_index = 0;
            }

            if (!isset($this->theme->templates['sidebars'][$this->sidebar_index])) {
                $this->theme->templates['sidebars'][$this->sidebar_index] = array();
            }
        }

        return $name;
    }

    protected function setValue($name, $s)
    {
        if (Str::begin($name, 'sidebar')) {
            $this->setwidgetvalue($name, $s);
        } elseif (isset($this->paths[$name])) {
            $this->set_value($name, $s);
        } elseif (($name == '') || ($name == '$template')) {
            $this->theme->templates['index'] = $s;
        } elseif (Str::begin($name, '$custom') || Str::begin($name, 'custom')) {
            $this->setcustom($name, $s);
        } else {
            $this->error("The '$name' tag not found. Content \n$s");
        }
    }

    public function set_value($name, $value)
    {
        $this->parsedtags[] = $name;
        switch ($name) {
        case 'content.menu':
            //fix old ver
            $this->theme->templates['content.author'] = str_replace('menu', 'author', $value);
            break;


        case 'menu.item':
            $this->theme->templates['menu.single'] = $value;
            $this->theme->templates['menu.current'] = $value;
            break;
        }

        $this->theme->templates[$name] = $value;
    }

    public function getInfo($name, $child)
    {
        if (Str::begin($child, '$template.sidebar') && (substr_count($child, '.') == 1)) {
            return array(
                'path' => substr($child, strlen('$template.')) ,
                'tag' => $child,
                'replace' => $child
            );
        }

        if (($name == '') || ($child == '$template')) {
            return 'index';
        }

        if (Str::begin($name, '$template.')) {
            $name = substr($name, strlen('$template.'));
        }
        if ($name == '$template') {
            $name = '';
        }

        foreach ($this->paths as $path => $info) {
            if (Str::begin($path, $name)) {
                if ($child == $info['tag']) {
                    $info['path'] = $path;
                    return $info;
                }
            }
        }

        $path = $name . '.' . substr($child, 1);
        if (Str::begin($name, 'sidebar')) {
            return array(
                'path' => $path,
                'tag' => $child,
                'replace' => $child == '$classes' ? '' : $child
            );
        }

        if (Str::begin($name, '$custom') || Str::begin($name, 'custom')) {
            return array(
                'path' => $path,
                'tag' => $child,
                'replace' => ''
            );
        }

        $this->error("The '$child' not found in path '$name'");
    }

    private function setWidgetValue($path, $value)
    {
        if (!strpos($path, '.')) {
            return;
        }

        if (!preg_match('/^sidebar(\d?)\.(\w\w*+)(\.\w\w*+)*$/', $path, $m)) {
            $this->error("The '$path' is not a widget path");
        }
        $widgetname = $m[2];
        //backward compability deprecated submenu
        if ($widgetname == 'submenu') {
            return;
        }

        if (($widgetname != 'widget') && (!in_array($widgetname, static ::getWidgetNames()))) {
            $this->error("Unknown widget '$widgetname' name");
        }

        $path = $this->getWidgetPath(empty($m[3]) ? '' : $m[3]);
        if ($path === false) {
            $this->error("Unknown '$path' widget path");
        }

        $this->setWidgetItem($widgetname, $path, $value);

        if ($widgetname == 'widget') {
            foreach (static ::getWidgetNames() as $widgetname) {
                if ((($widgetname == 'posts') || ($widgetname == 'comments')) && ($path == '.item')) {
                    continue;
                }

                $this->setwidgetitem($widgetname, $path, $value);
            }
        }
    }

    private function setWidgetitem($widgetname, $path, $value)
    {
        $sidebar = & $this->theme->templates['sidebars'][$this->sidebar_index];
        if (!isset($sidebar[$widgetname])) {
            foreach (array(
                '',
                '.items',
                '.item',
                '.subcount',
                '.subitems'
            ) as $name) {
                $sidebar[$widgetname . $name] = isset($sidebar['widget' . $name]) ? $sidebar['widget' . $name] : '';
            }
            if ($widgetname == 'meta') {
                $sidebar['meta.classes'] = '';
            }
        }

        $sidebar[$widgetname . $path] = $value;
    }

    public function setCustom($path, $value)
    {
        $names = explode('.', $path);
        if (count($names) < 2) {
            return;
        }

        if (($names[0] != '$custom') && ($names[0] != 'custom')) {
            $this->error("The '$path' path is not a custom path");
        }
        $name = $names[1];
        switch (count($names)) {
        case 2:
            $this->theme->templates['custom'][$name] = $value;
            return;

        case 3:
            return;

        case 4:
            $tag = $names[3];
            $admin = & $this->theme->templates['customadmin'];
            if (!isset($admin[$name])) {
                $admin[$name] = array();
            }

            if ($tag == 'values') {
                $value = explode(',', $value);
                foreach ($value as $i => $v) {
                    $value[$i] = trim($v);
                }
            }

            $admin[$name][$tag] = $value;
            return;
        }
    }

    public function afterparse($theme)
    {
        parent::afterparse($theme);

        $templates = & $this->theme->templates;
        $templates['menu.hover'] = isset($templates['menu.hover']) ? ($templates['menu.hover'] == 'true' ? 'true' : ($templates['menu.hover'] == 'bootstrap' ? 'bootstrap' : 'false')) : 'true';

        if (!isset($templates['content.post.templatecomments'])) {
            $templates['content.post.templatecomments'] = '';
        }
        if (!isset($templates['content.post.templatecomments.confirmform'])) {
            echo implode('<br>', array_keys($templates));
            $this->error('template "content.post.templatecomments.confirmform" not exists');
        }

        $post = 'content.post.';
        $excerpt = 'content.excerpts.excerpt.';

        //normalize filelist
        foreach (array(
            'file',
            'image',
            'audio',
            'video',
            'flash'
        ) as $name) {
            if (!isset($templates["{$post}filelist.{$name}s"]) || empty($templates["{$post}filelist.{$name}s"])) {
                $templates["{$post}filelist.{$name}s"] = "\$$name";
            }

            if (!isset($templates["{$excerpt}filelist.$name"])) {
                $templates["{$excerpt}filelist.$name"] = $templates["{$post}filelist.$name"];
            }

            if (!isset($templates["{$excerpt}filelist.{$name}s"])) {
                $templates["{$excerpt}filelist.{$name}s"] = $templates["{$post}filelist.{$name}s"];
            }
        }

        //fix preview
        if (!isset($templates["{$excerpt}filelist.preview"])) {
            $templates["{$excerpt}filelist.preview"] = $templates["{$post}filelist.preview"];
        }

        foreach (array(
            'date',
            'filelist',
            'filelist.file',
            'filelist.image',
            'filelist.preview',
            'filelist.audio',
            'filelist.video',
            'filelist.flash',
            'filelist.files',
            'filelist.images',
            'filelist.audios',
            'filelist.videos',
            'filelist.flashs',
            'catlinks',
            'catlinks.item',
            'catlinks.divider',
            'taglinks',
            'taglinks.item',
            'taglinks.divider'
        ) as $name) {
            if (empty($templates[$excerpt . $name])) {
                $templates[$excerpt . $name] = $templates[$post . $name];
            }
        }

        $sidebars = & $templates['sidebars'];
        for ($i = 0; $i < count($sidebars); $i++) {
            $sidebar = & $sidebars[$i];
            foreach (static ::getWidgetNames() as $widgetname) {
                foreach (array(
                    '',
                    '.items',
                    '.item',
                    '.subcount',
                    '.subitems'
                ) as $name) {
                    if (empty($sidebar[$widgetname . $name])) {
                        $sidebar[$widgetname . $name] = $sidebar['widget' . $name];
                    }
                }

                if (in_array(
                    $widgetname, array(
                    'widget',
                    'categories',
                    'tags',
                    'archives'
                    )
                )) {
                    $v = $sidebar[$widgetname . '.item'];
                    if (!strpos($v, '$subcount')) {
                        $sidebar[$widgetname . '.item'] = str_replace('$subitems', '$subcount$subitems', $v);
                    }
                }
            }

            if (is_string($sidebar['meta.classes'])) {
                $sidebar['meta.classes'] = static ::getmetaclasses($sidebar['meta.classes']);
            }
        }

        //add spaces
        foreach (array(
            'content.excerpts.excerpt.taglinks.divider',
            'content.post.taglinks.divider',
            'content.excerpts.excerpt.catlinks.divider',
            'content.post.catlinks.divider'
        ) as $k) {
            if (substr($templates[$k], -1) != ' ') {
                $templates[$k].= ' ';
            }
        }

        $templates['content.post.templatecomments.confirmform'] = str_replace('$lang.formhead', '$lang.checkspam', $templates['content.post.templatecomments.confirmform']);

        $form = 'content.post.templatecomments.form';
        $templates[$form] = trim(str_replace('<script type="text/javascript" src="$site.files$template.jsmerger_comments"></script>', '', $templates[$form]));
        if (!strpos($templates[$form], '$mesg')) {
            $templates[$form] = '<div id="before-commentform">$mesg</div>' . $templates[$form];
        }

        $regform = 'content.post.templatecomments.regform';
        if (!in_array($regform, $this->parsedtags) && in_array('content.admin.editor', $this->parsedtags)) {
            $editor = strtr(
                $templates['content.admin.editor'], array(
                '$lang.$name' => $this->replacelang ? Lang::i('comment')->content : '$lang.content',
                '$name' => 'content',
                '$value' => ''
                )
            );

            $templates[$regform] = '								<div id="before-commentform">$mesg</div>
      <h4 id="respond">$lang.leavereply</h4>
      <form action="$site.url/send-comment.php" method="post" id="commentform">' . $editor . '<p>
      <input type="hidden" name="postid" value="$postid" />
      <input type="hidden" name="antispam" value="$antispam" />
      
      <input type="submit" name="submitbutton" id="submitcomment" value="' . ($this->replacelang ? Lang::i()->send : '$lang.send') . '" /></p>
      </form>';
        }

        $comment = 'content.post.templatecomments.comments.comment';
        $templates[$comment] = str_replace('$moderate', '<div class="moderationbuttons" data-idcomment="$comment.id" data-idauthor="$comment.author"></div>', $templates[$comment]);

        $this->reuse($templates);
    }

    public static function getMetaclasses($s)
    {
        $result = array(
            'rss' => '',
            'comments' => '',
            'media' => '',
            'foaf' => '',
            'profile' => '',
            'sitemap' => ''
        );
        foreach (explode(',', $s) as $class) {
            if ($i = strpos($class, '=')) {
                $classname = trim(substr($class, 0, $i));
                $value = trim(substr($class, $i + 1));
                if ($value != '') {
                    $result[$classname] = sprintf('class="%s"', $value);
                }
            }
        }
        return $result;
    }

    public function getWidgetPath($path)
    {
        if ($path === '') {
            return '';
        }

        switch ($path) {
        case '.items':
            return '.items';

        case '.items.item':
        case '.item':
            return '.item';

        case '.items.item.subcount':
        case '.item.subcount':
        case '.subcount':
            return '.subcount';

        case '.items.item.subitems':
        case '.item.subitems':
        case '.subitems':
            return '.subitems';

        case '.classes':
        case '.items.classes':
            return '.classes';
        }

        return false;
    }

    public static function getWidgetNames()
    {
        return array(
            'categories',
            'tags',
            'archives',
            'links',
            'posts',
            'comments',
            'friends',
            'meta'
        );
    }
}