<?php
namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use function htmlentities;
use DOMDocument;

class Wysiwyg extends \Magento\Ui\Component\Listing\Columns\Column
{

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
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if(isset($item[$fieldName])) {
                    $value = $item[$fieldName];
                    try {
                        $html = $this->sanitizeHtml($value);
                    } catch (\Exception $exception) {
                        $html = htmlentities($value);
                    }
                    $item[$fieldName] = $html;
                }
            }
        }

        return $dataSource;
    }

    public function sanitizeHtml($html) {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $tags_to_remove = array('script','style','iframe','link');
        foreach($tags_to_remove as $tag){
            $element = $dom->getElementsByTagName($tag);
            foreach($element  as $item){
                $item->parentNode->removeChild($item);
            }
        }
        foreach ($dom->getElementsByTagname('*') as $element)
        {
            foreach (iterator_to_array($element->attributes) as $name => $attribute)
            {
                if (substr_compare($name, 'on', 0, 2, TRUE) === 0)
                {
                    $element->removeAttribute($name);
                }
            }
        }

        return $dom->saveHTML();
    }
}
