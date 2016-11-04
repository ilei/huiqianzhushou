<?php 
/**
 * 错误跳转对应的模板文件
 * 
 * CT: 2014-09-18 10:00 by YLX
 * UT: 2014-11-03 17:00 by YLX
 */
?>

<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <?php echo C('MEDIA_CSS.BOOTSTRAP'); ?>
        <?php echo C('MEDIA_CSS.FONT_AWESOME'); ?>
        <import type='css' file="meetelf.css.tpl" />

        <title>跳转提示</title>
        <style type="text/css">
            *{ padding: 0; margin: 0; }
            body{ background: #fff; font-family: '微软雅黑'; color: #333; font-size: 16px; }
            .system-message{ padding: 24px 48px; }
            .system-message h1{ font-size: 100px; font-weight: normal; line-height: 120px; margin-bottom: 12px; }
            .system-message .jump{ padding-top: 10px}
            .system-message .jump a{ color: #333;}
            .system-message .success,.system-message .error,.system-message ul.error li{ line-height: 1.8em; font-size: 36px }
            .system-message ul.error { margin-left: 48px; }
            .system-message .detail{ font-size: 12px; line-height: 20px; margin-top: 12px; display:none}
        </style>
    </head>
    <body class="bodybg">

    <!-- Main Starting -->
    <div class="container">
        <div class="row pagemain">
            <?php $img = isset($message)?'jumps.jpg':'jumpf.jpg'; ?>
            <div class="col-xs-5"><img width="380" height="440" src="/Public/common/images/<?php echo $img; ?>"></div>
            <div class="col-xs-6 pdlf30">
                <?php if(isset($message)) { ?>
                    <h1 class="jpfont1"><nameb>成功!</nameb></h1>
                    <div class="jumph"><h4><?php echo($message); ?></h4></div>
                <?php }else{?>
                    <h1 class="jpfont1"><nameo>失败!</nameo></h1>
                    <div class="jumph"><h4>
                    <?php
                        if (!is_array($error)) {
                            echo($error);
                        }else{
                            foreach ($error as $e){
                                echo $e."<br/ >";
                            }
                        }
                    ?>
                    </h4></div>
                <?php }?>
                <p class="detail"></p>

                <h3 class="mt30">页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 等待时间：<b id="wait"><?php echo($waitSecond); ?></b></h3>
                <!--<div class="mt20"><button type="button" class="btn mybtn active">返回</button></div>-->
            </div>
        </div>
    </div>

        <script type="text/javascript">
        (function(){
            var wait = document.getElementById('wait'),href = document.getElementById('href').href;
            var interval = setInterval(function(){
            	var time = --wait.innerHTML;
            	if(time <= 0) {
            		location.href = href;
            		clearInterval(interval);
            	};
            }, 1000);
        })();
        </script>
    </body>
</html>
