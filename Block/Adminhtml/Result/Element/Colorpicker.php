<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Result\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Colorpicker extends AbstractElement
{
    public function getElementHtml()
    {
        $this->addClass('input-text admin__control-text');
        $input = parent::getElementHtml();

        return $input . $this->_getScript();
    }

    private function _getScript()
    {
        return '<script type="text/javascript">
            require(["jquery","VladimirPopov_WebForms/js/colpick"], function ($) {
                $(document).ready(function (e) {
                    var el = $("#' . $this->getHtmlId() . '");
                    el.css("background-color","#' . $this->getData('value') . '");
                    el.colpick({
                        layout:"hex",
                        submit:0,
                        color: "#' . $this->getData('value') . '",
                        onChange:function(hsb,hex,rgb,el,bySetColor) {
                            $(el).css("background-color","#"+hex);
                            if(!bySetColor) $(el).val(hex);
                        }
                    }).keyup(function(){
                        $(this).colpickSetColor(this.value);
                    });
                });
            });
        </script>';
    }

}
