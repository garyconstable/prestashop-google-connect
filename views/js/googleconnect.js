

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
