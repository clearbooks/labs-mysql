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

    public function tearDown()
    {
        $this->deleteAddedToggles();
        $this->deleteAddedReleases();
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

        $this->addToggle( "test1", $id );
        $this->addToggle( "test2", $id );

        $expectedToggle = new Toggle( "test1", $id );
        $expectedToggle2 = new Toggle( "test2", $id );

        $expectedToggles = [ $expectedToggle, $expectedToggle2 ];
        $returnedToggles = $this->gateway->getTogglesForRelease( $id );

        $this->assertEquals( $expectedToggles, $returnedToggles );

        foreach ( $expectedToggles as $key => $value ) {
            $this->assertGetters( $value, $returnedToggles[ $key ] );
        }


    }

    /**
     * @test
     */
    public function givenExistentTogglesInDifferentReleases_ReleaseToggleColoction_ReturnsArrayOfExistentTogglesForRequestedRelease()
    {
        $releaseName = 'Test release for toggle 3.1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $releaseName2 = 'Test release for toggle 3.2';
        $url2 = 'a helpful url2';
        $id2 = $this->addRelease( $releaseName2, $url2 );

        $this->addToggle( "test1", $id );
        $this->addToggle( "test2", $id );
        $this->addToggle( "test3", $id2 );
        $this->addToggle( "test4", $id2 );

        $expectedToggle = new Toggle( "test1", $id );
        $expectedToggle2 = new Toggle( "test2", $id );

        $expectedToggles = [ $expectedToggle, $expectedToggle2 ];
        $returnedToggles = $this->gateway->getTogglesForRelease( $id );

        $this->assertEquals( $expectedToggles, $returnedToggles );

        foreach ( $expectedToggles as $key => $value ) {
            $this->assertGetters( $value, $returnedToggles[ $key ] );
        }


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
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false )
    {
        $this->addToggleToDatebase( $name, $releaseId, $isActive );
        return $this->connection->lastInsertId( "`toggle`" );
    }

    /**
     * @param string $name
     * @param string $releaseId
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
