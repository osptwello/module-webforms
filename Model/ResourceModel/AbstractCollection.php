<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Model\ResourceModel;

use function is_array;
use function unserialize;

/**
 * Abstract collection
 *
 */
class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Store table name
     *
     * @var string
     */
    protected $_storeTable;

    protected $_storeFactory;

    protected $_store_id;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \VladimirPopov\WebForms\Model\StoreFactory $storeFactory,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    )
    {
        $this->_storeFactory = $storeFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('VladimirPopov\WebForms\Model\AbstractModel', 'VladimirPopov\WebForms\Model\ResourceModel\AbstractResource');
    }

    /**
     * Returns select count sql
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $countSelect = clone $this->getSelect();

        $countSelect->reset(\Zend_Db_Select::HAVING);

        return $select;
    }

    public function setStoreId($store_id)
    {
        $this->_store_id = $store_id;
        return $this;
    }

    public function getStoreId()
    {
        return $this->_store_id;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        $store_id = $this->getStoreId();
        if (!$store_id)
            $store_id = $this->getResource()->getStoreId();
        if ($store_id) {
            foreach ($this as $item) {
                $store = $this->_storeFactory->create()->search($store_id, $this->getResource()->getEntityType(), $item->getId());
                $store_data = $store->getStoreData();
                if ($store_data) {
                    foreach ($store_data as $key => $val) {
                        if (is_array($val)) {
                            $orig_val = $item->getData($key);
                            if(!is_array($orig_val)) $orig_val = @unserialize($orig_val);
                            if(is_array($orig_val)) {
                                foreach ($orig_val as $k => $v) {
                                    if(empty($val[$k]))
                                        $val[$k] = $v;
                                }
                            }
                        }
                        $item->setData($key, $val);
                    }
                }
            }
        }
        return $this;
    }

}
