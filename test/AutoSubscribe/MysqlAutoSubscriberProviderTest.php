<?php
use Clearbooks\Labs\AutoSubscribe\Gateway\AutoSubscriberProvider;
use Clearbooks\Labs\Bootstrap;
use Clearbooks\LabsMysql\AutoSubscribe\Entity\User;
use Clearbooks\LabsMysql\AutoSubscribe\MysqlAutoSubscriberProvider;
use Doctrine\DBAL\Connection;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 03/09/2015
 * Time: 11:06
 */
class MysqlAutoSubscriberProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AutoSubscriberProvider
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

        $this->gateway = new MysqlAutoSubscriberProvider( $this->connection );
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
    public function givenNoSuscribers_ReturnEmptyArray()
    {
        $response = $this->gateway->getSubscribers();
        $this->assertEquals( [ ], $response );
    }

    /**
     * @test
     */
    public function givenExistentSubscribers_ReturnsArrayOfUsers()
    {
        $this->addNewSubscriber( "brolli" );
        $this->addNewSubscriber( "user1" );
        $this->addNewSubscriber( "user2" );

        $expectedSubscribers = [ new User( "brolli" ), new User( "user1" ), new User( "user2" ) ];
        $response = $this->gateway->getSubscribers();

        $this->assertEquals( $expectedSubscribers, $response );
    }

    /**
     * @test
     */
    public function givenExistentSubscribers_whenGettingCallingGetID_ReturnsCorrectId()
    {
        $subscriberName = "TheSubscribes";
        $subscriber = new User( $subscriberName );
        $this->assertEquals( $subscriberName, $subscriber->getId() );
    }
}
