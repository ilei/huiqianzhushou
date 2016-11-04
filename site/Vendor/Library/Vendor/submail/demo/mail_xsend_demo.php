<?PHP
    /*
     | Submail mail/xsend API demo
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
     |init MAILXsend class
     |--------------------------------------------------------------------------
     */
    
    $submail=new MAILXsend($mail_configs);
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |The First para: recipient email address
     |The second para: recipient name(optional)
     |@Multi-para
     |--------------------------------------------------------------------------
     */
    
    $submail->AddTo('leo@submail.cn','leo');
    
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
     |Optional para
     |--------------------------------------------------------------------------
     |set sender address and name
     |The First para: sender email address
     The second para: sender display name (optional)
     |--------------------------------------------------------------------------
     */
    
    //$submail->SetSender('no-reply@submail.cn','SUBMAIL');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |set reply address
     |--------------------------------------------------------------------------
     */
    
    //$submail->SetReply('service@submail.cn');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |set email subject
     |--------------------------------------------------------------------------
     */
    
    //$submail->SetSubject('test SDK');
    
    /*
     |Required para
     |--------------------------------------------------------------------------
     |set project sign
     |--------------------------------------------------------------------------
     */
    
    $submail->SetProject('ThJBE4');

    /*
     |Optional para
     |--------------------------------------------------------------------------
     |submail email text content filter
     |@Multi-para
     |--------------------------------------------------------------------------
     */
    
    $submail->AddVar('name','leo');
    $submail->AddVar('age','32');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |submail email link content filter
     |@Multi-para
     |--------------------------------------------------------------------------
     */
    
    $submail->AddLink('developer','http://submail.cn/chs/developer');
    $submail->AddLink('store','http://submail.cn/chs/store');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |email headers
     |@Multi-para
     |--------------------------------------------------------------------------
     */
    
    $submail->AddHeaders('X-Accept','zh-cn');
    $submail->AddHeaders('X-Mailer','leo App');

    $xsend=$submail->xsend();
    
    print_r($xsend);
