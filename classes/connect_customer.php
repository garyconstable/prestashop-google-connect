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


class connect_customer
{    
    private $auth_user;
    protected $context;
    
    
    
    /**
     * login method - forward to either login or create account
     * --
     */
    public function loginToStore()
    {
        $this->auth_user = $this->service->userinfo->get();
        $i_customer_id = CustomerCore::customerExists($this->auth_user->email, true);
        if( $i_customer_id ){
            $this->login( $i_customer_id );
        }else{
            $this->createAccount();
        }
    }
    
    
    
    /**
     * Inflate customer via id and authenticate
     * --
     */
    public function login($i_id_customer = 0)
    {   
        $cookie = $this->context->cookie;
        $customer = new Customer();
        $customer->id = $i_id_customer;
        $cookie->id_customer = (int)$customer->id;
        $cookie->customer_lastname = $customer->lastname;
        $cookie->customer_firstname = $customer->firstname;
        $cookie->logged = 1;
        $cookie->passwd = $customer->passwd;
        $cookie->email = $customer->email;
        
        if (Configuration::get('PS_CART_FOLLOWING') AND (empty($cookie->id_cart) OR Cart::getNbProducts($cookie->id_cart) == 0)){
            $cookie->id_cart = (int)Cart::lastNoneOrderedCart((int)$customer->id);
        }
        if(version_compare(_PS_VERSION_, '1.5', '>')){
            Hook::exec('actionAuthentication');
        } else {
            Module::hookExec('authentication');
        }
        
        $this->redirectOpenerAccount();
    }
    
    
    
    /**
     * create a new user account
     * --
     */
    public function createAccount()
    {        
        //user
        $gender = 1;
        $id_default_group = (int)Configuration::get('PS_CUSTOMER_GROUP');		
        $firstname = pSQL($this->auth_user->givenName);
        $lastname = pSQL($this->auth_user->familyName);		
        $email = $this->auth_user->email;
        
        // generate passwd
        srand((double)microtime()*1000000);
        $passwd = Tools::substr(uniqid(rand()),0,12);
        $real_passwd = $passwd; 
        $passwd = md5(pSQL(_COOKIE_KEY_.$passwd)); 
        
        //dates
        $last_passwd_gen = date('Y-m-d H:i:s', strtotime('-'.Configuration::get('PS_PASSWD_TIME_FRONT').'minutes'));
        $secure_key = md5(uniqid(rand(), true));
        $active = 1;
        $date_add = date('Y-m-d H:i:s'); //'2011-04-04 18:29:15';
        $date_upd = $date_add;
        
        //gen sql
        $sql = 'insert into `'._DB_PREFIX_.'customer` SET 
        id_gender = '.$gender.', id_default_group = '.$id_default_group.',
        firstname = \''.$firstname.'\', lastname = \''.$lastname.'\',
        email = \''.$email.'\', passwd = \''.$passwd.'\',
        last_passwd_gen = \''.$last_passwd_gen.'\',
        secure_key = \''.$secure_key.'\', active = '.$active.',
        date_add = \''.$date_add.'\', date_upd = \''.$date_upd.'\', optin = 1 ';
        
        //make the insert and return the last id 
        Db::getInstance()->Execute($sql);
        $insert_id = Db::getInstance()->Insert_ID();
			    
        $sql = 'INSERT into `'._DB_PREFIX_.'customer_group` SET id_customer = '.$insert_id.', id_group = '.$id_default_group.' ';
        Db::getInstance()->Execute($sql);
				
        // auth customer
        $cookie = $this->context->cookie;
        $customer = new Customer();
        
        //atempt
        $authentication = $customer->getByEmail(trim($email), trim($real_passwd));
	                       
        if (!$authentication || !$customer->id) {
            
            $this->authenticationFailed();
            
        }else{
            
            $cookie->id_customer = (int)$customer->id;
            $cookie->customer_lastname = $customer->lastname;
            $cookie->customer_firstname = $customer->firstname;
            $cookie->logged = 1;
            $cookie->passwd = $customer->passwd;
            $cookie->email = $customer->email;

            if (Configuration::get('PS_CART_FOLLOWING') AND (empty($cookie->id_cart) OR Cart::getNbProducts($cookie->id_cart) == 0)){
                $cookie->id_cart = (int)Cart::lastNoneOrderedCart((int)$customer->id);
            }

            Hook::exec('actionAuthentication');
            
            //check if the wecome email exists
            if (Configuration::get('PS_CUSTOMER_CREATION_EMAIL')) 
            {
                Mail::Send(
                    $this->context->language->id,
                    'account',
                    Mail::l('Welcome!'),
                    array(
                        '{firstname}' => $customer->firstname,
                        '{lastname}' => $customer->lastname,
                        '{email}' => $customer->email,
                        '{passwd}' => trim($real_passwd)
                    ),
                    $customer->email,
                    $customer->firstname.' '.$customer->lastname
                );
            }
            $this->login($customer->id);   
        }
    }
    
    
    
    /**
     * echo script to refresh popup opener
     * --
     */
    private function redirectOpenerAccount(){
        echo '<script>
        window.opener.location.href = "/my-account";
        window.opener.focus();
        window.close();
        </script>';
        die();
    }
    
    
    
    /**
     * Auth failed - show messgae
     * --
     */
    private function authenticationFailed(){
        echo '<h3>Sorry</h3><p>We could not log you in, please contact a member of customer support for more information.</p>';
        die();
    }
}