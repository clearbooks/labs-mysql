<?php

namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit_Framework_TestCase;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 14/08/2015
 * Time: 15:12
 */
class MysqlUserToggleGatewayTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MysqlUserToggleGateway
     */
    private $gateway;

    /**
     * @var Connection $connection
     */
    private $connection;

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
        $this->gateway = new MysqlUserToggleGateway( $this->connection );
    }

    /**
     * @test
     */
    public function givenNoUserTogglesFound_MysqlUserToggleGateway_ReturnsEmptyArray()
    {
        $returnedToggle = $this->gateway->getAllUserToggles();
        $this->assertEquals( [ ], $returnedToggle );
    }

    /**
     * @test
     */
    public function givenExistentUserTogglesFound_MysqlUserToggleGateway_ReturnsArrayOfUserToggles()
    {
        $releaseName = 'Test user toggle 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggleId1 = $this->addToggle( "test1", $id, true );

        $expectedToggle = new Toggle( "test1", $id, true );

        $expectedToggles[] = $expectedToggle;
        $returnedToggles = $this->gateway->getAllUserToggles();

        // Teardown: Teardown is done before assert in order to keep DataBase clean in case of test failure
        $this->deleteAddedToggle( $toggleId1 );
        $this->deleteAddedRelease( $id );

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }

    /**
     * @test
     */
    public function givenExistentUserTogglesAndNonUserTogglesFound_MysqlUserToggleGateway_ReturnsArrayOfUserTogglesOnly()
    {
        $releaseName = 'Test user toggle 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        //Parameters: name, release_id, is_activatable, toggle_type
        $toggleId1 = $this->addToggle( "test1", $id, true, 1 );
        $toggleId2 = $this->addToggle( "test2", $id, true, 1 );
        $toggleId3 = $this->addToggle( "test3", $id, true, 2 );
        $toggleId4 = $this->addToggle( "test4", $id, true, 2 );

        $expectedToggle = new Toggle( "test1", $id, true );
        $expectedToggle2 = new Toggle( "test2", $id, true );

        $expectedToggles = [ $expectedToggle, $expectedToggle2 ];
        $returnedToggles = $this->gateway->getAllUserToggles();

        // Teardown: Teardown is done before assert in order to keep DataBase clean in case of test failure
        $this->deleteAddedToggle( $toggleId1 );
        $this->deleteAddedToggle( $toggleId2 );
        $this->deleteAddedToggle( $toggleId3 );
        $this->deleteAddedToggle( $toggleId4 );
        $this->deleteAddedRelease( $id );

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }

    /**
     * @param string $id
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function deleteAddedRelease( $id )
    {
        $this->connection->delete( '`release`', [ 'id' => $id ] );
    }

    /**
     * @param string $id
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function deleteAddedToggle( $id )
    {
        $this->connection->delete( '`toggle`', [ 'id' => $id ] );
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
        $this->addToggleToDatebase( $name, $releaseId, $isActive, $toggle_type );
        return $this->connection->lastInsertId( "`toggle`" );
    }

    /**
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @param int $toggle_type
     * @return int
     */
    public function addToggleToDatebase( $name, $releaseId, $isActive, $toggle_type )
    {
        return $this->connection->insert( "`toggle`",
            [ 'name' => $name, 'release_id' => $releaseId, 'toggle_type' => $toggle_type, 'is_active' => $isActive ] );
    }
}
