<?php


namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\MassActions\SubActions;


use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;
use VladimirPopov\WebForms\Model\ResultFactory;

class AjaxSendEmail extends Action
{

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param array|\JsonSerializable $actions
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        array $components = [],
        array $data = [],
        $actions = null
    ) {
        parent::__construct($context, $components, $data, $actions);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $actions = [];
        $actions[] = [
            'type' => 'input',
            'label' => __('E-mail address:'),
            'url' => $this->urlBuilder->getUrl('webforms/result/customer_massEmail'),
            'isAjax' => true,
            'confirm' => [
                'title' => __('Email items'),
                'message' => __('Send selected results to notification e-mail address?'),
                '__disableTmpl' => true,
            ],
        ];
        $this->actions = $actions;
        parent::prepare();
    }

}