<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\core;

class StorageInc extends Storage
{
    private $opcacheEnabled;

    public function __construct()
    {
        $this->opcacheEnabled = ini_get('opcache.enable') && !ini_get('opcache.restrict_api');
    }

    public function getExt(): string
    {
        return '.inc.php';
    }

    public function serialize(array $data): string
    {
        return \var_export($data, true);
    }

    public function unserialize(string $str)
    {
        $this->error('Call unserialize');
    }

    public function before(string $str): string
    {
        return \sprintf('<?php return %s;', $str);
    }

    public function after(string $str): string
    {
        $this->error('Call after method');
    }

    public function loadData(string $filename)
    {
        if (\file_exists($filename . $this->getExt())) {
            return include $filename . $this->getExt();
        }

        return false;
    }

    public function loadFile(string $filename)
    {
        $this->error('Call loadfile');
    }

    public function saveFile(string $filename, string $content): bool
    {
        if (parent::saveFile($filename, $content)) {
            if ($this->opcacheEnabled) {
                opcache_compile_file($filename . $this->getExt());
            }

                return true;
        }

        return false;
    }
}
