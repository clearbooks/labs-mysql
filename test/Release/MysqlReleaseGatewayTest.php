<?php
/**
 * @author: Ryan Wood <ryanw@clearbooks.co.uk>
 * @created: 10/08/15
 */

namespace Clearbooks\LabsMysql\Release;


use Clearbooks\Labs\Bootstrap;
use Clearbooks\Labs\Release\Release;
use Doctrine\DBAL\Connection;

class MysqlReleaseGatewayTest extends \PHPUnit_Framework_TestCase
{
    const RELEASE_NAME = "test";
    const RELEASE_URL = "brollies";
    /**
     * @var MysqlReleaseGateway
     */
    private $gateway;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param $releaseName
     * @param $url
     * @param bool $isVisible
     * @param \DateTimeInterface $releaseDate
     * @return string
     */
    private function addRelease( $releaseName, $url, $isVisible = true, $releaseDate = null )
    {
        $releaseDate = $releaseDate ?: new \DateTime();
        $this->gateway->addRelease( $releaseName, $url, $isVisible, $releaseDate );
        return $this->connection->lastInsertId( "`release`" );
    }

    /**
     * @param Release $expectedRelease
     * @param Release $release
     */
    private function assertReleasesMatch( $expectedRelease, $release )
    {
        $this->assertEquals( $expectedRelease->getReleaseName(), $release->getReleaseName() );
        $this->assertEquals( $expectedRelease->getReleaseInfoUrl(), $release->getReleaseInfoUrl() );
    }

    /**
     * @return \DateTime
     */
    private function getFutureDate()
    {
        return (new \DateTime)->modify("+1 days")->setTime(0, 0);
    }

    /**
     * @param Release[] $expectedReleases
     * @param array $response
     */
    private function assertAllReleasesMatch($expectedReleases, $response)
    {
        foreach ($expectedReleases as $index => $expectedRelease) {
            $this->assertReleasesMatch($expectedRelease, $response[$index]);
        }
    }

    public function setUp()
    {
        parent::setUp();

        $this->connection = Bootstrap::getInstance()->getDIContainer()
            ->get( Connection::class );

        $this->connection->beginTransaction();
        $this->connection->setRollbackOnly();

        $this->gateway = new MysqlReleaseGateway( $this->connection );
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->connection->rollBack();
    }

