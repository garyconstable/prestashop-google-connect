<?php

/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/


if (!defined('_PS_VERSION_'))
  exit;

/** 
 * timelog: 6hrs
 */

class GoogleConnect extends Module
{
    /**
     * Module Constructor
     * --
     */
    public function __construct()
    {
        $this->name = 'googleconnect';
        $this->tab = 'social_networks';
        $this->version = '1.0.0';
        $this->author = 'Gary Constable';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
        $this->bootstrap = true;
        $this->module_key = 'cbb37c9bd98b74efc65852f31e6bb2e5';
        
        parent::__construct();
 
        $this->displayName = $this->l('Google Connect');
        $this->description = $this->l('Login to prestashop one click with google.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }
    
    
    
    /**
     * Module install method
     * --
     * @return boolean
     */
    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);
 
        if (!parent::install() ||
            !$this->registerHook('header') ||
                !Configuration::updateValue('GOOGLE_CONNECT_CLIENT_ID', '') ||
                    !Configuration::updateValue('GOOGLE_CONNECT_CLIENT_SECRET', '') ||
                        !Configuration::updateValue('GOOGLE_CONNECT_CALLBACK_URL', '') 
        ){
            return false;
        }
        return true;
    }
    
    
    
    /**
     * Module uninstall method
     * --
     * @return boolean
     */
    public function uninstall()
    {
        if (!parent::uninstall() ||
                !Configuration::deleteByName('GOOGLE_CONNECT_CLIENT_ID') ||
                    !Configuration::deleteByName('GOOGLE_CONNECT_CLIENT_SECRET') ||
                        !Configuration::deleteByName('GOOGLE_CONNECT_CALLBACK_URL')
        ){
            return false;
        }
        return true;
    }
    
    
    /**
     * Module conig page - get content
     * --
     * @return type
     */
    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name))
        {
            //error counter
            $i_errors = 0;
            
            //array of settings
            $a_validate = array(
                'GOOGLE_CONNECT_CLIENT_ID' => array( 
                    $this->l('Google client ID'), 
                    (string)Tools::getValue('GOOGLE_CONNECT_CLIENT_ID') 
                ),
                'GOOGLE_CONNECT_CLIENT_SECRET' => array(
                    $this->l('Google client secret'),
                    (string)Tools::getValue('GOOGLE_CONNECT_CLIENT_SECRET')
                ),
                'GOOGLE_CONNECT_CALLBACK_URL' => array(
                    $this->l('Google callback url'),
                    (string)Tools::getValue('GOOGLE_CONNECT_CALLBACK_URL')
                )
            );
            
            //vaidate post vars / settings
            foreach($a_validate as $key => $value)
            {    
                if(!$value[1] || empty($value[1]) || !Validate::isGenericName($value[1])){
                    $output .= $this->displayError($this->l('Invalid Configuration value') . ' : ' . $value[0]);
                    $i_errors++;
                }else{
                    Configuration::updateValue($key, $value[1]);
                }
            }
            
            //display success message
            if($i_errors === 0){
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output.$this->displayForm();
    }
    
    
    
    /**
     * Display the config page form
     * --
     * @return type
     */
    public function displayForm()
    {
        //fields for the form
        $fields_form = array();
        
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Google client ID'),
                    'name' => 'GOOGLE_CONNECT_CLIENT_ID',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Google client secret'),
                    'name' => 'GOOGLE_CONNECT_CLIENT_SECRET',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Google callback url'),
                    'name' => 'GOOGLE_CONNECT_CALLBACK_URL',
                    'required' => true
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button'
            )
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $helper->fields_value['GOOGLE_CONNECT_CLIENT_ID'] = Configuration::get('GOOGLE_CONNECT_CLIENT_ID');
        $helper->fields_value['GOOGLE_CONNECT_CLIENT_SECRET'] = Configuration::get('GOOGLE_CONNECT_CLIENT_SECRET');
        $helper->fields_value['GOOGLE_CONNECT_CALLBACK_URL'] = Configuration::get('GOOGLE_CONNECT_CALLBACK_URL');

        return $helper->generateForm($fields_form);
    }
    
    
    
    /**
     * load the front end assets
     * ---
     */
    public function loadAssets()
    {
        require_once rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/modules/googleconnect/vendor/autoload.php';
        $this->context->controller->addCSS(($this->_path).'views/css/googleconnect.css', 'all');
        $this->context->controller->addJS(($this->_path).'views/js/googleconnect.js');
    }
    
    
    
    /**
     * Header Hook
     * --
     * @param type $params
     * @return type
     */
    public function hookHeader($params)
    {   
        //load front end assets
        $this->loadAssets();
    	return $this->display( __FILE__, 'views/templates/front/head.tpl' );
    }
}