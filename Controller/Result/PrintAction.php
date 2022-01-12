<?php
namespace VladimirPopov\WebForms\Controller\Result;

use Magento\Framework\App\Filesystem\DirectoryList;
use VladimirPopov\WebForms\Controller\ResultAction;

/**
 * Class PrintAction
 * @package VladimirPopov\WebForms\Controller\Result
 */
class PrintAction extends ResultAction
{
    public function execute()
    {
        $this->_init();
        $webform = $this->_result->getWebform();
        if (!in_array('print', $webform->getCustomerResultPermissions())) $this->_redirect('webforms/customer/account', ['webform_id' => $webform->getId()]);

        if (class_exists('\Mpdf\Mpdf')) {
            if (@class_exists('\Mpdf\Mpdf')) {
                $mpdf = @new \Mpdf\Mpdf(['mode' => 'utf-8', 'tempDir' => $this->_dir->getPath('tmp')]);
                @$mpdf->WriteHTML($this->_result->toPrintableHtml());
                return $this->_fileFactory->create(
                    $this->_result->getPdfFilename(),
                    @$mpdf->Output('', 'S'),
                    DirectoryList::TMP
                );
            }
        } else {
            $this->messageManager->addError(__('Printing is disabled.'));
            $this->_redirect('webforms/customer/account', array('webform_id' => $webform->getId()));
        }

        parent::execute();
    }
}