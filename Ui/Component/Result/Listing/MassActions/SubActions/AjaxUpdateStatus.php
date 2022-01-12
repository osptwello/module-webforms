<?php


namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\MassActions\SubActions;


use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;
use VladimirPopov\WebForms\Model\ResultFactory;

class AjaxUpdateStatus extends Action
{

    /** @var UrlInterface */
    protected $urlBuilder;

    /** @var ResultFactory */
    protected $resultFactory;

    /**
     * @param UrlInterface $urlBuilder
     * @param ResultFactory $resultFactory
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param array|\JsonSerializable $actions
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ResultFactory $resultFactory,
        ContextInterface $context,
        array $components = [],
        array $data = [],
        $actions = null
    ) {
        parent::__construct($context, $components, $data, $actions);
        $this->urlBuilder = $urlBuilder;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $statuses = $this->resultFactory->create()->getApprovalStatuses();
        $actions = [];

        foreach ($statuses as $status_id => $status_name) {
            $actions[] = [
                'type' => $status_name,
                'label' => __($status_name),
                'url' => $this->urlBuilder->getUrl('webforms/result/customer_massStatus', ['status' => $status_id]),
                'isAjax' => true,
                'confirm' => [
                    'title' => __('Update status'),
                    'message' => __('Are you sure to update status for selected results?'),
                    '__disableTmpl' => true,
                ],
            ];
        }

        $this->actions = $actions;
        parent::prepare();
    }

}