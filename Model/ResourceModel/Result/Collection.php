<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Model\ResourceModel\Result;

use function floatval;

/**
 * Result collection
 *
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_fieldFactory;

    protected $_loadValues = false;

    protected $customerFactory;

    public function __construct(
        \VladimirPopov\WebForms\Model\FieldFactory $fieldFactory,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    )
    {
        $this->_fieldFactory   = $fieldFactory;
        $this->customerFactory = $customerFactory;
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
        $this->_init('VladimirPopov\WebForms\Model\Result', 'VladimirPopov\WebForms\Model\ResourceModel\Result');
    }

    public function setLoadValues($value)
    {
        $this->_loadValues = $value;
        return $this;
    }

    /**
     * Returns select count sql
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);

        if (count($this->getSelect()->getPart(\Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(\Zend_Db_Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(\Zend_Db_Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
        } else {
            $countSelect->columns('COUNT(*)');
        }
        return $countSelect;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this as $item) {
            if ($this->_loadValues) {
                if ($item->getData('id')) {
                    $query   = $this->getConnection()->select()
                        ->from($this->getTable('webforms_results_values'))
                        ->where($this->getTable('webforms_results_values') . '.result_id = ' . $item->getData('id'));
                    $results = $this->getConnection()->fetchAll($query);
                    foreach ($results as $result) {
                        $item->setData('field_' . $result['field_id'], trim($result['value']));
                        $item->setData('key_' . $result['field_id'], $result['key']);
                    }
                }
            }
            $item->setData('ip', long2ip($item->getCustomerIp()));

        }

        $this->_eventManager->dispatch('webforms_results_collection_load', array('collection' => $this));

        return $this;
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'customer') {
            // custom condition
            $value = substr($condition['like'], 1, -1);
            while (strstr($value, "  ")) {
                $value = str_replace("  ", " ", $value);
            }
            $customers_array = [];

            $name      = explode(" ", $value);
            $firstname = $name[0];

            $lastname = $name[count($name) - 1];

            $customers = $this->customerFactory->create()->getCollection()
                ->addAttributeToFilter('firstname', $firstname);
            if (count($name) == 2)
                $customers->addAttributeToFilter('lastname', $lastname);
            foreach ($customers as $customer) {
                $customers_array[] = $customer->getId();
            }
            return parent::addFieldToFilter('customer_id', ['in' => $customers_array]);
        }

        if (isset($condition['gteq']) && !$this->validateDate($condition['gteq'])) $condition['gteq'] = floatval($condition['gteq']);
        if (isset($condition['lteq']) && !$this->validateDate($condition['lteq'])) $condition['lteq'] = floatval($condition['lteq']);

        return parent::addFieldToFilter($field, $condition);
    }

    private function validateDate($date)
    {
        return (bool)strtotime($date);
    }

    public function addFieldFilter($field_id, $value, $strict = false)
    {
        $prefix = '%';
        if ($strict == true) {
            $prefix = '';
        }
        $field        = $this->_fieldFactory->create()->load($field_id);
        $cond         = "";
        $search_value = "";
        if (is_string($value)) {
            $search_value = trim(str_replace(array("\\"), array("\\\\"), $value));
            $search_value = trim(str_replace(array("'"), array("\\'"), $search_value));
            $cond         = "results_values_$field_id.value like '" . $prefix . $search_value . $prefix . "'";
        }
        if ($field->getType() == 'select' || $field->getType() == 'select/radio') {
            $cond = "results_values_$field_id.value like '" . $search_value . "'";
        }
        if (is_array($value)) {
            if (strstr($field->getType(), 'date')) {
                if (!empty($value['from'])) $value['from'] = "'" . date($field->getDbDateFormat(), strtotime($value['orig_from'])) . "'";
                if (!empty($value['to'])) $value['to'] = "'" . date($field->getDbDateFormat(), strtotime($value['orig_to'])) . "'";
            }
            if (!empty($value['from'])) {
                $cond = "results_values_$field_id.value >= $value[from]";
            }
            if (!empty($value['to'])) {
                $cond = "results_values_$field_id.value <= $value[to]";
            }
            if (!empty($value['from']) && !empty($value['to'])) {
                $cond = "results_values_$field_id.value >= $value[from] AND results_values_$field_id.value <= $value[to]";
            }
            if (!empty($value['in']) && is_array($value['in'])) {
                $cond = "results_values_$field_id.value IN ('" . implode("','", str_replace("'", "\'", $value['in'])) . "')";
            }
        }
        $this->getSelect()
            ->join(array('results_values_' . $field_id => $this->getTable('webforms_results_values')), 'main_table.id = results_values_' . $field_id . '.result_id', array('main_table.*'))
            ->group('main_table.id');

        $this->getSelect()
            ->where("results_values_$field_id.field_id = $field_id AND $cond");

        return $this;
    }


}
