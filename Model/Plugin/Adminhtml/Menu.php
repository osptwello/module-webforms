<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Model\Plugin\Adminhtml;

use Magento\Backend\Model\Menu\ItemFactory;
use VladimirPopov\WebForms\Model\ResourceModel\Form\CollectionFactory;

class Menu
{
    protected $_itemFactory;

    protected $_formCollectionFactory;

    public function __construct(
        ItemFactory $itemFactory,
        CollectionFactory $formCollectionFactory
    )
    {
        $this->_itemFactory = $itemFactory;
        $this->_formCollectionFactory = $formCollectionFactory;
    }

    public function beforeToHtml(\Magento\Backend\Block\Menu $menuBlock)
    {
        $menu = $menuBlock->getMenuModel();
        if ($menu) {
            // check available forms
            $collection = $this->_formCollectionFactory->create()
                ->addFilter('menu', 1)
                ->addOrder('name', 'asc');

            // add forms to menu
            $i = 100;
            foreach ($collection as $form) {
                $title = $form->getName();

                if(strlen($title) > 46) {
                    if (function_exists('mb_substr')) {
                        $title = mb_substr($title, 0, 43) . '...';
                    } else {
                        $title = substr($title, 0, 43) . '...';
                    }
                }

                $menuItem = $this->_itemFactory->create(['data' => [
                    'id' => 'VladimirPopov_WebForms::form' . $form->getId(),
                    'title' => '[ '.$title.' ]',
                    'model' => 'VladimirPopov_WebForms',
                    'action' => 'webforms/result/index/webform_id/' . $form->getId(),
                    'resource' => 'VladimirPopov_WebForms::form' . $form->getId()
                ]]);
                $menu->add($menuItem, 'VladimirPopov_WebForms::webforms', $i);
                $i++;
            }
        }
    }
}
