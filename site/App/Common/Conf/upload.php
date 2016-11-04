<?php

/**
 * 上传设置
 * 
 * CT: 2014-10-22 16:00 by YLX
 * UT: 2014-10-22 16:00 by YLX
 */

return array(
    'SAVE_EXT' => 'png',
    'MAX_UPLOAD_SIZE' => 3*1024*1024,
    'MAX_UPLOAD_CREDENTIALS_SIZE' => 4*1024*1024,
    'ALLOWED_EXTS'      => array('jpg', 'png', 'jpeg'),
    'ALLOWED_EXTS_PATTERN' => '/\.(jpe?g|png)$/i',
    'UPLOAD_DIRS' => array(
        'avatar' => '/user/', 
    ),

    'UPLOAD_GOODS' => array(
        'maxSize' => 2*1024*1024, // 2M
    )
);
