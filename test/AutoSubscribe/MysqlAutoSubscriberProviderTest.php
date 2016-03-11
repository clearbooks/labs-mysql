<?php
namespace Clearbooks\LabsMysql\AutoSubscribe;

use Clearbooks\Labs\AutoSubscribe\Gateway\AutoSubscriberProvider;
use Clearbooks\Labs\LabsTest;
use Clearbooks\LabsMysql\AutoSubscribe\Entity\User;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 03/09/2015
 * Time: 11:06
 */
class MysqlAutoSubscriberProviderTest extends LabsTest
{
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
        $this->gateway = new MysqlAutoSubscriberProvider( $this->connection );
    }

    /**
     * @test
     */
    public function givenNoSubscribers_ReturnEmptyArray()
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
}
