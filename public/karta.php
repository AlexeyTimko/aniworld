<?
if(!empty($_POST)){
    $data = base64_decode(substr($_POST['data'], stripos($_POST['data'],',')+1));
    file_put_contents('q.png',$data);
}else{
    $data = file_exists('q.png');
?>
<!DOCTYPE html>
<html>
<head>
    <script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="/js/colorpicker.js"></script>
    <link rel="stylesheet" media="screen" type="text/css" href="/css/colorpicker.css" />
    <style type="text/css">
        #dream_map{
            border: 1px solid black;
            cursor: url(http://cursors4.totallyfreecursors.com/thumbnails/pencil2.gif),pointer;
        }
        #dream_map.eraser{
            cursor: url(/img/eraser.png),pointer;
        }
        #dream_map.color_picker{
            cursor: url(/img/colorpicker.png),pointer;
        }
        #scale_num{
            width: 40px;
        }
        #scale_line{
            display: inline-block;
            width: 100px;
            height: 3px;
            border: 1px solid lightblue;
            border-radius: 3px;
            background-color: #aeaeae;
            position: relative;
        }
        #scale_button{
            display: inline-block;
            position: absolute;
            width: 5px;
            height: 11px;
            border: 1px solid whitesmoke;
            border-radius: 3px;
            background-color: steelblue;
            top: -5px;
            margin-left: -2px;
            cursor: pointer;
        }
        #colorSelector{
            width: 25px;
            height: 25px;
            padding: 2px;
            background-color: lightgray;
        }
        #colorSelector>div{
            height: 25px;
            width: 25px;
            background-color: black;
        }
    </style>
</head>
<body>
    <canvas height='500' width='500' id='dream_map'>Обновите браузер</canvas>
    <input type="button" id="save_map" value="Сохранить"/>
    <div>
        <img src="http://cursors4.totallyfreecursors.com/thumbnails/pencil2.gif" id="pencil" title="Карандаш"/>
        <img src="/img/eraser.png" id="eraser" title="Ластик"/>
        <img src="/img/colorpicker.png" id="colorpicker" title="Определить цвет"/>
        <div id="scale">
            <input type="number" value="1" id="scale_num"/>
            <span id="scale_line"><span id="scale_button"></span></span>
        </div>
        <div id="colorSelector">
            <div></div>
        </div>
    </div>
    <script type="text/javascript">
        $(function(){
            var dream_map = $('#dream_map'),
                offset = dream_map.offset(),
                save_button = $('#save_map'),
                ctx = dream_map[0].getContext('2d'),
                W = dream_map.width(),
                H = dream_map.height(),
                bMouseIsDown = false,
                lastX = 0, lastY = 0,
                mode = 'pencil';
            <?if($data){?>
            var i = new Image();
            i.src = 'q.png';
            i.onload = function(){
                ctx.drawImage(i,0,0);
            }
            <?}?>
//            dream_map.on('click', function(e){
//                var X = (e.pageX - offset.left),
//                    Y = (e.pageY - offset.top);
//                ctx.fillRect(X,Y,1+ctx.lineWidth,1+ctx.lineWidth);
//            });
            dream_map.on('mousedown', function(e) {
                bMouseIsDown = true;
                lastX = (e.pageX - offset.left);
                lastY = (e.pageY - offset.top);
            });
            $(window).on('mouseup', function() {
                bMouseIsDown = false;
                ctx.closePath();
            });
            dream_map.on('mousemove', function(e) {
                if (bMouseIsDown && (mode == 'eraser' || mode == 'pencil')) {
                    var iX = (e.pageX - offset.left);
                    var iY = (e.pageY - offset.top);
                    ctx.beginPath();
                    ctx.moveTo(lastX, lastY);
                    ctx.lineTo(iX, iY);
                    ctx.stroke();
                    if(ctx.lineWidth > 1){
                        ctx.arc(iX,iY,Math.floor(ctx.lineWidth/2),0,360);
                        ctx.fill();
                    }
                    lastX = iX;
                    lastY = iY;
                }
            });
            dream_map.on('click', function(e){
                var iX = (e.pageX - offset.left);
                var iY = (e.pageY - offset.top);
                switch (mode){
                    case 'colorpicker':
                        var iData = ctx.getImageData(iX,iY,1,1).data,
                            hex = rgbToHex(iData[0],iData[1],iData[2]);
                        $('#colorSelector div').css('backgroundColor', '#' + hex);
                        if(mode != 'eraser'){
                            ctx.strokeStyle = "#" + hex;
                            ctx.fillStyle = "#" + hex;
                        }
                        break;
                }
            });
            save_button.on('click', function(){
                var dataURL = dream_map[0].toDataURL();//,
//                    image_data = ctx.getImageData(0,0,W,H),
//                    data = btoa(JSON.stringify(image_data.data));
                $.ajax({
                    url: 'http://ani-world.ru/karta.php',
                    type: 'post',
                    data: {data: dataURL},
                    success: function(content){
                        alert('Успешно сохранено');
                    }
                });
            });
            $('#colorpicker').on('click', function(){
                dream_map.attr('class','');
                dream_map.addClass('color_picker');
                mode = 'colorpicker';
            });
            $('#eraser').on('click', function(){
                dream_map.addClass('eraser');
                ctx.strokeStyle = "#ffffff";
                ctx.fillStyle = "#ffffff";
                mode = 'eraser';
            });
            $('#pencil').on('click', function(){
                var color = $('#colorSelector div').css('backgroundColor');
                dream_map.attr('class','');
                ctx.strokeStyle = color;
                ctx.fillStyle = color;
                mode = 'pencil';
            });

            $('#scale_button').on('mousedown', function(){
                var sb = $(this),
                    win = $(window),
                    line = $('#scale_line'),
                    o = line.offset(),
                    sn = $('#scale_num');
                win.on('mousemove', function(e){
                    var bX = e.pageX - o.left;
                    if(bX <= line.width() && bX >= 1){
                        sb.css('left', bX + 'px');
                        sn.val(Math.round(bX/line.width()*100));
                        ctx.lineWidth = sn.val();
                    }
                });
            });
            $(window).on('mouseup', function(){
                $(this).unbind("mousemove");
            });
            $('#scale_num').on('change blur keyup', function(){
                var sn = $(this);
                if(sn.val() > 100){
                    sn.val(100);
                }
                if(sn.val() < 1){
                    sn.val(1);
                }
                $('#scale_button').css('left', sn.val() + 'px');
                ctx.lineWidth = sn.val();
            });
            $('#colorSelector').ColorPicker({
                color: '#000000',
                onShow: function (colpkr) {
                    $(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    $(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    $('#colorSelector div').css('backgroundColor', '#' + hex);
                    if(mode != 'eraser'){
                        ctx.strokeStyle = "#" + hex;
                        ctx.fillStyle = "#" + hex;
                    }
                }
            });
            function rgbToHex (r, g, b){
                r = r.toString(16);
                g = g.toString(16);
                b = b.toString(16);
                if (r.length == 1) r = '0' + r;
                if (g.length == 1) g = '0' + g;
                if (b.length == 1) b = '0' + b;
                return (r + g + b).toUpperCase();
            }
        });
    </script>
</body>
</html>
<?}?>