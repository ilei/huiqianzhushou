<?PHP
    /*
     | Submail mail/send API demo
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
     |init MAILSend class
     |--------------------------------------------------------------------------
     */
    
    $submail=new MAILSend($mail_configs);
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |The First para: recipient email address
     |The second para: recipient name(optional)
     |@Multi-para
     |--------------------------------------------------------------------------
     */
    
    $submail->AddTo('leo@submail.cn','leo');
    $submail->AddCc('mailer@submail.cn');
     $submail->AddBcc('leo@drinkfans.com');
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
     |set sender address and name
     |The First para: sender email address
     The second para: sender display name (optional)
     |--------------------------------------------------------------------------
     */
    
    $submail->SetSender('no-reply@submail.cn','SUBMAIL');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |set reply address
     |--------------------------------------------------------------------------
     */
    
    $submail->SetReply('service@submail.cn');
    
    /*
     |Required para
     |--------------------------------------------------------------------------
     |set email subject
     |--------------------------------------------------------------------------
     */
    
    $submail->SetSubject('test SDK');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |set email text content
     |--------------------------------------------------------------------------
     */
    
    $submail->SetText('test SDK text');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |set email html content
     |--------------------------------------------------------------------------
     */
    
    $submail->SetHtml('test SDK html @var(name),@var(age) <a href="var://@link(test)">testLink</a> <a href="var://@link(test2)">testLink2</a>');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |submail email text content filter
     |@Multi-para
     |--------------------------------------------------------------------------
     */
    
    //$submail->AddVar('name','leo');
    //$submail->AddVar('age','32');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |submail email link content filter
     |@Multi-para
     |--------------------------------------------------------------------------
     */
    
    //$submail->AddLink('developer','http://submail.cn/chs/developer');
    //$submail->AddLink('store','http://submail.cn/chs/store');

    /*
     |Optional para
     |--------------------------------------------------------------------------
     |email headers
     |@Multi-para
     |--------------------------------------------------------------------------
     */
    
    //$submail->AddHeaders('X-Accept','zh-cn');
    //$submail->AddHeaders('X-Mailer','leo App');
    
    /*
     |Optional para
     |--------------------------------------------------------------------------
     |Attachment
     |@file path
     |@Multi-para
     |--------------------------------------------------------------------------
     */
    
    //$submail->AddAttachment('/subfile/tmp/c4ebf8621b652936ea41ed70adf9107b691.jpg');
    //$submail->AddAttachment('/subfile/tmp/c43934a06c28cfcacb6ca9700871b70b118_0_1920x1200.jpg');
    
    
    $send=$submail->send();
    
    print_r($send);
