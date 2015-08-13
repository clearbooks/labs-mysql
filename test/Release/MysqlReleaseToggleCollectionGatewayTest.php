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

        $expectedToggles = [ $expectedToggle, $expectedToggle2 ];
        $returnedToggles = $this->gateway->getTogglesForRelease( $id );

        // Teardown
        $this->deleteAddedToggle( $toggleId1 );
        $this->deleteAddedToggle( $toggleId2 );
        $this->deleteAddedRelease( $id );

        $this->assertEquals( $expectedToggles, $returnedToggles );

        foreach ( $expectedToggles as $key => $value ) {
            $this->assertGetters( $value, $returnedToggles[ $key ] );
        }


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
            [ 'name' => $name, 'release_id' => $releaseId, 'toggle_type' => 1, 'is_active' => $isActive ] );
    }

    /**
     * @param Toggle $expectedToggle
     * @param Toggle $returnedToggle
     */
    private function assertGetters( $expectedToggle, $returnedToggle )
    {
        $this->assertEquals( $expectedToggle->getName(),
            $returnedToggle->getName() );
        $this->assertEquals( $expectedToggle->getRelease(),
            $returnedToggle->getRelease() );
        $this->assertEquals( $expectedToggle->isActive(),
            $returnedToggle->isActive() );
    }
}
