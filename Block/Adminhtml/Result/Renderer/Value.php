<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Result\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManager;
use VladimirPopov\WebForms\Model\FieldFactory;
use function htmlentities;
use function json_decode;

class Value extends AbstractRenderer
{
    protected $_customerFactory;

    protected $_fieldFactory;

    protected $_storeManager;

    protected $regCollectionFactory;

    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        FieldFactory $fieldFactory,
        StoreManager $storeManager,
        CollectionFactory $regCollectionFactory,
        array $data = []
    )
    {

        parent::__construct($context, $data);
        $this->regCollectionFactory = $regCollectionFactory;
        $this->_customerFactory = $customerFactory;
        $this->_fieldFactory = $fieldFactory;
        $this->_storeManager = $storeManager;
    }

    public function render(DataObject $row)
    {
        $field_id = str_replace('field_', '', $this->getColumn()->getIndex());
        $field = $this->_fieldFactory->create()->load($field_id);
        $value = $row->getData($this->getColumn()->getIndex());
        $html = '';

        if ($field->getType() == 'stars') {
            $html = $this->getStarsBlock($row);
        }
        if ($field->getType() == 'textarea') {
            $html = $this->getTextareaBlock($row);
        }
        if ($field->getType() == 'wysiwyg') {
            $html = $this->getHtmlTextareaBlock($row);
        }
        if (strstr($field->getType(), 'date')) {
            $html = $field->formatDate($value);
        }
        if ($field->getType() == 'email') {
            if($value){
                $websiteId = false;
                try{$websiteId = $this->_storeManager->getStore($row->getStoreId())->getWebsite()->getId();}
                catch(LocalizedException $e){}
                $customer = $this->_customerFactory->create()->setData('website_id',$websiteId)->loadByEmail($value);
                $html = htmlspecialchars($value);
                if($customer->getId()){
                    $html.= " [<a href='" . $this->getCustomerUrl($customer->getId()) . "' target='_blank'>" . htmlspecialchars($customer->getName()) . "</a>]";
                }
            }
        }
        if($field->getType() == 'region'){
            if($value){
                $regionInfo = json_decode($value, true);
                if (isset($regionInfo['region']))
                    $html = htmlentities($regionInfo['region']);

                if (isset($regionInfo['region_id'])) {
                    $collection = $this->regCollectionFactory->create()->addFilter('main_table.region_id', $regionInfo['region_id']);
                    $region     = $collection->getFirstItem();
                    if ($region->getName())
                        $html = htmlentities($region->getName());
                }
            }
        }
        if($field->getType() == 'colorpicker'){
            $html = '<div class="colorpicker-grid-swatch" style="background-color: #'.$value.'"></div>'.$value;
        }

        $html_object = new DataObject(array('html' => $html));

        $this->_eventManager->dispatch('webforms_block_adminhtml_results_renderer_value_render', array('field' => $field, 'html_object' => $html_object, 'value' => $value));

        if ($html_object->getHtml())
            return $html_object->getHtml();

        return nl2br(htmlspecialchars($value));
    }

    public function getTextareaBlock(DataObject $row)
    {
        $field_id = str_replace('field_', '', $this->getColumn()->getIndex());
        $value = htmlspecialchars($row->getData($this->getColumn()->getIndex()));
        if (strlen($value) > 200 || mb_substr_count($value, "\n") > 11) {
            $div_id = 'x_' . $field_id . '_' . $row->getId();
            $onclick = "$('$div_id').style.display ='block'; this.style.display='none';  return false;";
            $pos = @strpos($value, "\n", 200);
            if ($pos > 300 || !$pos)
                $pos = @strpos($value, " ", 200);
            if ($pos > 300)
                $pos = 200;
            if (!$pos) $pos = 200;
            $html = '<div>' . nl2br(mb_substr($value, 0, $pos)) . '</div>';
            $html .= '<div id="' . $div_id . '" style="display:none">' . nl2br(mb_substr($value, $pos, strlen($value))) . '<br></div>';
            $html .= '<a onclick="' . $onclick . '" style="text-decoration:none;float:right">[' . __('Read more') . ']</a>';
            return $html;
        }
        return nl2br($value);
    }

    public function getHtmlTextareaBlock(DataObject $row)
    {
        $field_id = str_replace('field_', '', $this->getColumn()->getIndex());
        $value = $row->getData($this->getColumn()->getIndex());
        if (strlen(strip_tags($value)) > 200 || mb_substr_count($value, "\n") > 11) {
            $div_id = 'x_' . $field_id . '_' . $row->getId();
            $preview_div_id = 'preview_x_' . $field_id . '_' . $row->getId();
            $onclick = "$('{$preview_div_id}').hide(); $('$div_id').style.display='block'; this.style.display='none';  return false;";
            $html = '<div style="min-width:400px" id="' . $preview_div_id . '">' . $this->htmlCut($value, 200) . '</div>';
            $html .= '<div id="' . $div_id . '" style="display:none;min-width:400px">' . $value . '</div>';
            $html .= '<a onclick="' . $onclick . '" style="text-decoration:none;float:right">[' . __('Read more') . ']</a>';
            return $html;
        }
        return $value;
    }

    public function getStarsBlock(DataObject $row)
    {
        $field_id = str_replace('field_', '', $this->getColumn()->getIndex());
        $field = $this->_fieldFactory->create()->load($field_id);
        $value = (int)$row->getData($this->getColumn()->getIndex());
        $blockwidth = ($field->getStarsCount() * 16) . 'px';
        $width = round(100 * $value / $field->getStarsCount()) . '%';
        $html = "<div class='stars' style='width:$blockwidth'><ul class='stars-bar'><li class='stars-value' style='width:$width'></li></ul></div>";
        return $html;
    }

    public function getCustomerUrl($customerId)
    {

        return $this->getUrl('customer/index/edit', array('id' => $customerId, '_current' => false));
    }

    public function htmlCut($text, $max_length)
    {
        $tags = array();
        $result = "";

        $is_open = false;
        $grab_open = false;
        $is_close = false;
        $in_double_quotes = false;
        $in_single_quotes = false;
        $tag = "";

        $i = 0;
        $stripped = 0;

        $stripped_text = strip_tags($text);

        while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length) {
            $symbol = $text[$i];
            $result .= $symbol;

            switch ($symbol) {
                case '<':
                    $is_open = true;
                    $grab_open = true;
                    break;

                case '"':
                    if ($in_double_quotes)
                        $in_double_quotes = false;
                    else
                        $in_double_quotes = true;

                    break;

                case "'":
                    if ($in_single_quotes)
                        $in_single_quotes = false;
                    else
                        $in_single_quotes = true;

                    break;

                case '/':
                    if ($is_open && !$in_double_quotes && !$in_single_quotes) {
                        $is_close = true;
                        $is_open = false;
                        $grab_open = false;
                    }

                    break;

                case ' ':
                    if ($is_open)
                        $grab_open = false;
                    else
                        $stripped++;

                    break;

                case '>':
                    if ($is_open) {
                        $is_open = false;
                        $grab_open = false;
                        array_push($tags, $tag);
                        $tag = "";
                    } else if ($is_close) {
                        $is_close = false;
                        array_pop($tags);
                        $tag = "";
                    }

                    break;

                default:
                    if ($grab_open || $is_close)
                        $tag .= $symbol;

                    if (!$is_open && !$is_close)
                        $stripped++;
            }

            $i++;
        }

        while ($tags)
            $result .= "</" . array_pop($tags) . ">";

        return $result;
    }
}
