<?php

namespace Syno\Storm\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class PrivacyConsentCookie
{
    private const COOKIE_NAME = 'privacy_consent_accepted_v';
    private string $version;
    
    public function __construct(string $version)
    {
        $this->version = $version;
    }
    
    public function setCookie(Response $response): Response
    {
        $cookie = Cookie::create($this->getCookieName())
            ->withValue(true)
            ->withDomain($_SERVER['HTTP_HOST']);
        
        $response->headers->setCookie($cookie);
        
        return $response;
    }
    
    public function isCookieSet(Request $request): bool
    {
        $cookie = $request->cookies->get($this->getCookieName());
        
        return !is_null($cookie);
    }
    
    private function getCookieName(): string
    {
        return self::COOKIE_NAME . $this->version;
    }
}
