<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 03/09/2015
 * Time: 13:16
 */

namespace Clearbooks\LabsMysql\AutoSubscribe;


use Clearbooks\Labs\AutoSubscribe\Entity\User;
use Clearbooks\Labs\AutoSubscribe\Gateway\AutoSubscriptionProvider;

class MysqlAutoSubscriptionProvider implements AutoSubscriptionProvider
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * MysqlAutoSubscriptionProvider constructor.
     * @param Connection $connection
     */
    public function __construct( $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @param User $user
     * @param bool $subscribe
     */
    public function updateSubscription( User $user, $subscribe )
    {
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isSubscribed( $user )
    {
        return null;
    }
}