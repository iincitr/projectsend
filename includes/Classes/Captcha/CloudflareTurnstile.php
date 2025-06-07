<?php
namespace ProjectSend\Classes\Captcha;

use ProjectSend\Classes\Abstracts\CaptchaAbstract;

final class CloudflareTurnstile extends CaptchaAbstract
{
    protected $method_name;
    protected $site_key;
    protected $secret_key;

    public function __construct()
    {
        parent::__construct('Cloudflare Turnstile');
    }

    public function check()
    {
        
    }
}
