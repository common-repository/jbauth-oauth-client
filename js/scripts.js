jQuery(document).ready(function ($) {
    var oldUrl = $('.jbauth-oauth-login-pop a').attr("href");
    if (oldUrl != undefined) {
        var newUrl = oldUrl.replace("state=oiEWJOD82938ojdKK", "state=" + menuitem.nonce);
    }
    $('.jbauth-oauth-login-pop a').attr("href", newUrl);
    if (menuitem.logged == 'on') {
        $('.jbauth-oauth-login-pop a').html(menuitem.text);
        $('.jbauth-oauth-login-pop a').attr("href", menuitem.lgurl);
    }
});