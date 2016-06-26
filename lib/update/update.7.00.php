<?php
namespace 
{
    function update700()
    {
\litepubl\update\update700();
    }
}

namespace litepubl
{
    function update700()
    {
\litepubl\update\update700();
    }
}

namespace litepubl\update
{
    function update700()
    {
        //litepubl::$app->site->jquery_version = '1.12.4';
require (__DIR__ . '/update7/migrate.php');
migrate::run();
}
}