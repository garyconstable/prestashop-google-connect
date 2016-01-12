{if !$logged }
{literal}
<script>
    $(function(){
        var img = '<img src="/modules/googleconnect/views/img/sign-in-with-google.png" alt="Sign in with google." />';
        var html = '<div class="header_user_info"><a href="" id="google_connect_login">'+img+'</a></div>';
        $('header .nav nav .header_user_info').first().before(html);
        console.log('wdw')
    });
</script>
{/literal}
{/if}