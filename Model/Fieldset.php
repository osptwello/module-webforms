<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Store\Model\ScopeInterface;

class Fieldset extends AbstractModel implements IdentityInterface
{
    /**#@+
     * Page's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * Fieldset cache tag
     */
    const CACHE_TAG = 'webforms_fieldset';

    /**
     * @var string
     */
    protected $_cacheTag = 'webforms_fieldset';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webforms_fieldset';

    protected $_fieldFactory;

    protected $_fieldsetFactory;

    protected $_scopeConfig;

    protected $_localDate;

    public function __construct(
        \VladimirPopov\WebForms\Model\FieldFactory $fieldFactory,
        \VladimirPopov\WebForms\Model\FieldsetFactory $fieldsetFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $localeDate,
        \VladimirPopov\WebForms\Model\StoreFactory $storeFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_fieldFactory = $fieldFactory;
        $this->_fieldsetFactory = $fieldsetFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_localDate = $localeDate;
        parent::__construct($storeFactory, $context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('VladimirPopov\WebForms\Model\ResourceModel\Fieldset');
    }

    /**
     * Prepare form's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData('id');
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getId();
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getData('is_active');
    }

    public function duplicate()
    {
        // duplicate fieldset
        $fieldset = $this->_fieldsetFactory->create()
            ->setData($this->getData())
            ->setId(null)
            ->setName($this->getName() . ' ' . __('(new copy)'))
            ->setIsActive(false)
            ->setCreatedTime($this->_localDate->gmtDate())
            ->setUpdateTime($this->_localDate->gmtDate())
            ->save();

        // duplicate store data
        $stores = $this->_storeFactory->create()
            ->getCollection()
            ->addFilter('entity_id', $this->getId())
            ->addFilter('entity_type', $this->getEntityType());

        foreach ($stores as $store) {
            $duplicate = $this->_storeFactory->create()
                ->setData($store->getData())
                ->setId(null)
                ->setEntityId($fieldset->getId())
                ->save();
        }

        // duplicate fields
        $fields = $this->_fieldFactory->create()->getCollection()->addFilter('fieldset_id', $this->getId());
        foreach ($fields as $field) {
            $field->duplicate()
                ->setFieldsetId($fieldset->getId())
                ->save();
        }

        return $fieldset;
    }

    public function getName()
    {
        if ($this->_scopeConfig->getValue('webforms/general/use_translation',ScopeInterface::SCOPE_STORE)) {
            return __($this->getData('name'));
        }

        return $this->getData('name');
    }

    public function getSizeValues($empty_option = false)
    {
        $sizes = [
            "1-1" => __('1/1'),
            "1-2" => __('1/2'),
            "1-3" => __('1/3'),
            "1-4" => __('1/4'),
            "2-3" => __('2/3'),
            "3-4" => __('3/4'),
        ];
        if ($empty_option) $sizes = array_merge([false => __('Not set')], $sizes);

        return $sizes;

    }

    public function getResponsiveCss()
    {
        $prefix = $this->_fieldFactory->create()->getCssGridPrefix() . "-";
        $class = [];

        if ($this->getWidthLg()) {
            $class [] = $prefix . "lg-" . $this->getWidthLg();
        }
        if ($this->getWidthMd()) {
            $class [] = $prefix . "md-" . $this->getWidthMd();
        }
        if ($this->getWidthSm()) {
            $class [] = $prefix . "sm-" . $this->getWidthSm();
        }

        if ($this->getRowLg()) {
            $class [] = $prefix . "lg-row";
        }
        if ($this->getRowMd()) {
            $class [] = $prefix . "md-row";
        }
        if ($this->getRowSm()) {
            $class [] = $prefix . "sm-row";
        }
        return implode(" ", $class);
    }
}
