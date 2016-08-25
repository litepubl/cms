<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\post;

class Node
{
    public static function attr($node, $name, $value)
    {
        $attr = $node->ownerDocument->createAttribute($name);
        $attr->value = $value;
        $node->appendChild($attr);
        return $attr;
    }

    public static function add($node, $name)
    {
        $result = $node->ownerDocument->createElement($name);
        $node->appendChild($result);
        return $result;
    }

    public static function addvalue($node, $name, $value)
    {
        $result = $node->ownerDocument->createElement($name);
        $textnode = $node->ownerDocument->createTextNode($value);
        $result->appendChild($textnode);
        $node->appendChild($result);
        return $result;
    }

    public static function addcdata($node, $name, $value)
    {
        $result = $node->ownerDocument->createElement($name);
        $textnode = $node->ownerDocument->createCDATASection($value);
        $result->appendChild($textnode);
        $node->appendChild($result);
        return $result;
    }

    public static function copy($node)
    {
        $result = $node->ownerDocument->createElement($node->nodeName);
        foreach ($node->attributes as $value) {
            $result->setAttribute($value->nodeName, $value->value);
        }
        if (!$node->childNodes) {
            return $result;
        }

        foreach ($node->childNodes as $child) {
            if ($child->nodeName == "#text") {
                $result->appendChild($node->ownerDocument->createTextNode($child->nodeValue));
            } else {
                $result->appendChild(static ::copy($child));
            }
        }

        return $result;
    }
}
