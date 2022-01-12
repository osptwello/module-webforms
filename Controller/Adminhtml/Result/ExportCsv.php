<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Result;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class ExportCsv
 * @deprecated replaced with custom UI export
 * @package VladimirPopov\WebForms\Controller\Adminhtml\Result
 */
class ExportCsv extends \VladimirPopov\WebForms\Controller\Adminhtml\Result\Index
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \VladimirPopov\WebForms\Helper\Data $webformsHelper,
        \VladimirPopov\WebForms\Model\FormFactory $formFactory
    )
    {
        $this->_fileFactory = $fileFactory;
        parent::__construct($coreRegistry, $context, $resultPageFactory, $webformsHelper, $formFactory);
    }

    /**
     * Export results grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {

        $this->_initForm('webform_id');

        $this->_view->loadLayout();
        $fileName = 'results.csv';
        $content = $this->_view->getLayout()->getBlock('admin.result.grid');

        return $this->_fileFactory->create(
            $fileName,
            $content->getCsvFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
