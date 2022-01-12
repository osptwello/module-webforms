<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Result;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use VladimirPopov\WebForms\Model\Result;
use VladimirPopov\WebForms\Model\ResultFactory as WebformsResultFactory;

/**
 * Class Pdf
 * @package VladimirPopov\WebForms\Controller\Result
 */
class Pdf extends Action
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var WebformsResultFactory
     */
    protected $resultFactory;

    /**
     * @var DirectoryList
     */
    protected $_dir;

    /**
     * Pdf constructor.
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param Session $session
     * @param WebformsResultFactory $resultFactory
     * @param DirectoryList $_dir
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Session $session,
        WebformsResultFactory $resultFactory,
        DirectoryList $_dir
    )
    {
        $this->_fileFactory  = $fileFactory;
        $this->session       = $session;
        $this->resultFactory = $resultFactory;
        $this->_dir          = $_dir;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Forward|ResultInterface
     * @throws FileSystemException
     * @throws \Mpdf\MpdfException
     */
    public function execute()
    {
        // init model and delete
        $hash = $this->getRequest()->getParam('hash');
        if ($hash) {
            $id = $this->session->getData($hash);
            if ($id) {
                /** @var Result $model */
                $model = $this->resultFactory->create()->load($id);
                if (@class_exists('\Mpdf\Mpdf')) {
                    $mpdf = @new \Mpdf\Mpdf(['mode' => 'utf-8', 'tempDir' => $this->_dir->getPath('tmp')]);
                    @$mpdf->WriteHTML($model->toPrintableHtml());
                    return $this->_fileFactory->create(
                        $model->getPdfFilename(),
                        @$mpdf->Output('', 'S'),
                        DirectoryList::TMP
                    );
                }
            }
        }
        /** @var Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('noroute');
        return $resultForward;
    }
}
