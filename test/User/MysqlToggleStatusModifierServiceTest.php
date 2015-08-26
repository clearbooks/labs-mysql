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
     * @throws InvalidArgumentException
     */
    private function deleteAddedGroupActivatedToggles()
    {
        $this->connection->delete( '`group_activated_toggle`', [ '*' ] );
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
     * @param string $user_id
     * @param bool $status
     */
    private function addUserActivatedToggle( $toggle_id, $user_id, $status = false )
    {
        $this->connection->insert( "`user_activated_toggle`",
            [ 'user_id' => $user_id, 'toggle_id' => $toggle_id, 'is_active' => $status ] );
    }

    /**
     * @param string $toggle_id
     * @param string $user_id
     * @param bool $status
     */
    private function addGroupActivatedToggle( $toggle_id, $user_id, $status = false )
    {
        $this->connection->insert( "`group_activated_toggle`",
            [ 'group_id' => $user_id, 'toggle_id' => $toggle_id, 'active' => $status ] );
    }

    /**
     * @param string $toggle_id
     * @param string $user_id
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

    /**
     * @param string $toggle_id
     * @param string $group_id
     * @return array
     */
    private function getGroupActivatedToggleEntry( $toggle_id, $group_id )
    {
        $data = $this->connection->fetchAssoc( 'SELECT * FROM `group_activated_toggle` WHERE toggle_id = ? AND group_id = ?',
            [ $toggle_id, $group_id ] );
        if ( empty( $data ) ) {
            return [ ];
        }
        $entery = [ 1 => $data[ 'toggle_id' ], 2 => $data[ 'group_id' ], 3 => $data[ 'active' ] ];
        return $entery;
    }

    /**
     * @param string $toggle_id
     * @param string $user_id
     * @param bool $isActive
     * @param bool $isEmpty
     * @param bool $isGroup
     */
    private function assertInsertedDatabaseData( $toggle_id = "", $user_id = null, $isActive = false, $isEmpty = false,
                                                 $isGroup = false )
    {
        $expectedEntry = [ 1 => $toggle_id, 2 => $user_id, 3 => (int) $isActive ];
        if ( $isEmpty ) {
            $expectedEntry = [ ];
        }
        if ( !$isGroup ) {
            $actualEntry = $this->getUserActivatedToggleEntry( $toggle_id, $user_id );
        } else {
            $actualEntry = $this->getGroupActivatedToggleEntry( $toggle_id, $user_id );
        }
        $this->assertEquals( $expectedEntry, $actualEntry );
    }

    /**
     * @param bool $toggle_status
     * @param bool $needToBeCreated
     * @param bool $isGroup
     * @return array
     */
    private function addDataToDatabase( $toggle_status = false, $needToBeCreated = true, $isGroup = false )
    {
        $id = $this->addRelease( 'Test ToggleStatusModifierService', 'a helpful url' );

        $toggle_id = $this->addToggle( "test1", $id, true );
        $toggle_id2 = $this->addToggle( "test2", $id, true );
        $toggle_id3 = $this->addToggle( "test3", $id, true );
        $user_id = 1;
        $user2_id = 2;
        $user3_id = 3;
        $user4_id = 4;

        if ( !$isGroup ) {
            if ( $needToBeCreated ) {
                $this->addUserActivatedToggle( $toggle_id, $user_id, $toggle_status );
            }
            $this->addUserActivatedToggle( $toggle_id2, $user2_id, false );
            $this->addUserActivatedToggle( $toggle_id3, $user3_id, false );
            $this->addUserActivatedToggle( $toggle_id3, $user4_id, true );
        } else {
            if ( $needToBeCreated ) {
                $this->addGroupActivatedToggle( $toggle_id, $user_id, $toggle_status );
            }
            $this->addGroupActivatedToggle( $toggle_id2, $user2_id, false );
            $this->addGroupActivatedToggle( $toggle_id3, $user3_id, false );
            $this->addGroupActivatedToggle( $toggle_id3, $user4_id, true );
        }
        return array( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id );
    }

    /**
     * @param string $toggle_id
     * @param string $user_id
     * @param string $toggle_id2
     * @param string $user2_id
     * @param string $toggle_id3
     * @param string $user3_id
     * @param string $user4_id
     * @param bool $toggle_status
     * @param bool $isEmpty
     */
    private function validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id,
                                           $user4_id, $toggle_status, $isEmpty = false, $isGroup = false )
    {
        $this->assertInsertedDatabaseData( $toggle_id, $user_id, $toggle_status, $isEmpty, $isGroup );
        $this->assertInsertedDatabaseData( $toggle_id2, $user2_id, false, false, $isGroup );
        $this->assertInsertedDatabaseData( $toggle_id3, $user3_id, false, false, $isGroup );
        $this->assertInsertedDatabaseData( $toggle_id3, $user4_id, true, false, $isGroup );
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
        $this->deleteAddedGroupActivatedToggles();
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
    public function givenInvalidToggleStatusIntoSetToggleStatusForUser__MysqlToggleStatusModifierService_ReturnsFalse()
    {
        $response = $this->gateway->setToggleStatusForUser( "test", "this is invalid", 1 );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringSetToggleStatusForUserActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
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
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringSetToggleStatusForUserActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
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
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringSetToggleStatusForUserActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
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
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringSetToggleStatusForUserDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
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
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringSetToggleStatusForUserDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
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
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringSetToggleStatusForUserDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
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
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringSetToggleStatusForUserUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
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
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringSetToggleStatusForUserUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $user_id );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            true, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringSetToggleStatusForUserUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( false );

        $response = $this->gateway->setToggleStatusForUser( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $user_id );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            false, true );
    }

    /**
     * @test
     */
    public function givenNullPassedAsAToggleIndentifierIntoSetToggleStatusForGroup__MysqlToggleStatusModifierService_ReturnsFalse()
    {
        $response = $this->gateway->setToggleStatusForGroup( null, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE, 1, 1 );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenEmptyStringPassedAsAToggleIndentifierIntoSetToggleStatusForGroup__MysqlToggleStatusModifierService_ReturnsFalse()
    {
        $response = $this->gateway->setToggleStatusForGroup( "", ToggleStatusModifier::TOGGLE_STATUS_ACTIVE, 1, 1 );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenNullPassedAsAGroupIndentifierIntoSetToggleStatusForGroup__MysqlToggleStatusModifierService_ReturnsFalse()
    {
        $response = $this->gateway->setToggleStatusForGroup( "test", ToggleStatusModifier::TOGGLE_STATUS_ACTIVE, null,
            1 );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenNullPassedAsAactingUserIdentifierIntoSetToggleStatusForGroup__MysqlToggleStatusModifierService_ReturnsFalse()
    {
        $response = $this->gateway->setToggleStatusForGroup( "test", ToggleStatusModifier::TOGGLE_STATUS_ACTIVE, 1,
            null );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenInvalidToggleStatusIntoSetToggleStatusForGroup__MysqlToggleStatusModifierService_ReturnsFalse()
    {
        $response = $this->gateway->setToggleStatusForGroup( "test", "this is invalid", 1, 1 );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringSetToggleStatusForGroupActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true,
            false, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $user_id, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            true, false, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringSetToggleStatusForGroupActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true,
            true, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $user_id, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            true, false, true );
    }


    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringSetToggleStatusForGroupActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( false,
            true, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $user_id, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            true, false, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringSetToggleStatusForGroupDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true,
            false, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $user_id, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            false, false, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringSetToggleStatusForGroupDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true,
            true, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $user_id, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            false, false, true );

    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringSetToggleStatusForGroupDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( false,
            true, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $user_id, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            false, false, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringSetToggleStatusForGroupUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true,
            false, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $user_id, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            true, true, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringSetToggleStatusForGroupUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( true,
            true, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $user_id, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            true, true, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringSetToggleStatusForGroupUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggle_id, $toggle_id2, $toggle_id3, $user_id, $user2_id, $user3_id, $user4_id ) = $this->addDataToDatabase( false,
            true, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggle_id, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $user_id, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggle_id, $user_id, $toggle_id2, $user2_id, $toggle_id3, $user3_id, $user4_id,
            true, true, true );
    }
}
