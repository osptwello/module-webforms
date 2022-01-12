<?php

namespace VladimirPopov\WebForms\Controller\Adminhtml\License;

use Magento\Backend\App\Action;

class Verify extends \Magento\Backend\App\Action
{
    protected $_metadata;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    protected $resultJsonFactory;

    protected $webformsHelper;

    protected $curl;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        \Magento\Framework\Module\ModuleList $moduleList,
        \VladimirPopov\WebForms\Helper\Data $webformsHelper
    )
    {
        $this->webformsHelper = $webformsHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_metadata = $metadata;
        $this->_moduleList = $moduleList;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms') || $this->_authorization->isAllowed('VladimirPopov_WebForms::settings');
    }

    final public function execute()
    {
        $result = $this->webformsHelper->isProduction();
        $verified = $result['verified'];
        $errors = $result['errors'];
        $warnings = $result['warnings'];

        $url = 'https://mageme.com/licensecenter/serial/check';
        $request_params = $this->getCurlParams();

        // verify serial registration
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16');
        $dataJson = curl_exec($ch);
        if (!json_decode($dataJson)) {
            $errors[]= __('Unexpected license server response.');
        } else {
            $data = json_decode($dataJson, true);
            if(!empty($data['valid']) && $verified){
                $verified = $data['valid'];
            }
            if(!empty($data['errors'])){
                $errors = array_merge($errors, $data['errors']);
            }
            if(!empty($data['warnings'])){
                $warnings = array_merge($warnings, $data['warnings']);
            }
        }
        if(count($errors))
        {
            $verified = false;
        }

        $json = json_encode(['verified' => $verified, 'errors' => $errors, 'warnings' => $warnings]);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setJsonData($json);

    }

    final protected function getCurlParams()
    {
        $moduleInfo = $this->_moduleList->getOne('VladimirPopov_WebForms');
        $version = (string)$moduleInfo['setup_version'];

        $serial = $this->webformsHelper->getSerial();

        $curl_params = [
            'serial' => $serial,
            'product_name' => 'WFP2M2',
            'product_version' => $version,
            'magento_edition' => $this->_metadata->getEdition(),
            'magento_version' => $this->_metadata->getVersion()
        ];

        return http_build_query($curl_params);
    }

}