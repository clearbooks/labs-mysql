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

        // Teardown
        $this->deleteAddedToggle( $toggleId1 );
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
     * @param stirng $name
     * @param string $releaseId
     * @param bool $isActive
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false )
    {
        $this->addToggleToDatebase( $name, $releaseId, $isActive );
        return $this->connection->lastInsertId( "`toggle`" );
    }

    /**
     * @param string $name
     * @param stirng $releaseId
     * @param bool $isActive
     * @return int
     */
    public function addToggleToDatebase( $name, $releaseId, $isActive )
    {
        return $this->connection->insert( "`toggle`",
            [ 'name' => $name, 'release_id' => $releaseId, 'toggle_type' => 1, 'is_activatable' => $isActive ] );
    }
}
