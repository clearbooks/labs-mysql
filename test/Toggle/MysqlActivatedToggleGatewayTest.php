<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 27/08/2015
 * Time: 13:06
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\GroupStub;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Clearbooks\LabsMysql\Toggle\Entity\UserStub;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class MysqlActivatedToggleGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MysqlActivatedToggleGateway
     */
    private $gateway;

    CONST USER_ID = "userTest";

    /**
     * @throws InvalidArgumentException
     */
    private function deleteAddedReleases()
    {
        $this->connection->delete( '`release`', [ '*' ] );
    }

    /**
     * @throws InvalidArgumentException
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
     * @param int $toggleType
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false, $toggleType = 1 )
    {
        $this->addToggleToDatabase( $name, $releaseId, $isActive, $toggleType );
        return $this->connection->lastInsertId( "`toggle`" );
    }

    /**
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @param int $toggleType
     * @return int
     */
    public function addToggleToDatabase( $name, $releaseId, $isActive, $toggleType )
    {
        return $this->connection->insert( "`toggle`",
            [ 'name' => $name, 'release_id' => $releaseId, 'toggle_type' => $toggleType, 'is_active' => $isActive ] );
    }

    /**
     * @return void
     */
    private function addDataToDatabase()
    {
        $id = $this->addRelease( 'Test ActivatedToggleGateway', 'a helpful url' );

        $this->addToggle( "test1", $id, true );
        $this->addToggle( "test2", $id, true );
        $this->addToggle( "test3", $id, true );

        return $id;
    }


    public function setUp()
    {
        parent::setUp();


        parent::setUp();
        $connectionParams = array(
            'dbname' => 'labs',
            'user' => 'root',
            'password' => '',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        );

        $this->connection = DriverManager::getConnection( $connectionParams, new Configuration() );

        $activatedToggles = [ "test1" => true, "test2" => false, "test3" => true ];
        $this->gateway = new MysqlActivatedToggleGateway( $this->connection, new ToggleCheckerMock( self::USER_ID, $activatedToggles ) );

    }

    public function tearDown()
    {
        $this->deleteAddedToggles();
        $this->deleteAddedReleases();
    }

    /**
     * @test
     */
    public function givenNoActivatedTogglesFound_MysqlActivatedToggleGateway_ReturnsEmptyArray()
    {
        $response = $this->gateway->getAllMyActivatedToggles( self::USER_ID );
        $this->assertEquals( [ ], $response );
    }

    /**
     * @test
     */
    public function givenExistentActivatedUserToggles_MysqlActivatedToggleGateway_ReturnsArrayOfUserActivatedToggles()
    {
        $id = $this->addDataToDatabase();

        $expectedResult = [ new Toggle( "test1", $id, true ) ];
        $response = $this->gateway->getAllMyActivatedToggles( self::USER_ID );

        $this->assertEquals( $expectedResult[ 0 ], $response[ 0 ] );
    }

    /**
     * @test
     */
    public function givenExistentActivatedUserAndGroupToggles_MysqlActivatedToggleGateway_ReturnsArrayOfUserAndGroupActivatedToggles()
    {
        $id = $this->addDataToDatabase();

        $expectedResult = [ new Toggle( "test1", $id, true ), new Toggle( "test3", $id, true ) ];
        $response = $this->gateway->getAllMyActivatedToggles( self::USER_ID );

        $this->assertEquals( $expectedResult, $response );
    }
}
