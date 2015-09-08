<?php

namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
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

    public function tearDown()
    {
        $this->deleteAddedToggles();
        $this->deleteAddedReleases();
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

        $toggleId = $this->addToggle( "test1", $id, true );

        $expectedToggle = new Toggle( $toggleId, "test1", $id, true );

        $expectedToggles[] = $expectedToggle;
        $returnedToggles = $this->gateway->getAllUserToggles();

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

        $toggleId = $this->addToggle( "test1", $id, true, 1 );
        $toggleId2 = $this->addToggle( "test2", $id, true, 1 );
        $this->addToggle( "test3", $id, true, 2 );
        $this->addToggle( "test4", $id, true, 2 );

        $expectedToggles = [ new Toggle( $toggleId, "test1", $id, true ), new Toggle( $toggleId2, "test2", $id, true ) ];
        $returnedToggles = $this->gateway->getAllUserToggles();

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }

    /**
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function deleteAddedReleases()
    {
        $this->connection->delete( '`release`', [ '*' ] );
    }

    /**
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function deleteAddedToggles()
    {
        $this->connection->delete( '`toggle`', [ '*' ] );
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
            [ 'name' => $name, 'release_id' => $releaseId, 'type' => $toggle_type, 'visible' => $isActive ] );
    }
}
