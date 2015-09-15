<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 19/08/2015
 * Time: 14:50
 */

namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit_Framework_TestCase;

class MysqlGroupToggleGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MysqlGroupToggleGateway
     */
    private $gateway;

    /**
     * @var Connection $connection
     */
    private $connection;

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
     * @param string $toggle_type
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false, $toggle_type = "simple" )
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
        $this->gateway = new MysqlGroupToggleGateway( $this->connection );
    }

    public function tearDown()
    {
        $this->deleteAddedToggles();
        $this->deleteAddedReleases();
    }

    /**
     * @test
     */
    public function givenNoGroupTogglesFound_MysqlGroupToggleGateway_ReturnsEmptyArray()
    {
        $returnedToggle = $this->gateway->getAllGroupToggles();
        $this->assertEquals( [ ], $returnedToggle );
    }

    /**
     * @test
     */
    public function givenExistentGroupTogglesFound_MysqlGroupToggleGateway_ReturnsArrayOfGroupToggles()
    {
        $id = $this->addRelease( 'Test group toggle 1', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $id, true, "group" );

        $expectedToggles = [
            new Toggle( $toggleId, "test1", $id, true, null, null, null, null, null, null, null, "group" )
        ];

        $returnedToggles = $this->gateway->getAllGroupToggles();

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }

    /**
     * @test
     */
    public function givenExistentGroupTogglesAndNonGroupTogglesFound_MysqlGroupToggleGateway_ReturnsArrayOfGroupTogglesOnly()
    {
        $id = $this->addRelease( 'Test group toggle 2', 'a helpful url' );

        //Parameters: name, release_id, is_activatable, toggle_type
        $toggleId = $this->addToggle( "test1", $id, true, "group" );
        $toggleId2 = $this->addToggle( "test2", $id, true, "group" );
        $this->addToggle( "test3", $id, true, "simple" );
        $this->addToggle( "test4", $id, true, "simple" );

        $expectedToggles = [
            new Toggle( $toggleId, "test1", $id, true, null, null, null, null, null, null, null, "group" ),
            new Toggle( $toggleId2, "test2", $id, true, null, null, null, null, null, null, null, "group" )
        ];
        $returnedToggles = $this->gateway->getAllGroupToggles();

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }
}
