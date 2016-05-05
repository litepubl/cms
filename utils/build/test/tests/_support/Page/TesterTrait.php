<?php
namespace Page;

trait testerTrait
{
    protected $tester;

    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public static function route($param)
    {
        return static::$URL.$param;
    }

}