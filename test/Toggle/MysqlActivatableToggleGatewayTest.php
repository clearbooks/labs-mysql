<?php
use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Clearbooks\LabsMysql\Toggle\MysqlActivatableToggleGateway;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 13/08/2015
 * Time: 14:38
 */
class MysqlActivatableToggleGatewayTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MysqlActivatableToggleGateway
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
        $this->gateway = new MysqlActivatableToggleGateway( $this->connection );
    }

    /**
     * @test
     */
    public function givenNoExistentToggleWithProvidedName_MysqlActivatableToggleGateway_ReturnsNull()
    {
        $returnedToggle = $this->gateway->getActivatableToggleByName( "test" );
        $this->assertEquals( null, $returnedToggle );
    }

    /**
     * @test
     */
    public function givenExistentToggleButNotActivatable_MysqlActivatableToggleGateway_ReturnsNull()
    {
        $releaseName = 'Test activatable toggle 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggleId1 = $this->addToggle( "test1", $id );

        $returnedToggles = $this->gateway->getActivatableToggleByName( "test1" );

        // Teardown
        $this->deleteAddedToggle( $toggleId1 );
        $this->deleteAddedRelease( $id );

        $this->assertEquals( null, $returnedToggles );
    }

    /**
     * @test
     */
    public function givenExistentActivatableToggle_MysqlActivatableToggleGateway_RetusnsExistentToggle()
    {
        $releaseName = 'Test activatable toggle 2';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggleId1 = $this->addToggle( "test1", $id, true );

        $expectedToggle = new Toggle( "test1", $id, true );

        $expectedToggles = $expectedToggle;
        $returnedToggles = $this->gateway->getActivatableToggleByName( "test1" );

        // Teardown
        $this->deleteAddedToggle( $toggleId1 );
        $this->deleteAddedRelease( $id );

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }

    /**
     * @test
     */
    public function givenMultipleExistentToggleWithDifferentNames_MysqlActivatableToggleGateway_RetusnsRequestedExistentToggle()
    {
        $releaseName = 'Test activatable toggle 3';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggleId1 = $this->addToggle( "test1", $id, true );
        $toggleId2 = $this->addToggle( "test2", $id );

        $expectedToggle = new Toggle( "test1", $id, true );

        $returnedToggle = $this->gateway->getActivatableToggleByName( "test1" );

        // Teardown
        $this->deleteAddedToggle( $toggleId1 );
        $this->deleteAddedToggle( $toggleId2 );
        $this->deleteAddedRelease( $id );

        $this->assertEquals( $expectedToggle, $returnedToggle );
        //testing isActive()
        $this->assertEquals( $expectedToggle->isActive(), $returnedToggle->isActive() );
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
