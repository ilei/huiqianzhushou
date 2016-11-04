<?PHP
    /*
     | Submail addressbook/mail/subscribe API demo
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
    
    $addressbook=new ADDRESSBOOKMail($mail_configs);
    
    /*
     |Required para
     |--------------------------------------------------------------------------
     |The First para: recipient email address
     |The second para: recipient name(optional)
     |--------------------------------------------------------------------------
     */
    
    $addressbook->setAddress('leo@apple.cn','leo');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |set target addressbook sign : Optional
     |default value: subscribe
     |--------------------------------------------------------------------------
     */
    
    //$addressbook->setAddressbook('subscribe');
    
    
    $subscribe=$addressbook->subscribe();
    
    print_r($subscribe);
