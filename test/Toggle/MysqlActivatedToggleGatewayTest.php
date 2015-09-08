<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 27/08/2015
 * Time: 13:06
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\Labs\Bootstrap;
use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\GroupStub;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Clearbooks\LabsMysql\Toggle\Entity\UserStub;
use Doctrine\DBAL\Connection;

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
            [ 'name' => $name, 'release_id' => $releaseId, 'toggle_type' => $toggleType, 'visible' => $isActive ] );
    }

    /**
     * @return array
     */
    private function addDataToDatabase()
    {
        $releaseId = $this->addRelease( 'Test ActivatedToggleGateway', 'a helpful url' );

        $activeToggleId = $this->addToggle( "test1", $releaseId, true );
        $this->addToggle( "test2", $releaseId, true );
        $activeToggleId2 = $this->addToggle( "test3", $releaseId, true );

        return array( $releaseId, $activeToggleId, $activeToggleId2 );
    }


    public function setUp()
    {
        parent::setUp();

        $this->connection = Bootstrap::getInstance()->getDIContainer()
            ->get( Connection::class );

        $this->connection->beginTransaction();
        $this->connection->setRollbackOnly();

        $activatedToggles = [ "test1" => true, "test2" => false, "test3" => true ];
        $this->gateway = new MysqlActivatedToggleGateway( $this->connection,
            new ToggleCheckerMock( self::USER_ID, $activatedToggles ) );

    }

    public function tearDown()
    {
        parent::tearDown();
        $this->connection->rollBack();
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
    public function givenExistentActivatedToggles_MysqlActivatedToggleGateway_ReturnsArrayOfActivatedToggles()
    {
        list( $releaseId, $toggleId, $toggleId2 ) = $this->addDataToDatabase();

        $expectedResult = [ new Toggle( $toggleId, "test1", $releaseId, true ), new Toggle( $toggleId2, "test3", $releaseId, true ) ];
        $response = $this->gateway->getAllMyActivatedToggles( self::USER_ID );

        $this->assertEquals( $expectedResult, $response );
    }
}
