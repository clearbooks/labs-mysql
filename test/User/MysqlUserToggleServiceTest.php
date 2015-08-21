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

    private function assertInsertedDatabaseData( $toggle_id = "", $user_id = null, $isActive = false, $isEmpty = false )
    {
        $expectedEntry = [ 1 => $toggle_id, 2 => $user_id, 3 => (int) $isActive ];
        if ( $isEmpty ) {
            $expectedEntry = [ ];
        }
        $actualEntry = $this->getUserActivatedToggleEntry( $toggle_id, $user_id );

        $this->assertEquals( $expectedEntry, $actualEntry );
    }

    /**
     * @param bool $toggle_status
     * @param bool $needToBeCreated
     * @return array
     */
    private function addDataToDatabase( $toggle_status = false, $needToBeCreated = true )
    {
        $releaseName = 'Test ToggleStatusModifierService';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test1", $id, true );
        $toggle_id2 = $this->addToggle( "test2", $id, true );
        $toggle_id3 = $this->addToggle( "test3", $id, true );
        $user_id = 1;
        $user2_id = 2;
        $user3_id = 3;
        $user4_id = 4;

        if ( $needToBeCreated ) {
            $this->addUserActivatedToggle( $toggle_id, $user_id, $toggle_status );
        }
        $this->addUserActivatedToggle( $toggle_id2, $user2_id, false );
        $this->addUserActivatedToggle( $toggle_id3, $user3_id, false );
        $this->addUserActivatedToggle( $toggle_id3, $user4_id, true );
        return array( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id );
    }

    /**
     * @param string $toggle_id
     * @param int $user_id
     * @param string $toggle_id2
     * @param int $user2_id
     * @param string $toggle_id3
     * @param int $user3_id
     * @param int $user4_id
     * @param bool $toggle_status
     * @param bool $isEmpty
     */
    private function validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id,
                                           $user4_id, $toggle_status, $isEmpty = false )
    {
        $this->assertInsertedDatabaseData( $toggle_id, $user_id, $toggle_status, $isEmpty );
        $this->assertInsertedDatabaseData( $toggle_id2, $user2_id, false );
        $this->assertInsertedDatabaseData( $toggle_id3, $user3_id, false );
        $this->assertInsertedDatabaseData( $toggle_id3, $user4_id, true );
    }

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

    public function tearDown()
    {
        $this->deleteAddedUserActivatedToggles();
        $this->deleteAddedToggles();
        $this->deleteAddedReleases();
    }

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

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true,
            false );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $user_id );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $user_id );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            true );
    }


    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( false );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $user_id );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true,
            false );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $user_id );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            false, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $user_id );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            false );

    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( false );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $user_id );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            false );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true,
            false );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $user_id );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            true, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $user_id );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            true, true);
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( false );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $user_id );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            false, true);
    }
}
