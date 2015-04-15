<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function get_themegen_content($self) {
  $result = '';
  tlocal::usefile('themegenerator');
  $lang = tlocal::i('themegenerator');
  $self->colors = $lang->ini['themecolors'];
  
  $tml = '<p>
  <input type="button" name="colorbutton-$name" id="colorbutton-$name" rel="$name" value="' . $lang->selectcolor . '" />
  <input type="hidden" name="color_$name" id="text-color-$name" value="$value" />
  <strong>$label</strong></p>';
  
  $theme = tview::i($self->idview)->theme;
  $args = new targs();
  $a = new targs;
  foreach ($self->colors as $name => $value) {
    $args->name = $name;
    $args->value = $value;
    $args->label = $lang->$name;
    $a->$name = $theme->parsearg($tml, $args);
  }
  
  $a->headerurl = $self->colors['headerurl'];
  $a->logourl = $self->colors['logourl'];
  
  $form = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR  . 'res' . DIRECTORY_SEPARATOR  . 'form.tml');
  $result .= $theme->parsearg($form, $a);
  return sprintf('[html]%s[/html]', $result);
}

function tthemegeneratorInstall($self) {
  if (!ttheme::exists('generator')) die('Theme "generator" not exists');
  //litepublisher::$urlmap->addget('/theme-generator.htm', get_class($self));
  
  $about = tplugins::getabout(tplugins::getname(__file__));
  $name = basename(dirname(__file__));
  $merger = tlocalmerger::i();
  $merger->lock();
  $merger->add('themegenerator', "plugins/$name/res/scheme.ini");
  $merger->add('themegenerator', sprintf('plugins/%s/res/%s.ini', $name, litepublisher::$options->language));
  $merger->unlock();
  
  $js = tjsmerger::i();
  $js->lock();
  $js->add('themegenerator', '/plugins/colorpicker/js/colorpicker.js');
  $js->add('themegenerator', '/js/swfupload/swfupload.js');
  $js->add('themegenerator', sprintf('/plugins/%s/themegenerator.min.js', basename(dirname(__file__))));
  $js->addlang('themegenerator', 'lang', array(
  'upload_header' => $about['upload_header'],
  'upload_logo' => $about['upload_logo']
  ));
  $js->unlock();
  
  $dir = litepublisher::$paths->files . 'themegen';
  if (!is_dir($dir)) {
    mkdir($dir, 0777);
    @chmod($dir, 0777);
    file_put_contents($dir . DIRECTORY_SEPARATOR . 'index.htm', ' ');
    @chmod($dir . DIRECTORY_SEPARATOR . 'index.htm', 0666);
  }
  
  tcron::i()->addnightly(get_class($self), 'cron', null);
  
  $self->lock();
  $self->title = $about['name'];
  $views = tviews::i();
  $self->idview = $views->add($about['name']);
  $view = tview::i($self->idview);
  $view->themename = 'generator';
  
  $self->leftview = $views->add($about['left']);
  $view = tview::i($self->leftview);
  $view->themename = 'generator-left';
  
  $self->rightview = $views->add($about['right']);
  $view = tview::i($self->rightview);
  $view->themename = 'generator-right';
  
  $self->url = '/theme-generator.htm';
  $self->content = get_themegen_content($self);
  $self->parseselectors();
  
  $menus = tmenus::i();
  $menus->lock();
  $menus->add($self);
  
  $fake = new tfakemenu();
  $fake->title = $about['left'];
  $fake->url = $self->url . '?type=left';
  $fake->parent = $self->id;
  $menus->add($fake);
  
  $fake = new tfakemenu();
  $fake->title = $about['right'];
  $fake->url = $self->url . '?type=right';
  $fake->parent = $self->id;
  $menus->add($fake);
  
  $menus->unlock();
  $self->unlock();
  litepublisher::$urlmap->setvalue($self->idurl, 'type', 'get');
}

function tthemegeneratorUninstall($self) {
  if ($self->id == 0) {
    if ($id = $self->getowner()->class2id(get_class($self))) $self->loaddata($id);
  }
  
  $views = tviews::instance();
  $views->delete($self->idview);
  $views->delete($self->leftview);
  $views->delete($self->rightview);
  
  $merger = tlocalmerger::i();
  unset($merger->items['themegenerator']);
  $merger->save();
  
  $js = tjsmerger::i();
  unset($js->items['themegenerator']);
  $js->save();
  
  $template = ttemplate::i();
  unset($template->data['jsmerger_themegenerator']);
  $template->save();
  
  tcron::i()->unbind($self);
  
  $menus = tmenus::i();
  $menus->deletetree($self->id);
  
  tfiler::delete(litepublisher::$paths->files . 'themegen', true, true);
}