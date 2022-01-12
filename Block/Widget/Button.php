<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Math\Random;

class Button extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /** @var Random */
    protected $random;

    protected $containerId;

    public function __construct(
        Template\Context $context,
        Random $random,
        array $data = []
    )
    {
        $this->random = $random;
        parent::__construct($context, $data);
    }

    public function getContainerId()
    {
        if (!$this->containerId) {
            $this->containerId = $this->random->getRandomString(10);
        }
        return $this->containerId;
    }
}
