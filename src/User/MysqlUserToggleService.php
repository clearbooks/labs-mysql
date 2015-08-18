<?php

namespace Clearbooks\LabsMysql\User;
use Clearbooks\Labs\User\UseCase\UserToggleService;

/**
 * Created by PhpStorm.
 * User: Vovaxs
 * Date: 18/08/2015
 * Time: 12:26
 */
class MysqlUserToggleService implements UserToggleService
{

    /**
     * MysqlUserToggleService constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $toggleIdentifier
     * @param int $userIdentifier
     * @return bool
     */
    public function activateToggle($toggleIdentifier, $userIdentifier)
    {
        return false;
    }

    /**
     * @param string $toggleIdentifier
     * @param int $userIdentifier
     * @return bool
     */
    public function deActivateToggle($toggleIdentifier, $userIdentifier)
    {
        // TODO: Implement deActivateToggle() method.
    }
}