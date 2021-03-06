<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Form\Edit\Tab;

class Fieldsets extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Fieldsets');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Fieldsets');
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
        return $this->_coreRegistry->registry('webforms_form')->getId() ? false : true;
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
     * @var \VladimirPopov\WebForms\Model\ResourceModel\Fieldset\CollectionFactory
     */
    protected $_fieldsetCollectionFactory;

    /**
     * @var \VladimirPopov\WebForms\Model\FieldsetFactory
     */
    protected $_fieldsetFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \VladimirPopov\WebForms\Model\ResourceModel\Fieldset\CollectionFactory $fieldsetCollectionFactory
     * @param \VladimirPopov\WebForms\Model\FieldsetFactory $fieldsetFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \VladimirPopov\WebForms\Model\ResourceModel\Fieldset\CollectionFactory $fieldsetCollectionFactory,
        \VladimirPopov\WebForms\Model\FieldsetFactory $fieldsetFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_fieldsetCollectionFactory = $fieldsetCollectionFactory;
        $this->_fieldsetFactory = $fieldsetFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('fieldsets_section');
        $this->setDefaultSort('position');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_fieldsetCollectionFactory->create()
            ->setStoreId($this->_coreRegistry->registry('webforms_form')->getStoreId())
            ->addFilter('webform_id', $this->_coreRegistry->registry('webforms_form')->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
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
            'index' => 'id'
        ));

        $this->addColumn('name', array(
            'header' => __('Name'),
            'index' => 'name'
        ));

        $fieldsetModel = $this->_fieldsetFactory->create();

        if ($modelForm->getData('show_width_lg'))
            $this->addColumn('width_lg', array(
                'type' => 'options',
                'header' => __('Width (Large)'),
                'prefix' => 'fieldsets_width',
                'index' => 'width_lg',
                'renderer' => 'VladimirPopov\WebForms\Block\Adminhtml\Form\Edit\Tab\Renderer\Width',
                'options' => $fieldsetModel->getSizeValues(true)
            ));

        if ($modelForm->getData('show_width_md'))
            $this->addColumn('width_md', array(
                'type' => 'options',
                'header' => __('Width (Medium)'),
                'prefix' => 'fieldsets_width',
                'index' => 'width_md',
                'renderer' => 'VladimirPopov\WebForms\Block\Adminhtml\Form\Edit\Tab\Renderer\Width',
                'options' => $fieldsetModel->getSizeValues(true)
            ));

        if ($modelForm->getData('show_width_sm'))
            $this->addColumn('width_sm', array(
                'type' => 'options',
                'header' => __('Width (Small)'),
                'prefix' => 'fieldsets_width',
                'index' => 'width_sm',
                'renderer' => 'VladimirPopov\WebForms\Block\Adminhtml\Form\Edit\Tab\Renderer\Width',
                'options' => $fieldsetModel->getSizeValues(true)
            ));

        $this->addColumn('is_active', array(
            'header' => __('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => $this->_fieldsetFactory->create()->getAvailableStatuses(),
        ));

        $config = array(
            'header' => __('Position'),
            'name' => 'position',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'position',
            'align' => 'right',
            'prefix' => 'fieldsets_position',
        );
        if (!$this->getRequest()->getParam('store')) {
            $config['renderer'] = 'VladimirPopov\WebForms\Block\Adminhtml\Form\Edit\Tab\Renderer\Position';
            $config['editable'] = true;
        }

        $this->addColumn('position', $config);

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        /** @var \VladimirPopov\WebForms\Model\Form $modelForm */
        $modelForm = $this->_coreRegistry->registry('webforms_form');

        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setTemplate('VladimirPopov_WebForms::webforms/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('fieldsets');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('webforms/fieldset/massDelete', ['_current' => true]),
                'confirm' => __('Are you sure?')
            ]
        );
        $statuses = $this->_fieldsetFactory->create()->getAvailableStatuses();

        $fieldsetModel = $this->_fieldsetFactory->create();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('webforms/fieldset/massStatus', ['_current' => true]),
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

        if ($modelForm->getData('show_width_lg'))
            $this->getMassactionBlock()->addItem(
                'width_lg',
                [
                    'label' => __('Set width (large screen)'),
                    'url' => $this->getUrl('webforms/fieldset/massWidth', ['_current' => true]),
                    'additional' => [
                        'visibility' => [
                            'name' => 'width_lg',
                            'type' => 'select',
                            'label' => __('Width'),
                            'options' => $fieldsetModel->getSizeValues()
                        ]
                    ]
                ]
            );

        if ($modelForm->getData('show_width_md'))
            $this->getMassactionBlock()->addItem(
                'width_md',
                [
                    'label' => __('Set width (medium screen)'),
                    'url' => $this->getUrl('webforms/fieldset/massWidth', ['_current' => true]),
                    'additional' => [
                        'visibility' => [
                            'name' => 'width_md',
                            'type' => 'select',
                            'label' => __('Width'),
                            'options' => $fieldsetModel->getSizeValues()
                        ]
                    ]
                ]
            );

        if ($modelForm->getData('show_width_sm'))
            $this->getMassactionBlock()->addItem(
                'width_sm',
                [
                    'label' => __('Set width (small screen)'),
                    'url' => $this->getUrl('webforms/fieldset/massWidth', ['_current' => true]),
                    'additional' => [
                        'visibility' => [
                            'name' => 'width_sm',
                            'type' => 'select',
                            'label' => __('Width'),
                            'options' => $fieldsetModel->getSizeValues()
                        ]
                    ]
                ]
            );

        $this->getMassactionBlock()->addItem(
            'duplicate',
            [
                'label' => __('Duplicate'),
                'url' => $this->getUrl('*/fieldset/massDuplicate', ['_current' => true]),
                'confirm' => __('Are you sure?')
            ]
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('webforms/fieldset/grid', ['_current' => true]);
    }

    /**
     * @param \VladimirPopov\WebForms\Model\Fieldset|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'webforms/fieldset/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
        );
    }
}
