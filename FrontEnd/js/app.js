$(document).ready(function () {
    $('#page-preloader').hide();
//    setTimeout(function () {
//        $('#page-preloader').hide();
//    }, 10);
    

    $(window).bind('popstate', function () {
        console.log('popstate ' + location.pathname);
        AjaxFromContent(location.pathname);
    });

    $(document).on("click", "a[href^='/']", function (event) {
        let url = $(this).attr('href');
        //console.log('link ' + url);
        if (url !== window.location) {
            AjaxFromContent(url);
            window.history.pushState({path: url}, '', url);
            event.preventDefault();
        }
    });
});
function AjaxFromContent(url) {
    $('#page-preloader').show();
    $.ajax({
        url: url + "?AJAX=TRUE",
        success: function (data) {
            if (IsJsonString(data)) {
                ar = $.parseJSON(data);
                $('#content').html(ar.content);
                $('title').text(ar.title);
                $('#ErrorMsg').html(ar.ErrorMessage);
            } else {
                $('#content').html(data);
            }
//            $('#content').html(data);   
            setTimeout(function () {
                $('#page-preloader').fadeOut("slow");
            }, 100);
        }
    });
}
function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}
function addscript(src) {
    var script = document.createElement('script');
    script.src = src;
    document.getElementsByTagName('head')[0].appendChild(script);
}

