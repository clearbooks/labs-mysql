<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 22/01/16
 * Time: 14:48
 */

namespace Clearbooks\LabsMysql\Feedback;

use Clearbooks\Labs\Feedback\Gateway\GetFeedbackForTogglesGateway;
use Clearbooks\Labs\LabsTest;
use Clearbooks\LabsMysql\Feedback\Entity\ToggleFeedback;

class MysqlGetAllFeedbackForTogglesTest extends LabsTest
{
    const TOGGLE_ID = 9001;
    const MOOD = 1;
    const MESSAGE = "message";
    const USER_ID = 2;
    const GROUP_ID = 3;
    const TOGGLE_NAME = 'meowToggle';

    /** @var GetFeedbackForTogglesGateway */
    private $gateway;

    public function setUp(): void
    {
        parent::setUp();
        $this->gateway = new MysqlGetAllFeedbackForToggles($this->connection);
    }

    /**
     * @test
     */
    public function givenToggleWithFeedback_whenGettingFeedback_returnArrayOfFeedbackToggles()
    {
        $this->connection->insert('`toggle`', ['name' => self::TOGGLE_NAME, 'type' => 'simple', 'visible' => 1]);
        $toggleId = $this->connection->lastInsertId();
        (new MysqlInsertFeedbackForToggleGateway($this->connection))->addFeedbackForToggle($toggleId, self::MOOD,
            self::MESSAGE, self::USER_ID, self::GROUP_ID);

        $expectedToggleFeedback = new ToggleFeedback(self::TOGGLE_NAME, self::MOOD, self::MESSAGE, self::USER_ID, self::GROUP_ID);

        $this->assertEquals([$expectedToggleFeedback], $this->gateway->getFeedbackForToggles());
    }
}
