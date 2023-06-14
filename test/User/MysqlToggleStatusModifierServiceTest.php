<?php

namespace Clearbooks\LabsMysql\User;

use Clearbooks\Labs\Db\Table\GroupPolicy;
use Clearbooks\Labs\Db\Table\UserPolicy;
use Clearbooks\Labs\LabsTest;
use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\Labs\User\UseCase\ToggleStatusModifier;

/**
 * Created by PhpStorm.
 * User: Vovaxs
 * Date: 18/08/2015
 * Time: 11:57
 */
class MysqlToggleStatusModifierServiceTest extends LabsTest
{
    /**
     * @var MysqlToggleStatusModifierService
     */
    private $gateway;

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
     * @param int $toggleType
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false, $toggleType = 1 )
    {
        $this->addToggleToDatabase( $name, $releaseId, $isActive, $toggleType );
        return $this->connection->lastInsertId( "`toggle`" );
    }

    /**
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @param int $toggleType
     * @return int
     */
    public function addToggleToDatabase( $name, $releaseId, $isActive, $toggleType )
    {
        return $this->connection->insert( "`toggle`",
            [ 'name' => $name, 'release_id' => $releaseId, 'type' => $toggleType, 'visible' => $isActive ] );
    }

    /**
     * @param string $toggleId
     * @param string $userId
     * @param bool $status
     */
    private function addUserActivatedToggle( $toggleId, $userId, $status = false )
    {
        $this->connection->insert( "`user_policy`",
            [ 'user_id' => $userId, 'toggle_id' => $toggleId, 'active' => $status ] );
    }

    /**
     * @param string $toggleId
     * @param string $userId
     * @param bool $status
     */
    private function addGroupActivatedToggle( $toggleId, $userId, $status = false )
    {
        $this->connection->insert( "`group_policy`",
            [ 'group_id' => $userId, 'toggle_id' => $toggleId, 'active' => $status ] );
    }

    /**
     * @param string $toggleId
     * @param string $userId
     * @return array
     */
    private function getUserActivatedToggleEntry( $toggleId, $userId )
    {
        $data = $this->connection->executeQuery( 'SELECT * FROM `user_policy` WHERE toggle_id = ? AND user_id = ?',
            [ $toggleId, $userId ] )->fetchAssociative();
        if ( empty( $data ) ) {
            return [ ];
        }
        $entry = [ 1 => $data[ 'toggle_id' ], 2 => $data[ 'user_id' ], 3 => $data[ 'active' ] ];
        return $entry;
    }

    /**
     * @param string $toggleId
     * @param string $groupId
     * @return array
     */
    private function getGroupActivatedToggleEntry( $toggleId, $groupId )
    {
        $data = $this->connection->executeQuery( 'SELECT * FROM `group_policy` WHERE toggle_id = ? AND group_id = ?',
            [ $toggleId, $groupId ] )->fetchAssociative();
        if ( empty( $data ) ) {
            return [ ];
        }
        $entry = [ 1 => $data[ 'toggle_id' ], 2 => $data[ 'group_id' ], 3 => $data[ 'active' ] ];
        return $entry;
    }

    /**
     * @param string $toggleId
     * @param string $userId
     * @param bool $isActive
     * @param bool $isEmpty
     * @param bool $isGroup
     */
    private function assertInsertedDatabaseData( $toggleId = "", $userId = null, $isActive = false, $isEmpty = false,
                                                 $isGroup = false )
    {
        $expectedEntry = [ 1 => $toggleId, 2 => $userId, 3 => (int) $isActive ];
        if ( $isEmpty ) {
            $expectedEntry = [ ];
        }
        if ( !$isGroup ) {
            $actualEntry = $this->getUserActivatedToggleEntry( $toggleId, $userId );
        } else {
            $actualEntry = $this->getGroupActivatedToggleEntry( $toggleId, $userId );
        }
        $this->assertEquals( $expectedEntry, $actualEntry );
    }

    /**
     * @param bool $toggleStatus
     * @param bool $needToBeCreated
     * @param bool $isGroup
     * @return array
     */
    private function addDataToDatabase( $toggleStatus = false, $needToBeCreated = true, $isGroup = false )
    {
        $id = $this->addRelease( 'Test ToggleStatusModifierService', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $id, true );
        $toggleId2 = $this->addToggle( "test2", $id, true );
        $toggleId3 = $this->addToggle( "test3", $id, true );
        $userId = 1;
        $userId2 = 2;
        $userId3 = 3;
        $userId4 = 4;

        if ( !$isGroup ) {
            if ( $needToBeCreated ) {
                $this->addUserActivatedToggle( $toggleId, $userId, $toggleStatus );
            }
            $this->addUserActivatedToggle( $toggleId2, $userId2, false );
            $this->addUserActivatedToggle( $toggleId3, $userId3, false );
            $this->addUserActivatedToggle( $toggleId3, $userId4, true );
        } else {
            if ( $needToBeCreated ) {
                $this->addGroupActivatedToggle( $toggleId, $userId, $toggleStatus );
            }
            $this->addGroupActivatedToggle( $toggleId2, $userId2, false );
            $this->addGroupActivatedToggle( $toggleId3, $userId3, false );
            $this->addGroupActivatedToggle( $toggleId3, $userId4, true );
        }
        return array( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 );
    }

    /**
     * @param string $toggleId
     * @param string $userId
     * @param string $toggleId2
     * @param string $userId2
     * @param string $toggleId3
     * @param string $userId3
     * @param string $userId4
     * @param bool $toggleStatus
     * @param bool $isEmpty
     * @param bool $isGroup
     */
    private function validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3,
                                           $userId4, $toggleStatus, $isEmpty = false, $isGroup = false )
    {
        $this->assertInsertedDatabaseData( $toggleId, $userId, $toggleStatus, $isEmpty, $isGroup );
        $this->assertInsertedDatabaseData( $toggleId2, $userId2, false, false, $isGroup );
        $this->assertInsertedDatabaseData( $toggleId3, $userId3, false, false, $isGroup );
        $this->assertInsertedDatabaseData( $toggleId3, $userId4, true, false, $isGroup );
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->gateway = new MysqlToggleStatusModifierService( $this->connection, new UserPolicy(), new GroupPolicy() );
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
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( true,
            false );

        $response = $this->gateway->setToggleStatusForUser( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $userId );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringSetToggleStatusForUserActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( true );

        $response = $this->gateway->setToggleStatusForUser( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $userId );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            true );
    }


    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringSetToggleStatusForUserActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( false );

        $response = $this->gateway->setToggleStatusForUser( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $userId );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringSetToggleStatusForUserDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( true,
            false );

        $response = $this->gateway->setToggleStatusForUser( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $userId );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            false );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringSetToggleStatusForUserDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( true );

        $response = $this->gateway->setToggleStatusForUser( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $userId );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            false );

    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringSetToggleStatusForUserDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( false );

        $response = $this->gateway->setToggleStatusForUser( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $userId );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            false );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringSetToggleStatusForUserUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( true,
            false );

        $response = $this->gateway->setToggleStatusForUser( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $userId );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            true, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringSetToggleStatusForUserUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( true );

        $response = $this->gateway->setToggleStatusForUser( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $userId );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            true, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringSetToggleStatusForUserUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( false );

        $response = $this->gateway->setToggleStatusForUser( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $userId );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
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
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( true,
            false, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $userId, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            true, false, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringSetToggleStatusForGroupActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( true,
            true, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $userId, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            true, false, true );
    }


    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringSetToggleStatusForGroupActivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( false,
            true, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_ACTIVE,
            $userId, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            true, false, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringSetToggleStatusForGroupDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( true,
            false, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $userId, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            false, false, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringSetToggleStatusForGroupDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( true,
            true, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $userId, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            false, false, true );

    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringSetToggleStatusForGroupDeactivationAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( false,
            true, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_INACTIVE,
            $userId, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            false, false, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithUnsetGivenToggle_DuringSetToggleStatusForGroupUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( true,
            false, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $userId, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            true, true, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithActivatedGivenToggle_DuringSetToggleStatusForGroupUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( true,
            true, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $userId, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            true, true, true );
    }

    /**
     * @test
     */
    public function givenValidParameters_AndAUserWithDeactivatedGivenToggle_DuringSetToggleStatusForGroupUnsetAttempt_MysqlToggleStatusModifierService_ReturnsTrue()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4 ) = $this->addDataToDatabase( false,
            true, true );

        $response = $this->gateway->setToggleStatusForGroup( $toggleId, ToggleStatusModifier::TOGGLE_STATUS_UNSET,
            $userId, 1 );

        $this->assertTrue( $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            true, true, true );
    }
}
