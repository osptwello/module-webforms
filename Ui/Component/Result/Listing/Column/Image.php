<?php
namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManager;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\UrlInterface;
use VladimirPopov\WebForms\Model\ResourceModel\File\CollectionFactory as FileCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Image extends File{

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManager $storeManager,
        CustomerFactory $customerFactory,
        UrlInterface $urlBuilder,
        FileCollectionFactory $fileCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory,$storeManager,$customerFactory,$urlBuilder,$fileCollectionFactory, $components, $data);
        $this->_scopeConfig = $scopeConfig;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $field_id = str_replace('field_', '', $fieldName);

            foreach ($dataSource['data']['items'] as & $item) {
                if(isset($item[$fieldName])) {
                    $value = $item[$fieldName];
                    $files = $this->fileCollectionFactory->create()
                        ->addFilter('result_id', $item['result_id'])
                        ->addFilter('field_id', $field_id);
                    $html  = '';
                    foreach ($files as $file) {
                        $nameStart = '<div class="webforms-file-link-name">' . mb_substr($file->getName(), 0, mb_strlen($file->getName()) - 7) . '</div>';
                        $nameEnd   = '<div class="webforms-file-link-name-end">' . mb_substr($file->getName(), -7) . '</div>';
                        if (file_exists($file->getFullPath())) {
                            $width  = $this->_scopeConfig->getValue('webforms/images/grid_thumbnail_width');
                            $height = $this->_scopeConfig->getValue('webforms/images/grid_thumbnail_height');
                            if ($file->getThumbnail($width, $height)) {

                                $html .= '<a class="grid-button-action webforms-file-link" href="' . $file->getDownloadLink() . '">
                            <figure>
                                <p><img src="' . $file->getThumbnail($width, $height) . '"/></p>
                                <figcaption>' . $file->getName() . ' <small>[' . $file->getSizeText() . ']</small></figcaption>
                            </figure>
                        </a>';
                            } else {
                                $html .= '<nobr><a class="grid-button-action webforms-file-link" href="' . $file->getDownloadLink(true) . '">' . $nameStart . $nameEnd . ' <small>[' . $file->getSizeText() . ']</small></a></nobr>';
                            }
                        } else {
                            $html .= '<nobr><a class="grid-button-action webforms-file-link" href="javascript:alert(\'' . __('File not found.') . '\')">' . $nameStart . $nameEnd . ' <small>[' . $file->getSizeText() . ']</small></a></nobr>';
                        }
                    }
                    $item[$fieldName] = $html;
                }
            }
        }

        return $dataSource;
    }
}