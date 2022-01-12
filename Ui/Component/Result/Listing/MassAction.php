<?php

namespace VladimirPopov\WebForms\Ui\Component\Result\Listing;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use VladimirPopov\WebForms\Model\ResultFactory;
use VladimirPopov\WebForms\Model\FormFactory;

class MassAction extends \Magento\Ui\Component\MassAction
{
    /** @var UiComponentFactory */
    protected $componentFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /** @var ResultFactory */
    protected $resultFactory;

    /** @var RequestInterface */
    protected $request;

    /** @var FormFactory */
    protected $formFactory;

    public function __construct(
        UiComponentFactory $componentFactory,
        UrlInterface $urlBuilder,
        ResultFactory $resultFactory,
        FormFactory $formFactory,
        ContextInterface $context,
        RequestInterface $request,
        $components = [],
        array $data = [])
    {
        parent::__construct($context, $components, $data);
        $this->componentFactory = $componentFactory;
        $this->urlBuilder = $urlBuilder;
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->formFactory = $formFactory;
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $formId = $this->request->getParam('webform_id');

        if ($formId) {

            $form = $this->formFactory->create()->load($formId);

            if ($form->getApprove()) {
                $config = [
                    'type' => 'status',
                    'label' => __('Change status'),
                    'actions' => []
                ];

                $statuses = $this->resultFactory->create()->getApprovalStatuses();

                foreach ($statuses as $status => $label) {
                    $config['actions'][] = [
                        'type' => 'status' . $status,
                        'url' => $this->urlBuilder->getUrl('webforms/result/massStatus', ['_current' => true, 'status' => $status]),
                        'label' => $label
                    ];
                }

                $arguments = [
                    'data' => [
                        'config' => $config,
                    ],
                    'context' => $this->getContext(),
                ];

                $actionComponent = $this->componentFactory->create('status', 'action', $arguments);
                $this->addComponent('status', $actionComponent);
            }
        }

        parent::prepare();
    }

}