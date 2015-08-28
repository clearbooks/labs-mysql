<?php
namespace Clearbooks\LabsMysql\Toggle\Entity;

use Clearbooks\Labs\Toggle\Entity\ActivatableToggle;
use Clearbooks\Labs\Toggle\Entity\UserToggle;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 12/08/2015
 * Time: 14:14
 */
class Toggle implements \Clearbooks\Labs\Toggle\Entity\MarketableToggle, UserToggle, ActivatableToggle
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $releaseId;
    /**
     * @var bool
     */
    private $isActive;
    /**
     * @var string
     */
    private $screenshotUrl;
    /**
     * @var string
     */
    private $toggleDesc;
    /**
     * @var string
     */
    private $functionalityDes;
    /**
     * @var string
     */
    private $reasonWhyDesc;
    /**
     * @var string
     */
    private $locationDesc;
    /**
     * @var string
     */
    private $guideUrl;
    /**
     * @var string
     */
    private $notificationCopy;

    /**
     * Toggle constructor.
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @param string $screenshotUrl
     * @param string $toggleDesc
     * @param string $functionalityDes
     * @param string $reasonWhyDesc
     * @param string $locationDesc
     * @param string $guideUrl
     * @param string $notificationCopy
     */
    public function __construct( $name, $releaseId, $isActive = false,
                                 $screenshotUrl = '', $toggleDesc = '',
                                 $functionalityDes = '', $reasonWhyDesc = '',
                                 $locationDesc = '', $guideUrl = '', $notificationCopy = '' )
    {
        $this->name = $name;
        $this->releaseId = $releaseId;
        $this->isActive = $isActive;
        $this->screenshotUrl = $screenshotUrl;
        $this->toggleDesc = $toggleDesc;
        $this->functionalityDes = $functionalityDes;
        $this->reasonWhyDesc = $reasonWhyDesc;
        $this->locationDesc = $locationDesc;
        $this->guideUrl = $guideUrl;
        $this->notificationCopy = $notificationCopy;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRelease()
    {
        return $this->releaseId;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->releaseId;
    }

    /**
     * @return string
     */
    public function getScreenshotUrl()
    {
        return $this->screenshotUrl;
    }

    /**
     * @return string
     */
    public function getDescriptionOfToggle()
    {
        return $this->toggleDesc;
    }

    /**
     * @return string
     */
    public function getDescriptionOfFunctionality()
    {
        return $this->functionalityDes;
    }

    /**
     * @return string
     */
    public function getDescriptionOfImplementationReason()
    {
        return $this->reasonWhyDesc;
    }

    /**
     * @return string
     */
    public function getDescriptionOfLocation()
    {
        return $this->locationDesc;
    }

    /**
     * @return string
     */
    public function getGuideUrl()
    {
        return $this->guideUrl;
    }

    /**
     * @return string
     */
    public function getAppNotificationCopyText()
    {
        return $this->notificationCopy;
    }
}