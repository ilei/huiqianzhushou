<?PHP
    require 'message.php';
    class MESSAGEXsend{
        /*
         | @set vars start
         |--------------------------------------------------------------------------
         */
        
        /*
         | Submail message appid
         |@type string
         |--------------------------------------------------------------------------
         */
        
        protected $appid='';
        
        /*
         | Submail message appkey
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
         |to: message recipient
         |@array to rfc 822
         |--------------------------------------------------------------------------
         */
        
        protected $To=array();
        
        /*
         |add message recipient from addressbook
         |@array to string
         |--------------------------------------------------------------------------
         */
        
        protected $Addressbook=array();
        
        /*
         |message project sign
         |--------------------------------------------------------------------------
         */
        
        protected $Project='';
        
        /*
         |vars: the submail message text content filter
         |@type array to json string
         |--------------------------------------------------------------------------
         */
        
        protected $Vars=array();
        
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
         |add message cellphone number
         |--------------------------------------------------------------------------
         */
        
        public function AddTo($address){
            array_push($this->To,$address);
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
         |Set message project
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
         |build request array
         |--------------------------------------------------------------------------
         */
        
        public function buildRequest(){
            $request=array();
            
            /*
             |convert To array to string
             |--------------------------------------------------------------------------
             */
            
            if(!empty($this->To)){
                $request['to']='';
                foreach($this->To as $tmp){
                    $request['to'].=$tmp.',';
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
            $message_configs['appid']=$this->appid;
            $message_configs['appkey']=$this->appkey;
            
            /*
             |set sign_type,if is set
             |--------------------------------------------------------------------------
             */
            
            if($this->sign_type!=''){
                $message_configs['sign_type']=$this->sign_type;
            }
            
            /*
             |init mail class
             |--------------------------------------------------------------------------
             */
            
            $message=new message($message_configs);
            
            /*
             |build request and send email and return the result
             |--------------------------------------------------------------------------
             */
            
            return $message->xsend($this->buildRequest());
        }
        
    }
