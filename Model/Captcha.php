<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Model;

use Magento\Framework\Locale\Resolver;
use Magento\Framework\Math\Random;
use Magento\Framework\Registry;
use function substr;

class Captcha
{
    protected $_publicKey;

    protected $_privateKey;

    protected $_theme = 'standard';

    /** @var Random */
    protected $_random;

    /** @var Registry */
    protected $_registry;

    /** @var Resolver * */
    protected $_localeResolver;

    protected $_version;

    public function __construct(
        Registry $registry,
        Random $random,
        Resolver $localeResolver
    )
    {

        $this->_random         = $random;
        $this->_registry       = $registry;
        $this->_localeResolver = $localeResolver;
    }

    public function setPublicKey($value)
    {
        $this->_publicKey = $value;
        return $this;
    }

    public function setPrivateKey($value)
    {
        $this->_privateKey = $value;
        return $this;
    }

    public function setTheme($value)
    {
        $this->_theme = $value;
        return $this;
    }

    public function setVersion($version)
    {
        $this->_version = $version;
        return $this;
    }

    public function getVersion()
    {
        return $this->_version;
    }

    function getCurlData($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
        $curlData = curl_exec($curl);
        curl_close($curl);
        return $curlData;
    }

    public function verify($response)
    {

        //Get user ip
        $ip = $_SERVER['REMOTE_ADDR'];

        //Build up the url
        $url      = 'https://www.google.com/recaptcha/api/siteverify';
        $full_url = $url . '?secret=' . $this->_privateKey . '&response=' . $response . '&remoteip=' . $ip;

        //Get the response back decode the json
        $data = json_decode($this->getCurlData($full_url));

        //Return true or false, based on users input
        if (isset($data->success) && $data->success == true) {
            return true;
        }

        return false;
    }

    public function getHtml()
    {
        if ($this->_version == '3')
            return $this->v3Html();
        return $this->v2Html();
    }

    public function v2Html()
    {
        $languageCode = substr($this->_localeResolver->getDefaultLocale(), 0, 2);

        $output = '';
        $rand   = $this->_random->getRandomString(6);
        if (!$this->_registry->registry('webforms_recaptcha_gethtml')) {
            $output .= '<script>var reWidgets =[];</script>';
        }

        $output .= <<<HTML
<script>
    function recaptchaCallback{$rand}(response){
        $('re{$rand}').value = response;
        Validation.validate($('re{$rand}'));
        for(var i=0; i<reWidgets.length;i++){
            if(reWidgets[i].id != '{$rand}')
                grecaptcha.reset(reWidgets[i].inst);
        }
    }
    reWidgets.push({id:'{$rand}',inst : '',callback: recaptchaCallback{$rand}});

</script>
<div id="g-recaptcha{$rand}" class="g-recaptcha"></div>
<input type="hidden" id="re{$rand}" name="recapcha{$rand}" class="required-entry"/>
HTML;

        if (!$this->_registry->registry('webforms_recaptcha_gethtml')) {
            $output .= <<<HTML
<script>
    function recaptchaOnload(){
        for(var i=0; i<reWidgets.length;i++){
            reWidgets[i].inst = grecaptcha.render('g-recaptcha'+reWidgets[i].id,{
                'sitekey' : '{$this->_publicKey}',
                'theme' : '{$this->_theme}',
                'callback': reWidgets[i].callback
            });
        }
    }
</script>
<script src="https://www.google.com/recaptcha/api.js?onload=recaptchaOnload&render=explicit&hl={$languageCode}" async></script>
HTML;
        }
        if (!$this->_registry->registry('webforms_recaptcha_gethtml')) $this->_registry->register('webforms_recaptcha_gethtml', true);

        $output .= '<div class="validation-advice" id="advice-required-entry-re' . $rand . '" style="display:none">' . __('This is a required field.') . '</div>';

        return $output;
    }


    public function v3Html()
    {
        $languageCode = substr($this->_localeResolver->getDefaultLocale(), 0, 2);

        $output = <<<HTML
            <input type="hidden" name="g-recaptcha-response" class="required-entry"/>
HTML;
        if (!$this->_registry->registry('webforms_recaptcha_gethtml')) {
            $output .= <<<HTML
                <script src="https://www.google.com/recaptcha/api.js?onload=recaptchaOnload&render={$this->_publicKey}&hl={$languageCode}" async></script>
                <script>
                    function recaptchaOnload(){
                        grecaptcha.ready(function() {
                            function getCaptchaToken(){
                                grecaptcha.execute('{$this->_publicKey}', {action: 'webforms'}).then(function(token) {
                                   var rFields = document.querySelectorAll('[name="g-recaptcha-response"]');
                                   for(var i =0; i<rFields.length; i++){
                                       rFields[i].value = token;
                                   }
                                });
                            }
                            getCaptchaToken();
                            setInterval(getCaptchaToken, 60000);
                        })
                    }
                </script>
HTML;
        }
        if (!$this->_registry->registry('webforms_recaptcha_gethtml')) $this->_registry->register('webforms_recaptcha_gethtml', true);

        return $output;
    }
}
