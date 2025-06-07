<?php

function upgrade_2025042301()
{
    $method = (recaptcha2_is_enabled()) ? 'recaptcha2' : null;
    add_option_if_not_exists('captcha_method', $method);
    add_option_if_not_exists('cloudflare_turnstile_site_key', null);
    add_option_if_not_exists('cloudflare_turnstile_secret_key', null);
}
