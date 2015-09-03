<?php
namespace Clearbooks\LabsMysql\AutoSubscribe;

use Clearbooks\Labs\AutoSubscribe\Entity\User;
use Clearbooks\Labs\AutoSubscribe\Gateway\AutoSubscriberProvider;
use Doctrine\DBAL\Connection;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 03/09/2015
 * Time: 11:11
 */
class MysqlAutoSubscriberProvider implements AutoSubscriberProvider
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * MysqlAutoSubscriberProvider constructor.
     * @param Connection $connection
     */
    public function __construct( Connection $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @return User[]
     */
    public function getSubscribers()
    {
        return [ ];
    }
}