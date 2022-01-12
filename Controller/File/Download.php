<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\File;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Context;

class Download extends Action
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

    protected $_session;


    public function __construct(
        Context $context,
        \VladimirPopov\WebForms\Model\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Session\SessionManagerInterface $session
    )
    {
        $this->fileFactory = $fileFactory;
        $this->_filesystem = $filesystem;
        $this->_session = $session;
        parent::__construct($context);
    }

    public function execute()
    {
        $hash = $this->getRequest()->getParam('hash');

        if ($hash) {

            /** @var \VladimirPopov\WebForms\Model\File $file */

            $file = $this->fileFactory->create()->loadByHash($hash);

            $result = $file->getResult();
            if ($result) {
                $webform = $result->getWebform();
                if ($webform && $webform->getData('frontend_download')) {

                    if (file_exists($file->getFullPath())) {
                        /** @var \VladimirPopov\WebForms\Model\Result $result */
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

                        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);

                        $this->getResponse()->clearBody();
                        $this->getResponse()->sendHeaders();

                        $this->_workingDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);

                        $handle = $this->_workingDirectory->openFile($file->getPath());
                        $file= "";
                        while (true == ($buffer = $handle->read(1024))) {
                            $file .= $buffer;
                        }
                        return $this->getResponse()->setBody($file);
                    }
                }
            }
        }
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('noroute');
        return $resultForward;
    }

}
