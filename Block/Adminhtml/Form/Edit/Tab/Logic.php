<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Form\Edit\Tab;

class Logic extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Logic');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Logic');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return $this->_coreRegistry->registry('webforms_form')->getLogic()->count() ? false : true;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @var \VladimirPopov\WebForms\Model\ResourceModel\Logic\CollectionFactory
     */
    protected $_logicCollectionFactory;

    /**
     * @var \VladimirPopov\WebForms\Model\LogicFactory
     */
    protected $_logicFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    protected $_logicAction;

    protected $_logicCondition;

    protected $_logicAggregation;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \VladimirPopov\WebForms\Model\ResourceModel\Logic\CollectionFactory $logicCollectionFactory
     * @param \VladimirPopov\WebForms\Model\LogicFactory $logicFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \VladimirPopov\WebForms\Model\ResourceModel\Logic\CollectionFactory $logicCollectionFactory,
        \VladimirPopov\WebForms\Model\LogicFactory $logicFactory,
        \Magento\Framework\Registry $registry,
        \VladimirPopov\WebForms\Model\Logic\Action $logicAction,
        \VladimirPopov\WebForms\Model\Logic\Condition $logicCondition,
        \VladimirPopov\WebForms\Model\Logic\Aggregation $logicAggregation,
        array $data = []
    )
    {
        $this->_logicCollectionFactory = $logicCollectionFactory;
        $this->_logicFactory = $logicFactory;
        $this->_coreRegistry = $registry;
        $this->_logicAction = $logicAction;
        $this->_logicCondition = $logicCondition;
        $this->_logicAggregation = $logicAggregation;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('logic_section');
        $this->setDefaultSort('id');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_coreRegistry->registry('webforms_form')->getLogic();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _filterIdCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addFilter('main_table.id', $value);
    }
    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        /** @var \VladimirPopov\WebForms\Model\Form $modelForm */
        $modelForm = $this->_coreRegistry->registry('webforms_form');
        $this->addColumn('id', array(
            'header' => __('ID'),
            'width' => 60,
            'index' => 'id',
            'filter_condition_callback' => array($this, '_filterIdCondition'),
        ));

        $this->addColumn('name', array(
            'header' => __('Field'),
            'index' => 'name',
        ));

        $this->addColumn('logic_condition', array(
            'header' => __('Condition'),
            'index' => 'logic_condition',
            'type' => 'options',
            'options' => $this->_logicCondition->getOptions()
        ));

        $this->addColumn('value', array(
            'header' => __('Trigger value(s)'),
            'index' => 'value',
            'renderer' => 'VladimirPopov\WebForms\Block\Adminhtml\Logic\Renderer\Value'
        ));

        $this->addColumn('action', array(
            'header' => __('Action'),
            'index' => 'action',
            'type' => 'options',
            'options' => $this->_logicAction->getOptions()
        ));

        $this->addColumn('target', array(
            'header' => __('Target element(s)'),
            'filter' => false,
            'index' => 'target',
            'renderer' => 'VladimirPopov\WebForms\Block\Adminhtml\Logic\Renderer\Target'
        ));

        $this->addColumn('aggregation', array(
            'header' => __('Logic aggregation'),
            'index' => 'aggregation',
            'type' => 'options',
            'options' => $this->_logicAggregation->getOptions()
        ));

        $this->addColumn('is_active', array(
            'header' => __('Status'),
            'index' => 'main_table.is_active',
            'type' => 'options',
            'options' => $this->_logicFactory->create()->getAvailableStatuses(),
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $modelForm = $this->_coreRegistry->registry('webforms_form');

        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setTemplate('VladimirPopov_WebForms::webforms/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('logic');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('webforms/logic/massDelete', ['webform_id' => $modelForm->getId()]),
                'confirm' => __('Are you sure?')
            ]
        );
        $statuses = $this->_logicFactory->create()->getAvailableStatuses();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('webforms/logic/massStatus', ['webform_id' => $modelForm->getId()]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'label' => __('Status'),
                        'options' => $statuses
                    ]
                ]
            ]
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('webforms/form/logicGrid', ['_current' => true]);
    }

    /**
     * @param \VladimirPopov\WebForms\Model\Logic|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'webforms/logic/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId(), 'webform_id' => $this->_coreRegistry->registry('webforms_form')->getId()]
        );
    }
}
