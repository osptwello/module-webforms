<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Form\Renderer\Fieldset;

use function is_array;

class Element extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * Initialize block template
     */
    protected $_template = 'VladimirPopov_WebForms::webforms/form/renderer/fieldset/element.phtml';

    public function getDataObject()
    {
        return $this->getElement()->getForm()->getDataObject();
    }

    public function usedDefault()
    {
        if ($this->getRequest()->getParam('store')) {
            $data = $this->getDataObject();
            if ($data) {
                $store_data = $data->getStoreData();
                $id         = $this->getElement()->getId();
                if (is_array($store_data) && array_key_exists($id, $store_data))
                    return false;
            }
            return true;
        }
        return false;
    }

    public function canDisplayUseDefault()
    {
        if ($this->getElement()->getGlobal()) return false;

        if ($this->getRequest()->getParam('store')) {
            return true;
        }
        return false;
    }

    public function checkFieldDisable()
    {
        if ($this->canDisplayUseDefault() && $this->usedDefault()) {
            $this->getElement()->setDisabled(true);
        }
        return $this;
    }

    public function getScopeLabel()
    {
        if ($this->getElement()->getGlobal())
            return '[GLOBAL]';
        return '[STORE VIEW]';
    }
}
