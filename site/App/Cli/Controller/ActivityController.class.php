<?php
namespace Cli\Controller;
use Cli\Controller\BaseController;
//php index.php Cli/Activity/repaire_act_undertaker


class ActivityController extends BaseController{

	public function __construct(){
		parent::__construct();
	}

    public function repaire_act_undertaker(){
        $activity = M('Activity')->select(); 
        $activity = array_columns($activity, 'user_guid', 'guid'); 
        $organizer = M('OrganizerInfo')->select(); 
        foreach($organizer as $key => $value){
            $guid = array_search($value['user_guid'], $activity);
            $org[$guid][$value['guid']] = $value['name']; 
        }
        $undertaker = M('ActivityAttrUndertaker')->select();
        foreach($undertaker  as $key => $value){
            if($value['type'] == 1 && !$value['organizer_guid']){
                if($key = array_search($value['name'], $org[$value['activity_guid']])){
                    M('ActivityAttrUndertaker')->where(array('guid' => $value['guid']))->save(array('organizer_guid' => $key));
                } 
            }
            if($value['type'] == 2){
                $update = array('type' => '承办方', 'partner_guid' => 'A394C130702CCBC6396E5781A57B6701'); 
                M('ActivityAttrUndertaker')->where(array('guid' => $value['guid']))->save($update);
            }
            if($value['type'] == 3){
                $update = array('type' => '协办方', 'partner_guid' => '8CA16AFBAEF29E2FB6E9126E4D8D8CFF'); 
                M('ActivityAttrUndertaker')->where(array('guid' => $value['guid']))->save($update);
            }
            if($value['type'] == 4){
                $update = array('type' => '特别鸣谢', 'partner_guid' => '20E4B5403F7822FE05DCC5497A015F74'); 
                M('ActivityAttrUndertaker')->where(array('guid' => $value['guid']))->save($update);
            }
            echo 111 . "\n";
        }
          
    }
}