    /**
     * @test
     */
    public function givenReleaseNameAndUrl_AddRelease()
    {
        $releaseName = 'Test release 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $expectedRelease = array(
            'id' => $id,
            'name' => $releaseName,
            'info' => $url,
            'visibility' => 1,
            'release_date' => (new \DateTime())->format('Y-m-d')
        );
        $this->assertEquals( $expectedRelease,
            $this->connection->fetchAssoc( 'SELECT * FROM `release` WHERE `id` = ?', [ $id ] ) );
    }

    /**
     * @test
     */
    public function givenNoReleases_getReleaseReturnsNull()
    {
        $this->assertNull( $this->gateway->getRelease( 'blergh' ) );
    }

    /**
     * @test
     */
    public function givenRelease_WhenGetReleaseCalledWithWrongId_ReturnsNull()
    {
        $this->addRelease( 'Test release 1', 'a helpful url' );
        $this->assertNull( $this->gateway->getRelease( 'blergh' ) );
    }

    /**
     * @test
     */
    public function givenRelease_getReleaseWithCorrectId_ReturnsRelease()
    {
        $releaseName = 'Test release 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $release = $this->gateway->getRelease( $id );
        $expectedRelease = new Release( 1, $releaseName, $url, new \DateTime() );

        $this->assertReleasesMatch( $expectedRelease, $release );
    }

    /**
     * @test
     */
    public function givenNoReleases_getAllReleasesReturnsEmptyArray()
    {
        $this->assertEquals( [ ], $this->gateway->getAllReleases() );
    }

    /**
     * @test
     */
    public function givenRelease_getAllReleasesReturnsReleaseInArray()
    {
        $releaseName = 'Test release 1';
        $url = 'a helpful url';
        $this->addRelease( $releaseName, $url );

        $expectedRelease = new Release( 1, $releaseName, $url, new \DateTime() );
        $releases = $this->gateway->getAllReleases();

        $this->assertReleasesMatch( $expectedRelease, $releases[ 0 ] );
    }

    /**
     * @test
     */
    public function givenMultipleReleases_getAllReleasesReturnsArrayOfReleases()
    {
        /**
         * @var Release[] $expectedReleases
         */
        $expectedReleases = array(
            new Release( 1, 'Test release 1', 'a helpful url', new \DateTime() ),
            new Release( 2, 'Test release 2', 'another helpful url', new \DateTime() ),
            new Release( 3, 'Test release 3', 'a third helpful url', new \DateTime() )
        );

        $releasesToDelete = array();

        foreach ( $expectedReleases as $expectedRelease ) {
            $releasesToDelete[] = $this->addRelease( $expectedRelease->getReleaseName(),
                $expectedRelease->getReleaseInfoUrl() );
        }

        $releases = $this->gateway->getAllReleases();

        $this->assertCount( 3, $releases );

        foreach ( $expectedReleases as $index => $expectedRelease ) {
            $this->assertReleasesMatch( $expectedRelease, $releases[ $index ] );
        }
    }

    /**
     * @test
     */
    public function givenNoToggleWithGivenToggleIdFound_withNoTogglesInTheDatabase_returnFalse()
    {
        $response = $this->gateway->editRelease( "123", self::RELEASE_NAME, self::RELEASE_URL);
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenNoToggleWithGivenToggleIdFound_withTogglesInTheDatabase_returnFalse()
    {
        $this->addRelease( self::RELEASE_NAME, self::RELEASE_URL );
        $response = $this->gateway->editRelease( "123", self::RELEASE_NAME, self::RELEASE_URL);
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenToggleFound_editReleaseCalledAndUrlIsChanged_returnTrueAndModifyToggle()
    {
        $releaseId = $this->addRelease( self::RELEASE_NAME, "url" );
        $response = $this->gateway->editRelease( $releaseId, self::RELEASE_NAME, self::RELEASE_URL);
        $this->assertTrue( $response );
        $this->assertEquals( new Release( $releaseId, self::RELEASE_NAME, self::RELEASE_URL, new \DateTime(), true ), $this->gateway->getRelease( $releaseId ) );
    }

    /**
     * @test
     */
    public function givenToggleFound_editReleaseCalledAndNothingIsChanged_returnTrueAndDoNotModifyToggle()
    {
        $releaseId = $this->addRelease( self::RELEASE_NAME, self::RELEASE_URL);
        $response = $this->gateway->editRelease( $releaseId, self::RELEASE_NAME, self::RELEASE_URL);
        $this->assertTrue( $response );
        $this->assertEquals( new Release( $releaseId, self::RELEASE_NAME, self::RELEASE_URL, new \DateTime(), true ), $this->gateway->getRelease( $releaseId ) );
    }

    /**
     * @test
     */
    public function givenReleaseNotVisible_whenGettingFutureVisibleReleases_returnNothing()
    {
        $this->addRelease(self::RELEASE_NAME, self::RELEASE_URL, false);
        $response = $this->gateway->getAllFutureVisibleReleases();
        $this->assertEmpty($response);
    }

    /**
     * @test
     */
    public function givenReleaseVisibleAndInPast_whenGettingFutureVisibleReleases_returnNothing()
    {
        $pastDate = (new \DateTime)->modify("-1 days");
        $this->addRelease(self::RELEASE_NAME, self::RELEASE_URL, false, $pastDate);
        $response = $this->gateway->getAllFutureVisibleReleases();
        $this->assertEmpty($response);
    }

    /**
     * @test
     */
    public function givenReleaseInFutureAndNotVisible_whenGettingFutureVisibleReleases_returnNothing()
    {
        $futureDate = $this->getFutureDate();
        $this->addRelease(self::RELEASE_NAME, self::RELEASE_URL, false, $futureDate);
        $response = $this->gateway->getAllFutureVisibleReleases();
        $this->assertEmpty($response);
    }

    /**
     * @test
     */
    public function givenReleaseInFutureAndVisible_whenGettingFutureVisibleReleases_returnRelease()
    {
        $futureDate = $this->getFutureDate();
        $releaseId = $this->addRelease(self::RELEASE_NAME, self::RELEASE_URL, true, $futureDate);
        $response = $this->gateway->getAllFutureVisibleReleases();
        $expectedReleases = [new Release($releaseId, self::RELEASE_NAME, self::RELEASE_URL, $futureDate)];
        $this->assertAllReleasesMatch($expectedReleases, $response);
    }

    /**
     * @test
     */
    public function givenMultipleVisibleReleasesInTheFuture_whenGettingFutureVisibleReleases_returnAllReleases()
    {
        $futureDate = $this->getFutureDate();
        $expectedReleases = [];
        $releaseId = $this->addRelease(self::RELEASE_NAME, self::RELEASE_URL, true, $futureDate);
        $secondReleaseId = $this->addRelease(self::RELEASE_NAME, self::RELEASE_URL, true, $futureDate);
        $expectedReleases[] = new Release($releaseId, self::RELEASE_NAME, self::RELEASE_URL, $futureDate);
        $expectedReleases[] = new Release($secondReleaseId, self::RELEASE_NAME, self::RELEASE_URL, $futureDate);
        $response = $this->gateway->getAllFutureVisibleReleases();
        $this->assertAllReleasesMatch($expectedReleases, $response);
    }

    /**
     * @test
     */
    public function givenTwoReleasesInFutureWithOnlyOneVisible_whenGettingFutureVisibleReleases_returnOnlyVisibleRelease()
    {
        $futureDate = $this->getFutureDate();
        $expectedReleases = [];
        $releaseId = $this->addRelease(self::RELEASE_NAME, self::RELEASE_URL, true, $futureDate);
        $expectedReleases[] = new Release($releaseId, self::RELEASE_NAME, self::RELEASE_URL, $futureDate);
        $this->addRelease(self::RELEASE_NAME, self::RELEASE_URL, false, $futureDate);
        $response = $this->gateway->getAllFutureVisibleReleases();
        $this->assertAllReleasesMatch($expectedReleases, $response);
    }
}

//EOF MysqlReleaseGatewayTest.php