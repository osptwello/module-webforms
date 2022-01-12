<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Model\ResourceModel\Field;

use VladimirPopov\WebForms\Model\Field;
use VladimirPopov\WebForms\Model\ResourceModel\AbstractCollection;
use VladimirPopov\WebForms\Model\ResourceModel\Field as ResourceField;
use function is_array;
use function unserialize;

/**
 * Field collection
 *
 */
class Collection extends AbstractCollection
{
    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Field::class, ResourceField::class);
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        $store_id = $this->getStoreId();
        if ($store_id) {
            foreach ($this as $item) {

                $store      = $this->_storeFactory->create()->search($store_id, $this->getResource()->getEntityType(), $item->getId());
                $store_data = $store->getStoreData();
                if ($store_data) {
                    foreach ($store_data as $key => $val) {
                        if ($val) {
                            if (is_array($val)) {
                                $orig_val = $item->getData($key);
                                if (!is_array($orig_val)) $orig_val = @unserialize($orig_val);
                                if (is_array($orig_val)) {
                                    foreach ($orig_val as $k => $v) {
                                        if (empty($val[$k]))
                                            $val[$k] = $v;
                                    }
                                }
                            }
                            $item->setData($key, $val);
                        }
                    }
                }
            }
        }

        foreach ($this as $item) {

            if (!is_array($item->getValue())) {
                $unserialized_value = @unserialize($item->getValue());
            }
            if (is_array($item->getValue())) {

                $unserialized_value = $item->getValue();
            }

            if (!empty($unserialized_value)) {

                switch ($item->getType()) {
                    case 'url':
                        if (!empty($unserialized_value["text_url"]))
                            $unserialized_value["text"] = $unserialized_value["text_url"];
                        break;
                    case 'email':
                        if (!empty($unserialized_value["text_email"]))
                            $unserialized_value["text"] = $unserialized_value["text_email"];
                        break;
                    case 'select/radio':
                        if (!empty($unserialized_value["options_radio"]))
                            $unserialized_value["options"] = $unserialized_value["options_radio"];
                        break;
                    case 'select/checkbox':
                        if (!empty($unserialized_value["options_checkbox"]))
                            $unserialized_value["options"] = $unserialized_value["options_checkbox"];
                        break;
                    case 'select/contact':
                        if (!empty($unserialized_value["options_contact"]))
                            $unserialized_value["options"] = $unserialized_value["options_contact"];
                        break;
                }
                if (!empty($unserialized_value["text"])) {
                    $unserialized_value["text_url"]   = $unserialized_value["text"];
                    $unserialized_value["text_email"] = $unserialized_value["text"];
                }
                if (!empty($unserialized_value["options"])) {
                    $unserialized_value["options_radio"]    = $unserialized_value["options"];
                    $unserialized_value["options_checkbox"] = $unserialized_value["options"];
                    $unserialized_value["options_contact"]  = $unserialized_value["options"];
                }
                if (!empty($unserialized_value["hint"])) {
                    if (empty($unserialized_value["hint_email"])) $unserialized_value["hint_email"] = $unserialized_value["hint"];
                    if (empty($unserialized_value["hint_url"])) $unserialized_value["hint_url"] = $unserialized_value["hint"];
                    if (empty($unserialized_value["hint_textarea"])) $unserialized_value["hint_textarea"] = $unserialized_value["hint"];
                }
                if (!empty($unserialized_value["dropzone"])) {
                    $unserialized_value["dropzone_image"] = $unserialized_value["dropzone"];
                }
                if (!empty($unserialized_value["dropzone_text"])) {
                    $unserialized_value["dropzone_text_image"] = $unserialized_value["dropzone_text"];
                }
                if (empty($unserialized_value["dropzone_text"]) && !empty($unserialized_value["dropzone_text_image"])) {
                    $unserialized_value["dropzone_text"] = $unserialized_value["dropzone_text_image"];
                }
                if (!empty($unserialized_value["dropzone_maxfiles"])) {
                    $unserialized_value["dropzone_maxfiles_image"] = $unserialized_value["dropzone_maxfiles"];
                }
                $item->setValue($unserialized_value);

            } else {
                // support for old value format
                $value       = $item->getValue();
                $stars_value = explode("\n", $value);
                if (empty($stars_value[1])) $stars_value[1] = false;
                $value_array = array(
                    'text' => $value,
                    'text_email' => $value,
                    'text_url' => $value,
                    'textarea' => $value,
                    'newsletter' => $value,
                    'stars_init' => $stars_value[1],
                    'stars_max' => $stars_value[0],
                    'options' => $value,
                    'options_radio' => $value,
                    'options_checkbox' => $value,
                    'options_contact' => $value,
                    'allowed_extensions' => $value,
                    'html' => $value,
                    'hidden' => $value,
                );
                $item->setValue($value_array);
            }

        }

        return $this;
    }

}
