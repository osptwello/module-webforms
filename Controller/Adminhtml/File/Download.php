<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\File;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magento\Backend\App\Action
{
    /**
     * Resource open handle
     *
     * @var \Magento\Framework\Filesystem\File\ReadInterface
     */
    protected $_handle = null;

    protected $fileFactory;

    protected $_filesystem;

    protected $_workingDirectory;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \VladimirPopov\WebForms\Model\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    )
    {
        $this->fileFactory = $fileFactory;
        $this->_filesystem = $filesystem;
        parent::__construct($context);

    }

    public function execute()
    {
        $hash = $this->getRequest()->getParam('hash');

        if ($hash) {
            /** @var \VladimirPopov\WebForms\Model\File $file */
            $file = $this->fileFactory->create()->loadByHash($hash);
            if (file_exists($file->getFullPath())) {
                /** @var \VladimirPopov\WebForms\Model\Result $result */
                $result = $file->getResult();
                $fileName = $file->getName();
                $contentType = $file->getMimeType();

                $this->getResponse()->setHttpResponseCode(
                    200
                )->setHeader(
                    'Pragma',
                    'public',
                    true
                )->setHeader(
                    'Cache-Control',
                    'must-revalidate, post-check=0, pre-check=0',
                    true
                )->setHeader(
                    'Content-type',
                    $contentType,
                    true
                );

                if ($fileSize = $file->getSize()) {
                    $this->getResponse()->setHeader('Content-Length', $fileSize);
                }

                $this->getResponse()->setHeader('Content-Disposition','attachment; filename=' . $fileName);

                $this->getResponse()->clearBody();
                $this->getResponse()->sendHeaders();

                $this->_workingDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);

                $handle = $this->_workingDirectory->openFile($file->getPath());

                $file = "";
                while (true == ($buffer = $handle->read(1024))) {
                    $file .= $buffer;
                }
                return $this->getResponse()->setBody($file);
            }
        }
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('noroute');
        return $resultForward;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }
}
