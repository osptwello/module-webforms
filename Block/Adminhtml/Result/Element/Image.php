<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Result\Element;

class Image extends \VladimirPopov\WebForms\Block\Adminhtml\Result\Element\File
{
    protected function _getPreviewHtml()
    {
        $html = '';
        if ($this->getData('result_id')) {
            $result = $this->_resultFactory->create()->load($this->getData('result_id'));
            $field_id = $this->getData('field_id');
            $files = $this->fileCollectionFactory->create()
                ->addFilter('result_id', $result->getId())
                ->addFilter('field_id', $field_id);
            /** @var \VladimirPopov\WebForms\Model\File $file */
            $width = $this->_scopeConfig->getValue('webforms/images/grid_thumbnail_width');
            $height = $this->_scopeConfig->getValue('webforms/images/grid_thumbnail_height');

            if (count($files)) {
                $html .= '<div class="webforms-file-pool">';
                $html .= $this->_getSelectAllHtml();
                foreach ($files as $file) {
                    $html .= '<div class="webforms-file-cell">';

                    if (file_exists($file->getFullPath())) {
                        $nameStart = '<div class="webforms-file-link-name">' . mb_substr($file->getName(), 0, mb_strlen($file->getName()) - 7) . '</div>';
                        $nameEnd = '<div class="webforms-file-link-name-end">' . mb_substr($file->getName(), -7) . '</div>';

                        $thumbnail = $file->getThumbnail(100);
                        if ($thumbnail) {
                            $html .= '<a class="grid-button-action webforms-file-link" href="' . $file->getDownloadLink(true) . '">
                            <figure>
                                <p><img src="' . $file->getThumbnail($width, $height) . '"/></p>
                                <figcaption>' . $file->getName() . ' <span>[' . $file->getSizeText() . ']</span></figcaption>
                            </figure>
                        </a>';
                        } else {
                            $html .= '<nobr><a class="grid-button-action webforms-file-link" href="' . $file->getDownloadLink(true) . '">' . $nameStart . $nameEnd . ' <small>[' . $file->getSizeText() . ']</small></a></nobr>';
                        }
                    }
                    $html .= $this->_getDeleteCheckboxHtml($file);

                    $html .= '</div>';

                }
                $html .= '</div>';
            }

        }
        return $html;

    }

}
