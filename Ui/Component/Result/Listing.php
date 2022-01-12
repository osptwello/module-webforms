<?php
namespace VladimirPopov\WebForms\Ui\Component\Result;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;

class Listing extends \Magento\Ui\Component\Listing
{
    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        $data['buttons'][] = [
            'name' => __('Add Result'),
            'label' => __('Add Result'),
            'class' => 'primary',
            'url' => $urlBuilder->getUrl('*/*/new', ['webform_id' => $request->getParam('webform_id')])
        ];

        $data['buttons'][] = [
            'name' => __('Edit Form'),
            'label' => __('Edit Form'),
            'class' => 'edit',
            'url' => $urlBuilder->getUrl('*/form/edit', ['id' => $request->getParam('webform_id')])
        ];

        parent::__construct($context, $components, $data);
    }
}