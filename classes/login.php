<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/modules/googleconnect/classes/connect_customer.php';
require_once rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/modules/googleconnect/vendor/autoload.php';
require_once rtrim($_SERVER['DOCUMENT_ROOT'], '/') .'/config/config.inc.php';
require_once rtrim($_SERVER['DOCUMENT_ROOT'], '/') .'/init.php';

class GoogleConnectLogin extends ConnectCustomer
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
        
        if (Tools::getValue('code')) {
            
            $this->client->authenticate(Tools::getValue('code'));
            $this->context->cookie->__set('googleconnect_access_token', serialize($this->client->getAccessToken()));
            Tools::redirect(filter_var($this->redirect_url, FILTER_SANITIZE_URL));
            
        } else {
            
            if ($this->context->cookie->__isset('googleconnect_access_token')) {
                
                $a_token = unserialize($this->context->cookie->__get('googleconnect_access_token'));
                $this->client->setAccessToken($a_token);
                
                if (!$this->client->isAccessTokenExpired()) {
                    
                    $this->context->cookie->__unset('googleconnect_access_token');
                    $this->loginToStore();
                    
                } else {
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
    public function setAuthUrl()
    {
        $this->authUrl = $this->client->createAuthUrl();
        $this->redirectAuth();
    }
    
    
    
    /**
     * redirect to google auth page
     * --
     */
    public function redirectAuth()
    {
        Tools::redirect($this->authUrl);
    }
}

$o_login = new GoogleConnectLogin();
$o_login->flow();
