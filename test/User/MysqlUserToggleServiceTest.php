<?php



use Clearbooks\LabsMysql\User\MysqlUserToggleService;

/**
 * Created by PhpStorm.
 * User: Vovaxs
 * Date: 18/08/2015
 * Time: 11:57
 */
class MysqlUserToggleServiceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    /**
     * @test
     */
    public function givenNoToggleAndNoUserFound_MysqlUserToggleService_ReturnsFalse()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }

    /**
     * @test
     */
    public function givenNoToggleFoundButWithExistentUser_MysqlUserToggleService_ReturnsFalse()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }

    /**
     * @test
     */
    public function givenNoUserFoundButWithExistentToggle_MysqlUserToggleService_ReturnsFalse()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }

    /**
     * @test
     */
    public function givenExistentUserWithNotActivatedGivenExistentToggle_DuringDeactivationAttempt_MysqlUserToggleService_ReturnsFalse()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }

    /**
     * @test
     */
    public function givenExistentUserWithNotActivatedGivenExistentToggle_DuringActivationAttempt_MysqlUserToggleService_ReturnsTrue()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }

    /**
     * @test
     */
    public function givenExistentUserWithActivatedGivenExistentToggle_DuringActivationAttempt_MysqlUserToggleService_ReturnsFalse()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }

    /**
     * @test
     */
    public function givenExistentUserWithActivatedGivenExistentToggle_DuringDeactivationAttempt_MysqlUserToggleService_ReturnsTrue()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }
}
