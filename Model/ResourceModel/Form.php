<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use function serialize;
use VladimirPopov\WebForms\Model\ResourceModel\Fieldset\CollectionFactory;
use VladimirPopov\WebForms\Model\StoreFactory;

/**
 * Form resource model
 *
 */
class Form extends AbstractResource
{
    /**
     *
     */
    const ENTITY_TYPE = 'form';

    /**
     * @return bool|string
     */
    public function getEntityType()
    {
        return self::ENTITY_TYPE;
    }

    /**
     * Name of scope for error messages
     *
     * @var string
     */
    protected $_messagesScope = 'webforms/session';

    /**
     * @var Field\CollectionFactory
     */
    protected $_fieldCollectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $_fieldsetCollectionFactory;

    /**
     * Form constructor.
     * @param Field\CollectionFactory $fieldCollectionFactory
     * @param CollectionFactory $fieldsetCollectionFactory
     * @param Context $context
     * @param DateTime\DateTime $date
     * @param StoreFactory $storeFactory
     * @param DateTime $dateTime
     * @param null $connectionName
     */
    public function __construct(
        Field\CollectionFactory $fieldCollectionFactory,
        CollectionFactory $fieldsetCollectionFactory,
        Context $context,
        DateTime\DateTime $date,
        StoreFactory $storeFactory,
        DateTime $dateTime,
        $connectionName = null
    )
    {
        $this->_fieldCollectionFactory    = $fieldCollectionFactory;
        $this->_fieldsetCollectionFactory = $fieldsetCollectionFactory;
        parent::__construct($context, $date, $storeFactory, $dateTime, $connectionName);
    }

    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('webforms', 'id');
    }

    /**
     * Set error messages scope
     *
     * @param string $scope
     * @return void
     */
    public function setMessagesScope($scope)
    {
        $this->_messagesScope = $scope;
    }

    /**
     * @param AbstractModel $object
     * @return AbstractResource
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        //delete fields
        $fields = $this->_fieldCollectionFactory->create()->addFilter('webform_id', $object->getId());
        foreach ($fields as $field) {
            $field->delete();
        }
        //delete fieldsets
        $fieldsets = $this->_fieldsetCollectionFactory->create()->addFilter('webform_id', $object->getId());
        foreach ($fieldsets as $fieldset) {
            $fieldset->delete();
        }

        return parent::_beforeDelete($object);
    }

    /**
     * @param AbstractModel $object
     * @return AbstractResource|void
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $object->setData('access_groups_serialized', serialize($object->getData('access_groups')));
        $object->setData('dashboard_groups_serialized', serialize($object->getData('dashboard_groups')));
        $object->setData('customer_result_permissions_serialized', serialize($object->getData('customer_result_permissions')));

        if ($object->isObjectNew() && !$object->hasCreatedTime()) {
            $object->setCreatedTime($this->_date->gmtDate());
        }

        $object->setUpdateTime($this->_date->gmtDate());

        parent::_beforeSave($object);
    }

    /**
     * @param AbstractModel $object
     * @return AbstractResource
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $object->setData('access_groups', @unserialize($object->getData('access_groups_serialized')));
        $object->setData('dashboard_groups', @unserialize($object->getData('dashboard_groups_serialized')));
        $object->setData('customer_result_permissions', @unserialize($object->getData('customer_result_permissions_serialized')));

        return parent::_afterLoad($object);
    }
}
