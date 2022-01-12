<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Result;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use VladimirPopov\WebForms\Helper\Data as WebFormsHelper;
use VladimirPopov\WebForms\Model\ResultFactory as WebformResultFactory;
use VladimirPopov\WebForms\Model\ResourceModel\Result\CollectionFactory as CollectionFactory;

/**
 * Class MassEmail
 * @package VladimirPopov\WebForms\Controller\Adminhtml\Result
 */
class MassEmail extends Action
{
    /**
     *
     */
    const ID_FIELD = 'selected';

    /**
     *
     */
    const REDIRECT_URL = '*/*/';

    /**
     *
     */
    const MODEL = 'VladimirPopov\WebForms\Model\Result';

    /**
     * @var array
     */
    protected $redirect_params = ['_current' => true];

    /**
     * @var WebFormsHelper
     */
    protected $webformsHelper;

    /**
     * @var WebformResultFactory
     */
    protected $webformResultFactory;

    /**
     * @var CollectionFactory
     */
    protected $webformResultCollectionFactory;

    /**
     * MassEmail constructor.
     * @param Action\Context $context
     * @param WebFormsHelper $webformsHelper
     * @param CollectionFactory $webformResultFactory
     */
    public function __construct(
        Action\Context $context,
        WebFormsHelper $webformsHelper,
        WebformResultFactory $webformResultFactory,
        CollectionFactory $webformResultCollectionFactory
    )
    {
        $this->webformsHelper       = $webformsHelper;
        $this->webformResultFactory = $webformResultFactory;
        $this->webformResultCollectionFactory = $webformResultCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return bool|int
     */
    protected function _isAllowed()
    {
        if ($this->getRequest()->getParam('webform_id')) {
            return $this->webformsHelper->isAllowed($this->getRequest()->getParam('webform_id'));
        }
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $Ids = [];
        if ($this->getRequest()->getParam('excluded') === 'false') {
            $webformId = $this->getRequest()->getParam('webform_id');

            if ($webformId) {
                $filters = $this->getRequest()->getParam('filters');
                /** @var \VladimirPopov\WebForms\Model\ResourceModel\Result\Collection $collection */
                $collection = $this->webformResultCollectionFactory->create()->addFilter('webform_id', $webformId);

                foreach ($filters as $fieldName => $value) {
                    if (strstr($fieldName, 'field_')) {
                        $fieldID = str_replace('field_', '', $fieldName);
                        $collection->addFieldFilter($fieldID, $value);
                    }
                }
                if (isset($filters['created_time'])) {
                    $from = $to = false;
                    if (!empty($filters['created_time']['from'])) $from = date('Y-m-d', strtotime($filters['created_time']['from'])) . ' 00:00:00';
                    if (!empty($filters['created_time']['to'])) $to = date('Y-m-d', strtotime($filters['created_time']['to'])) . ' 23:59:59';
                    if ($from)
                        $collection->addFieldToFilter('created_time', ['gteq' => $from]);
                    if ($to)
                        $collection->addFieldToFilter('created_time', ['lteq' => $to]);
                }
                if (isset($filters['id'])) {
                    $from = $to = false;
                    if (!empty($filters['id']['from'])) $from = $filters['id']['from'];
                    if (!empty($filters['id']['to'])) $to = $filters['id']['to'];
                    if ($from)
                        $collection->addFieldToFilter('id', ['gteq' => $from]);
                    if ($to)
                        $collection->addFieldToFilter('id', ['lteq' => $to]);
                }
                if (isset($filters['approved'])) {
                    $collection->addFilter('approved', $filters['approved']);
                }
                if (isset($filters['customer'])) {
                    $collection->addFieldToFilter('customer', ['like' => '%' . $filters['customer'] . '%']);
                }
                foreach ($collection as $result) {
                    $Ids[] = $result->getId();
                }
            }
        } else {
            $Ids = $this->getRequest()->getParam(static::ID_FIELD);
        }

        if (!is_array($Ids) || empty($Ids)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                $contact   = false;
                $recipient = 'admin';
                $email     = $this->getRequest()->getParam('input');
                if ($email) {
                    $contact   = array(
                        'name' => $email,
                        'email' => $email);
                    $recipient = 'contact';
                }
                foreach ($Ids as $id) {
                    /** @var \VladimirPopov\WebForms\Model\Result $item */
                    $item = $this->webformResultFactory->create()->load($id);
                    $item->sendEmail($recipient, $contact);
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been emailed.', count($Ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(static::REDIRECT_URL, $this->redirect_params);
    }
}
