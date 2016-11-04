<?PHP
    require 'mail.php';
    class MAILXsend{
        /*
         | @set vars start
         |--------------------------------------------------------------------------
         */
        
        /*
         | Submail mail appid
         |@type string
         |--------------------------------------------------------------------------
         */
        
        protected $appid='';
        
        /*
         | Submail mail appkey
         |@type string
         |--------------------------------------------------------------------------
         */
        
        protected $appkey='';
        
        
        /*
         | sign type (Optional)
         |@options: normal or md5 or sha1
         |--------------------------------------------------------------------------
         */
        
        protected $sign_type='';
        
        /*
         |to: email recipient
         |@array to rfc 822
         |--------------------------------------------------------------------------
         */
        
        protected $To=array();
        
        /*
         |add email recipient from addressbook
         |@array to string
         |--------------------------------------------------------------------------
         */
        
        protected $Addressbook=array();
        
        /*
         |Sender email
         |@type email
         |--------------------------------------------------------------------------
         */
        
        protected $From='';
        
        /*
         |Sender display name
         |@type string
         |--------------------------------------------------------------------------
         */
        
        protected $From_name='';
        
        /*
         |Sender reply email
         |@type email
         |--------------------------------------------------------------------------
         */
        
        protected $Reply='';
        
        /*
         |cc recipient email
         |@array to string
         |--------------------------------------------------------------------------
         */
        
        protected $Cc=array();
        
        /*
         |bcc recipient email
         |@array to string
         |--------------------------------------------------------------------------
         */
        
        protected $Bcc=array();
        
        /*
         |the email subject
         |@string
         |--------------------------------------------------------------------------
         */
        
        protected $Subject='';
        
        /*
         |email project sign
         |--------------------------------------------------------------------------
         */
        
         protected $Project='';
        
        /*
         |vars: the submail email text content filter
         |@type array to json string
         |--------------------------------------------------------------------------
         */
        
        protected $Vars=array();
        
        /*
         |links: the submail email links content filter
         |@type array to json string
         |--------------------------------------------------------------------------
         */
        
        protected $Links=array();
        
        /*
         |email headers
         |@type  array to json string
         |--------------------------------------------------------------------------
         */
        
        protected $Headers=array();
        
        /*
         |Init appid,appkey,sign_type(Optional)
         |--------------------------------------------------------------------------
         */
        
        function __construct($configs){
            $this->appid=$configs['appid'];
            $this->appkey=$configs['appkey'];
            if(!empty($configs['sign_type'])){
                $this->sign_type=$configs['sign_type'];
            }
        }
        
        /*
         |addTo function
         |set email and name to array
         |--------------------------------------------------------------------------
         */
        
        public function AddTo($address,$name=''){
            array_push($this->To,array('address'=>$address,'name'=>$name));
        }
        
        /*
         |AddAddressbook function
         |set addressbook sign to array
         |--------------------------------------------------------------------------
         */
        
        public function AddAddressbook($addressbook){
            array_push($this->Addressbook,$addressbook);
        }
        
        /*
         |setSender function
         |set From and From_name
         |--------------------------------------------------------------------------
         */
        
        public function SetSender($sender,$name=''){
            $this->From=$sender;
            $this->From_name=$name;
        }
        
        /*
         |SetReply function
         |set Reply address
         |--------------------------------------------------------------------------
         */
        
        public function SetReply($reply){
            $this->Reply=$reply;
        }
        
        /*
         |AddCc function
         |set cc recipient to array
         |--------------------------------------------------------------------------
         */
        
        public function AddCc($address,$name=''){
            array_push($this->Cc,array('address'=>$address,'name'=>$name));
        }
        
        /*
         |AddBcc function
         |set bcc recipient to array
         |--------------------------------------------------------------------------
         */
        
        public function AddBcc($address,$name=''){
            array_push($this->Bcc,array('address'=>$address,'name'=>$name));
        }
        
        /*
         |Set email subject
         |--------------------------------------------------------------------------
         */
        
        public function SetSubject($subject){
            $this->Subject=$subject;
        }
        
        /*
         |Set email subject
         |--------------------------------------------------------------------------
         */
        
        public function SetProject($project){
            $this->Project=$project;
        }
        /*
         |AddVar function
         |set var to array
         |--------------------------------------------------------------------------
         */
        
        public function AddVar($key,$val){
            $this->Vars[$key]=$val;
        }
        
        /*
         |AddLink function
         |set link var to array
         |--------------------------------------------------------------------------
         */
        
        public function AddLink($key,$val){
            $this->Links[$key]=$val;
        }
        
        /*
         |AddHeaders function
         |set headers to array
         |--------------------------------------------------------------------------
         */
        
        public function AddHeaders($key,$val){
            $this->Headers[$key]=$val;
        }

        /*
         |build request array
         |--------------------------------------------------------------------------
         */
        
        protected function buildRequest(){
            $request=array();
            
            /*
             |convert To array to rfc 822 format
             |--------------------------------------------------------------------------
             */
            
            if(!empty($this->To)){
                $request['to']='';
                foreach($this->To as $tmp){
                    $request['to'].=$tmp['name'].'<'.$tmp['address'].'>,';
                }
                $request['to'] = substr($request['to'],0,count($request['to'])-2);
            }
            
            /*
             |convert Addressbook array to string
             |--------------------------------------------------------------------------
             */
            
            if(!empty($this->Addressbook)){
                $request['addressbook']='';
                foreach($this->Addressbook as $tmp){
                    $request['addressbook'].=$tmp.',';
                }
                $request['addressbook'] = substr($request['addressbook'],0,count($request['addressbook'])-2);
            }
            
            /*
             |set sender email
             |--------------------------------------------------------------------------
             */
            
            if($this->From!=''){
                $request['from']=$this->From;
            }
            
            /*
             |set sender display name, if is not empty
             |--------------------------------------------------------------------------
             */
            
            if($this->From_name!=''){
                $request['from_name']=$this->From_name;
            }
            
            /*
             |set sender reply email, if is not empty
             |--------------------------------------------------------------------------
             */
            
            if($this->Reply!=''){
                $request['reply']=$this->Reply;
            }
            
            /*
             |convert Cc array to rfc 822 format, if is not empty
             |--------------------------------------------------------------------------
             */
            
            if(!empty($this->Cc)){
                $request['cc']='';
                foreach($this->Cc as $tmp){
                    $request['cc'].=$tmp['name'].'<'.$tmp['address'].'>,';
                }
                $request['cc'] = substr($request['cc'],0,count($request['cc'])-2);
            }
            
            /*
             |convert Bcc array to rfc 822 format, if is not empty
             |--------------------------------------------------------------------------
             */
            
            if(!empty($this->Bcc)){
                $request['bcc']='';
                foreach($this->Bcc as $tmp){
                    $request['bcc'].=$tmp['name'].'<'.$tmp['address'].'>,';
                }
                $request['bcc'] = substr($request['bcc'],0,count($request['bcc'])-2);
            }
            
            /*
             |set email subject
             |--------------------------------------------------------------------------
             */
            if($this->Subject!=''){
                $request['subject']=$this->Subject;
            }
            
            /*
             |set project sign
             |--------------------------------------------------------------------------
             */
            
            $request['project']=$this->Project;
            
            /*
             |convert vars array to json string, if is not empty
             |--------------------------------------------------------------------------
             */
            
            if(!empty($this->Vars)){
                $request['vars']=json_encode($this->Vars);
            }
            
            /*
             |convert links array to json string, if is not empty
             |--------------------------------------------------------------------------
             */
            
            if(!empty($this->Links)){
                $request['links']=json_encode($this->Links);
            }
            
            /*
             |convert Headers array to json string, if is not empty
             |--------------------------------------------------------------------------
             */
            
            if(!empty($this->Headers)){
                $request['headers']=json_encode($this->Headers);
            }
            return $request;
            
        }
        /*
         |xsend email
         |--------------------------------------------------------------------------
         */
        
        public function xsend(){
            /*
             |set appid and appkey
             |--------------------------------------------------------------------------
             */
            $mail_configs['appid']=$this->appid;
            $mail_configs['appkey']=$this->appkey;
            
            /*
             |set sign_type,if is set
             |--------------------------------------------------------------------------
             */
            
            if($this->sign_type!=''){
                $mail_configs['sign_type']=$this->sign_type;
            }
            
            /*
             |init mail class
             |--------------------------------------------------------------------------
             */
            
            $mail=new mail($mail_configs);
            
            /*
             |build request and send email and return the result
             |--------------------------------------------------------------------------
             */
            
            return $mail->xsend($this->buildRequest());
        }

    }