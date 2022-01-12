<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Form\Edit\Tab\Renderer;

class Width extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Select
{


    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $name = $this->getNameAttribute($row);
        $html = '<select name="' . $this->escapeHtml($name) . '" ' . $this->getColumn()->getValidateClass() . '>';
        $value = $row->getData($this->getColumn()->getIndex());
        foreach ($this->getColumn()->getOptions() as $val => $label) {
            $selected = $val == $value && $value !== null ? ' selected="selected"' : '';
            $html .= '<option value="' . $this->escapeHtml($val) . '"' . $selected . '>';
            $html .= $this->escapeHtml($label) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getNameAttribute(\Magento\Framework\DataObject $row)
    {
        if ($this->getColumn()->getPrefix()) {
            return $this->getColumn()->getPrefix() . '[' . $this->getColumn()->getIndex() . ']' . '[' . $row->getId() . ']';
        }
        return $this->getColumn()->getIndex() . '[' . $row->getId() . ']';
    }
}
