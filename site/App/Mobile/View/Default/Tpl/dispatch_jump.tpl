<?php 
/**
 * 错误跳转对应的模板文件
 * 
 * CT: 2014-09-18 10:00 by YLX
 * UT: 2014-11-28 17:00 by YLX
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
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

        <?php echo C('MEDIA_CSS.BOOTSTRAP'); ?>
        <import type='css' file="common.css.results" />

        <title>跳转提示</title>
    </head>
    <body class="bodybg">

    <div class="main">

        <?php if(isset($message)) { ?>
        <div class="imgmb"><img src="<?php echo PUBLIC_URL ?>/common/images/success.png" width="150" height="150"></div>
        <h3><strong><?php echo $message; ?></strong></h3>
        <h5>即将返回活动页面</h5>
        <?php }else{ ?>
        <div class="imgmb"><img src="<?php echo PUBLIC_URL ?>/common/images/error.png" width="150" height="150"></div>
        <h3 class="error"><strong>错误</strong></h3>
        <h5><?php
                        if (!is_array($error)) {
                            echo($error);
                        }else{
                            foreach ($error as $e){
                                echo $e."<br/ >";
            }
            }
            ?></h5>
        <?php } ?>
    </div>
    <a id="href" href="<?php echo($jumpUrl); ?>" style="display: none;">跳转</a>
    <b id="wait" style="display: none;"><?php echo($waitSecond); ?></b>


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
