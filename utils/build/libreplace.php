<?php

function libReplace($s) 
{
    $s = str_replace("\r\n", "\n", $s);
    $s = strtr(
        $s, [
        'litepubl::$secret' => 'Config::$secret',
        'litepubl::$debug' => 'Config::$debug',
        'config::$' => 'config::$',
        '$urlmap' => '$router',
        'idview' => 'idschema',
        'tviews::' =>'Schemas::',
        'tview::' => 'Schema::',
        '$views' => '$schemes',
        '$view' => '$schema',
        'litepubl::$app' => 'litepubl@app',
        'turlmap::unsub' => '$this->getApp()->router->unbind',
        ]
    );

    $s = strtr(
        $s, [
        'turlmap::' => '\litepubl\core\Router::',
        'litepubl::$' => ' $this->getApp()->',
        'litepubl@app' => 'litepubl::$app',
        'tlocal' => 'Lang',
        'new targs' => 'new Args',
        'targs::i()' => 'new Args()',
        'tadminhtml::array2combo' => '$this->theme->comboItems',
        '$form->items ' => '$form->body ',
        'tablebuilder' => 'Table',
        ' Ulist' => ' UList',
        'processform' => 'processForm',
        'doprocessform' => 'doProcessForm',
        'DataStorage' => 'PoolStorage',
        'Schemas' => 'Schemes',
        ]
    );

    $s = insertUse($s, 'Config::', 'litepubl\Config');
    $s = renameFunctions($s);
    //$s = replaceIfReturn($s);
    $s = camelCase($s);
    return $s;
}

function renameFunctions($s) 
{
    $s = strtr(
        $s, [
        'sqldate' => 'Str::sqlDate',
        'sqltime' => 'Str::sqlTime',
        'dbquote' => 'Str::quote',
        'Str::uuote' => 'Str::quote',
        'md5rand' => 'Str::md5Rand',
        'md5uniq' => 'Str::md5Uniq',
        'basemd5' => 'Str::baseMd5',
        'strbegin' => 'Str::begin',
        'strbegins' => 'Str::begins',
        'strend' => 'Str::end',
        'strip_utf' => 'Str::trimUtf',
        'array_delete' => 'Arr::delete',
        'array_delete_value' => 'Arr::deleteValue',
        'array_clean' => 'Arr::clean',
        'array_insert' => 'Arr::insert',
        'array_move' => 'Arr::move',
        'strtoarray' => 'Str::toArray',
        'tojson' => 'Str::toJson',
        'jsonattr' => 'Str::jsonAttr',
        'toenum' => 'Arr::toEnum',
        'tdatabase::str2array' => 'Str::toIntArray',
        'dumpstr' => 'Str::dump',
        'dumpvar' => 'Str::log',
        ]
    );

    if (false === strpos($s, 'namespace litepubl\core')) {
        $s = insertUse($s, 'Str::', 'litepubl\core\Str');
        $s = insertUse($s, 'Arr::', 'litepubl\core\Arr');
    }

    return $s;
}

function insertUse($s, $fnc, $ns) 
{
    if (!strpos($s, $fnc)) { return $s; 
    }
    $s = removeUse($s, $fnc, $ns);

    $uns = "use $ns;";
    if (strpos($s, $uns)) { return $s; 
    }

    $root = substr($ns, 0, strrpos($ns, '\\'));
    if (strpos($s, "namespace $root;")) { return $s; 
    }

    $i = strpos($s, "\n\n", strpos($s, 'namespace '));
    if (!$i) {
        echo "Cant insert $uns<br>";
        return$s;
    }

    $s = substr($s, 0, $i) . "\n" . $uns . substr($s, $i);
    return $s;
}

function removeUse($s, $fnc, $ns) 
{
    if (!strpos($s, $fnc)) { return $s; 
    }
    $uns = "use $ns;";
    if (false === ($i = strpos($s, $uns))) { return $s; 
    }

    $root = substr($ns, 0, strrpos($ns, '\\'));
    if (!strpos($s, "namespace $root;")) { return $s; 
    }

    $s = rtrim(substr($s, 0, $i)) . substr($s, $i + strlen($uns));
    return $s;
}

function sortUse($s) 
{
    $i = strpos($s, 'namespace ');
    if (!$i) { return $s; 
    }

    $i = strpos($s, ';', $i) + 1;
    $s2 = ltrim(substr($s, $i));
    if ('use ' != substr($s2, 0, 4)) {
        return $s;
    }

    $j = strpos($s2, "\n\n");
    if (!$j) { return $s; 
    }
    $sort = explode("\n", trim(substr($s2, 0, $j)));
    sort($sort);

    return substr($s, 0, $i) . "\n\n" . implode("\n", $sort) . "\n\n" . ltrim(substr($s2, $j));
}

