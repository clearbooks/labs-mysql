<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 08/09/2015
 * Time: 10:37
 */

namespace Clearbooks\LabsMysql\Toggle\Entity;


use Clearbooks\Labs\Client\Toggle\Entity\User;

class UserStub implements User
{

    const USER_ID = "test1";

    /**
     * @return string
     */
    public function getId()
    {
        return self::USER_ID;
    }
}