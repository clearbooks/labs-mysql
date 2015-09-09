<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 08/09/2015
 * Time: 10:38
 */

namespace Clearbooks\LabsMysql\Toggle\Entity;


use Clearbooks\Labs\Client\Toggle\Entity\Group;

class GroupStub implements Group
{
    const GROUP_ID = "testGroupStub";

    /**
     * @return string
     */
    public function getId()
    {
        return self::GROUP_ID;
    }
}