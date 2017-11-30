<?php

namespace PhantomInstaller;

class PhantomBinary
{
    const BIN = 'C:\OSPanel\domains\cms.cms\utils\build\test\vendor\bin\phantomjs.exe';
    const DIR = 'C:\OSPanel\domains\cms.cms\utils\build\test\vendor\bin';

    public static function getBin() {
        return self::BIN;
    }

    public static function getDir() {
        return self::DIR;
    }
}
