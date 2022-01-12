<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Logic;

class Grid extends \VladimirPopov\WebForms\Controller\Adminhtml\Index
{

    public function execute()
    {
        if($this->getRequest()->getParam('id')){
            $model = $this->fieldFactory->create()->load($this->getRequest()->getParam('field_id'));
            $this->_coreRegistry->register('webforms_field',$model);
        }
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }

    protected function _isAllowed()
    {
        if($this->getRequest()->getParam('id')){
            $model = $this->fieldFactory->create()->load($this->getRequest()->getParam('id'));
            return $this->webformsHelper->isAllowed($model->getWebformId());
        }
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }
}
