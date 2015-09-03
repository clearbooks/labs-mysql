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
use Doctrine\DBAL\Connection;

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
    public function __construct( Connection $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @param User $user
     * @param bool $subscribe
     */
    public function updateSubscription( User $user, $subscribe )
    {
        if ( $subscribe ) {
            if ( !$this->isSubscribed( $user ) ) {
                $this->connection->insert( '`subscribers`', [ 'user_id' => $user->getId() ] );
            }
        } else {
            if ( $this->isSubscribed( $user ) ) {
                $this->connection->delete( '`subscribers`', [ 'user_id' => $user->getId() ] );
            }
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isSubscribed( $user )
    {
        return !empty( $this->getIsUserSubscribed( $user ) );
    }

    /**
     * @param User $user
     * @return array
     */
    protected function getIsUserSubscribed( User $user )
    {
        return $this->connection->fetchAssoc( 'SELECT * FROM `subscribers` WHERE user_id = ?', [ $user->getId() ] );
    }
}