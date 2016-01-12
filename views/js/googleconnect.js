/**
 * 2007-2016 PrestaShop
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
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

var googleconnect = (function() {
    
    var initStr = '---> Google connect';
    
    function getInitMessage() {
        return initStr;
    }
    
    return {
        init: function()
        {    
            console.log( getInitMessage() );
            
            $(document).on('click', '#google_connect_login', function(e){
                console.log('---> Clicked google login');
                window.open("/modules/googleconnect/classes/login.php", "Google connect", "width=450, height=700");
                e.preventDefault();
            });
        },
    }; 
    
})();

googleconnect.init();
