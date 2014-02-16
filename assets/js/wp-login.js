var a0_wp_login = (function($){
    
    var login_click = function(){
        $('#wp-login-form-wrapper').fadeIn();
        return false;
    }
    
    var add_login_method_btn = function(){
        var btn = $('<span/>').addClass('a0-zocial a0-block a0-wp-login').text(wpa0.wp_btn);
        $(btn).click(login_click);
        $('#a0-widget .a0-iconlist').append(btn);
    };
    
    return {
        initialize: function(){
            add_login_method_btn();
            setTimeout(function(){
                $('#a0-widget #a0-onestep').attr('style', '');
                $('#a0-widget form, #a0-widget .a0-iconlist').attr('style', 'margin-top: 0!important;');
            }, 100);
            
        }
    };
})(jQuery);