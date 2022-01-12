<?php

namespace VladimirPopov\WebForms\Ui\Component\Result\Listing;

class ExportButton extends \Magento\Ui\Component\ExportButton
{
    public function prepare()
    {
        $context = $this->getContext();
        $config = $this->getData('config');
        if (isset($config['options'])) {
            $options = [];
            foreach ($config['options'] as $option) {
                $additionalParams = $this->getAdditionalParams($config, $context);
                $option['url'] = $this->urlBuilder->getUrl($option['url'], $additionalParams);
                $options[] = $option;
            }
            $config['options'] = $options;
            $this->setData('config', $config);
        }
        parent::prepare();
    }

    protected function getAdditionalParams($config, $context)
    {
        $additionalParams = [];
        if (isset($config['additionalParams'])) {
            foreach ($config['additionalParams'] as $paramName => $paramValue) {
                if ('*' == $paramValue) {
                    $paramValue = $context->getRequestParam($paramName);
                }
                $additionalParams[$paramName] = $paramValue;
            }
        }
        return $additionalParams;
    }
}
