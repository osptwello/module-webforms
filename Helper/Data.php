<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Helper;

use Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Model\Authorization;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\AuthorizationInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Zend_Validate_Regex;
use function sha1;
use function substr;

class Data extends AbstractHelper
{
    const DKEY = 'WF1DMM2';
    const SKEY = 'WFSRVM2';
    const DEV_CHECK = true;

    protected $storeManager;

    protected $_rulesCollectionFactory;

    protected $_metadata;

    protected $roleLocator;

    protected $resourceConnection;

    protected $state;

    /**
     * @var AuthorizationInterface
     */
    protected $_authorization;

    public function __construct(
        Context $context,
        StoreManager $storeManager,
        CollectionFactory $rulesCollectionFactory,
        AuthorizationInterface $authorization,
        ProductMetadataInterface $metadata,
        ResourceConnection $resourceConnection,
        State $state,
        Authorization\RoleLocator $roleLocator
    )
    {
        $this->storeManager            = $storeManager;
        $this->roleLocator             = $roleLocator;
        $this->_authorization          = $authorization;
        $this->_metadata               = $metadata;
        $this->_rulesCollectionFactory = $rulesCollectionFactory;
        $this->resourceConnection      = $resourceConnection;
        $this->state                   = $state;
        parent::__construct($context);
    }

    final protected function getDomain($url)
    {
        $url = explode('/', str_replace(array('http://', 'https://'), '', $url))[0];
        $tmp = explode('.', $url);
        $cnt = count($tmp);

        if (empty($tmp[$cnt - 2]) || empty($tmp[$cnt - 1])) {
            return $url;
        }

        $suffix = $tmp[$cnt - 2] . '.' . $tmp[$cnt - 1];

        $exceptions = array(
            'com.au', 'com.br', 'com.bz', 'com.ve', 'com.gp',
            'com.ge', 'com.eg', 'com.es', 'com.ye', 'com.kz',
            'com.cm', 'net.cm', 'com.cy', 'com.co', 'com.km',
            'com.lv', 'com.my', 'com.mt', 'com.pl', 'com.ro',
            'com.sa', 'com.sg', 'com.tr', 'com.ua', 'com.hr',
            'com.ee', 'ltd.uk', 'me.uk', 'net.uk', 'org.uk',
            'plc.uk', 'co.uk', 'co.nz', 'co.za', 'co.il',
            'co.jp', 'ne.jp', 'net.au', 'com.ar',
        );

        if (in_array($suffix, $exceptions)) {
            $domain = $tmp[$cnt - 3] . '.' . $tmp[$cnt - 2] . '.' . $tmp[$cnt - 1];
        } else {
            $domain = $suffix;
        }

        $domain = explode(':', $domain);
        $domain = $domain[0];

        return $domain;
    }

    final protected function verify($domain, $checkstr)
    {
        if ("wf" . substr(sha1(self::DKEY . $domain), 0, 18) == substr($checkstr, 0, 20)) {
            return true;
        }
        if ("wf" . substr(sha1(self::SKEY . $this->getServer('SERVER_ADDR')), 0, 10) == substr($checkstr, 0, 12)) {
            return true;
        }

        $dns_record = @dns_get_record($this->getServer('SERVER_NAME'), DNS_A);
        if (isset($dns_record[0]) && !empty($dns_record[0]['ip'])) {

            if ("wf" . substr(sha1(self::SKEY . $dns_record[0]['ip']), 0, 10) == substr($checkstr, 0, 12)) {
                return true;
            }
        }

        $dns_record = @dns_get_record($domain, DNS_A);
        if (isset($dns_record[0]) && !empty($dns_record[0]['ip'])) {
            if ("wf" . substr(sha1(self::SKEY . $dns_record[0]['ip']), 0, 10) == substr($checkstr, 0, 12)) {
                return true;
            }
        }

        $base       = $this->getDomain(parse_url($this->storeManager->getStore(0)->getConfig('web/unsecure/base_url'), PHP_URL_HOST));
        $dns_record = @dns_get_record($base, DNS_A);
        if (isset($dns_record[0]) && !empty($dns_record[0]['ip'])) {
            if ("wf" . substr(sha1(self::SKEY . $dns_record[0]['ip']), 0, 10) == substr($checkstr, 0, 12)) {
                return true;
            }
        }

        if ("wf" . substr(sha1(self::SKEY . gethostbyname($this->getServer())), 0, 10) == substr($checkstr, 0, 12)) {
            return true;
        }

        if ("wf" . substr(sha1(self::SKEY . gethostbyname($domain)), 0, 10) == substr($checkstr, 0, 12)) {
            return true;
        }

        $base = $this->getDomain(parse_url($this->storeManager->getStore(0)->getConfig('web/unsecure/base_url'), PHP_URL_HOST));
        if ("wf" . substr(sha1(self::SKEY . gethostbyname($base)), 0, 10) == substr($checkstr, 0, 12)) {
            return true;
        }

        if (substr(sha1(self::SKEY . $base), 0, 8) == substr($checkstr, 12, 8)) {
            return true;
        }

        if ($this->verifyIpMask(array($this->getServer('SERVER_ADDR'), $this->getServer(), $domain, $base), $checkstr)) {
            return true;
        }

        return false;
    }

    final private function verifyIpMask($data, $checkstr)
    {
        if (!is_array($data)) {
            $data = array($data);
        }
        foreach ($data as $name) {
            $ipdata = explode('.', gethostbyname($name));
            if (isset($ipdata[3])) {
                $ipdata[3] = '*';
            }

            $mask = implode('.', $ipdata);
            if ("wf" . substr(sha1(self::SKEY . $mask), 0, 10) == substr($checkstr, 0, 12)) {
                return true;
            }
            if (isset($ipdata[2])) {
                $ipdata[2] = '*';
            }

            $mask = implode('.', $ipdata);
            if ("wf" . substr(sha1(self::SKEY . $mask), 0, 10) == substr($checkstr, 0, 12)) {
                return true;
            }
        }
        return false;
    }

