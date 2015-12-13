<?php

function twidgetUninstall($self) {
  twidgets::i()->deleteclass(get_class($self));
}