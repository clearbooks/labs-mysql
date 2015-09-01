<?php

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 01/09/2015
 * Time: 14:29
 */

use Clearbooks\Labs\Bootstrap;
use Clearbooks\Labs\Db\DbDIDefinitionProvider;
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../vendor/clearbooks/labs-php-client-mysql/src/Bootstrap.php";
Bootstrap::getInstance()->init( [ DbDIDefinitionProvider::class ] );