<?php

namespace Clearbooks\LabsMysql\AutoSubscribe\Entity;

use Clearbooks\Labs\AutoSubscribe\Entity\User as IUser;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 03/09/2015
 * Time: 12:16
 */
class User implements IUser
{
    /**
     * @var
     */
    private $userId;

    /**
     * User constructor.
     * @param $userId
     */
    public function __construct( $userId )
    {
        $this->userId = $userId;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->userId;
    }
}