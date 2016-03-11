<?php
namespace Clearbooks\LabsMysql\AutoSubscribe\Entity;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 04/09/2015
 * Time: 10:18
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    const USER_ID = "TheChosenOne";

    /**
     * @var array
     */
    private $emptyUserData;

    /**
     * @param User $user
     * @param array $emptyUserData
     */
    private function assertUserGettersMatchExpectedUserData( User $user, $emptyUserData )
    {
        $this->assertEquals( $emptyUserData[ 'userId' ], $user->getId() );
    }

    public function setUp()
    {
        parent::setUp();
        $this->emptyUserData = array(
            'userId' => ''
        );
    }

    /**
     * @test
     */
    public function givenNoData_AllGettersDefaultCorrectly()
    {
        $user = new User( "" );
        $this->assertUserGettersMatchExpectedUserData( $user, $this->emptyUserData );
    }

    /**
     * @test
     */
    public function givenUserWithUserId_GetIdGetterReturnsCorrectId()
    {
        $user = new User( self::USER_ID );

        $userData = $this->emptyUserData;
        $userData[ 'userId' ] = self::USER_ID;

        $this->assertUserGettersMatchExpectedUserData( $user, $userData );
    }
}
