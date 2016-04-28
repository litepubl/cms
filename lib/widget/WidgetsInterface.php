<?php

namespace litepubl\widget;
use litepubl\core\Str;

interface WidgetsInterface
{
public function getWidgets(\ArrayObject $items, $sidebar);
public function getSidebar(Str $str, $sidebar);
}