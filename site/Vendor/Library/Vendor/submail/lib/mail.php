<?PHP
    class mail{
        var $mail_configs;
        /*
         | @set default signType
         |--------------------------------------------------------------------------
         */
        var $signType='normal';
        /*
         | @init
         |--------------------------------------------------------------------------
         */
        function __construct($mail_config){
            $this->mail_configs=$mail_config;
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
                    $r=$this->mail_configs['appkey'];
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
            $app=$this->mail_configs['appid'];
            $appkey=$this->mail_configs['appkey'];
            while (list ($key, $val) = each ($request)) {
                /*
                 | @remove the attachments array from the signature array
                 |--------------------------------------------------------------------------
                 */
                if (strpos($key,"attachments")===false){
                    $arg.=$key."=".$val."&";
                }
            }
            /*
             | @remove last ','
             |--------------------------------------------------------------------------
             */
            $arg = substr($arg,0,count($arg)-2);
            if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
            
            /*
             | @md5 or sha1 the signature
             |--------------------------------------------------------------------------
             */
            
            if($this->signType=='sha1'){
                $r=sha1($app.$appkey.$arg.$app.$appkey);
            }else{
                $r=md5($app.$appkey.$arg.$app.$appkey);
            }
            return $r;
        }
        /*
         | @arg sort
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
        public function getTimestamp(){
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
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $output = curl_exec($ch);
            curl_close($ch);
            return json_decode($output,true);
            //return $output;
        }
        
        /*
         | @ mail/send
         |--------------------------------------------------------------------------
         */
        
        public function send($request){
            
            /*
             | @setup API httpRequest URI
             |--------------------------------------------------------------------------
             */
            
            $api='https://api.submail.cn/mail/send.json';
            
            

            /*
             |--------------------------------------------------------------------------
             | create final API post query Start
             |--------------------------------------------------------------------------
            */
            
            $request['appid']=$this->mail_configs['appid'];
            
            /*
             | @get timestamp from server
             |--------------------------------------------------------------------------
             */
            $request['timestamp']=$this->getTimestamp();
            
            /*
             | @setup sign_type
             |--------------------------------------------------------------------------
             */
            if(empty($this->mail_configs['sign_type'])
               || $this->mail_configs['sign_type']==""
               || $this->mail_configs['sign_type']!="normal"
               || $this->mail_configs['sign_type']!="md5"
               || $this->mail_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->mail_configs['sign_type'];
                $request['sign_type']=$this->mail_configs['sign_type'];
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
         | @ mail/xsend
         |--------------------------------------------------------------------------
         */
        
        public function xsend($request){
            /*
             | @setup API httpRequest URI
             |--------------------------------------------------------------------------
             */
            $api='https://api.submail.cn/mail/xsend.json';
            
            /*
             |--------------------------------------------------------------------------
             | create final API post query Start
             |--------------------------------------------------------------------------
             */
            $request['appid']=$this->mail_configs['appid'];
            /*
             | @get timestamp from server
             |--------------------------------------------------------------------------
             */
            $request['timestamp']=$this->getTimestamp();
            /*
             | @setup sign_type
             |--------------------------------------------------------------------------
             */
            if(empty($this->mail_configs['sign_type'])
               || $this->mail_configs['sign_type']==""
               || $this->mail_configs['sign_type']!="normal"
               || $this->mail_configs['sign_type']!="md5"
               || $this->mail_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->mail_configs['sign_type'];
                $request['sign_type']=$this->mail_configs['sign_type'];
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
             | @send request
             |--------------------------------------------------------------------------
             */
            $xsend=$this->APIHttpRequestCURL($api,$request);
            /*
             |--------------------------------------------------------------------------
             | API Request End
             |--------------------------------------------------------------------------
             */
            
            return $xsend;
        }
        /*
         | @ addressbook/mail/subscribe
         |--------------------------------------------------------------------------
         */
        public function subscribe($request){
            
            /*
             | @setup API httpRequest URI
             |--------------------------------------------------------------------------
             */
            $api='https://api.submail.cn/addressbook/mail/subscribe.json';
            
            /*
             |--------------------------------------------------------------------------
             | create final API post query Start
             |--------------------------------------------------------------------------
             */
            $request['appid']=$this->mail_configs['appid'];
            
            /*
             | @get timestamp from server
             |--------------------------------------------------------------------------
             */
            $request['timestamp']=$this->getTimestamp();
            
            
            /*
             | @setup sign_type
             |--------------------------------------------------------------------------
             */
            if(empty($this->mail_configs['sign_type'])
               || $this->mail_configs['sign_type']==""
               || $this->mail_configs['sign_type']!="normal"
               || $this->mail_configs['sign_type']!="md5"
               || $this->mail_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->mail_configs['sign_type'];
                $request['sign_type']=$this->mail_configs['sign_type'];
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
         | @ addressbook/mail/unsubscribe
         |--------------------------------------------------------------------------
         */
        public function unsubscribe($request){
            /*
             | @setup API httpRequest URI
             |--------------------------------------------------------------------------
             */
            $api='https://api.submail.cn/addressbook/mail/unsubscribe.json';
            
            
            /*
             |--------------------------------------------------------------------------
             | create final API post query Start
             |--------------------------------------------------------------------------
             */

            $request['appid']=$this->mail_configs['appid'];
            
            /*
             | @get timestamp from server
             |--------------------------------------------------------------------------
             */
            
            $request['timestamp']=$this->getTimestamp();
            
            
            /*
             | @setup sign_type
             |--------------------------------------------------------------------------
             */
            if(empty($this->mail_configs['sign_type'])
               || $this->mail_configs['sign_type']==""
               || $this->mail_configs['sign_type']!="normal"
               || $this->mail_configs['sign_type']!="md5"
               || $this->mail_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->mail_configs['sign_type'];
                $request['sign_type']=$this->mail_configs['sign_type'];
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