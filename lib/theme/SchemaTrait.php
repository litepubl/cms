<?php

namespace litepubl\theme;

trait SchemaTrait
 {

public function getschema() {
return Schema::getSchema($this);
}
}