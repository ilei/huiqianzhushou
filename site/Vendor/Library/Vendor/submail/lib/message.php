<?PHP
    class message{
        var $message_configs;
        /*
         | @set default signType
         |--------------------------------------------------------------------------
         */
        var $signType='normal';
        /*
         | @init
         |--------------------------------------------------------------------------
         */
        function __construct($message_configs){
            $this->message_configs=$message_configs;
        }
        /*
         | @createSignature
         |--------------------------------------------------------------------------
         */
        protected function createSignature($request){
            $r="";
            /*
             | @switch signType
             |--------------------------------------------------------------------------
             */
            switch($this->signType){
                case 'normal':
                    $r=$this->message_configs['appkey'];
                    break;
                case 'md5':
                    $r=$this->buildSignature($this->argSort($request));
                    break;
                case 'sha1':
                    $r=$this->buildSignature($this->argSort($request));
                    break;
            }
            return $r;
        }
        /*
         | @buildSignature
         |--------------------------------------------------------------------------
         */
        
        protected function buildSignature($request){
            $arg="";
            $app=$this->message_configs['appid'];
            $appkey=$this->message_configs['appkey'];
            while (list ($key, $val) = each ($request)) {
                $arg.=$key."=".$val."&";
            }
            $arg = substr($arg,0,count($arg)-2);
            if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
            
            if($this->signType=='sha1'){
                $r=sha1($app.$appkey.$arg.$app.$appkey);
            }else{
                $r=md5($app.$appkey.$arg.$app.$appkey);
            }
            
            return $r;
        }
        /*
         | @argSort
         |--------------------------------------------------------------------------
         */
        protected function argSort($request) {
            ksort($request);
            reset($request);
            return $request;
        }
        /*
         | @getTimestamp
         |--------------------------------------------------------------------------
         */
        protected function getTimestamp(){
            $api='https://api.submail.cn/service/timestamp.json';
            $ch = curl_init($api) ;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ;
            $output = curl_exec($ch) ;
            $timestamp=json_decode($output,true);
            
            return $timestamp['timestamp'];
        }
        /*
         | @APIHttpRequestCURL
         |--------------------------------------------------------------------------
         */
        protected function APIHttpRequestCURL($api,$post_data){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            $output = curl_exec($ch);
            curl_close($ch);
            $output = trim($output, "\xEF\xBB\xBF");
            return json_decode($output,true);
        }

        /*
         | @send
         |--------------------------------------------------------------------------
         */
        public function send($request){
            /*
             | @setup API httpRequest URI
             |--------------------------------------------------------------------------
             */
            $api='https://api.submail.cn/message/send.json';
            
            /*
             |--------------------------------------------------------------------------
             | create final API post query Start
             |--------------------------------------------------------------------------
             */
            
            $request['appid']=$this->message_configs['appid'];
            
            /*
             | @get timestamp from server
             |--------------------------------------------------------------------------
             */
            
            $request['timestamp']=$this->getTimestamp();
            
            /*
             | @setup sign_type
             |--------------------------------------------------------------------------
             */
            
            if(empty($this->message_configs['sign_type'])
               || $this->message_configs['sign_type']==""
               || $this->message_configs['sign_type']!="normal"
               || $this->message_configs['sign_type']!="md5"
               || $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }

            /*
             | @create signature
             |--------------------------------------------------------------------------
             */
            
            $request['signature']=$this->createSignature($request);
            
            /*
             |--------------------------------------------------------------------------
             | create final API post query End
             |--------------------------------------------------------------------------
             */
            
            
            
            /*
             |--------------------------------------------------------------------------
             | API Request Start
             |--------------------------------------------------------------------------
             */
            /*
             | @send request
             |--------------------------------------------------------------------------
             */
            $send=$this->APIHttpRequestCURL($api,$request);
            /*
             |--------------------------------------------------------------------------
             | API Request End
             |--------------------------------------------------------------------------
             */
            
            return $send;

        }
        /*
         | @xsend
         |--------------------------------------------------------------------------
         */
        public function xsend($request){
            
            /*
             | @setup API httpRequest URI
             |--------------------------------------------------------------------------
             */
            $api='https://api.submail.cn/message/xsend.json';
            
            /*
             |--------------------------------------------------------------------------
             | create final API post query Start
             |--------------------------------------------------------------------------
             */

            $request['appid']=$this->message_configs['appid'];
            
            /*
             | @get timestamp from server
             |--------------------------------------------------------------------------
             */

            $request['timestamp']=$this->getTimestamp();
            
            
            /*
             | @setup sign_type
             |--------------------------------------------------------------------------
             */
            
            if(empty($this->message_configs['sign_type'])
               || $this->message_configs['sign_type']==""
               || $this->message_configs['sign_type']!="normal"
               || $this->message_configs['sign_type']!="md5"
               || $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }
            
            /*
             | @create signature
             |--------------------------------------------------------------------------
             */
            
            $request['signature']=$this->createSignature($request);
            
            /*
             |--------------------------------------------------------------------------
             | create final API post query End
             |--------------------------------------------------------------------------
             */
            
            
            /*
             |--------------------------------------------------------------------------
             | API Request Start
             |--------------------------------------------------------------------------
             */
            /*
             | @send request
             |--------------------------------------------------------------------------
             */
            $send=$this->APIHttpRequestCURL($api,$request);
            /*
             |--------------------------------------------------------------------------
             | API Request End
             |--------------------------------------------------------------------------
             */
            
            return $send;

        }
        /*
         | @addressbook/message/subscribe
         |--------------------------------------------------------------------------
         */
        public function subscribe($request){
            
            /*
             | @setup API httpRequest URI
             |--------------------------------------------------------------------------
             */
            $api='https://api.submail.cn/addressbook/message/subscribe.json';
            
            /*
             |--------------------------------------------------------------------------
             | create final API post query Start
             |--------------------------------------------------------------------------
             */
            $request['appid']=$this->message_configs['appid'];
            /*
             | @get timestamp from server
             |--------------------------------------------------------------------------
             */
            $request['timestamp']=$this->getTimestamp();
            
            /*
             | @setup sign_type
             |--------------------------------------------------------------------------
             */
            
            if(empty($this->message_configs['sign_type'])
               || $this->message_configs['sign_type']==""
               || $this->message_configs['sign_type']!="normal"
               || $this->message_configs['sign_type']!="md5"
               || $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }

            /*
             | @create signature
             |--------------------------------------------------------------------------
             */
            
            $request['signature']=$this->createSignature($request);
            

            
            
            /*
             |--------------------------------------------------------------------------
             | API Request Start
             |--------------------------------------------------------------------------
             */
            /*
             | @subscribe request
             |--------------------------------------------------------------------------
             */
            $subscribe=$this->APIHttpRequestCURL($api,$request);
            /*
             |--------------------------------------------------------------------------
             | API Request End
             |--------------------------------------------------------------------------
             */
            
            return $subscribe;
        }
        
        /*
         | @addressbook/message/unsubscribe
         |--------------------------------------------------------------------------
         */
        public function unsubscribe($request){
            /*
             | @setup API httpRequest URI
             |--------------------------------------------------------------------------
             */
            $api='https://api.submail.cn/addressbook/message/unsubscribe.json';
            
            /*
             |--------------------------------------------------------------------------
             | create final API post query Start
             |--------------------------------------------------------------------------
             */
            $request['appid']=$this->message_configs['appid'];
            /*
             | @get timestamp from server
             |--------------------------------------------------------------------------
             */
            $request['timestamp']=$this->getTimestamp();
            /*
             | @setup sign_type
             |--------------------------------------------------------------------------
             */
            
            if(empty($this->message_configs['sign_type'])
               || $this->message_configs['sign_type']==""
               || $this->message_configs['sign_type']!="normal"
               || $this->message_configs['sign_type']!="md5"
               || $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }
            

            /*
             | @create signature
             |--------------------------------------------------------------------------
             */
            
            $request['signature']=$this->createSignature($request);
            
            
            
            /*
             |--------------------------------------------------------------------------
             | API Request Start
             |--------------------------------------------------------------------------
             */
            /*
             | @unsubscribe request
             |--------------------------------------------------------------------------
             */
            $unsubscribe=$this->APIHttpRequestCURL($api,$request);
            /*
             |--------------------------------------------------------------------------
             | API Request End
             |--------------------------------------------------------------------------
             */
            
            return $unsubscribe;
        }

    }