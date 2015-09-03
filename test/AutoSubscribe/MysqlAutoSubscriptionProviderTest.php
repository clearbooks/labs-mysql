<?php
use Clearbooks\Labs\Bootstrap;
use Clearbooks\LabsMysql\AutoSubscribe\MysqlAutoSubscriptionProvider;
use Doctrine\DBAL\Connection;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 03/09/2015
 * Time: 13:15
 */
class MysqlAutoSubscriptionProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AutoSubscriptionProvider
     */
    private $gateway;

    /**
     * @param string $name
     */
    private function addNewSubscriber( $name )
    {
        $this->connection->insert( '`subscribers`', [ 'user_id' => $name ] );
    }

    public function setUp()
    {
        parent::setUp();

        $this->connection = Bootstrap::getInstance()->getDIContainer()
            ->get( Connection::class );

        $this->connection->beginTransaction();
        $this->connection->setRollbackOnly();

        $this->gateway = new MysqlAutoSubscriptionProvider( $this->connection );
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->connection->rollBack();
    }

    /**
     * @test
     */
    public function duringConstructionOfGateway_GatewayIsNotNull()
    {
        $this->assertNotNull( $this->gateway );
    }

    /**
     * @test
     */
    public function givenNoSubscribers_duringIsSubscribedAttempt_ReturnsFalse()
    {
        $response = $this->gateway->isSubscribed( "test" );
        $this->assertFalse( $response );
    }
}
