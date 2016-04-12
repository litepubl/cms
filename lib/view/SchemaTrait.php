<?php

namespace litepubl\view;

trait SchemaTrait
 {

public function getschema() {
return Schema::getSchema($this);
}
}