    final public function getSerial()
    {
        $serial = $this->scopeConfig->getValue('webforms/license/serial', ScopeInterface::SCOPE_STORE);
        if ($this->_request->getParam('website')) {
            $serial = $this->storeManager->getWebsite($this->_request->getParam('website'))->getConfig('webforms/license/serial');
        }
        if ($this->_request->getParam('store')) {
            $serial = $this->storeManager->getStore($this->_request->getParam('store'))->getConfig('webforms/license/serial');
        }

        return $serial;
    }

    final public function isProduction()
    {

        $errors   = [];
        $warnings = [];

        $serial = $this->getSerial();

        $checkstr = strtolower(str_replace(array(" ", "-"), "", $serial));

        // check local environment
        if (self::DEV_CHECK) {
            if ($this->isLocal()) {
                return ['verified' => true, 'errors' => $errors, 'warnings' => $warnings];
            }
        }

        // check domain
        $domain  = $this->getDomain($this->getServer());
        $domain2 = $this->getDomain($this->scopeConfig->getValue('web/unsecure/base_url', ScopeInterface::SCOPE_STORE));
        if ($this->_request->getParam('website')) {
            $domain2 = $this->getDomain($this->storeManager->getWebsite($this->_request->getParam('website'))->getConfig('web/unsecure/base_url'));
        }
        if ($this->_request->getParam('store')) {
            $domain2 = $this->getDomain($this->storeManager->getStore($this->_request->getParam('store'))->getConfig('web/unsecure/base_url'));
        }
        $verified = $this->verify($domain, $checkstr) || $this->verify($domain2, $checkstr);

        if (!$verified) {
            $errors[] = __('Incorrect serial number.');
        } else {

            // check development
            if (substr(strtoupper(sha1('DEV')), 0, 2) == substr($serial, -2)) {
                $warnings[] = __('Development license detected. Please do not use for production.');
            } else {
                // check Magento edition
                $magento_edition = $this->_metadata->getEdition();
                $edition         = substr(strtoupper(sha1(strtoupper(substr($magento_edition, 0, 1) . 'E'))), 0, 2);
                if (substr($serial, -2) != substr(strtoupper(sha1(strtoupper('EE'))), 0, 2)) {
                    if ($edition != substr($serial, -2)) {
                        $errors[] = __('The license is not valid for Magento %1 edition. Please do not use for production.', $magento_edition);
                    }
                }
            }
        }

        return ['verified' => $verified, 'errors' => $errors, 'warnings' => $warnings];
    }

    public function getMagentoVersion()
    {
        return $this->_metadata->getVersion();
    }

    public function checkStoreCode($storeCode)
    {
        if (!$storeCode) {
            return false;
        }

        $resource       = $this->resourceConnection;
        $connection     = $resource->getConnection();
        $storeTableName = $connection->getTableName('store');
        $select         = $connection->select()->from($storeTableName)->where('code = ?', $storeCode)->limit(1);
        $query          = $connection->query($select);
        return count($query->fetchAll()) > 0;
    }

    final public function isLocal()
    {
        $domain = $this->getDomain($this->getServer());

        return substr($domain, -6) == '.local' ||
            substr($domain, -4) == '.dev' ||
            substr($domain, -5) == '.test' ||
            $this->getServer() == 'localhost' ||
            substr($domain, -10) == '.localhost' ||
            substr($domain, -18) == '.magentosite.cloud' ||
            substr($this->getServer(), -7) == '.xip.io';
    }

    public function getServer($param = 'SERVER_NAME')
    {
        return $this->_request->getServer($param);
    }

    final public function getNote()
    {
        if ($this->scopeConfig->getValue('webforms/license/serial', ScopeInterface::SCOPE_STORE)) {
            return __('WebForms Professional Edition license number is not valid for store domain.');
        }
        return __('License serial number for WebForms Professional Edition is missing.');
    }

    public function isInEmailStoplist($email)
    {
        if (!$email) {
            return false;
        }

        $stoplist = preg_split("/[\s\n,;]+/", $this->scopeConfig->getValue('webforms/email/stoplist'));

        $flag = false;
        foreach ($stoplist as $blocked_email) {
            $pattern = trim($blocked_email);

            // clear global modifier
            if (substr($pattern, 0, 1) == '/' && substr($pattern, -2) == '/g') {
                $pattern = substr($pattern, 0, strlen($pattern) - 1);
            }

            $status = @preg_match($pattern, "Test");
            if ($status !== false) {
                $validate = new Zend_Validate_Regex($pattern);
                if ($validate->isValid($email)) {
                    $flag = true;
                }

            }
            if ($email == $blocked_email) {
                return true;
            }

        }
        return $flag;
    }

    public function isAllowed($formId)
    {
        if ($this->_authorization->isAllowed('Magento_Backend::all')) {
            return true;
        }

        if (!$formId) {
            return false;
        }

        $collection = $this->_rulesCollectionFactory->create()
            ->addFilter('role_id', $this->roleLocator->getAclRoleId())
            ->addFilter('resource_id', 'VladimirPopov_WebForms::form' . $formId)
            ->addFilter('permission', 'allow');

        return $collection->count();
    }

    public function isAdmin()
    {
        $app_state = $this->state;
        $area_code = $app_state->getAreaCode();
        if ($area_code == FrontNameResolver::AREA_CODE) {
            return true;
        }
        return false;
    }
}
