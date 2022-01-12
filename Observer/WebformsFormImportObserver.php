<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    VladimirPopov
 * @package     VladimirPopov_CustomerRegistration
 * @author      MageMe Team <support@mageme.com>
 * @copyright   Copyright (c) MageMe (https://mageme.com)
 * @license     https://mageme.com/license
 */

namespace VladimirPopov\WebForms\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use function is_array;
use function json_decode;

class WebformsFormImportObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $form          = $observer->getData('form');
        $elementMatrix = $observer->getData('elementMatrix');
        foreach ($form->getFieldsToFieldsets(true) as $fsId => $fieldset) {
            foreach ($fieldset['fields'] as $field) {
                $value = $field->getValue();
                if (!empty($value['region_country_field_id']) && isset($elementMatrix['field_' . $value['region_country_field_id']]))
                    $value['region_country_field_id'] = $elementMatrix['field_' . $value['region_country_field_id']];
                $field->setValue($value)->save();
            }
        }
    }
}
