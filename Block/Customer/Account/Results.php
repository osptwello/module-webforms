<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Customer\Account;

use function in_array;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;
use VladimirPopov\WebForms\Model\ResourceModel;
use Magento\Customer\Model\Session;
use VladimirPopov\WebForms\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use VladimirPopov\WebForms\Model\ResourceModel\Result\CollectionFactory as ResultCollectionFactory;
use \VladimirPopov\WebForms\Model\Result;


/**
 * Class Results
 * @package VladimirPopov\WebForms\Block\Customer\Account
 */
class Results extends Template
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /** @var  ResourceModel\Result\Collection */
    protected $_resultsCollection;

    /**
     * @var
     */
    protected $_resultCollectionFactory;

    /**
     * @var MessageCollectionFactory
     */
    protected $_messageCollectionFactory;

    /**
     * @var Pager
     */
    protected $_htmlPagerBlock;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * Results constructor.
     * @param Template\Context $context
     * @param ResultCollectionFactory $resultCollectionFactory
     * @param MessageCollectionFactory $messageCollectionFactory
     * @param Context $httpContext
     * @param Registry $coreRegistry
     * @param Pager $htmlPagerBlock
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ResultCollectionFactory $resultCollectionFactory,
        MessageCollectionFactory $messageCollectionFactory,
        Context $httpContext,
        Registry $coreRegistry,
        Pager $htmlPagerBlock,
        Session $session,
        array $data = [])
    {
        $this->_resultsCollectionFactory = $resultCollectionFactory;
        $this->httpContext               = $httpContext;
        $this->_coreRegistry             = $coreRegistry;
        $this->_messageCollectionFactory = $messageCollectionFactory;
        $this->_htmlPagerBlock           = $htmlPagerBlock;
        $this->_session                  = $session;
        parent::__construct($context, $data);
    }

    /**
     * @return $this|Template
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->_htmlPagerBlock) {
            $toolbar->setCollection($this->getCollection());
            $this->addChild('toolbar', $toolbar);
        }

        return $this;
    }

    /**
     * @return ResourceModel\Result\Collection
     */
    public function getCollection()
    {
        if (null === $this->_resultsCollection) {
            $webform                  = $this->_coreRegistry->registry('webforms_form');
            $this->_resultsCollection = $this->_resultsCollectionFactory->create()
                ->setLoadValues(true)
                ->addFilter('webform_id', $webform->getId())
                ->addFilter('customer_id', $this->_coreRegistry->registry('customer_id'))
                ->addOrder('created_time', 'desc');
        }
        return $this->_resultsCollection;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->_coreRegistry->registry('webforms_form');
    }

    /**
     * @param Result $result
     * @return string
     */
    public function getUrlResultView(Result $result)
    {
        return $this->getUrl('webforms/customer/result', ['id' => $result->getId()]);
    }

    /**
     * @param Result $result
     * @return string
     */
    public function getUrlResultEdit(Result $result)
    {
        return $this->getUrl('webforms/result/edit', ['id' => $result->getId()]);
    }

    /**
     * @param Result $result
     * @return string
     */
    public function getUrlResultDelete(Result $result)
    {
        return $this->getUrl('webforms/result/delete', ['id' => $result->getId()]);
    }

    /**
     * @param Result $result
     * @return string
     */
    public function getUrlResultPrint(Result $result)
    {
        return $this->getUrl('webforms/result/print', ['id' => $result->getId()]);
    }

    /**
     * @param Result $result
     * @return Phrase
     */
    public function getRepliedStatus(Result $result)
    {
        $messages = $this->_messageCollectionFactory->create()->addFilter('result_id', $result->getId())->count();
        if ($messages) return __('Yes');
        return __('No');
    }

    /**
     * @param Result $result
     * @return mixed
     */
    public function getApproveStatus(Result $result)
    {
        $statuses = $result->getApprovalStatuses();
        foreach ($statuses as $id => $text)
            if ($result->getApproved() == $id)
                return $text;

    }

    public function getPermission($permission)
    {
        $webform = $this->getForm();
        return in_array($permission, $webform->getData('customer_result_permissions'));
    }
}
