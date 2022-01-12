<?php


namespace VladimirPopov\WebForms\Controller\Adminhtml\Export;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use VladimirPopov\WebForms\Model\Export\ConvertResultGridToXml;

class ResultGridToXml extends Action
{
    /**
     * @var ConvertResultGridToXml
     */
    protected $converter;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @param Context $context
     * @param ConvertResultGridToXml $converter
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        ConvertResultGridToXml $converter,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Export data provider to XML
     *
     * @return ResponseInterface
     * @throws \Exception
     * @throws LocalizedException
     */
    public function execute()
    {
        $fileName = 'export.xml';
        return $this->fileFactory->create($fileName, $this->converter->getXmlFile(), DirectoryList::VAR_DIR);
    }
}