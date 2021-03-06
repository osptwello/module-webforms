<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDuplicate
 */
class AbstractMassDuplicate extends \Magento\Backend\App\Action
{
    const ID_FIELD = 'id';

    const REDIRECT_URL = '*/*/';

    protected $redirect_params = [];

    protected $webformsHelper;

    protected $entityModel;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Model\AbstractModel $entityModel,
        \VladimirPopov\WebForms\Helper\Data $webformsHelper
    )
    {
        $this->webformsHelper = $webformsHelper;
        $this->entityModel = $entityModel;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        if($this->getRequest()->getParam('webform_id')){
            return $this->webformsHelper->isAllowed($this->getRequest()->getParam('webform_id'));
        }
        if($this->getRequest()->getParam('id')){
            return  $this->webformsHelper->isAllowed($this->getRequest()->getParam('id'));
        }
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }


    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $Ids = $this->getRequest()->getParam(static::ID_FIELD);
        if (!is_array($Ids) || empty($Ids)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                foreach ($Ids as $id) {
                    $item = $this->entityModel->load($id);
                    $item->duplicate();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been duplicated.', count($Ids))
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
