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
 * @package     VladimirPopov_WebForms
 * @author      MageMe Team <support@mageme.com>
 * @copyright   Copyright (c) MageMe (https://mageme.com)
 * @license     https://mageme.com/license
 */
namespace VladimirPopov\WebForms\Block\Adminhtml\Info;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Products
 * @package VladimirPopov\WebForms\Block\Adminhtml
 */
class Plugins extends AbstractInfo
{
    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $list = [];
        $info = $this->getInfo();
        if ($info) {
            foreach ($info as $id => $plugin) {
                if ($id !== self::MODULE_NAME) {
                    $list[] = __(
                            "<div class='mageme-plugin'><a href='%1' target='_blank'><img src='%2' alt='%3'/>%3</a><div class='mageme-plugin-description'>%4</div></div>",
                            $plugin['url'],
                            $plugin['image'],
                            $plugin['name'],
                            $plugin['description']
                        );
                }
            }
        }

        return '<div class="control-value special">' . implode('', $list) . '</div>';
    }
}
