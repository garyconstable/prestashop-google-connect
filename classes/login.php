<?php

require_once __DIR__ . '/connect_customer.php';
require_once __DIR__ . '/../vendor/autoload.php';

require_once rtrim($_SERVER['DOCUMENT_ROOT'], '/') .'/config/config.inc.php';
require_once rtrim($_SERVER['DOCUMENT_ROOT'], '/') .'/init.php';


class google_connect_login extends connect_customer
{
    protected $client_id;
    protected $client_secret;
    protected $redirect_url;
    protected $client;
    protected $service;
    protected $authUrl;
    
    
    
    /**
     * class constructor - set vars
     * --
     */
    public function __construct()
    {
        $this->client_id = Configuration::get('GOOGLE_CONNECT_CLIENT_ID');
        $this->client_secret = Configuration::get('GOOGLE_CONNECT_CLIENT_SECRET');
        $this->redirect_url = Configuration::get('GOOGLE_CONNECT_CALLBACK_URL');
    }
    
    
    
    /**
     * Google login method
     * --
     */
    public function flow()
    {
        $client = new Google_Client();
        $client->setClientId($this->client_id);
        $client->setClientSecret($this->client_secret);
        $client->setRedirectUri($this->redirect_url);
        $client->addScope("email");
        $client->addScope("profile");
        
        $this->context = Context::getContext();
        $this->client = $client;
        $this->service = new Google_Service_Oauth2($this->client);
        
        if( Tools::getValue('code') ){
            
            $this->client->authenticate($_GET['code']);
            $this->context->cookie->__set('googleconnect_access_token', serialize($this->client->getAccessToken()) );
            Tools::redirect(filter_var($this->redirect_url, FILTER_SANITIZE_URL));
            
        }else{
            
            if( $this->context->cookie->__isset('googleconnect_access_token') ){
                
                $a_token = unserialize($this->context->cookie->__get('googleconnect_access_token'));
                $this->client->setAccessToken($a_token['access_token']);
                
                if( $this->client->isAccessTokenExpired() ){
                    $this->context->cookie->__unset('googleconnect_access_token');
                    $this->loginToStore();
                }else{
                    $this->setAuthUrl();
                }
                
            } else {
                $this->setAuthUrl();
            }
            
        }
        
    }
    
    
    
    /**
     * set the auth url and redirect to
     * --
     */
    public function setAuthUrl(){
        $this->authUrl = $this->client->createAuthUrl();
        $this->redirectAuth();
    }
    
    
    
    /**
     * redirect to google auth page
     * --
     */
    public function redirectAuth(){
        Tools::redirect($this->authUrl);
    }
}

$o_login = new google_connect_login();
$o_login->flow();