function replaceIfReturn($str) 
{
    $a = explode("\n", $str);
    foreach ($a as $i => $s) {
        if (strpos($s, ' if (')  
            && (($j = strpos($s, ' return')))  
            || ($j = strpos($s, ' continue'))
        ) {
            $s = substr($s, 0, $j) . " {\n" . substr($s, $j) . "\n}\n\n";
            $a[$i] = $s;
        }
    }

    return implode("\n", $a);
}

function camelCase($s) 
{
    $a = explode("\n", $s);
    $func = ' function ';
    foreach ($a as $i => $s) {
        if ($j = strpos($s, $func)) {
            $s2 = ltrim(substr($s, $j + strlen($func)));
            if (str_begin($s2, 'get') || str_begin($s2, 'set')) {
                $s2[3] = strtoupper($s2[3]);
                $a[$i] = substr($s, 0, $j) . $func . $s2;
            }
        }
    }

    return implode("\n", $a);
}

/*
//if ($php && ! strbegin($s, 'namespace')) {
if ($php) {
$s = str_replace(
"namespace litepubl\plugins;\nuse litepubl;\n\n",
"namespace litepubl;\n\n",
$s);
}
*/

function str_begin($s, $begin) 
{
    return strncmp($s, $begin, strlen($begin)) == 0;
}

function afterFix($s) 
{
    $s = strtr(
        $s, [
        'SharedStorage' => 'PoolStorage',
        'sharedStorage' => 'poolStorage',
        'Schemas' => 'Schemes',
        'ttheme::clearcache' => 'Base::clearCache',
        'tjsmerger::' => 'Js::',
        'tcssmerger::' => 'Css::',
        'Langmerger' => 'LangMerger',
        'tplugins::' => 'Plugins::',
        ' iadmin' => ' \litepubl\admin\AdminInterface',
        'extends tadminmenu' => "extends \\litepubl\\admin\\Menu\n",
        'extends tplugin' => "extends \\litepubl\\core\\Plugin\n",
        'ttheme::' => 'Theme::',
        'tthemeparser' => 'Parser',
        'tdbmanager' => 'DBManager',
        //'ttemplate:' => 'MainView:',
        'Str::uuote' => 'Str::quote',
        ]
    );

    $s = insertUse($s, 'Lang::', 'litepubl\view\Lang');
    $s = insertUse($s, 'Args::', 'litepubl\view\Args');
    $s = insertUse($s, 'new Args', 'litepubl\view\Args');
    $s = insertUse($s, 'Base::', 'litepubl\view\Base');
    $s = insertUse($s, 'Js::', 'litepubl\view\Js');
    $s = insertUse($s, 'Css::', 'litepubl\view\Css');
    $s = insertUse($s, 'LangMerger::', 'litepubl\view\LangMerger');
    $s = insertUse($s, 'Plugins::', 'litepubl\core\Plugins');
    $s = insertUse($s, 'AdminInterface', 'litepubl\admin\AdminInterface');
    $s = insertUse($s, 'Theme::', 'litepubl\view\Theme');
    $s = insertUse($s, 'Parser:', 'litepubl\view\Parser');
    $s = insertUse($s, 'DBManager:', 'litepubl\core\DBManager');
    $s = insertUse($s, 'MainView:', 'litepubl\view\MainView');

    if (strpos($s, 'tadminmenus')) {
        $s = str_replace('tadminmenus', 'Menus', $s);
        $s = insertUse($s, 'AdminMenus::', 'litepubl\admin\Menus as AdminMenus');
    }

    return $s;
}

function afterFix2($s) 
{
    $s = strtr(
        $s, [
        '->getApp()->router->clearcache()' => '->getApp()->cache->clear()',
        'BaseTheme' => 'Base',
        ' targs ' => ' Args ',
        'ttemplate:' => 'MainView:',
        'tcontentfilter:' => 'Filter:',
        ' getinstance(' => ' static::iGet(',
        'tlinkgenerator::' => 'LinkGenerator::',
        '$this->getApp()->router->onclose' => '$this->getApp()->onClose->on(',
        'Schema::getView' => 'Schema::getSchema',
        'childdata' => 'childData',
        'childtable' => 'childTable',
        'itemexists' => 'itemExists',
        'parsearg' => 'parseArg',
        'setstruct' => 'setStruct',
        'router->page' => 'context->request->page',
        'router->redir' => 'context->response->redir',
        'getvalue(' => 'getValue(',
        'setvalue(' => 'setValue(',
        'getdb(' => 'getDB(',
        '$this->view->admintheme' => '$this->admintheme',
        'http::get' => 'Http::get',
        'http::post' => 'Http::post',
        'column_exists' => 'columnExists',
        ]
    );

    $s = insertUse($s, 'MainView:', 'litepubl\view\MainView');
    $s = insertUse($s, 'Filter:', 'litepubl\view\Filter');
    $s = insertUse($s, 'LinkGenerator:', 'litepubl\utils\LinkGenerator');
    //$s = insertUse($s, 'ArrayObject', 'ArrayObject');

    $s = sortUse($s);
    return $s;
}
