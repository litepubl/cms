<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\core;

class StorageInc extends Storage
{

    public function getExt()
    {
        return '.inc.php';
    }

    public function serialize(array $data)
    {
        return \var_export($data, true);
    }

    public function unserialize($str)
    {
        $this->error('Call unserialize');
    }

    public function before($str)
    {
        return \sprintf('<?php return %s;', $str);
    }

    public function after($str)
    {
        $this->error('Call after method');
    }

    public function loadData($filename)
    {
        if (\file_exists($filename . $this->getExt())) {
            return include ($filename . $this->getExt());
        }

        return false;
    }

    public function loadFile($filename)
    {
        $this->error('Call loadfile');
    }

}

