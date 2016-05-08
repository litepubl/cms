<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\options;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\view\Base;
use litepubl\view\Theme;
use litepubl\utils\Filer;
use litepubl\view\MainView;
use litepubl\utils\LinkGenerator;
use litepubl\post\MediaParser;

class Options extends \litepubl\admin\Menu
{
    public function getContent() {
        $options =  $this->getApp()->options;
        $template = MainView::i();
        Theme::$vars['template'] = $template;
        $result = '';
        $args = new Args();
        $lang = Lang::admin('options');
        $admin = $this->admintheme;

        switch ($this->name) {
            case 'options':
                $site =  $this->getApp()->site;
                $args->fixedurl = $site->fixedurl;
                $args->redirdom =  $this->getApp()->router->redirdom;
                $args->url = $site->url;
                $args->name = $site->name;
                $args->description = $site->description;
                $args->keywords = $site->keywords;
                $args->author = $site->author;
                $args->footer = $template->footer;

                $args->formtitle = $lang->options;
                return $admin->form('
      [checkbox=fixedurl]
      [checkbox=redirdom]
      [text=url]
      [text=name]
      [text=description]
      [text=keywords]
      [text=author]
      [editor=footer]
      ', $args);

                break;


            case 'files':
                $parser = MediaParser::i();
                $args->previewwidth = $parser->previewwidth;
                $args->previewheight = $parser->previewheight;
                $args->previewmode = $this->theme->getRadioItems('previewmode', array(
                    'fixed' => $lang->fixedsize,
                    'max' => $lang->maxsize,
                    'min' => $lang->minsize,
                    'none' => $lang->disablepreview,
                ) , $parser->previewmode);

                $args->maxwidth = $parser->maxwidth;
                $args->maxheight = $parser->maxheight;
                $args->alwaysresize = $parser->alwaysresize;

                $args->enablemidle = $parser->enablemidle;
                $args->midlewidth = $parser->midlewidth;
                $args->midleheight = $parser->midleheight;

                $args->quality_original = $parser->quality_original;
                $args->quality_snapshot = $parser->quality_snapshot;

                $args->audioext = $parser->audioext;
                $args->videoext = $parser->videoext;

                $args->video_width =  $this->getApp()->site->video_width;
                $args->video_height =  $this->getApp()->site->video_height;

                $args->formtitle = $lang->files;
                return $admin->form('
      <h4>$lang.imagesize</h4>
      [checkbox=alwaysresize]
      [text=maxwidth]
      [text=maxheight]
      [text=quality_original]
      
      [checkbox=enablemidle]
      [text=midlewidth]
      [text=midleheight]
      
      <h4>$lang.previewoptions</h4>
      $previewmode
      [text=previewwidth]
      [text=previewheight]
      [text=quality_snapshot]
      
      <h4>$lang.extfile</h4>
      [text=audioext]
      [text=videoext]
      
      [text=video_width]
      [text=video_height]
      ', $args);
                break;


            case 'links':
                $linkgen = LinkGenerator::i();
                $args->urlencode = $linkgen->urlencode;
                $args->post = $linkgen->post;
                $args->menu = $linkgen->menu;
                $args->category = $linkgen->category;
                $args->tag = $linkgen->tag;
                $args->archive = $linkgen->archive;

                $args->formtitle = $lang->schemalinks;
                return $admin->form('
      <p>$lang.taglinks</p>
      [checkbox=urlencode]
      [text=post]
      [text=menu]
      [text=category]
      [text=tag]
      [text=archive]
      ', $args);

            case 'cache':
                $args->enabledcache = $options->cache;
                $args->expiredcache = $options->expiredcache;
                $args->admincache = $options->admincache;
                $args->ob_cache = $options->ob_cache;
                $args->compress = $options->compress;
                $args->commentspool = $options->commentspool;

                $args->formtitle = $lang->optionscache;
                $result = $admin->form('
      [checkbox=enabledcache]
      [text=expiredcache]
      [checkbox=ob_cache]
      [checkbox=admincache]
      [checkbox=commentspool]
      ', $args);

                $form = new adminform($args);
                $form->submit = 'clearcache';
                $result.= $form->get();
                return $result;

            case 'catstags':
            case 'lite': //old version suports
                $cats =  $this->getApp()->classes->categories;
                $args->parentcats = $cats->includeparents;
                $args->childcats = $cats->includechilds;

                $tags =  $this->getApp()->classes->tags;
                $args->parenttags = $tags->includeparents;
                $args->childtags = $tags->includechilds;
                $lang = Lang::admin('options');
                $args->formtitle = $lang->catstags;
                $admin = $this->admintheme;
                return $admin>form('
      [checkbox=parentcats] [checkbox=childcats]
      [checkbox=parenttags] [checkbox=childtags]', $args);

            case 'robots':
                $admin = $this->admintheme;
                $args->formtitle = 'robots.txt';
                $args->robots = trobotstxt::i()->text;
                $args->appcache = appcache_manifest::i()->text;
                $tabs = new tabs($this->admintheme);
                $tabs->add('robots.txt', '[editor=robots]');
                $tabs->add('manifest.appcache', '[editor=appcache]');
                return $admin->form($tabs->get() , $args);
        }

        return $result;
    }

    public function processForm() {
        extract($_POST, EXTR_SKIP);
        $options =  $this->getApp()->options;

        switch ($this->name) {
            case 'options':
                 $this->getApp()->router->redirdom = isset($redirdom);
                $site =  $this->getApp()->site;
                $site->fixedurl = isset($fixedurl);
                $site->url = $url;
                $site->name = $name;
                $site->description = $description;
                $site->keywords = $keywords;
                $site->author = $author;
                $this->getdb('users')->setvalue(1, 'name', $author);
                MainView::i()->footer = $footer;
                break;


            case 'files':
                $parser = MediaParser::i();
                $parser->previewmode = $previewmode;
                $parser->previewwidth = (int)trim($previewwidth);
                $parser->previewheight = (int)trim($previewheight);

                $parser->maxwidth = (int)trim($maxwidth);
                $parser->maxheight = (int)trim($maxheight);
                $parser->alwaysresize = isset($alwaysresize);

                $parser->quality_snapshot = (int)trim($quality_snapshot);
                $parser->quality_original = (int)trim($quality_original);

                $parser->enablemidle = isset($enablemidle);
                $parser->midlewidth = (int)trim($midlewidth);
                $parser->midleheight = (int)trim($midleheight);

                $parser->audioext = trim($audioext);
                $parser->videoext = trim($videoext);

                $parser->save();

                 $this->getApp()->site->video_width = $video_width;
                 $this->getApp()->site->video_height = $video_height;
                break;


            case 'links':
                $linkgen = LinkGenerator::i();
                $linkgen->urlencode = isset($urlencode);
                if (!empty($post)) {
                    $linkgen->post = $post;

                }

                if (!empty($menu)) {
                    $linkgen->menu = $menu;
                }

                if (!empty($category)) {
                    $linkgen->category = $category;
                }

                if (!empty($tag)) {
                    $linkgen->tag = $tag;
                }

                if (!empty($archive)) {
                    $linkgen->archive = $archive;
                }

                $linkgen->save();
                break;


            case 'cache':
                if (isset($clearcache)) {
                    Base::clearCache();
                } else {
                    $options->lock();
                    $options->cache = isset($enabledcache);
                    $options->admincache = isset($admincache);
                    if (!empty($expiredcache)) {
                        $options->expiredcache = (int)$expiredcache;
$options->filetime_offset = Filer::getFiletimeOffset();
                    }

                    $options->ob_cache = isset($ob_cache);
                    $options->commentspool = isset($commentspool);
                    $options->unlock();
                }
                break;


            case 'lite':
            case 'catstags':
                $cats =  $this->getApp()->classes->categories;
                $cats->includeparents = isset($parentcats);
                $cats->includechilds = isset($childcats);
                $cats->save();

                $tags =  $this->getApp()->classes->tags;
                $tags->includeparents = isset($parenttags);
                $tags->includechilds = isset($childtags);
                $tags->save();
                break;


            case 'robots':
                $robo = trobotstxt::i();
                $robo->text = $robots;
                $robo->save();

                $appcache_manifest = appcache_manifest::i();
                $appcache_manifest->text = $appcache;
                $appcache_manifest->save();
                break;
            }

            return '';
        }

}