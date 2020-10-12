<?php
namespace Clearbooks\LabsMysql\AutoSubscribe;

use Clearbooks\Labs\AutoSubscribe\Gateway\AutoSubscriptionProvider;
use Clearbooks\Labs\LabsTest;
use Clearbooks\LabsMysql\AutoSubscribe\Entity\User;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 03/09/2015
 * Time: 13:15
 */
class MysqlAutoSubscriptionProviderTest extends LabsTest
{
    /**
     * @var AutoSubscriptionProvider
     */
    private $gateway;

    /**
     * @param User $user
     */
    private function addNewSubscriber( User $user )
    {
        $this->connection->insert( '`subscribers`', [ 'user_id' => $user->getId() ] );
    }

    /**
     * @param User[] $subscribers
     */
    protected function addNewSubscribersToDatabase( $subscribers )
    {
        foreach ( $subscribers as $subscriber ) {
            $this->addNewSubscriber( $subscriber );
        }
    }

    /**
     * @param User[] $initialSubscribers
     * @return bool
     */
    private function validateSubscribersDatabase( $initialSubscribers )
    {
        $actualSubscribers = ( new MysqlAutoSubscriberProvider( $this->connection ) )->getSubscribers();
        $this->assertEquals( $initialSubscribers, $actualSubscribers );
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->gateway = new MysqlAutoSubscriptionProvider( $this->connection );
    }

    /**
     * @test
     */
    public function givenNoSubscribers_duringIsSubscribedAttempt_ReturnsFalse()
    {
        $response = $this->gateway->isSubscribed( new User( "test" ) );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenExistentSubscriber_duringIsSubscribedAttempt_ReturnsTrue()
    {
        $user = new User( "YES HE IS THE ONE" );
        $this->addNewSubscriber( $user );

        $response = $this->gateway->isSubscribed( $user );

        $this->assertTrue( $response );
    }

    /**
     * @test
     */
    public function givenExistentSubscribers_duringIsSubscribedAttempt_ReturnsTrueAndDoesNotEffectOtherSubscribers()
    {
        $user = new User( "YES HE IS THE ONE" );
        $subscribers = [ new User( "Brolli" ), new User( "test2" ), $user ];

        $this->addNewSubscribersToDatabase( $subscribers );

        $response = $this->gateway->isSubscribed( $user );

        $this->assertTrue( $response );
        $this->validateSubscribersDatabase( $subscribers );
    }

    /**
     * @test
     */
    public function givenNoExistentSubscriber_duringUpdateSubscriptionAttemptAndTrueGiven_NewUserWillBeAddedToSubscribers()
    {
        $user = new User( "YES HE IS THE ONE" );
        $subscribers = [ new User( "Brolli" ), new User( "test1" ), new User( "test2" ) ];

        $this->addNewSubscribersToDatabase( $subscribers );

        $subscribers [] = $user;

        $this->gateway->updateSubscription( $user, true );

        $this->validateSubscribersDatabase( $subscribers );
    }

    /**
     * @test
     */
    public function givenNoExistentSubscriber_duringUpdateSubscriptionAttemptAndFalseGiven_NoNewSubscribersWillBeCreatedNorDeleted()
    {
        $user = new User( "YES HE IS THE ONE" );
        $subscribers = [ new User( "Brolli" ), new User( "test1" ), new User( "test2" ) ];

        $this->addNewSubscribersToDatabase( $subscribers );

        $this->gateway->updateSubscription( $user, false );

        $this->validateSubscribersDatabase( $subscribers );
    }

    /**
     * @test
     */
    public function givenExistentSubscribers_duringUpdateSubscriptionAttemptAndTrueGiven_NoSubscribersWillBeCreatedNorDeleted()
    {
        $user = new User( "YES HE IS THE ONE" );
        $subscribers = [ new User( "Brolli" ), new User( "test1" ), new User( "test2" ), $user ];

        $this->addNewSubscribersToDatabase( $subscribers );

        $this->gateway->updateSubscription( $user, true );

        $this->validateSubscribersDatabase( $subscribers );
    }

    /**
     * @test
     */
    public function givenExistentSubscribers_duringUpdateSubscriptionAttemptAndFalseGiven_ChosenSubscriberWillBeDeletedFromSubscribers()
    {
        $user = new User( "YES HE IS THE ONE" );
        $subscribers = [ new User( "Brolli" ), new User( "test1" ), new User( "test2" ), $user ];

        $this->addNewSubscribersToDatabase( $subscribers );

        array_pop( $subscribers );

        $this->gateway->updateSubscription( $user, false );

        $this->validateSubscribersDatabase( $subscribers );
    }
}
