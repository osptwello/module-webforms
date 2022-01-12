<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Result;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use VladimirPopov\WebForms\Helper\Data;
use VladimirPopov\WebForms\Model\Result;
use VladimirPopov\WebForms\Model\ResultFactory;

/**
 * Class PrintAction
 * @package VladimirPopov\WebForms\Controller\Adminhtml\Result
 */
class PrintAction extends Action
{
    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var Data
     */
    protected $webformsHelper;

    /**
     * @var ResultFactory
     */
    protected $webformResultFactory;

    /**
     * @var DirectoryList
     */
    protected $_dir;

    /**
     * PrintAction constructor.
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param Data $webformsHelper
     * @param ResultFactory $webformResultFactory
     * @param DirectoryList $_dir
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Data $webformsHelper,
        ResultFactory $webformResultFactory,
        DirectoryList $_dir

    )
    {
        $this->_fileFactory         = $fileFactory;
        $this->webformsHelper       = $webformsHelper;
        $this->webformResultFactory = $webformResultFactory;
        $this->_dir                 = $_dir;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        if ($this->getRequest()->getParam('id')) {
            $model = $this->webformResultFactory->create()->load($this->getRequest()->getParam('id'));
            return $this->webformsHelper->isAllowed($model->getWebformId());
        }
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }

    /**
     * Delete action
     *
     * @return Redirect | ResponseInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                /** @var Result $model */
                $model = $this->webformResultFactory->create()->load($id);
                if (@class_exists('\Mpdf\Mpdf')) {
                    $mpdf = @new \Mpdf\Mpdf(['mode' => 'utf-8', 'tempDir' => $this->_dir->getPath('tmp')]);
                    @$mpdf->WriteHTML($model->toPrintableHtml());
                    return $this->_fileFactory->create(
                        $model->getPdfFilename(),
                        @$mpdf->Output('', 'S'),
                        DirectoryList::TMP
                    );
                }
                $this->messageManager->addWarning(__('Printing is disabled. Please install mPDF library. Run command: composer require mpdf/mpdf'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/form/');
    }
}
