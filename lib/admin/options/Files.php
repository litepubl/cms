<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\admin\options;

use litepubl\post\MediaParser;
use litepubl\view\Lang;

class Files extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $admin = $this->admintheme;
        $args = $this->newArgs();
        $lang = Lang::admin('options');

        $parser = MediaParser::i();
        $args->previewwidth = $parser->previewwidth;
        $args->previewheight = $parser->previewheight;
        $args->previewmode = $this->theme->getRadioItems(
            'previewmode', array(
            'fixed' => $lang->fixedsize,
            'max' => $lang->maxsize,
            'min' => $lang->minsize,
            'none' => $lang->disablepreview,
            ), $parser->previewmode
        );

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

        $args->formtitle = $lang->files;
        return $admin->form(
            $admin->getSection(
                $lang->imagesize, '[checkbox=alwaysresize]
      [text=maxwidth]
      [text=maxheight]
      [text=quality_original]
      
      [checkbox=enablemidle]
      [text=midlewidth]
      [text=midleheight]
'
            ) . $admin->getSection(
                $lang->previewoptions, '$previewmode
      [text=previewwidth]
      [text=previewheight]
      [text=quality_snapshot]
'
            ) . $admin->getSection(
                $lang->extfile, '[text=audioext]
      [text=videoext]
      '
            ), $args
        );
    }

    public function processForm()
    {
        extract($_POST, EXTR_SKIP);
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
    }
}
