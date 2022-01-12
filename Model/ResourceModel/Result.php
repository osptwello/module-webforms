<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Model\ResourceModel;

use function is_array;
use function json_encode;

/**
 * Result resource model
 *
 */
class Result extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Name of scope for error messages
     *
     * @var string
     */
    protected $_messagesScope = 'webforms/session';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    protected $_fieldFactory;

    protected $_localeResolver;

    protected $_eventManager;

    protected $_random;

    protected $_formFactory;

    protected $_fileStorage;

    protected $_messageFactoryCollection;

    protected $_customerFactory;

    protected $storeManager;

    protected $_fileCollectionFactory;

    protected $dropzoneFactory;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \VladimirPopov\WebForms\Model\FieldFactory $fieldFactory,
        \VladimirPopov\WebForms\Model\ResourceModel\File\CollectionFactory $fileCollectionFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Math\Random $random,
        \VladimirPopov\WebForms\Model\FormFactory $formFactory,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorage,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManager $storeManager,
        \VladimirPopov\WebForms\Model\ResourceModel\Message\CollectionFactory $messageFactoryCollection,
        \VladimirPopov\WebForms\Model\DropzoneFactory $dropzoneFactory,
        $resourcePrefix = null
    )
    {
        $this->_date = $date;
        $this->_fieldFactory = $fieldFactory;
        $this->_localeResolver = $localeResolver;
        $this->_eventManager = $eventManager;
        $this->_random = $random;
        $this->_formFactory = $formFactory;
        $this->_fileStorage = $fileStorage;
        $this->_messageFactoryCollection = $messageFactoryCollection;
        $this->_customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->_fileCollectionFactory = $fileCollectionFactory;
        $this->dropzoneFactory = $dropzoneFactory;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('webforms_results', 'id');
    }

    /**
     * Set error messages scope
     *
     * @param string $scope
     * @return void
     */
    public function setMessagesScope($scope)
    {
        $this->_messagesScope = $scope;
    }

    public function getLocale()
    {
        return $this->_localeResolver->getLocale();
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCreatedTime()) {
            $object->setCreatedTime($this->_date->gmtDate());
        }

        $object->setUpdateTime($this->_date->gmtDate());

        if (is_array($object->getData('field')) && count($object->getData('field')) > 0) {
            foreach ($object->getData('field') as $field_id => $value) {
                $field = $this->_fieldFactory->create()->load($field_id);

                // assign customer ID if email found
                if ($field->getType() == 'email' && $field->getValue('assign_customer_id_by_email') && !$object->getCustomerId() && $value) {
                    $customer = $this->_customerFactory->create();
                    $customer->setWebsiteId($this->storeManager->getStore($object->getStoreId())->getWebsiteId())->loadByEmail($value);
                    if ($customer->getId()) {
                        $object->setCustomerId($customer->getId());
                    }
                }
            }
        }

        parent::_beforeSave($object);
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        //insert field values
        if (is_array($object->getData('field')) && count($object->getData('field')) > 0) {
            foreach ($object->getData('field') as $field_id => $value) {
                $field = $this->_fieldFactory->create()->setStoreId($object->getDatA('store_id'))->load($field_id);

                //process region
                if($field->getType() == 'region'){
                    if(is_array($value)){
                        $value = json_encode($value);
                    }
                }

                if (is_array($value)) {
                    $value = implode("\n", $value);
                }

                if ($field->getId()) {
                    if (strstr($field->getType(), 'date')) {
                        // check if the date is already in db format
                        $format_date = true;
                        if (date('Y-m-d H:i:s', strtotime($value)) == $value || date('Y-m-d', strtotime($value)) == $value) {
                            $format_date = false;
                        }
                        if ($format_date) {
                            if (strlen($value) > 0) {
                                $dateFormat = $field->getDateFormat();
                                if ($field->getType() == 'datetime')
                                    $dateFormat .= " " . $field->getTimeFormat();
                                $dateArray = \Zend_Locale_Format::getDateTime($value, [
                                    'date_format' => $dateFormat
                                ]);
                                $date      = new \Zend_Date();
                                if (!empty($dateArray["year"])) $date->setYear($dateArray["year"]);
                                if (!empty($dateArray["month"])) $date->setMonth($dateArray["month"]);
                                if (!empty($dateArray["day"])) $date->setDay($dateArray["day"]);
                                if (!empty($dateArray["hour"])) $date->setHour($dateArray["hour"]);
                                if (!empty($dateArray["minute"])) $date->setMinute($dateArray["minute"]);
                                $value = date($field->getDbDateFormat(), $date->getTimestamp());
                            }
                        }
                    }

                    if ($field->getType() == 'select/contact' && is_numeric($value)) {
                        $value = $field->getContactValueById($value);
                    }

                    if ($value == $field->getHint()) {
                        $value = '';
                    }
                    // process dropZone
                    if ($field->getType() == 'file' || $field->getType() == 'image') {
                        if ($field->getValue('dropzone')) {
                            $input = $value;

                            $counter  = 0;
                            $maxFiles = $field->getValue('dropzone_maxfiles') ? $field->getValue('dropzone_maxfiles') : 5;
                            if (!empty($input)) {
                                $hash_array = explode(';', $input);
                                foreach ($hash_array as $hash) {
                                    /** @var \VladimirPopov\WebForms\Model\Dropzone $dropzone */
                                    $dropzone = $this->dropzoneFactory->create()->loadByHash($hash);
                                    if ($dropzone->getId()) {
                                        $file = $dropzone->toFile($object);
                                        $dropzone->delete();
                                        $counter++;
                                    }
                                    if ($counter >= $maxFiles) break;
                                }
                            }
                            $value = [];

                            $select       = $this->getConnection()->select()
                                ->from($this->getTable('webforms_files'))
                                ->where('result_id = ?', $object->getId())
                                ->where('field_id = ?', $field_id);
                            $result_value = $this->getConnection()->fetchAll($select);
                            foreach ($result_value as $item) {
                                $value[] = $item['name'];
                            }
                        }
                    }



                    $select = $this->getConnection()->select()
                        ->from($this->getTable('webforms_results_values'))
                        ->where('result_id = ?', $object->getId())
                        ->where('field_id = ?', $field_id);

                    $result_value = $this->getConnection()->fetchAll($select);

                    if (is_array($value)) {
                        $value = implode("\n", $value);
                    }

                    if (!empty($result_value[0])) {

                        $this->getConnection()->update($this->getTable('webforms_results_values'), [
                            "value" => $value
                        ],
                            "id = " . $result_value[0]['id']
                        );

                    } else {
                        $this->getConnection()->insert($this->getTable('webforms_results_values'), [
                            "result_id" => $object->getId(),
                            "field_id" => $field_id,
                            "value" => $value
                        ]);
                    }

                    // update object
                    $object->setData('field_' . $field_id, $value);
                }
            }
        }

        $this->_eventManager->dispatch('webforms_result_save', ['result' => $object]);

        return parent::_afterSave($object);
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $webform = $this->_formFactory->create()->load($object->getData('webform_id'));

        $select = $this->getConnection()->select()
            ->from($this->getTable('webforms_results_values'))
            ->where('result_id = ?', $object->getId());
        $values = $this->getConnection()->fetchAll($select);

        foreach ($values as $val) {
            $object->setData('field_' . $val['field_id'], $val['value']);
            $object->setData('key_' . $val['field_id'], $val['key']);
        }

        $object->setData('ip', long2ip($object->getCustomerIp()));

        $this->_eventManager->dispatch('webforms_result_load', ['webform' => $webform, 'result' => $object]);

        return parent::_afterLoad($object);
    }

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        //delete values
        $this->getConnection()->delete($this->getTable('webforms_results_values'),
            'result_id = ' . $object->getId()
        );

        //clear messages
        $messages = $this->_messageFactoryCollection->create()->addFilter('result_id', $object->getId());
        foreach ($messages as $message) $message->delete();

        //delete files
        $files = $this->_fileCollectionFactory->create()->addFilter('result_id', $object->getId());
        /** @var \VladimirPopov\WebForms\Model\File $file */
        foreach ($files as $file) {
            $file->delete();
        }

        $this->_eventManager->dispatch('webforms_result_delete', ['result' => $object]);

        return parent::_beforeDelete($object);
    }

    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") $this->rrmdir($dir . "/" . $object); else unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function getSummaryRatings($webform_id, $store_id)
    {
        $adapter = $this->getConnection();

        $sumColumn = new \Zend_Db_Expr("SUM(results_values.value)");
        $countColumn = new \Zend_Db_Expr("COUNT(*)");

        $select = $adapter->select()
            ->from(['results_values' => $this->getTable('webforms_results_values')],
                [
                    'sum' => $sumColumn,
                    'count' => $countColumn,
                    'field_id'
                ])
            ->join(['fields' => $this->getTable('webforms_fields')],
                'results_values.field_id = fields.id',
                [])
            ->join(['results' => $this->getTable('webforms_results')],
                'results_values.result_id = results.id',
                [])
            ->where('fields.type = "stars"')
            ->where('results.webform_id = ' . $webform_id)
            ->where('results.store_id = ' . $store_id)
            ->where('results.approved = 1')
            ->group('results_values.field_id');
        return $adapter->fetchAll($select);
    }
}
