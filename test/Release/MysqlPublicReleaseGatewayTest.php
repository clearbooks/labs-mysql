<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 28/08/15
 * Time: 14:54
 */

namespace Clearbooks\LabsMysql\Release;


use Clearbooks\Labs\Release\Release;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class MysqlPublicReleaseGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MysqlPublicReleaseGateway
     */
    private $gateway;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \DateTime
     */
    private $futureDateTime;

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
        $this->gateway = new MysqlPublicReleaseGateway( $this->connection );
        $this->futureDateTime = new \DateTime('3000-01-01 00:00:00');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->connection->delete( '`release`', [ '*' ] );
    }

    /**
     * @test
     */
    public function givenNoReleases_GetAllPublicReleasesReturnsEmptyArray()
    {
        $this->assertEquals( [], $this->gateway->getAllPublicReleases() );
    }

    /**
     * @test
     */
    public function givenNotVisibleRelease_GetAllPublicReleasesReturnsEmptyArray()
    {
        $this->connection->insert( '`release`', [ 'name' => "Test Release".rand(), 'info' => 'hi', 'visibility' => 0 ] );
        $this->assertEquals( [], $this->gateway->getAllPublicReleases() );
    }

    /**
     * @test
     */
    public function givenVisibleRelease_GetAllPublicReleasesReturnsRelease()
    {
        $expectedReleases[] = $this->addRelease( true );
        $this->assertEquals( $expectedReleases, $this->gateway->getAllPublicReleases() );
    }

    /**
     * @test
     */
    public function givenVisibleReleases_GetAllPublicReleasesReturnsReleases()
    {
        $expectedReleases[] = $this->addRelease( true );
        $expectedReleases[] = $this->addRelease( true );
        $this->assertEquals( $expectedReleases, $this->gateway->getAllPublicReleases() );
    }

    /**
     * @test
     */
    public function givenVisibleAndNonVisibleReleases_GetAllPublicReleasesReturnsOnlyVisibleReleases()
    {
        $expectedReleases[] = $this->addRelease( true );
        $this->addRelease( false );
        $this->assertEquals( $expectedReleases, $this-> gateway->getAllPublicReleases() );
    }

    /**
     * @test
     */
    public function givenNotVisibleButReleaseDateIsInThePast_GetAllPublicReleasesReturnsOnlyVisibleReleases()
    {
        $expectedReleases[] = $this->addRelease( false, new \DateTime( '2015-01-01' ) );
        $this->assertEquals( $expectedReleases, $this-> gateway->getAllPublicReleases() );
    }

    /**
     * @param bool $visibility
     * @param \DateTimeInterface $dateTime
     * @return array
     */
    public function addRelease( $visibility, \DateTimeInterface $dateTime = null )
    {
        $releaseName = "Test Release" . rand();
        $releaseInfoUrl = 'hi';
        $dateTime = (is_null( $dateTime ))?$this->futureDateTime:$dateTime;
        $this->connection->insert( '`release`', [ 'name' => $releaseName, 'info' => $releaseInfoUrl, 'visibility' => (int) $visibility, 'release_date' => $dateTime->format( 'Y-m-d' ) ] );
        $id = $this->connection->lastInsertId();
        return new Release( $id, $releaseName, $releaseInfoUrl, $dateTime, (int)$visibility );
    }
}