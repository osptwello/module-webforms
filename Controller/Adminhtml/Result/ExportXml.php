<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Result;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Framework\App\Response\Http\FileFactory;
use VladimirPopov\WebForms\Helper\Data;
use VladimirPopov\WebForms\Model\FormFactory;

/**
 * Class ExportXml
 * @deprecated replaced with custom UI export
 * @package VladimirPopov\WebForms\Controller\Adminhtml\Result
 */
class ExportXml extends Index
{
    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * ExportXml constructor.
     * @param Registry $coreRegistry
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param FileFactory $fileFactory
     * @param Data $webformsHelper
     * @param FormFactory $formFactory
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        PageFactory $resultPageFactory,
        FileFactory $fileFactory,
        Data $webformsHelper,
        FormFactory $formFactory
    )
    {
        $this->_fileFactory = $fileFactory;
        parent::__construct($coreRegistry, $context, $resultPageFactory, $webformsHelper, $formFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $this->_initForm('webform_id');

        $this->_view->loadLayout();
        $fileName = 'results.xml';
        $content  = $this->_view->getLayout()->getBlock('admin.result.grid');

        return $this->_fileFactory->create(
            $fileName,
            $content->getExcel($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
