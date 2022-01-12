<?php

namespace VladimirPopov\WebForms\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\DataObject;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use VladimirPopov\WebForms\Model\FormFactory;
use VladimirPopov\WebForms\Model\ResultFactory;
use VladimirPopov\WebForms\Model\Result;

/**
 * Class Result
 * @package VladimirPopov\WebForms\Controller
 */
class ResultAction extends Action
{
    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var Result
     */
    protected $_result;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var DirectoryList
     */
    protected $_dir;


    /**
     * ResultAction constructor.
     * @param Context $context
     * @param Session $session
     * @param FormFactory $formFactory
     * @param ResultFactory $resultFactory
     * @param Registry $registry
     * @param FileFactory $fileFactory
     * @param PageFactory $resultPageFactory
     * @param DirectoryList $_dir
     */
    public function __construct(
        Context $context,
        Session $session,
        FormFactory $formFactory,
        ResultFactory $resultFactory,
        Registry $registry,
        FileFactory $fileFactory,
        PageFactory $resultPageFactory,
        DirectoryList $_dir

    )
    {
        parent::__construct($context);
        $this->_session          = $session;
        $this->_eventManager     = $context->getEventManager();
        $this->_fileFactory      = $fileFactory;
        $this->formFactory       = $formFactory;
        $this->resultFactory     = $resultFactory;
        $this->registry          = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_dir              = $_dir;
    }

    /**
     *
     */
    protected function _init()
    {
        if (!$this->_session->isLoggedIn()) {
            $this->messageManager->addError(__('Please login to view the form.'));
            $this->_session->authenticate();
        }

        $resultId = $this->getRequest()->getParam('id');
        $result   = $this->resultFactory->create()->load($resultId);
        $result->addFieldArray();

        $access = new DataObject();
        $access->setAllowed(false);
        if ($result->getCustomerId() == $this->_session->getId())
            $access->setAllowed(true);

        $this->_eventManager->dispatch('webforms_controller_result_access', ['access' => $access, 'result' => $result]);

        if (!$access->getAllowed()) {
            $this->messageManager->addError(__('Access denied.'));
            $this->_redirect('customer/account');
        }

        $groupId = $this->_session->getCustomerGroupId();
        $webform = $this->formFactory->create()->setStoreId($result->getStoreId())->load($result->getWebformId());
        if (!$webform->getIsActive() || !$webform->getDashboardEnable() || !in_array($groupId, $webform->getDashboardGroups())) $this->_redirect('customer/account');
        $this->registry->register('result', $result);
        $this->_result = $result;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
    }
}
