<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Model;

class Feed extends \Magento\AdminNotification\Model\Feed
{
    const WEBFORMS_FEED_URL = 'mageme.com/feeds/webforms/m2.rss';

    public function getFeedUrl()
    {
        $httpPath = $this->_backendConfig->isSetFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
        if ($this->_feedUrl === null) {
            $this->_feedUrl = $httpPath . self::WEBFORMS_FEED_URL;
        }
        return $this->_feedUrl;
    }

    public function observe()
    {
        $this->checkUpdate();
    }

    /**
     * @inheritdoc
     */
    public function getLastUpdate()
    {
        return $this->_cacheManager->load('webforms_notifications_lastcheck');
    }

    /**
     * @inheritdoc
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), 'webforms_notifications_lastcheck');

        return $this;
    }
}
