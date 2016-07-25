<?php

namespace PhantomInstaller;

class PhantomBinary
{
    const BIN = 'D:\OpenServer\domains\cms.cms\utils\build\test\vendor\bin\phantomjs.exe';
    const DIR = 'D:\OpenServer\domains\cms.cms\utils\build\test\vendor\bin';

    public static function getBin() {
        return self::BIN;
    }

    public static function getDir() {
        return self::DIR;
    }
}
