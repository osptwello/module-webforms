<?php

namespace VladimirPopov\WebForms\Model\Config\Result;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Permission
 * @package VladimirPopov\WebForms\Model\Config\Result
 */
class Permission implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'add', 'label' => __('Add')],
            ['value' => 'view', 'label' => __('View')],
            ['value' => 'edit', 'label' => __('Edit')],
            ['value' => 'delete', 'label' => __('Delete')],
            ['value' => 'print', 'label' => __('Print')],
        ];
    }
}