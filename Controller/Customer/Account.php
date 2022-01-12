<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Customer;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManager;
use VladimirPopov\WebForms\Model\FormFactory;


class Account extends Action
{
    protected $_customerSession;

    protected $_coreRegistry;

    protected $resultPageFactory;

    protected $_formFactory;

    protected $_storeManager;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SessionFactory $sessionFactory,
        FormFactory $formFactory,
        StoreManager $storeManager,
        Registry $coreRegistry
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $sessionFactory->create();
        $this->_formFactory = $formFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object
     *
     * @return Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    public function execute()
    {
        $webformId = $this->getRequest()->getParam('webform_id');
        $groupId = $this->_getSession()->getCustomerGroupId();
        $webform = $this->_formFactory->create()->setStoreId($this->_storeManager->getStore()->getId())->load($webformId);

        $dashboardGroups = [];
        if(is_array($webform->getDashboardGroups())) $dashboardGroups = $webform->getDashboardGroups();

        if(!$webform->getIsActive() || !$webform->getDashboardEnable() || !in_array($groupId, $dashboardGroups)) $this->_redirect('customer/account');

        $this->_coreRegistry->register('webforms_form', $webform);
        $this->_coreRegistry->register('customer_id', $this->_getSession()->getCustomerId());

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('page.main.title')->setPageTitle($webform->getName());
        $resultPage->getConfig()->getTitle()->set($webform->getName());
        return $resultPage;
    }
}
