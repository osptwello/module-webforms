<?php
namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use VladimirPopov\WebForms\Model\FieldFactory;
use function htmlentities;
use function str_replace;

class Select extends \Magento\Ui\Component\Listing\Columns\Column
{

    /** @var FieldFactory */
    protected $fieldFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FieldFactory $fieldFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->fieldFactory = $fieldFactory;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $field_id = str_replace('field_', '', $fieldName);
            $field = $this->fieldFactory->create()->load($field_id);
            $selectOptions = $field->getSelectOptions();
            foreach ($dataSource['data']['items'] as & $item) {
                if(isset($item[$fieldName])) {
                    $value = $item[$fieldName];
                    if (!empty($selectOptions[$value])) {
                        $value = $selectOptions[$value];
                    }
                    $item[$fieldName] = htmlentities($value);
                }
            }
        }

        return $dataSource;
    }
}
