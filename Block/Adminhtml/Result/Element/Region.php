<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Result\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Directory\Helper\Data;
use Magento\Framework\Escaper;
use function json_encode;
use function sprintf;

class Region extends AbstractElement
{
    protected $helper;

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        Data $helper,
        $data = []
    )
    {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->helper = $helper;
    }

    public function getElementHtml()
    {
        $regionInput = sprintf('<input id="%s" name="%s" %s style="display: none" value="%s"/>',
            $this->getHtmlId() . 'region',
            $this->getName() . '[region]',
            $this->serialize($this->getHtmlAttributes()),
            $this->getData('region')
        );

        $regionList = sprintf('<select id="%s" name="%s" %s style="display:none;"><option value="">%s</option></select>',
            $this->getHtmlId() . 'region_id',
            $this->getName() . '[region_id]',
            $this->serialize($this->getHtmlAttributes()),
            __('Please select a region, state or province.')
        );

        return $regionInput . $regionList . $this->_getScript();
    }

    private function _getScript()
    {
        $countryFieldId                  = '#' . $this->getData('country_field_id');
        $config['optionalRegionAllowed'] = true;
        $config['regionListId']          = '#' . $this->getHtmlId() . 'region_id';
        $config['regionInputId']         = '#' . $this->getHtmlId() . 'region';
        $config['regionJson']            = $this->helper->getRegionData();
        $config['isRegionRequired']      = (bool)$this->getData('required');
        $config['currentRegion']         = $this->getData('region_id');

        return sprintf('<script type="text/x-magento-init">{"%s": {"webformsRegion": %s}}</script>',
            $countryFieldId,
            json_encode($config)
        );
    }

}
