<?php
namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use VladimirPopov\WebForms\Model\FieldFactory;

class Stars extends \Magento\Ui\Component\Listing\Columns\Column
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
            $blockwidth = ($field->getStarsCount() * 16) . 'px';

            foreach ($dataSource['data']['items'] as & $item) {
                $value = $item[$fieldName];
                $width = round(100 * intval($value) / intval($field->getStarsCount())) . '%';
                $html = "<div class='stars' style='width:$blockwidth'><ul class='stars-bar'><li class='stars-value' style='width:$width'></li></ul></div>";
                $item[$fieldName] = $html;
            }
        }

        return $dataSource;
    }
}