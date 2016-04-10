<?php

namespace litepubl\core;

class DateFormater
 {
    public $date;

    public function __construct($date) {
        $this->date = $date;
    }

    public function __get($name) {
        return Lang::translate(date($name, $this->date) , 'datetime');
    }

}
