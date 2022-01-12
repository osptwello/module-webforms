<?php

namespace VladimirPopov\WebForms\Model\ResourceModel;

class Dropzone extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_date;

    protected $_eventManager;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    )
    {
        $this->_date = $date;
        $this->_eventManager = $eventManager;
        parent::__construct($context, $resourcePrefix);
    }

    protected function _construct()
    {
        $this->_init('webforms_dropzone', 'id');
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {

        if (!$object->getId() && $object->getCreatedTime() == "") {
            $object->setCreatedTime($this->_date->gmtDate());
        }
        return $this;
    }

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {

        @unlink($object->getFullPath());

        $this->_eventManager->dispatch('webforms_dropzone_delete', array('file' => $object));

        return parent::_beforeDelete($object);
    }
}