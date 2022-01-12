<?php

namespace VladimirPopov\WebForms\Ui\Component\Result\Listing;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use VladimirPopov\WebForms\Model\Field;
use VladimirPopov\WebForms\Model\FormFactory;
use VladimirPopov\WebForms\Model\ResourceModel\Field\CollectionFactory;
use VladimirPopov\WebForms\Model\ResultFactory;

/**
 * Class Columns
 * @package VladimirPopov\WebForms\Ui\Component\Result\Listing
 */
class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * Default columns max order
     */
    const DEFAULT_COLUMNS_MAX_ORDER = 100;

    /** @var CollectionFactory */
    protected $fieldCollectionFactory;

    /** @var RequestInterface */
    protected $request;

    /** @var UiComponentFactory */
    protected $componentFactory;

    /** @var FormFactory */
    protected $formFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Application Event Dispatcher
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var array
     */
    protected $filterMap = [
        'default' => 'text',
        'text' => 'text',
        'email' => 'text',
        'number' => 'textRange',
        'select' => 'select',
        'select/radio' => 'select',
        'select/checkbox' => 'select',
        'select/contact' => 'select',
        'country' => 'select',
        'subscribe' => 'select',
        'stars' => 'textRange',
        'date' => 'dateRange',
        'datetime' => 'dateRange',
        'date/dob' => 'dateRange',
        'region' => false,
    ];

    /**
     * @var array
     */
    protected $dataMap = [
        'default' => 'text',
        'select' => 'text',
        'select/radio' => 'text',
        'select/checkbox' => 'text',
        'select/contact' => 'text',
        'country' => 'select',
        'date' => 'date',
        'datetime' => 'date',
        'date/dob' => 'date',
    ];

    /**
     * @var array
     */
    protected $jsComponentMap = [
        'default' => 'Magento_Ui/js/grid/columns/column',
        'textarea' => 'VladimirPopov_WebForms/js/grid/columns/textarea',
        'wysiwyg' => 'VladimirPopov_WebForms/js/grid/columns/html',
        'select' => 'VladimirPopov_WebForms/js/grid/columns/textarea',
        'select/radio' => 'VladimirPopov_WebForms/js/grid/columns/textarea',
        'select/checkbox' => 'VladimirPopov_WebForms/js/grid/columns/textarea',
        'subscribe' => 'Magento_Ui/js/grid/columns/select',
        'country' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date',
        'datetime' => 'Magento_Ui/js/grid/columns/date',
        'date/dob' => 'Magento_Ui/js/grid/columns/date',
    ];

    /**
     * @var array
     */
    protected $bodyTmplMap = [
        'default' => 'ui/grid/cells/text',
        'email' => 'ui/grid/cells/html',
        'textarea' => 'VladimirPopov_WebForms/grid/columns/textarea',
        'wysiwyg' => 'VladimirPopov_WebForms/grid/columns/textarea',
        'stars' => 'ui/grid/cells/html',
        'file' => 'ui/grid/cells/html',
        'image' => 'ui/grid/cells/html',
        'select' => 'ui/grid/cells/html',
        'select/checkbox' => 'ui/grid/cells/html',
        'colorpicker' => 'ui/grid/cells/html',
    ];

    /**
     * @var array
     */
    protected $configMap = [
        'default' => [],
        'date' => ['dateFormat' => 'MMM d, YYYY'],
        'date/dob' => ['dateFormat' => 'MMM d, YYYY'],
    ];

    /**
     * @var array
     */
    protected $classMap = [
        'default' => 'Magento\Ui\Component\Listing\Columns\Column',
        'textarea' => 'VladimirPopov\WebForms\Ui\Component\Result\Listing\Column\Textarea',
        'password' => 'VladimirPopov\WebForms\Ui\Component\Result\Listing\Column\Password',
        'wysiwyg' => 'VladimirPopov\WebForms\Ui\Component\Result\Listing\Column\Wysiwyg',
        'file' => 'VladimirPopov\WebForms\Ui\Component\Result\Listing\Column\File',
        'image' => 'VladimirPopov\WebForms\Ui\Component\Result\Listing\Column\Image',
        'email' => 'VladimirPopov\WebForms\Ui\Component\Result\Listing\Column\Email',
        'stars' => 'VladimirPopov\WebForms\Ui\Component\Result\Listing\Column\Stars',
        'select' => 'VladimirPopov\WebForms\Ui\Component\Result\Listing\Column\Select',
        'select/radio' => 'VladimirPopov\WebForms\Ui\Component\Result\Listing\Column\Select',
        'select/checkbox' => 'VladimirPopov\WebForms\Ui\Component\Result\Listing\Column\Select',
        'region' => 'VladimirPopov\WebForms\Ui\Component\Result\Listing\Column\Region',
        'colorpicker' => 'VladimirPopov\WebForms\Ui\Component\Result\Listing\Column\Colorpicker',
    ];

    /**
     * @var array
     */
    protected $displayMap = [
            'default' => true,
            'html' => false,
        ];

    /**
     * Columns constructor.
     * @param ObjectManagerInterface $objectManager
     * @param ContextInterface $context
     * @param UiComponentFactory $componentFactory
     * @param RequestInterface $request
     * @param CollectionFactory $fieldCollectionFactory
     * @param FormFactory $formFactory
     * @param ResultFactory $resultFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ContextInterface $context,
        UiComponentFactory $componentFactory,
        RequestInterface $request,
        CollectionFactory $fieldCollectionFactory,
        FormFactory $formFactory,
        ResultFactory $resultFactory,
        UrlInterface $urlBuilder,
        EventManagerInterface $eventManager,
        array $components = [],
        array $data = []
    )
    {

        parent::__construct($context, $components, $data);
        $this->fieldCollectionFactory = $fieldCollectionFactory;
        $this->request = $request;
        $this->componentFactory = $componentFactory;
        $this->formFactory = $formFactory;
        $this->resultFactory = $resultFactory;
        $this->urlBuilder = $urlBuilder;
        $this->objectManager = $objectManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @param $fieldType
     * @return mixed
     */
    public function getJsComponent($fieldType)
    {
        return isset($this->jsComponentMap[$fieldType]) ? $this->jsComponentMap[$fieldType] : $this->jsComponentMap['default'];
    }

    /**
     * @param $fieldType
     * @return mixed
     */
    public function getFilterType($fieldType)
    {
        return isset($this->filterMap[$fieldType]) ? $this->filterMap[$fieldType] : $this->filterMap['default'];
    }

    /**
     * @param $fieldType
     * @return mixed
     */
    public function getDataType($fieldType)
    {
        return isset($this->dataMap[$fieldType]) ? $this->dataMap[$fieldType] : $this->dataMap['default'];
    }

    /**
     * @param $fieldType
     * @return mixed
     */
    public function getBodyTmpl($fieldType)
    {
        return isset($this->bodyTmplMap[$fieldType]) ? $this->bodyTmplMap[$fieldType] : $this->bodyTmplMap['default'];
    }

    /**
     * @param $fieldType
     * @return mixed
     */
    public function getDisplay($fieldType)
    {
        return isset($this->displayMap[$fieldType]) ? $this->displayMap[$fieldType] : $this->displayMap['default'];
    }

    /**
     * @param $fieldType
     * @return mixed
     */
    public function getColumnClass($fieldType)
    {
        return isset($this->classMap[$fieldType]) ? $this->classMap[$fieldType] : $this->classMap['default'];
    }

    /**
     * @param $fieldType
     * @return mixed
     */
    public function getColumnConfig($fieldType)
    {
        return isset($this->configMap[$fieldType]) ? $this->configMap[$fieldType] : $this->configMap['default'];
    }

    /**
     * @param Field $field
     * @return array
     */
    public function getOptions(Field $field)
    {
        return $field->getSelectValues(false);
    }

    /**
     * @return array
     */
    public function getStatusOptions()
    {
        $options = [];
        $statuses = $this->resultFactory->create()->getApprovalStatuses();
        foreach ($statuses as $key => $value) {
            $options[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $columnSortOrder = self::DEFAULT_COLUMNS_MAX_ORDER;
        $webform_id = $this->request->getParam('webform_id');
        $store = $this->request->getParam('store_id');
        $fields = $this->fieldCollectionFactory->create()->addFilter('webform_id', $webform_id);
        $webform = $this->formFactory->create();
        if ($store) $webform->setStoreId($store);
        $webform->load($webform_id);
        if ($webform->getApprove()) {
            $config = [
                'sortOrder' => 30,
                'label' => __('Status'),
                'filter' => 'select',
                'options' => $this->getStatusOptions(),
                'sortable' => true,
                'dataType' => 'select',
                'component' => 'VladimirPopov_WebForms/js/grid/columns/status',
                'url' => $this->urlBuilder->getUrl('*/*/setStatus', ['_current' => true])
            ];
            $arguments = [
                'data' => [
                    'config' => $config,
                ],
                'context' => $this->getContext(),
            ];
            $column = $this->componentFactory->create('approved', 'column', $arguments);
            $column->prepare();
            $this->addComponent('approved', $column);
        }

        /** @var Field $field */
        foreach ($fields as $field) {
            $sortable = true;
            $filter = $this->getFilterType($field->getType());
            if(count($fields) > 60){
                $filter = false;
                $sortable = false;
            }
            if($this->getDisplay($field->getType())) {
                if (!isset($this->components[$field->getId()])) {
                    $arguments = [
                        'data' => [
                            'config' => [
                                'label' => $field->getName(),
                                'dataType' => $this->getDataType($field->getType()),
                                'sortOrder' => ++$columnSortOrder,
                                'sortable' => $sortable,
                                'options' => $this->getOptions($field),
                                'filter' => $filter,
                                'class' => $this->getColumnClass($field->getType()),
                                'component' => $this->getJsComponent($field->getType()),
                                'bodyTmpl' => $this->getBodyTmpl($field->getType()),
                            ],
                            'name' => 'field_' . $field->getId(),
                        ],
                        'context' => $this->getContext(),
                    ];

                    $arguments['data']['config'] = array_merge($arguments['data']['config'], $this->getColumnConfig($field->getType()));
                    $class = $this->getColumnClass($field->getType());

                    $columnConfig = new DataObject(['class' => $class, 'arguments' => $arguments]);

                    $this->eventManager->dispatch('webforms_ui_component_result_listing_columns_prepare_config', ['field' => $field, 'column_config' => $columnConfig]);

                    $column = $this->objectManager->create($columnConfig->getClass(), $columnConfig->getArguments());
                    $column->prepare();

                    $this->addComponent('field_' . $field->getId(), $column);
                }
            }
        }
        parent::prepare();
    }
}
