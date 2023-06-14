<?php
namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\Labs\Db\Table\Toggle as ToggleTable;
use Clearbooks\Labs\Db\Table\Release as ReleaseTable;
use Clearbooks\Labs\LabsTest;
use Clearbooks\Labs\Toggle\Entity\MarketableToggle;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;

class MysqlGetTogglesVisibleWithoutReleaseGatewayTest extends LabsTest
{
    /**
     * @var ToggleTable
     */
    private $toggleTable;

    /**
     * @var ReleaseTable
     */
    private $releaseTable;

    /**
     * @var MysqlGetTogglesVisibleWithoutReleaseGateway
     */
    private $mysqlGetTogglesVisibleWithoutReleaseGateway;

    public function setUp(): void
    {
        parent::setUp();
        $this->toggleTable = new ToggleTable();
        $this->releaseTable = new ReleaseTable();
        $this->mysqlGetTogglesVisibleWithoutReleaseGateway = new MysqlGetTogglesVisibleWithoutReleaseGateway(
                $this->connection,
                $this->toggleTable
        );
    }

    /**
     * @param string $name
     * @param string $type
     * @param bool $visible
     * @param int $releaseId
     * @param bool $visibleWithoutRelease
     * @return Toggle
     */
    private function createTestToggle( $name, $type, $visible, $releaseId, $visibleWithoutRelease )
    {
        if ( $releaseId !== null ) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $numberOfReleasesWithGivenId = $queryBuilder->select( "COUNT(id)" )
                                                        ->from( (string)$this->releaseTable )
                                                        ->where( "id = ?" )
                                                        ->setParameter( 0, $releaseId )
                                                        ->executeQuery()
                                                        ->fetchOne();

            if ( $numberOfReleasesWithGivenId == 0 ) {
                $this->connection->insert(
                        (string)$this->releaseTable,
                        [
                                "id" => $releaseId,
                                "name" => "Test release " . $releaseId,
                                "info" => "",
                                "visibility" => 1,
                                "release_date" => "2099-12-31"
                        ]
                );
            }
        }

        $affectedRows = $this->connection->insert(
                (string)$this->toggleTable,
                [
                        "name" => $name,
                        "type" => $type,
                        "visible" => $visible ? 1 : 0,
                        "release_id" => $releaseId,
                        "visible_without_release" => $visibleWithoutRelease ? 1 : 0
                ]
        );

        if ( $affectedRows < 1 ) {
            return null;
        }

        $toggleId = $this->connection->lastInsertId();
        return new Toggle( $toggleId, $name, $releaseId, $visible, $type );
    }

    /**
     * @param MarketableToggle[] $expectedToggles
     */
    private function assertRetrievedUserTogglesEqual( array $expectedToggles )
    {
        $toggles = $this->mysqlGetTogglesVisibleWithoutReleaseGateway->getUserTogglesVisibleWithoutRelease();
        $this->assertEquals(
                $expectedToggles,
                $toggles
        );
    }

    /**
     * @param MarketableToggle[] $expectedToggles
     */
    private function assertRetrievedGroupTogglesEqual( array $expectedToggles )
    {
        $toggles = $this->mysqlGetTogglesVisibleWithoutReleaseGateway->getGroupTogglesVisibleWithoutRelease();
        $this->assertEquals(
                $expectedToggles,
                $toggles
        );
    }

    /**
     * @test
     */
    public function GivenNoToggles_WhenRetrievingUserToggles_ExpectEmptyArray()
    {
        $this->assertRetrievedUserTogglesEqual( [ ] );
    }

    /**
     * @test
     */
    public function GivenNoToggles_WhenRetrievingGroupToggles_ExpectEmptyArray()
    {
        $this->assertRetrievedGroupTogglesEqual( [ ] );
    }

    /**
     * @test
     */
    public function GivenVisibleUserToggleExistButNotVisibleWithoutRelease_WhenRetrievingUserToggles_ExpectEmptyArray()
    {
        $this->createTestToggle( "Test toggle", ToggleTable::TYPE_SIMPLE, true, null, false );
        $this->assertRetrievedUserTogglesEqual( [ ] );
    }

    /**
     * @test
     */
    public function GivenVisibleGroupToggleExistButNotVisibleWithoutRelease_WhenRetrievingGroupToggles_ExpectEmptyArray()
    {
        $this->createTestToggle( "Test toggle", ToggleTable::TYPE_GROUP, true, null, false );
        $this->assertRetrievedGroupTogglesEqual( [ ] );
    }

    /**
     * @test
     */
    public function GivenVisibleUserToggleExistAndVisibleWithoutRelease_WhenRetrievingUserToggles_ExpectToggle()
    {
        $expectedToggle = $this->createTestToggle( "Test toggle", ToggleTable::TYPE_SIMPLE, true, null, true );
        $this->assertRetrievedUserTogglesEqual( [ $expectedToggle ] );
    }

    /**
     * @test
     */
    public function GivenVisibleGroupToggleExistAndVisibleWithoutRelease_WhenRetrievingGroupToggles_ExpectToggle()
    {
        $expectedToggle = $this->createTestToggle( "Test toggle", ToggleTable::TYPE_GROUP, true, null, true );
        $this->assertRetrievedGroupTogglesEqual( [ $expectedToggle ] );
    }

    /**
     * @test
     */
    public function GivenVisibleUserToggleExistAndVisibleWithoutReleaseButReleaseIdIsSet_WhenRetrievingUserToggles_ExpectEmptyArray()
    {
        $this->createTestToggle( "Test toggle", ToggleTable::TYPE_SIMPLE, true, 1, true );
        $this->assertRetrievedUserTogglesEqual( [ ] );
    }

    /**
     * @test
     */
    public function GivenVisibleGroupToggleExistAndVisibleWithoutReleaseButReleaseIdIsSet_WhenRetrievingGroupToggles_ExpectEmptyArray()
    {
        $this->createTestToggle( "Test toggle", ToggleTable::TYPE_GROUP, true, 1, true );
        $this->assertRetrievedGroupTogglesEqual( [ ] );
    }

    /**
     * @test
     */
    public function GivenMultipleTogglesExist_WhenRetrievingUserToggles_ExpectUserToggles()
    {
        $expectedToggles = [ ];
        $expectedToggles[] = $this->createTestToggle( "Test toggle 1", ToggleTable::TYPE_SIMPLE, true, null, true );
        $this->createTestToggle( "Test toggle 2", ToggleTable::TYPE_GROUP, true, null, true );
        $expectedToggles[] = $this->createTestToggle( "Test toggle 3", ToggleTable::TYPE_SIMPLE, true, null, true );
        $expectedToggles[] = $this->createTestToggle( "Test toggle 4", ToggleTable::TYPE_SIMPLE, true, null, true );

        $this->assertRetrievedUserTogglesEqual( $expectedToggles );
    }

    /**
     * @test
     */
    public function GivenMultipleTogglesExist_WhenRetrievingGroupToggles_ExpectGroupToggles()
    {
        $expectedToggles = [ ];
        $expectedToggles[] = $this->createTestToggle( "Test toggle 1", ToggleTable::TYPE_GROUP, true, null, true );
        $this->createTestToggle( "Test toggle 2", ToggleTable::TYPE_SIMPLE, true, null, true );
        $expectedToggles[] = $this->createTestToggle( "Test toggle 3", ToggleTable::TYPE_GROUP, true, null, true );
        $expectedToggles[] = $this->createTestToggle( "Test toggle 4", ToggleTable::TYPE_GROUP, true, null, true );

        $this->assertRetrievedGroupTogglesEqual( $expectedToggles );
    }
}
