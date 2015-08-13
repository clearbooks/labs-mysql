<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 12/08/2015
 * Time: 13:13
 */

namespace Clearbooks\LabsMysql\Release;


use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;

class MysqlReleaseToggleCollectionGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MysqlReleaseToggleCollectionGateway
     */
    private $gateway;

    /**
     * @var Connection
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
        $this->gateway = new MysqlReleaseToggleCollectionGateway( $this->connection );
    }

    /**
     * @test
     */
    public function givenNoExistentRelease_ReleaseToggleColection_ReturnsEmptyArray()
    {
        $returnedToggles = $this->gateway->getTogglesForRelease( 'bloop' );
        $this->assertEquals( [ ], $returnedToggles );
    }

    /**
     * @test
     */
    public function givenNoExistentTogglesInTheExistentRelase_ReleaseToggleColection_ReturnsEmptyArray()
    {
        $releaseName = 'Test release for toggle 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $returnedToggles = $this->gateway->getTogglesForRelease( $id );

        // Teardown
        $this->deleteAddedRelease( $id );

        $this->assertEquals( [ ], $returnedToggles );
    }

    /**
     * @test
     */
    public function givenExistentTogglesInTheExistentRelease_ReleaseToggleColoction_ReturnsArrayOfExistentToggles()
    {
        $releaseName = 'Test release for toggle 2';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggleId1 = $this->addToggle( "test1", $id );
        $toggleId2 = $this->addToggle( "test2", $id );

        $expectedToggle = new Toggle( "test1", $id );
        $expectedToggle2 = new Toggle( "test2", $id );

        $returnedToggles = $this->gateway->getTogglesForRelease( $id );

        // Teardown
        $this->deleteAddedToggle( $toggleId1 );
        $this->deleteAddedToggle( $toggleId2 );
        $this->deleteAddedRelease( $id );

        $this->assertEquals( [ $expectedToggle, $expectedToggle2 ], $returnedToggles );

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
     * @return string
     */
    private function addToggle( $name, $releaseId )
    {
        $this->addToggleToDatebase( $name, $releaseId );
        return $this->connection->lastInsertId( "`toggle`" );
    }

    /**
     * @param string $name
     * @param stirng $releaseId
     * @return int
     */
    public function addToggleToDatebase( $name, $releaseId )
    {
        return $this->connection->insert( "`toggle`", [ 'name' => $name, 'release_id' => $releaseId ] );
    }
}
