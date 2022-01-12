<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Form;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Factory as MessageFactory;
use Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\UnreadFactory as MessageList;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /** @var MessageList */
    protected $messageList;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        MessageList $messageList,
        MessageFactory $messageFactory,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->messageManager    = $context->getMessageManager();
        $this->messageList       = $messageList;
        $this->messageFactory    = $messageFactory;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        foreach ($this->messageList->create() as $message) {
            if (strstr($message->getData('url'), 'webforms-pro-m2')) {
                $link = '<a href="' . $message->getData('url') . '" target="_blank">' . __('Read Details') . '</a>';
                $markAsRead = '<a class="mageme-mark-as-read" href="' . $this->getUrl('adminhtml/notification/markAsRead', ['id' => $message->getId()]) . '">' . __('Mark as Read') . '</a>';
                $text       = $message->getData('title') . ' ' . $link . $markAsRead;
                $this->messageManager->addMessage($this->messageFactory->create(MessageInterface::TYPE_NOTICE, $text));
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('VladimirPopov_WebForms::manage_forms');
        $resultPage->addBreadcrumb(__('Web-forms'), __('Web-forms'));
        $resultPage->addBreadcrumb(__('Manage Forms'), __('Manage Forms'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Forms'));

        return $resultPage;
    }
}
