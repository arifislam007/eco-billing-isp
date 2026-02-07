<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    public string $baseURL = 'http://localhost:8080/';
    public string $indexPage = '';
    public string $uriProtocol = 'REQUEST_URI';
    public array $allowedNulls = [];
    public string $defaultLocale = 'en';
    public bool $negotiateLocale = false;
    public array $supportedLocales = ['en'];
    public string $appTimezone = 'Asia/Dhaka';
    public string $charset = 'UTF-8';
    public bool $forceGlobalSecureRequests = false;
    public string $sessionDriver = 'CodeIgniter\Session\Handlers\DatabaseHandler';
    public string $sessionCookieName = 'ci_session';
    public int $sessionExpiration = 7200;
    public string $sessionSavePath = WRITEPATH . 'session';
    public bool $sessionRegenerateDestroy = false;
    public string $cookiePrefix = '';
    public string $cookieDomain = '';
    public string $cookiePath = '/';
    public bool $cookieSecure = false;
    public bool $cookieHTTPOnly = true;
    public string $cookieSameSite = 'Lax';
    public array $proxyIPs = [];
    public string $CSRFTokenName = 'csrf_token';
    public string $CSRFCookieName = 'csrf_cookie';
    public string $CSRFHeaderName = 'X-CSRF-TOKEN';
    public bool $CSRFRegenerate = true;
    public bool $CSRFRedirect = true;
    public bool $CSPEnabled = false;
}
