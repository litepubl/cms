<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\photoswipeThumbnail;

use litepubl\post\Files;
use litepubl\post\MediaParser;
use litepubl\view\Css;
use litepubl\view\Js;
use litepubl\view\Parser;

class PhotoSwipeThumbnail extends \litepubl\core\Plugin
{

    public function install()
    {
        $this->addJs();

        $parser = MediaParser::i();
        $parser->previewwidth = 120;
        $parser->previewheight = 120;
        $parser->previewmode = 'min';
        $parser->save();

        $this->rescale();
    }

    public function uninstall()
    {
        $this->deleteJs();

        $parser = MediaParser::i();
        $parser->previewwidth = 120;
        $parser->previewheight = 120;
        $parser->previewmode = 'fixed';
        $parser->save();

        $this->rescale();
    }

    public function addJs()
    {
        $plugindir = basename(dirname(__file__));
        $js = Js::i();
        $js->add('default', "plugins/$plugindir/resource/thumbnails.min.js");

        $css = Css::i();
        $css->add('default', "plugins/$plugindir/resource/thumbnails.min.css");
    }

    public function deleteJs()
    {
        $plugindir = basename(dirname(__file__));
        $js = Js::i();
        $js->deleteFile('default', "plugins/$plugindir/resource/thumbnails.min.js");

        $css = Css::i();
        $css->deleteFile('default', "plugins/$plugindir/resource/thumbnails.min.css");
    }

    public function rescale()
    {
        $parser = MediaParser::i();
        $files = Files::i();
        $db = $files->db;
        $t = $files->thistable;

        $items = $db->res2assoc(
            $db->query(
                "
    select  files.id as id, files.filename as filename, thumbs.id as idthumb, thumbs.filename as filenamethumb
    from $t  files, $t thumbs
    where files.preview > 0 and thumbs.id = files.preview
    "
            )
        );

        foreach ($items as $i => $item) {
            $srcfilename = $this->getApp()->paths->files . $item['filename'];
            $destfilename = $this->getApp()->paths->files . $item['filenamethumb'];
            $image = MediaParser::readimage($srcfilename);
            if ($size = MediaParser::createthumb($image, $destfilename, $parser->previewwidth, $parser->previewheight, $parser->quality_snapshot, $parser->previewmode)) {
                imagedestroy($image);

                $db->updateassoc(
                    [
                    'id' => $item['idthumb'],
                    'width' => $size['width'],
                    'height' => $size['height']
                    ]
                );
            }
        }
    }
}
