<?php

use Step\Acceptance\Install;
use Page\Install as InstallPage;

$I = new Install($scenario);
$I->switchLanguages();
$page = new InstallPage($I);
$page->fillForm();
$I->wantTo('perform actions and see result');

$