/**
 * Created by AxelDreamer on 20.04.14.
 */
var mousewheelevt=(/Firefox/i.test(navigator.userAgent))? "DOMMouseScroll" : "mousewheel" //FF doesn't recognize mousewheel as of FF3.x
$(function(){
    var content = $('#content'),
        contentHtml = content.find('.scrolling'),
        scrollPx = 50,
        selectPart = $('select#part'),
        loginBtn = $('#login_button');
    content.on(mousewheelevt, function(e){
        var margin = parseInt(contentHtml.css('margin-top').replace('px','')),
            delta = (/Firefox/i.test(navigator.userAgent))? e.originalEvent.detail * -1 : e.originalEvent.wheelDelta /120;
        if(delta > 0) {
            if(margin < 0){
                margin += scrollPx;
                e.preventDefault();
            }
        }
        else if(contentHtml.outerHeight() > margin * -1 + 500){
            margin -= scrollPx;
            e.preventDefault();
        }
        contentHtml.css('margin-top', margin);
    });
    if((/Android/i.test(navigator.userAgent))){
        content.css('overflow-y', 'scroll');
    }
    if(selectPart.length > 0){
        selectPart.on('change', function(){
            var loc = location.pathname;
            loc = loc.replace(/\/part\/\d*/i, '');
            location.replace(loc + '/part/' + $(this).val());
        });
    }
    if(loginBtn.length > 0){
        loginBtn.on('click', function(e){
            e.preventDefault();
            var params = "menubar=no,location=no,resizable=no,scrollbars=no,status=no,width=660,height=330"
            var win = window.open('https://oauth.vk.com/authorize?client_id=4321104&scope=email&redirect_uri=http://ani-world.ru/auth&response_type=code&v=5.21', 'vkauth', params);
            var pollTimer = window.setInterval(function() {
                if (win.closed !== false) { // !== is required for compatibility with Opera
                    window.clearInterval(pollTimer);
                    location.reload();
                }
            }, 200);
        });
    }
    $('a.delete').on('click', function(event){
        if(!confirm('Подтвердить удаление')){
            event.preventDefault();
        }
    });
    var rate = $('#rate').attr('data-rate');
    $('a.rate_item').hover(function(){
        var a = $(this);
        a.parent().attr('data-rate', a.attr('id'));
    }, function(){
        var a = $(this);
        a.parent().attr('data-rate', rate);
    })
        .on('click', function(e){
            e.preventDefault();
            $.ajax({
                url: $(this).attr('href'),
                type: 'post',
                success: function(){
                    location.reload();
                }
            });
        });
    $('form#search>img').on('click', function(){
        $('form#search').submit();
    });
    $('#toggle_message').on('click', function(event){
        event.preventDefault();
        $('#message_form').toggle();
    });
    $('#message_form').on('submit', function(event){
        var form = $(this);
        event.preventDefault();
        $.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serializeArray(),
            dataType: 'json',
            success: function(data){
                if(data.message){
                    alert(data.message);
                    form.trigger( 'reset' );
                    $('#message_form').toggle();
                }else{
                    alert('Ошибка! Проверте правильность заполнения формы.');
                }
            }
        });
    });
});