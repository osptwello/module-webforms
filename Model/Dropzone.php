<?php

namespace VladimirPopov\WebForms\Model;

use Magento\Framework\DataObject\IdentityInterface;

/**
 * @method \VladimirPopov\WebForms\Model\ResourceModel\Dropzone getResource()
 * @method \VladimirPopov\WebForms\Model\ResourceModel\Dropzone\Collection getCollection()
 */
class Dropzone extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'vladimirpopov_webforms_dropzone';
    protected $_cacheTag = 'vladimirpopov_webforms_dropzone';
    protected $_eventPrefix = 'vladimirpopov_webforms_dropzone';

    const UPLOAD_DIR = 'webforms/dropzone';

    protected $storeManager;

    protected $fileFactory;

    protected $uploaderFactory;

    protected $random;

    public function __construct(
        \Magento\Store\Model\StoreManager $storeManager,
        \VladimirPopov\WebForms\Model\FileFactory $fileFactory,
        \VladimirPopov\WebForms\Model\UploaderFactory $uploaderFactory,
        \Magento\Framework\Math\Random $random,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->storeManager = $storeManager;
        $this->fileFactory = $fileFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->random = $random;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

    }

    protected function _construct()
    {
        $this->_init('VladimirPopov\WebForms\Model\ResourceModel\Dropzone');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getUploadDir()
    {
        return $this->storeManager->getStore()->getBaseMediaDir() . '/' . self::UPLOAD_DIR;
    }

    public function getFullPath()
    {
        return $this->storeManager->getStore()->getBaseMediaDir() . '/' . $this->getPath();
    }

    public function upload($uploaded_files)
    {
        foreach ($uploaded_files as $file_id => $file) {

            $uploader = new \Magento\Framework\File\Uploader($file_id);
            $field_id = str_replace('file_', '', $file_id);
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);

            $tmp_name = $this->random->getRandomString(20);
            $hash = $this->random->getRandomString(40);
            $size = filesize($file['tmp_name']);
            $mime = \VladimirPopov\WebForms\Model\Uploader::getMimeType($file['tmp_name']);

            $success = $uploader->save($this->getUploadDir(), $tmp_name);

            if ($success) {
                // save new file
                $this->setData('name', $file['name'])
                    ->setData('field_id', $field_id)
                    ->setData('size', $size)
                    ->setData('mime_type', $mime)
                    ->setData('path', self::UPLOAD_DIR . '/' . $tmp_name)
                    ->setData('hash', $hash);
                $this->save();

                if ($this->getId()) {
                    return $hash;
                }
            }
        }

        return false;
    }

    public function toFile(\VladimirPopov\WebForms\Model\Result $result)
    {
        $tmp_name = $this->random->getRandomString(20);
        $link_hash = $this->random->getRandomString(40);

        /** @var \VladimirPopov\WebForms\Model\File $model */
        $model = $this->fileFactory->create();

        $uploader = $this->uploaderFactory->create()->setResult($result);

        $file_path = $uploader->getUploadDir() . '/' . $tmp_name;

        // save new file
        $model->setData('result_id', $result->getId())
            ->setData('field_id', $this->getFieldId())
            ->setData('name', $this->getName())
            ->setData('size', $this->getSize())
            ->setData('mime_type', $this->getMimeType())
            ->setData('path', $uploader->getPath() . '/' . $tmp_name)
            ->setData('link_hash', $link_hash);
        $model->save();

        if (!is_dir(dirname($file_path)))
            mkdir(dirname($file_path), 0755, true);

        copy($this->getFullPath(), $file_path);

        return $model;
    }

    public function cleanup()
    {
        $collection = $this->getCollection()->addFieldToFilter('created_time', array('lt' => date("Y-m-d H:i:s", strtotime('-1 hour'))));
        foreach ($collection as $item)
            $item->delete();
    }

    public function loadByHash($hash)
    {
        $collection = $this->getCollection()->addFilter('hash', $hash);
        return $collection->getFirstItem();
    }
}