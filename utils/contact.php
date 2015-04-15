<?php
$mode = 'contact';
include('index.php');
include('lib/installerclass.php');
 $Installer = &new TInstaller();
 $Installer->CreateMenuItem();
?>