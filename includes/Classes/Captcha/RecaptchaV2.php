<?php
namespace ProjectSend\Classes\Captcha;

use ProjectSend\Classes\Abstracts\CaptchaAbstract;

final class RecaptchaV2 extends CaptchaAbstract
{
    protected $method_name;
    protected $site_key;
    protected $secret_key;

    public function __construct()
    {
        parent::__construct('Recaptcha V2');
    }

    public function check()
    {
        
    }
}
