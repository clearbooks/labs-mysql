<?php

namespace Clearbooks\LabsMysql\User;

use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Clearbooks\LabsMysql\Toggle\MysqlActivatableToggleGateway;
use Clearbooks\Labs\User\UseCase\ToggleStatusModifier;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit_Framework_TestCase;

/**
 * Created by PhpStorm.
 * User: Vovaxs
 * Date: 18/08/2015
 * Time: 11:57
 */
class MysqlUserToggleServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Connection $connection
     */
    private $connection;


    /**
     * @var MysqlToggleStatusModifierService
     */
    private $gateway;

    public function setUp()
    {
        parent::setUp();

        $connectionParams = array(
            'dbname' => 'labs',
            'user' => 'root',
            'password' => '',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        );

        $this->connection = DriverManager::getConnection( $connectionParams, new Configuration() );
        $this->gateway = new MysqlToggleStatusModifierService( $this->connection );
    }

    //tearsdown all the database data created during run time.
    public function tearDown()
    {
        $this->deleteAddedUserActivatedToggles();
        $this->deleteAddedToggles();
        $this->deleteAddedReleases();
    }

    //--------------setToggleStatusForUser--------------//

    /**
     * @test
     */
    public function givenNullPassedAsAToggleIndentifier__MysqlToggleStatusModifierService_ReturnsFalse()
    {
        $response = $this->gateway->setToggleStatusForUser( null, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE, 1 );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenEmptyStringPassedAsAToggleIndentifier__MysqlToggleStatusModifierService_ReturnsFalse()
    {
        $response = $this->gateway->setToggleStatusForUser( "", ToggleStatusModifier::TOGGLE_STATUS_ACTIVE, 1 );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenNullPassedAsAUserIndentifier__MysqlToggleStatusModifierService_ReturnsFalse()
    {
        $response = $this->gateway->setToggleStatusForUser( "test", ToggleStatusModifier::TOGGLE_STATUS_ACTIVE, null );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenInvalidToggleStatus__MysqlToggleStatusModifierService_ReturnsFalse()
    {
        $response = $this->gateway->setToggleStatusForUser( "test", "this is invalid", 1 );
        $this->assertFalse( $response );
    }

    //--------------------ACTIVATION--------------------//

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        $releaseName = 'Test ToggleStatusModifierService 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test1", $id, true );
        $user_id = 1;

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $user_id );

        $this->assertTrue( $response );
        $this->assertIncertedDatabaseData($toggle_id, $user_id, true);
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringActivationAttempt_TogetherWithOtherEntries_MysqlToggleStatusModifierService_DoesNotChangeOtherEntries()
    {
        $releaseName = 'Test ToggleStatusModifierService 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test1.1", $id, true );
        $toggle_id2 = $this->addToggle( "test1.2", $id, true );
        $toggle_id3 = $this->addToggle( "test1.3", $id, true );
        $user_id = 1;
        $user2_id = 2;
        $user3_id = 3;
        $user4_id = 4;

        $this->addUserActivatedToggle( $toggle_id2, $user2_id, false );
        $this->addUserActivatedToggle( $toggle_id3, $user3_id, false );
        $this->addUserActivatedToggle( $toggle_id3, $user4_id, true );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $user_id );

        $this->assertTrue( $response );
        $this->assertIncertedDatabaseData($toggle_id, $user_id, true);
        $this->assertIncertedDatabaseData($toggle_id2, $user2_id, false);
        $this->assertIncertedDatabaseData($toggle_id3, $user3_id, false);
        $this->assertIncertedDatabaseData($toggle_id3, $user4_id, true);

        echo "this End!!";
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        $releaseName = 'Test ToggleStatusModifierService 2';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test2", $id, true );
        $user_id = 1;

        $this->addUserActivatedToggle( $toggle_id, 1, true );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $user_id );
        $this->assertTrue( $response );
        $this->assertIncertedDatabaseData($toggle_id, $user_id, true);
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringActivationAttempt_TogetherWithOtherEntries_MysqlToggleStatusModifierService_DoesNotChangeOtherEntries()
    {
        $releaseName = 'Test ToggleStatusModifierService 2';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test1.1", $id, true );
        $toggle_id2 = $this->addToggle( "test1.2", $id, true );
        $toggle_id3 = $this->addToggle( "test1.3", $id, true );
        $user_id = 1;
        $user2_id = 2;
        $user3_id = 3;
        $user4_id = 4;

        $this->addUserActivatedToggle( $toggle_id, $user_id, true );
        $this->addUserActivatedToggle( $toggle_id2, $user2_id, false );
        $this->addUserActivatedToggle( $toggle_id3, $user3_id, false );
        $this->addUserActivatedToggle( $toggle_id3, $user4_id, true );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $user_id );
        $this->assertTrue( $response );
        $this->assertIncertedDatabaseData($toggle_id, $user_id, true);
        $this->assertIncertedDatabaseData($toggle_id2, $user2_id, false);
        $this->assertIncertedDatabaseData($toggle_id3, $user3_id, false);
        $this->assertIncertedDatabaseData($toggle_id3, $user4_id, true);

        echo "this is end 2!!";
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        $releaseName = 'Test ToggleStatusModifierService 3';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test3", $id, true );
        $user_id = 1;

        $this->addUserActivatedToggle( $toggle_id, 1 );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $user_id );
        $this->assertTrue( $response );
        $this->assertIncertedDatabaseData($toggle_id, $user_id, true);
    }

    //-------------------DEACTIVATION-------------------//

    /**
     *
     * toggle is NOT activated NOR created in user_activated_toggle table
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        $releaseName = 'Test ToggleStatusModifierService 4';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test4", $id, true );
        $user_id = 1;

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $user_id );
        $this->assertTrue( $response );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        $releaseName = 'Test ToggleStatusModifierService 5';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test5", $id, true );
        $user_id = 1;

        $this->addUserActivatedToggle( $toggle_id, 1, true );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $user_id );
        $this->assertTrue( $response );
    }

    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        $releaseName = 'Test ToggleStatusModifierService 6';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test6", $id, true );
        $user_id = 1;

        $this->addUserActivatedToggle( $toggle_id, 1 );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $user_id );
        $this->assertTrue( $response );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function deleteAddedReleases()
    {
        $this->connection->delete( '`release`', [ '*' ] );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function deleteAddedToggles()
    {
        $this->connection->delete( '`toggle`', [ '*' ] );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function deleteAddedUserActivatedToggles()
    {
        $this->connection->delete( '`user_activated_toggle`', [ '*' ] );
    }

    /**
     * @param string $releaseName
     * @param string $url
     * @return string
     */
    private function addRelease( $releaseName, $url )
    {
        ( new MysqlReleaseGateway( $this->connection ) )->addRelease( $releaseName, $url );
        return $this->connection->lastInsertId( "`release`" );
    }

    /**
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @param int $toggle_type
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false, $toggle_type = 1 )
    {
        $this->addToggleToDatabase( $name, $releaseId, $isActive, $toggle_type );
        return $this->connection->lastInsertId( "`toggle`" );
    }

    /**
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @param int $toggle_type
     * @return int
     */
    public function addToggleToDatabase( $name, $releaseId, $isActive, $toggle_type )
    {
        return $this->connection->insert( "`toggle`",
            [ 'name' => $name, 'release_id' => $releaseId, 'toggle_type' => $toggle_type, 'is_active' => $isActive ] );
    }

    /**
     * @param string $toggle_id
     * @param int $user_id
     * @param bool $status
     */
    private function addUserActivatedToggle( $toggle_id, $user_id, $status = false )
    {
        $this->connection->insert( "`user_activated_toggle`",
            [ 'user_id' => $user_id, 'toggle_id' => $toggle_id, 'is_active' => $status ] );
    }

    /**
     * @param string $toggle_id
     * @param int $user_id
     * @return array
     */
    private function getUserActivatedToggleEntry( $toggle_id, $user_id )
    {
        $data = $this->connection->fetchAssoc( 'SELECT * FROM `user_activated_toggle` WHERE toggle_id = ? AND user_id = ?',
            [ $toggle_id, $user_id ] );
        if ( empty( $data ) ) {
            return [ ];
        }
        $entery = [ 1 => $data[ 'toggle_id' ], 2 => $data[ 'user_id' ], 3 => $data[ 'is_active' ] ];
        return $entery;
    }

    private function assertIncertedDatabaseData($toggle_id, $user_id, $isActive = false)
    {
        $expectedEntry = [1=> $toggle_id, 2=> $user_id, 3=> (int)$isActive];
        $actualEntry = $this->getUserActivatedToggleEntry( $toggle_id, $user_id );
        echo "Expected: ";
        print_r($expectedEntry);
        echo "Actual: ";
        print_r($actualEntry);
        $this->assertEquals($expectedEntry, $actualEntry);
    }
}
