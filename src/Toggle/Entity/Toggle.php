<?php
namespace Clearbooks\LabsMysql\Toggle\Entity;

use Clearbooks\Labs\Toggle\Entity\ActivatableToggle;
use Clearbooks\Labs\Toggle\Entity\MarketableToggle;
use Clearbooks\Labs\Toggle\Entity\UserToggle;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 12/08/2015
 * Time: 14:14
 */
class Toggle implements MarketableToggle, UserToggle, ActivatableToggle
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
    private $descriptionOfToggle;
    /**
     * @var string
     */
    private $descriptionOfFunctionality;
    /**
     * @var string
     */
    private $descriptionOfImplementationReason;
    /**
     * @var string
     */
    private $descriptionOfLocation;
    /**
     * @var string
     */
    private $guideUrl;
    /**
     * @var string
     */
    private $appNotificationCopyText;

    /**
     * Toggle constructor.
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @param string $screenshotUrl
     * @param string $descriptionOfToggle
     * @param string $descriptionOfFunctionality
     * @param string $descriptionOfImplementationReason
     * @param string $descriptionOfLocation
     * @param string $guideUrl
     * @param string $appNotificationCopyText
     */
    public function __construct( $name, $releaseId, $isActive = false, $screenshotUrl = "", $descriptionOfToggle = "",
                                 $descriptionOfFunctionality = "", $descriptionOfImplementationReason = "",
                                 $descriptionOfLocation = "", $guideUrl = "", $appNotificationCopyText = "" )
    {
        $this->name = $name;
        $this->releaseId = $releaseId;
        $this->isActive = $isActive;
        $this->screenshotUrl = $screenshotUrl;
        $this->descriptionOfToggle = $descriptionOfToggle;
        $this->descriptionOfFunctionality = $descriptionOfFunctionality;
        $this->descriptionOfImplementationReason = $descriptionOfImplementationReason;
        $this->descriptionOfLocation = $descriptionOfLocation;
        $this->guideUrl = $guideUrl;
        $this->appNotificationCopyText = $appNotificationCopyText;
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
        return $this->descriptionOfToggle;
    }

    /**
     * @return string
     */
    public function getDescriptionOfFunctionality()
    {
        return $this->descriptionOfFunctionality;
    }

    /**
     * @return string
     */
    public function getDescriptionOfImplementationReason()
    {
        return $this->descriptionOfImplementationReason;
    }

    /**
     * @return string
     */
    public function  getDescriptionOfLocation()
    {
        return $this->descriptionOfLocation;
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
        return $this->appNotificationCopyText;
    }
}