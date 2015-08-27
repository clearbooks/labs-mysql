<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 27/08/2015
 * Time: 13:06
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class MysqlActivatedToggleGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MysqlActivatedToggleGateway
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
            [ 'name' => $name, 'release_id' => $releaseId, 'toggle_type' => $toggleType, 'is_active' => $isActive ] );
    }

    /**
     * @param string $toggleId
     * @param string $userId
     * @param bool $status
     */
    private function addUserActivatedToggle( $toggleId, $userId, $status = false )
    {
        $this->connection->insert( "`user_activated_toggle`",
            [ 'user_id' => $userId, 'toggle_id' => $toggleId, 'is_active' => $status ] );
    }

    /**
     * @param string $toggleId
     * @param string $userId
     * @param bool $status
     */
    private function addGroupActivatedToggle( $toggleId, $userId, $status = false )
    {
        $this->connection->insert( "`group_activated_toggle`",
            [ 'group_id' => $userId, 'toggle_id' => $toggleId, 'active' => $status ] );
    }

    /**
     * @param string $toggleId
     * @param string $userId
     * @return array
     */
    private function getUserActivatedToggleEntry( $toggleId, $userId )
    {
        $data = $this->connection->fetchAssoc( 'SELECT * FROM `user_activated_toggle` WHERE toggle_id = ? AND user_id = ?',
            [ $toggleId, $userId ] );
        if ( empty( $data ) ) {
            return [ ];
        }
        $entry = [ 1 => $data[ 'toggle_id' ], 2 => $data[ 'user_id' ], 3 => $data[ 'is_active' ] ];
        return $entry;
    }

    /**
     * @param string $toggleId
     * @param string $groupId
     * @return array
     */
    private function getGroupActivatedToggleEntry( $toggleId, $groupId )
    {
        $data = $this->connection->fetchAssoc( 'SELECT * FROM `group_activated_toggle` WHERE toggle_id = ? AND group_id = ?',
            [ $toggleId, $groupId ] );
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
     * @param bool $groupAlso
     */
    private function assertInsertedDatabaseData( $toggleId = "", $userId = null, $isActive = false,
                                                 $groupAlso = false )
    {
        $expectedEntry = [ 1 => $toggleId, 2 => $userId, 3 => (int) $isActive ];

        if ( $groupAlso ) {
            $actualEntry = $this->getGroupActivatedToggleEntry( $toggleId, $userId );
            $this->assertEquals( $expectedEntry, $actualEntry );
        } else {
            $actualEntry = $this->getUserActivatedToggleEntry( $toggleId, $userId );
            $this->assertEquals( $expectedEntry, $actualEntry );
        }
    }

    /**
     * @param bool $groupAlso
     * @return array
     */
    private function addDataToDatabase( $groupAlso = false )
    {
        $id = $this->addRelease( 'Test ActivatedToggleGateway', 'a helpful url' );

        $toggleName = "test1";
        $toggleName2 = "test2";
        $toggleId = $this->addToggle( $toggleName, $id, true );
        $toggleId2 = $this->addToggle( $toggleName2, $id, true );
        $toggleId3 = $this->addToggle( "test3", $id, true );
        $userId = "1";
        $userId2 = "2";
        $userId3 = "3";
        $userId4 = "4";


        $this->addUserActivatedToggle( $toggleId, $userId, true );
        $this->addUserActivatedToggle( $toggleId2, $userId2, false );
        $this->addUserActivatedToggle( $toggleId3, $userId3, false );
        $this->addUserActivatedToggle( $toggleId3, $userId4, true );

        if ( $groupAlso ) {
            $this->addGroupActivatedToggle( $toggleId2, $userId, true );
            $this->addGroupActivatedToggle( $toggleId2, $userId2, false );
            $this->addGroupActivatedToggle( $toggleId3, $userId3, false );
            $this->addGroupActivatedToggle( $toggleId3, $userId4, true );
        }
        return array( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4, $toggleName, $id, $toggleName2 );
    }

    /**
     * @param string $toggleId
     * @param string $userId
     * @param string $toggleId2
     * @param string $userId2
     * @param string $toggleId3
     * @param string $userId3
     * @param string $userId4
     * @param bool $groupAlso
     */
    private function validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3,
                                           $userId4, $groupAlso = false )
    {
        $this->assertInsertedDatabaseData( $toggleId, $userId, true, false );
        $this->assertInsertedDatabaseData( $toggleId2, $userId2, false, false );
        $this->assertInsertedDatabaseData( $toggleId3, $userId3, false, false );
        $this->assertInsertedDatabaseData( $toggleId3, $userId4, true, false );
        if ( $groupAlso ) {
            $this->assertInsertedDatabaseData( $toggleId2, $userId, true, true );
            $this->assertInsertedDatabaseData( $toggleId2, $userId2, false, true );
            $this->assertInsertedDatabaseData( $toggleId3, $userId3, false, true );
            $this->assertInsertedDatabaseData( $toggleId3, $userId4, true, true );
        }
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
        $this->gateway = new MysqlActivatedToggleGateway( $this->connection );
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
    public function givenNoActivatedTogglesFound_MysqlActivatedToggleGateway_ReturnsEmptyArray()
    {
        $response = $this->gateway->getAllMyActivatedToggles( "I am a user" );
        $this->assertEquals( [ ], $response );
    }

    /**
     * @test
     */
    public function givenExistentActivatedUserToggles_MysqlActivatedToggleGateway_ReturnsArrayOfUserActivatedToggles()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4, $toggleName, $id ) = $this->addDataToDatabase( false );

        $expectedResult = [ new Toggle( $toggleName, $id, true ) ];
        $response = $this->gateway->getAllMyActivatedToggles( $userId );

        $this->assertEquals( $expectedResult[ 0 ], $response[ 0 ] );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            false );
    }

    /**
     * @test
     */
    public function givenExistentActivatedUserAndGroupToggles_MysqlActivatedToggleGateway_ReturnsArrayOfUserAndGroupActivatedToggles()
    {
        list( $toggleId, $toggleId2, $toggleId3, $userId, $userId2, $userId3, $userId4, $toggleName, $id, $toggleName2 ) = $this->addDataToDatabase( true );

        $expectedResult = [ new Toggle( $toggleName, $id, true ), new Toggle( $toggleName2, $id, true ) ];
        $response = $this->gateway->getAllMyActivatedToggles( $userId );

        $this->assertEquals( $expectedResult, $response );
        $this->validateDatabaseData( $toggleId, $userId, $toggleId2, $userId2, $toggleId3, $userId3, $userId4,
            true );
    }
}
