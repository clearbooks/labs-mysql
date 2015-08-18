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

    public function tearDown()
    {
        $this->deleteAddedToggles();
        $this->deleteAddedReleases();
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

        $this->addToggle( "test1", $id );

        $returnedToggles = $this->gateway->getActivatableToggleByName( "test1" );

        $this->assertEquals( null, $returnedToggles );
    }

    /**
     * @test
     */
    public function givenExistentActivatableToggle_MysqlActivatableToggleGateway_ReturnsExistentToggle()
    {
        $releaseName = 'Test activatable toggle 2';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $this->addToggle( "test1", $id, true );

        $expectedToggle = new Toggle( "test1", $id, true );

        $expectedToggles = $expectedToggle;
        $returnedToggles = $this->gateway->getActivatableToggleByName( "test1" );

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }

    /**
     * @test
     */
    public function givenMultipleExistentTogglesWithDifferentNames_MysqlActivatableToggleGateway_ReturnsRequestedExistentToggle()
    {
        $releaseName = 'Test activatable toggle 3';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $this->addToggle( "test1", $id, true );
        $this->addToggle( "test2", $id );

        $expectedToggle = new Toggle( "test1", $id, true );

        $returnedToggle = $this->gateway->getActivatableToggleByName( "test1" );

        $this->assertEquals( $expectedToggle, $returnedToggle );
        //testing isActive()
        $this->assertEquals( $expectedToggle->isActive(), $returnedToggle->isActive() );
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
        $this->addToggleToDatabase( $name, $releaseId, $isActive );
        return $this->connection->lastInsertId( "`toggle`" );
    }

    /**
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @return int
     */
    public function addToggleToDatabase( $name, $releaseId, $isActive )
    {
        return $this->connection->insert( "`toggle`",
            [ 'name' => $name, 'release_id' => $releaseId, 'toggle_type' => 1, 'is_active' => $isActive ] );
    }
}
