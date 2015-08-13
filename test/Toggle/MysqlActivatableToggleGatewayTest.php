<?php
use Doctrine\DBAL\Configuration;
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
    public function givenNoExistentToggleWithProvidedName_MysqlActivatableToggleGateway_ReturnsEmptyArray()
    {
        $returnedToggle = $this->gateway->getActivatableToggleByName("test");
        $this->assertEquals( [ ], $returnedToggle );
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
