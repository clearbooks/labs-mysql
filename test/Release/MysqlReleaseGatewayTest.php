<?php
/**
 * @author: Ryan Wood <ryanw@clearbooks.co.uk>
 * @created: 10/08/15
 */

namespace Clearbooks\LabsMysql\Release;

use Clearbooks\Labs\LabsTest;
use Clearbooks\Labs\Release\Release;

class MysqlReleaseGatewayTest extends LabsTest
{
    /**
     * @var MysqlReleaseGateway
     */
    private $gateway;

    /**
     * @param $releaseName
     * @param $url
     * @return string
     */
    private function addRelease( $releaseName, $url )
    {
        $this->gateway->addRelease( $releaseName, $url );
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

    public function setUp(): void
    {
        parent::setUp();
        $this->gateway = new MysqlReleaseGateway( $this->connection );
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
            'release_date' => null
        );
        $this->assertEquals( $expectedRelease,
            $this->connection->executeQuery( 'SELECT * FROM `release` WHERE `id` = ?', [ $id ] )->fetchAssociative() );
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
        $id = $this->addRelease( 'Test release 1', 'a helpful url' );
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
        $id = $this->addRelease( $releaseName, $url );

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
        $response = $this->gateway->editRelease( "123", "test", "brollies" );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenNoToggleWithGivenToggleIdFound_withTogglesInTheDatabase_returnFalse()
    {
        $this->addRelease( "test", "url" );
        $response = $this->gateway->editRelease( "123", "test", "brollies" );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenToggleFound_editReleaseCalledAndUrlIsChanged_returnTrueAndModifyToggle()
    {
        $releaseId = $this->addRelease( "test", "url" );
        $response = $this->gateway->editRelease( $releaseId, "test", "brollies" );
        $this->assertTrue( $response );
        $this->assertEquals( new Release( $releaseId, "test", "brollies", (new \DateTime())->modify('midnight'), true ), $this->gateway->getRelease( $releaseId ) );
    }

    /**
     * @test
     */
    public function givenToggleFound_editReleaseCalledAndNothingIsChanged_returnTrueAndDoNotModifyToggle()
    {
        $releaseId = $this->addRelease( "test", "brollies" );
        $response = $this->gateway->editRelease( $releaseId, "test", "brollies" );
        $this->assertTrue( $response );
        $this->assertEquals( new Release( $releaseId, "test", "brollies", (new \DateTime())->modify('midnight'), true ), $this->gateway->getRelease( $releaseId ) );
    }
}

//EOF MysqlReleaseGatewayTest.php
