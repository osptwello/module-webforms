<?php


namespace VladimirPopov\WebForms\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use VladimirPopov\WebForms\Model\Export\ConvertResultGridToCsv;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;


class ResultGridToCsv extends Action
{
    /**
     * @var ConvertResultGridToCsv
     */
    protected $converter;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @param Context $context
     * @param ConvertResultGridToCsv $converter
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        ConvertResultGridToCsv $converter,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Export data provider to CSV
     *
     * @throws LocalizedException
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'export.csv';
        return $this->fileFactory->create($fileName, $this->converter->getCsvFile(), DirectoryList::VAR_DIR);
    }
}