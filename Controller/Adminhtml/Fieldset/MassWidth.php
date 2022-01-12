<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Fieldset;

use Magento\Backend\App\Action;
use VladimirPopov\WebForms\Controller\Adminhtml\AbstractMassStatus;
use Magento\Framework\Controller\ResultFactory;

class MassWidth extends AbstractMassStatus
{
    const ID_FIELD = 'fieldsets';

    const REDIRECT_URL = 'webforms/form/edit';

//    const MODEL = 'VladimirPopov\WebForms\Model\Fieldset';

    protected $fieldsetFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Model\AbstractModel $entityModel,
        \VladimirPopov\WebForms\Helper\Data $webformsHelper,
        \VladimirPopov\WebForms\Model\FieldsetFactory $fieldsetFactory
    )
    {
        $this->fieldsetFactory = $fieldsetFactory;
        parent::__construct($context, $entityModel, $webformsHelper);
    }

    public function execute()
    {
        $this->redirect_params = ['id' => $this->getRequest()->getParam('id'), 'active_tab' => 'fieldsets_section'];

        $Ids = $this->getRequest()->getParam(static::ID_FIELD);
        $width_lg = $width_md = $width_sm = false;
        if ($this->getRequest()->getParam('width_lg')) $width_lg = $this->getRequest()->getParam('width_lg');
        if ($this->getRequest()->getParam('width_md')) $width_md = $this->getRequest()->getParam('width_md');
        if ($this->getRequest()->getParam('width_sm')) $width_sm = $this->getRequest()->getParam('width_sm');
        if (!is_array($Ids) || empty($Ids)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                foreach ($Ids as $id) {
                    $item = $this->fieldsetFactory->create()->load($id);
                    if ($width_lg) {
                        $item->setData('width_lg', $width_lg);
                    }
                    if ($width_md) {
                        $item->setData('width_md', $width_md);
                    }
                    if ($width_sm) {
                        $item->setData('width_sm', $width_sm);
                    }
                    $item->save();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been updated.', count($Ids))
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
