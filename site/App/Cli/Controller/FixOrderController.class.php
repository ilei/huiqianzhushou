<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/11/27
 * Time: 下午2:42
 */

namespace Cli\Controller;

class FixOrderController extends BaseController{
    public function __construct(){
        parent::__construct();
    }

    public  function  fixed(){

        echo "beginFixed..."."\r\n";

        $activityList = $this->getActivity();

        if (empty($activityList)) {
            echo "no data to fixed..." . "\r\n";
            return;
        }

        var_dump($activityList);

        foreach ($activityList as $v) {

            $result = M('Order')
                ->where(array(
                    'target_guid' => $v['activity_guid']
                ))
                ->save(array(
                    'activity_name' => $v['activity_name']
                ));

            if ($result === false) {
                echo "update data error... activity_guid: " . $v['activity_guid'] . "\r\n";
            } elseif ($result === 0) {
                echo "no row to update... activity_guid: " . $v['activity_guid'] . "\r\n";
            } else {
                echo "updated... activity_guid:" . $v["activity_guid"] . "\r\n";
            }
        }
        echo "endFixd ..."."\r\n";

    }

    private  function  getActivity(){

        //未取消的报名用户
        $activityList = M('Activity')
            ->where(array(
                'is_del' => '0',
            ))
            ->field(array(
                'guid' => 'activity_guid',
                'name' => 'activity_name'
            ))
            ->select();

        return $activityList;
    }
}