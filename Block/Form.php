<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use VladimirPopov\WebForms\Helper\Data;
use VladimirPopov\WebForms\Model\Captcha;
use VladimirPopov\WebForms\Model\CaptchaFactory;
use VladimirPopov\WebForms\Model\FormFactory;
use VladimirPopov\WebForms\Model\ResourceModel\Field\CollectionFactory as FieldCollectionFactory;
use VladimirPopov\WebForms\Model\Result;
use VladimirPopov\WebForms\Model\ResultFactory;
use VladimirPopov\WebForms\Model\ResourceModel\Result\CollectionFactory as ResultCollectionFactory;
use VladimirPopov_WebForms_Model_Results;
use function var_dump;

/**
 * Class Form
 * @package VladimirPopov\WebForms\Block
 */
class Form extends Template
{
    /**
     * @var
     */
    protected $_form;

    /**
     * @var FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var FormFactory
     */
    protected $_formFactory;

    /**
     * @var FieldCollectionFactory
     */
    protected $fieldCollectionFactory;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var SessionFactory
     */
    protected $_customerSessionFactory;

    /**
     * @var
     */
    protected $_success;

    /**
     * @var Url
     */
    protected $_customerUrl;

    /**
     * @var ResultCollectionFactory
     */
    protected $_resultCollectionFactory;

    /**
     * @var ResultFactory
     */
    protected $_resultFactory;

    /**
     * @var Http
     */
    protected $_response;

    /**
     * @var CaptchaFactory
     */
    protected $_captcha;

    /**
     * @var StoreManager
     */
    protected $_storeManager;

    /**
     * @var
     */
    protected $_uid;

    /**
     * @var Random
     */
    protected $random;

    /**
     * @var Data
     */
    protected $webformsHelper;

    /**
     * @var
     */
    protected $_result;

