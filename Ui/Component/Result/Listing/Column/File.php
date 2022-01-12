<?php

namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManager;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\UrlInterface;
use VladimirPopov\WebForms\Model\ResourceModel\File\CollectionFactory as FileCollectionFactory;

/**
 * Class File
 * @package VladimirPopov\WebForms\Ui\Component\Result\Listing\Column
 */
class File extends \Magento\Ui\Component\Listing\Columns\Column
{

    /** @var StoreManager */
    protected $storeManager;

    /** @var CustomerFactory */
    protected $customerFactory;

    /** @var FileCollectionFactory */
    protected $fileCollectionFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * File constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManager $storeManager
     * @param CustomerFactory $customerFactory
     * @param UrlInterface $urlBuilder
     * @param FileCollectionFactory $fileCollectionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManager $storeManager,
        CustomerFactory $customerFactory,
        UrlInterface $urlBuilder,
        FileCollectionFactory $fileCollectionFactory,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->urlBuilder = $urlBuilder;
        $this->fileCollectionFactory = $fileCollectionFactory;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $field_id = str_replace('field_', '', $fieldName);

            foreach ($dataSource['data']['items'] as & $item) {
                $files = $this->fileCollectionFactory->create()
                    ->addFilter('result_id', $item['result_id'])
                    ->addFilter('field_id', $field_id);
                $html = '';
                foreach ($files as $file) {
                    $file->setName(htmlentities($file->getName()));
                    $nameStart = '<div class="webforms-file-link-name">' . mb_substr($file->getName(), 0, mb_strlen($file->getName()) - 7) . '</div>';
                    $nameEnd = '<div class="webforms-file-link-name-end">' . mb_substr($file->getName(), -7) . '</div>';
                    if (file_exists($file->getFullPath())) {
                        $html .= '<nobr><a class="grid-button-action webforms-file-link" href="' . $file->getDownloadLink(true) . '">' . $nameStart . $nameEnd . ' <small>[' . $file->getSizeText() . ']</small></a></nobr>';
                    } else {
                        $html .= '<nobr><a class="grid-button-action webforms-file-link" href="javascript:alert(\'' . __('File not found.') . '\')">' . $nameStart . $nameEnd . ' <small>[' . $file->getSizeText() . ']</small></a></nobr>';
                    }
                }
                $item[$fieldName] = $html;
            }
        }

        return $dataSource;
    }

}
