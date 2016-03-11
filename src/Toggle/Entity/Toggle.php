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
     * @var
     */
    private $id;
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
     * @var string
     */
    private $toggleType;

    /**
     * @var string
     */
    private $marketingToggleTitle;

    /**
     * Toggle constructor.
     * @param $id
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @param string $toggleType
     * @param string $screenshotUrl
     * @param string $descriptionOfToggle
     * @param string $descriptionOfFunctionality
     * @param string $descriptionOfImplementationReason
     * @param string $descriptionOfLocation
     * @param string $guideUrl
     * @param string $appNotificationCopyText
     * @param string $marketingToggleTitle
     */
    public function __construct( $id, $name, $releaseId, $isActive = false, $toggleType = "simple", $screenshotUrl = null,
                                 $descriptionOfToggle = null, $descriptionOfFunctionality = null,
                                 $descriptionOfImplementationReason = null, $descriptionOfLocation = null, $guideUrl = null,
                                 $appNotificationCopyText = null, $marketingToggleTitle = null )
    {
        $this->id = $id;
        $this->name = $name;
        $this->releaseId = $releaseId;
        $this->isActive = $isActive;
        $this->screenshotUrl = $screenshotUrl;
        $this->descriptionOfToggle = $descriptionOfToggle;
        $this->descriptionOfFunctionality = $descriptionOfFunctionality;
        $this->descriptionOfImplementationReason = $descriptionOfImplementationReason;
        $this->descriptionOfLocation = $descriptionOfLocation;
        $this->guideUrl = $guideUrl;
        $this->appNotificationCopyText = $appNotificationCopyText;;
        $this->toggleType = $toggleType;
        $this->marketingToggleTitle = $marketingToggleTitle;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
        return $this->isActive;
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

    /**
     * @return string
     */
    public function getType()
    {
        return $this->toggleType;
    }

    /**
     * @return string
     */
    public function getMarketingToggleTitle()
    {
        return $this->marketingToggleTitle;
    }
}