    /**
     * Form constructor.
     * @param SessionFactory $customerSessionFactory
     * @param Template\Context $context
     * @param FilterProvider $filterProvider
     * @param Registry $coreRegistry
     * @param FormFactory $formFactory
     * @param Url $customerUrl
     * @param ResultCollectionFactory $resultCollectionFactory
     * @param ResultFactory $resultFactory
     * @param Http $response
     * @param CaptchaFactory $captcha
     * @param StoreManager $storeManager
     * @param Random $random
     * @param Data $webformsHelper
     * @param FieldCollectionFactory $fieldCollectionFactory
     * @param array $data
     */
    public function __construct(
        SessionFactory $customerSessionFactory,
        Template\Context $context,
        FilterProvider $filterProvider,
        Registry $coreRegistry,
        FormFactory $formFactory,
        Url $customerUrl,
        ResultCollectionFactory $resultCollectionFactory,
        ResultFactory $resultFactory,
        Http $response,
        CaptchaFactory $captcha,
        StoreManager $storeManager,
        Random $random,
        Data $webformsHelper,
        FieldCollectionFactory $fieldCollectionFactory,
        array $data = []
    )
    {
        $this->_customerUrl             = $customerUrl;
        $this->_filterProvider          = $filterProvider;
        $this->_coreRegistry            = $coreRegistry;
        $this->_formFactory             = $formFactory;
        $this->_customerSessionFactory  = $customerSessionFactory;
        $this->_customerSession         = $customerSessionFactory->create();
        $this->_resultCollectionFactory = $resultCollectionFactory;
        $this->_resultFactory           = $resultFactory;
        $this->_response                = $response;
        $this->_captcha                 = $captcha;
        $this->_storeManager            = $storeManager;
        $this->random                   = $random;
        $this->webformsHelper           = $webformsHelper;
        $this->fieldCollectionFactory   = $fieldCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \VladimirPopov\WebForms\Model\Form
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * @param $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * @param $value
     */
    public function setSuccess($value)
    {
        $this->_success = $value;
    }

    /**
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->_success;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return ScopeInterface::SCOPE_STORE;
    }

    /**
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->_coreRegistry;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->_customerSession;
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _toHtml()
    {
        if ($this->_coreRegistry->registry('current_category'))
            $this->_customerSession->setData('last_viewed_category_id', $this->_coreRegistry->registry('current_category')->getId());

        if ($this->_coreRegistry->registry('current_product'))
            $this->_customerSession->setData('last_viewed_product_id', $this->_coreRegistry->registry('current_product')->getId());

        if (!$this->_coreRegistry->registry('current_url')) {
            $this->_coreRegistry->register('current_url', $this->_storeManager->getStore()->getCurrentUrl(false));
        }
        $this->_customerSession->setData('last_viewed_url', $this->_coreRegistry->registry('current_url'));
        $this->setData('current_url', $this->_coreRegistry->registry('current_url'));

        if ($this->_coreRegistry->registry('webforms_preview')) {
            if (strlen($this->_storeManager->getStore()->getConfig('webforms/general/preview_template')) > 1) {
                $this->setTemplate($this->_storeManager->getStore()->getConfig('webforms/general/preview_template'));
            }

        }

        if (!$this->_coreRegistry->registry('webforms_preview')) {
            $this->initForm();
        }

        if (!$this->getForm()->canAccess()) {
            $this->setTemplate('VladimirPopov_WebForms::webforms/access_denied.phtml');
        }

        $html = parent::_toHtml();

        $messages   = $this->getLayout()->getMessagesBlock();
        $production = $this->webformsHelper->isProduction();
        if (!$production['verified']) {
            $messages->addNotice($this->webformsHelper->getNote());
        }

        $html = $messages->getGroupedHtml() . $html;

        return $html;
    }

    /**
     * @return \Magento\Framework\Filter\Template
     */
    public function getPageFilter()
    {
        return $this->_filterProvider->getPageFilter();
    }

    public function setResult(Result $result)
    {
        $this->_result = $result;
        return $this;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        if($this->_result)
            return $this->_result;
        return $this->_resultFactory->create();
    }

    /**
     * @return Result
     */
    public function getResultFromUrl()
    {
        $result = $this->_resultFactory->create()->setId(true);
        $data   = [];
        if ($this->_form) {
            if ($this->_form->getData('accept_url_parameters')) {
                $urlParams = $this->getRequest()->getParams();
                foreach ($urlParams as $fieldCode => $value) {
                    $field = $this->fieldCollectionFactory->create()->addFilter('webform_id', $this->_form->getId())->addFilter('code', $fieldCode)->getFirstItem();
                    if ($field->getId()) {
                        $data[$field->getId()] = $value;
                        $result->setData('field_' . $field->getId(), $value);
                    }
                }
            }
        }

        $result->setData('field', $data);

        return $result;
    }

    /**
     * @return $this|Http|HttpInterface
     * @throws NoSuchEntityException
     */
    protected function initForm()
    {

        $show_success = false;
        $data         = $this->getFormData();

        $form = $this->_formFactory->create()
            ->setStoreId($this->_storeManager->getStore()->getId());

        if ($this->getData('webform_code')) {
            $form = $form->getCollection()
                ->setStoreId($this->_storeManager->getStore()->getId())
                ->addFilter('code', $this->getData('webform_code'))
                ->getFirstItem();
        }

        if (!$form->getId()) $form->load($data['webform_id']);

        $this->setForm($form);

        $result = $this->getResultFromUrl();
        if($this->getResult() && $this->getResult()->getId()){
            $result = $this->getResult();
        }
        $form->getFieldsToFieldsets(false, $result);
        $this->_coreRegistry->unregister('webform');
        $this->_coreRegistry->register('webform', $form);

        //delete form temporary data
        if ($this->isAjax()) {
            $this->_session->setData('webform_result_tmp_' . $form->getId(), false);
        }


        //proccess texts

        if ($form->getDescription())
            $this->setDescription($this->getPageFilter()->filter($form->getDescription()));
        if ($form->getSuccessText())
            $this->setSuccessText($this->getPageFilter()->filter($form->getSuccessText()));

        $this->_customerSession = $this->_customerSessionFactory->create();
        $loggedIn               = $this->_customerSession->isLoggedIn();
        if ($form->getSurvey()) {
            $collection = $this->_resultCollectionFactory->create()->addFilter('webform_id', $data['webform_id']);

            if ($loggedIn) {
                $collection->addFilter('customer_id', $this->_customerSession->getCustomerId());
            } else {
                $customerSession_validator = $this->_customerSession->getData('_customerSession_validator_data');
                $collection->addFilter('customer_ip', ip2long($customerSession_validator['remote_addr']));
            }
            $count = $collection->count();

            if ($count > 0) {
                $show_success = true;
            }
        }

        if ($this->_customerSession->getFormSuccess() == $this->getForm()->getId() || $show_success) {
            $this->setSuccess(true);
            $this->_customerSession->setFormSuccess();
            if ($this->_customerSession->getData('webform_result_' . $form->getId())) {

                // apply custom variables
                $filter     = $this->getPageFilter();
                $formObject = new DataObject;
                $formObject->setData($form->getData());
                $resultObject = $this->_resultFactory->create()->load(($this->_customerSession->getData('webform_result_' . $form->getId())));
                $subject      = $resultObject->getEmailSubject('customer');
                $filter->setVariables(array(
                    'webform_result' => $resultObject->toHtml('customer'),
                    'result' => $resultObject->getTemplateResultVar(),
                    'webform' => $formObject,
                    'webform_subject' => $subject,
                ));
            }
        }
        if ($form->getAccessEnable() && !$loggedIn && !$this->getData('results')) {
            $this->_customerSession->setBeforeAuthUrl($this->_urlBuilder->getCurrentUrl());
            $login_url = $this->_customerUrl->getLoginUrl();
            $status    = 301;

            if ($this->_storeManager->getStore()->getConfig('webforms/general/login_redirect')) {
                $login_url = $this->getUrl($this->_storeManager->getStore()->getConfig('webforms/general/login_redirect'));

                if (strstr($this->_storeManager->getStore()->getConfig('webforms/general/login_redirect'), '://')) {
                    $login_url = $this->_storeManager->getStore()->getConfig('webforms/general/login_redirect');
                }

            }
            return $this->_response->setRedirect($login_url, $status);
        }
        // switch to async template
        if ($this->getData('async_load')) {
            $this->setData('widget_template', $this->getTemplate());
            $this->setData('async_url', $this->getUrl('webforms/form/load', ['key' => '#WIDGET_KEY#', '_current' => true]));
            $this->setTemplate('webforms/form/async.phtml');
        }

        return $this;
    }

    // check that form is available for direct access

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function isDirectAvailable()
    {
        $available = new DataObject;
        $status    = true;
        if ($this->_coreRegistry->registry('webforms_preview') && !$this->_storeManager->getStore()->getConfig('webforms/general/preview_enabled')) {
            $status = false;
        }

        $available->setData('status', $status);

        $this->_eventManager->dispatch('webforms_direct_available', array
        (
            'available' => $available,
            'form_data' => $this->getFormData(),
        ));

        return $available->getData('status');
    }

    /**
     * @return Phrase
     * @throws NoSuchEntityException
     */
    public function getNotAvailableMessage()
    {
        $message = __('Web-form is not active.');

        if ($this->getForm()->getIsActive() && !$this->isDirectAvailable()) {
            $message = __('Web-form is locked by configuration and can not be accessed directly.');
        }

        return $message;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getFormData()
    {
        $data = $this->getRequest()->getParams();

        if (isset($data['id'])) {
            $data['webform_id'] = $data['id'];
        }

        if ($this->getData('webform_id')) {
            $data['webform_id'] = $this->getData('webform_id');
        }

        if (empty($data['webform_id'])) {
            $data['webform_id'] = $this->_storeManager->getStore()->getConfig('webforms/contacts/webform');
        }
        return $data;
    }

    /**
     * @return Template|void
     * @throws NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->_coreRegistry->registry('webforms_preview')) {

            $this->initForm();
            $this->pageConfig->getTitle()->set($this->getForm()->getName());
        }
    }

    /**
     * @return bool|Captcha
     */
    public function getCaptcha()
    {
        return $this->getForm()->getCaptcha();
    }

    /**
     * @return string
     */
    public function getEnctype()
    {
        return 'multipart/form-data';
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function isAjax()
    {
        return $this->_storeManager->getStore()->getConfig('webforms/general/ajax');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getFormAction()
    {
        return $this->getUrl('webforms/form/submit', ['_secure' => true, 'ajax' => $this->isAjax()]);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function honeypot()
    {
        return $this->_storeManager->getStore()->getConfig('webforms/honeypot/enable');
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getUid()
    {
        if($this->_scopeConfig->getValue('webforms/general/use_uid')) {
            if (!$this->_uid) {
                $this->_uid = $this->random->getRandomString(6);
            }
            return $this->_uid;
        }
        return $this->getForm()->getId();
    }

    /**
     * @param $field_id
     * @return string
     * @throws LocalizedException
     */
    public function getFieldUid($field_id)
    {
        return $this->getUid() . $field_id;
    }

    /**
     * @return string
     */
    public function getSubmitVisibility()
    {
        $_targets = $this->getForm()->_getLogicTarget();
        foreach ($_targets as $target) {
            if ($target['id'] == 'submit')
                return $target['logic_visibility'];

        }
        return 'visible';
    }

    /**
     * @return mixed
     */
    public function getFormKey()
    {
        return $this->getData('form_key');
    }
}
