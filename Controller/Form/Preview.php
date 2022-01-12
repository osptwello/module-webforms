<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Form;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\StoreSwitcher\CannotSwitchStoreException;
use Magento\Store\Model\StoreSwitcherInterface;
use VladimirPopov\WebForms\Helper\Data;

/**
 * Class Preview
 * @package VladimirPopov\WebForms\Controller\Form
 */
class Preview extends Action
{
    /**
     * @var string
     */
    const COOKIE_NAME = 'section_data_clean';

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Data
     */
    protected $webformsHelper;

    /**
     * @var Customer
     */
    protected $session;
    /**
     * @var StoreRepository
     */
    protected $storeRepository;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var StoreSwitcherInterface
     */
    protected $storeSwitcher;

    /**
     * @param StoreRepository $storeRepository
     * @param StoreManagerInterface $storeManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManager
     * @param StoreSwitcherInterface $storeSwitcher
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $webformsHelper
     * @param Customer $session
     * @param Registry $coreRegistry
     */
    public function __construct(
        StoreRepository $storeRepository,
        StoreManagerInterface $storeManager,
        CookieMetadataFactory $cookieMetadataFactory,
        CookieManagerInterface $cookieManager,
        StoreSwitcherInterface $storeSwitcher,
        Context $context,
        PageFactory $resultPageFactory,
        Data $webformsHelper,
        Customer $session,
        Registry $coreRegistry
    )
    {

        parent::__construct($context);
        $this->resultPageFactory     = $resultPageFactory;
        $this->_coreRegistry         = $coreRegistry;
        $this->webformsHelper        = $webformsHelper;
        $this->session               = $session;
        $this->storeRepository       = $storeRepository;
        $this->storeManager          = $storeManager;
        $this->storeSwitcher         = $storeSwitcher;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager         = $cookieManager;

    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws CannotSwitchStoreException
     */
    public function execute()
    {
        $targetStoreCode = $this->_request->getParam(StoreResolver::PARAM_NAME);
        if ($targetStoreCode) {
            $targetStore = $this->storeRepository->get($targetStoreCode);
            if ($targetStore->getId()) {
                $this->storeManager->setCurrentStore($targetStore);
                $this->storeSwitcher->switch($targetStore, $targetStore, $this->_redirect->getRedirectUrl());
                $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                    ->setHttpOnly(false)
                    ->setDuration(15)
                    ->setPath($targetStore->getStorePath());
                $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $targetStore->getCode(), $cookieMetadata);
            }
        }
        $this->_coreRegistry->register('webforms_preview', true);
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
