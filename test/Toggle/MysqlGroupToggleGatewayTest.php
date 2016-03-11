<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 19/08/2015
 * Time: 14:50
 */

namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\Labs\LabsTest;
use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;

class MysqlGroupToggleGatewayTest extends LabsTest
{
    /**
     * @var MysqlGroupToggleGateway
     */
    private $gateway;

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
     * @param string $toggleType
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false, $toggleType = "group" )
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
            [ 'name' => $name, 'release_id' => $releaseId, 'type' => $toggleType, 'visible' => $isActive ] );
    }

    public function setUp()
    {
        parent::setUp();
        $this->gateway = new MysqlGroupToggleGateway( $this->connection );
    }

    /**
     * @test
     */
    public function givenNoGroupTogglesFound_MysqlGroupToggleGateway_ReturnsEmptyArray()
    {
        $returnedToggle = $this->gateway->getAllGroupToggles();
        $this->assertEquals( [ ], $returnedToggle );
    }

    /**
     * @test
     */
    public function givenExistentGroupTogglesFound_MysqlGroupToggleGateway_ReturnsArrayOfGroupToggles()
    {
        $id = $this->addRelease( 'Test group toggle 1', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $id, true );

        $expectedToggles = [ new Toggle( $toggleId, "test1", $id, true, "group" ) ];

        $returnedToggles = $this->gateway->getAllGroupToggles();

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }

    /**
     * @test
     */
    public function givenExistentGroupTogglesAndNonGroupTogglesFound_MysqlGroupToggleGateway_ReturnsArrayOfGroupTogglesOnly()
    {
        $id = $this->addRelease( 'Test group toggle 2', 'a helpful url' );

        //Parameters: name, release_id, is_activatable, toggle_type
        $toggleId = $this->addToggle( "test1", $id, true, 2 );
        $toggleId2 = $this->addToggle( "test2", $id, true, 2 );
        $this->addToggle( "test3", $id, true, 1 );
        $this->addToggle( "test4", $id, true, 1 );

        $expectedToggles = [
            new Toggle( $toggleId, "test1", $id, true, "group" ),
            new Toggle( $toggleId2, "test2", $id, true, "group" )
        ];
        $returnedToggles = $this->gateway->getAllGroupToggles();

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }

    /**
     * @test
     */
    public function givenGroupToggleWithMarketingInfo_GetAllGroupTogglesReturnsToggleWithMarketingInfo()
    {
        $id = $this->addRelease( 'Release name', 'url' );

        $toggleName = "marketing";
        $toggleId = $this->addToggle( $toggleName, $id, true );
        $marketingInfo = array(
            'toggle_id' => $toggleId,
            'screenshot_urls' => 'url',
            'description_of_toggle' => 'togggle',
            'description_of_functionality' => 'does this',
            'description_of_implementation_reason' => 'because this',
            'description_of_location' => 'find it there',
            'guide_url' => 'here is some help',
            'app_notification_copy_text' => 'look a new thing',
        );
        $this->connection->insert( '`toggle_marketing_information`', $marketingInfo );

        $expectedToggle = new Toggle( $toggleId, $toggleName,  $id, true, "group",
            $marketingInfo['screenshot_urls'],
            $marketingInfo['description_of_toggle'],
            $marketingInfo['description_of_functionality'],
            $marketingInfo['description_of_implementation_reason'],
            $marketingInfo['description_of_location'],
            $marketingInfo['guide_url'],
            $marketingInfo['app_notification_copy_text']);

        $expectedToggles[] = $expectedToggle;
        $returnedToggles = $this->gateway->getAllGroupToggles();

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }
}
