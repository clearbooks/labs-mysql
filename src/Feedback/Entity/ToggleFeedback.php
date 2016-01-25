<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 22/01/16
 * Time: 14:41
 */

namespace Clearbooks\LabsMysql\Feedback\Entity;

use Clearbooks\Labs\Feedback\Entity\IToggleFeedback;

class ToggleFeedback implements IToggleFeedback
{
    /**
     * @var
     */
    private $name;
    /**
     * @var
     */
    private $mood;
    /**
     * @var
     */
    private $message;
    /**
     * @var
     */
    private $userId;
    /**
     * @var
     */
    private $groupId;

    /**
     * ToggleFeedback constructor.
     * @param $name
     * @param $mood
     * @param $message
     * @param $userId
     * @param $groupId
     */
    public function __construct($name, $mood, $message, $userId, $groupId)
    {
        $this->name = $name;
        $this->mood = $mood;
        $this->message = $message;
        $this->userId = $userId;
        $this->groupId = $groupId;
    }


    /**
     * @return string
     */
    public function getToggleName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFeedbackMood()
    {
        return $this->mood;
    }

    /**
     * @return string
     */
    public function getFeedbackMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getFeedbackUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getFeedbackGroupId()
    {
        return $this->groupId;
    }
}