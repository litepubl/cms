<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\view;

interface ViewInterface extends \litepubl\core\ResponsiveInterface
{
    public function getTitle(): string;
    public function getKeywords(): string;
    public function getDescription(): string;
    public function getHead(): string;
    public function getCont(): string;
    public function getIdSchema(): int;
    public function setIdSchema(int $id);
}
