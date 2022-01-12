<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Model;

use Magento\Backend\Model\Auth;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use VladimirPopov\WebForms\Model\Logic\Action;
use VladimirPopov\WebForms\Model\Logic\Aggregation;
use VladimirPopov\WebForms\Model\Logic\Condition;
use function is_array;
use const BP;

class Logic extends AbstractModel implements IdentityInterface
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const VISIBILITY_HIDDEN = 'hidden';
    const VISIBILITY_VISIBLE = 'visible';

    /**
     * Logic cache tag
     */
    const CACHE_TAG = 'webforms_logic';

    /**
     * @var string
     */
    protected $_cacheTag = 'webforms_logic';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webforms_logic';

    protected $_auth;

    protected $_fieldFactory;

    public function __construct(
        Auth $auth,
        FieldFactory $fieldFactory,
        StoreFactory $storeFactory,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_auth         = $auth;
        $this->_fieldFactory = $fieldFactory;
        parent::__construct($storeFactory, $context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('VladimirPopov\WebForms\Model\ResourceModel\Logic');
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

    public function ruleCheck($data, $logic_rules, $fieldMap)
    {
        $flag        = false;
        $input       = "";
        $input_value = false;

        if (!empty($data[$this->getFieldId()]))
            $input = $data[$this->getFieldId()];
        if(isset($data['field']) && is_array($data['field']) && isset($data['field'][$this->getFieldId()])){
            $input = $data['field'][$this->getFieldId()];
        }
        if (!is_array($input)) $input = array($input);

        // get trigger field visibility and set empty value if its not visible
        $trigger_field_id         = $this->getFieldId();
        $trigger_field_visibility = true;
        foreach ($logic_rules as $rule) {
            if (in_array('field_' . $trigger_field_id, $rule['target'])) {
                $visibility = 'hidden';
                if ($rule['action'] == 'hide') $visibility = 'visible';
                $trigger_field_target = array(
                    'id' => 'field_' . $trigger_field_id,
                    'logic_visibility' => $visibility
                );
                // escape infinite loop
                if (!in_array($trigger_field_target['id'], $rule['target']))
                    $trigger_field_visibility = $this->getTargetVisibility($trigger_field_target, $logic_rules, $data, $fieldMap);
            }
        }

        if ($trigger_field_visibility == false) {
            $input = array();
        }

        if (
            $this->getAggregation() == Aggregation::AGGREGATION_ANY ||
            (
                $this->getAggregation() == Aggregation::AGGREGATION_ALL &&
                $this->getLogicCondition() == Condition::CONDITION_NOTEQUAL
            )
        ) {

            if ($this->getLogicCondition() == Condition::CONDITION_EQUAL) {

                foreach ($input as $input_value) {
                    if (in_array($input_value, $this->getFrontendValue($input_value)))
                        $flag = true;
                }
            } else {
                $flag = true;
                foreach ($input as $input_value) {
                    if (in_array($input_value, $this->getFrontendValue($input_value))) $flag = false;
                }
                if (!count($input)) $flag = false;
            }
        } else {
            $flag = true;
            foreach ($this->getFrontendValue($input_value) as $trigger_value) {
                if (!in_array($trigger_value, $input)) {
                    $flag = false;
                }
            }
        }

        return $flag;
    }

    public function getTargetVisibility($target, $logic_rules, $data, $fieldMap)
    {
        $isTarget   = false;
        $action     = false;
        $visibility = false;
        foreach ($logic_rules as $logic) {

            foreach ($logic->getTarget() as $t) {
                if ($target["id"] == $t) {
                    $isTarget = true;
                    $action   = $logic->getAction();

                    $flag = $logic->ruleCheck($data, $logic_rules, $fieldMap);

                    if ($flag) {
                        $action     = $logic->getAction();
                        $visibility = true;
                        if ($action == Action::ACTION_HIDE) {
                            $visibility = false;
                        }

                        return $visibility;
                    } else {
                        if ($logic->getLogicCondition() == Condition::CONDITION_NOTEQUAL) {
                            $visibility = false;
                            if ($action == Action::ACTION_HIDE) {
                                $visibility = true;
                            }
                            return $visibility;
                        }
                    }
                }
            }
        }
        if ($target["logic_visibility"] == self::VISIBILITY_VISIBLE)
            $visibility = true;
        if ($isTarget && $action == Action::ACTION_SHOW) {
            $visibility = false;
        }
        return $visibility;
    }

    public function getFrontendValue($input_value = false)
    {
        if ($this->_auth->isLoggedIn())
            return $this->getValue();
        $field = $this->_fieldFactory->create()->setStoreId($this->getStoreId())->load($this->getFieldId());
        if ($field->getType() == 'select/contact') {
            if ($input_value && !is_numeric($input_value)) return $this->getValue();
            $return  = array();
            $options = $field->getOptionsArray();
            foreach ($options as $i => $option) {
                foreach ($this->getValue() as $trigger) {
                    $contact         = $field->getContactArray($option['value']);
                    $trigger_contact = $field->getContactArray($trigger);
                    if ($contact == $trigger_contact) {
                        $value = $option["value"];
                        if ($option['null']) {
                            $value = '';
                        }
                        if ($contact['email']) $return[] = $i;
                        else $return[] = $value;
                    }
                }
            }
            return $return;
        }
        return $this->getValue();
    }
}
