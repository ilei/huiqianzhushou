<?PHP
    /*
     | Submail message/xsend API demo
     | SUBMAIL SDK Version 1.0.1 --PHP
     | copyright 2011 - 2014 SUBMAIL
     |--------------------------------------------------------------------------
     */
    
    /*
     |require config file
     |--------------------------------------------------------------------------
     */
    require '../app_config.php';
    
    /*
     |require_once SUBMAILAutoload file
     |--------------------------------------------------------------------------
     */
    
    require_once('../SUBMAILAutoload.php');
    
    /*
     |init MESSAGEXsend class
     |--------------------------------------------------------------------------
     */
    
    $submail=new MESSAGEXsend($message_configs);
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |recipient cell phone number
     |@Multi-para
     |--------------------------------------------------------------------------
     */
    
    $submail->AddTo('18616761881');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |set addressbook sign : Optional
     |add addressbook contacts to Multi-Recipients
     |@Multi-para
     |--------------------------------------------------------------------------
     */
    
    //$submail->AddAddressbook('subscribe');
    
    /*
     |Required para
     |--------------------------------------------------------------------------
     |set message project sign
     |--------------------------------------------------------------------------
     */
    
    $submail->SetProject('kZ9Ky3');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |submail email text content filter
     |@Multi-para
     |--------------------------------------------------------------------------
     */
    
    $submail->AddVar('code','198276');
    
    $xsend=$submail->xsend();
    
    print_r($xsend);
