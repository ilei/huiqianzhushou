<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/11/27
 * Time: 上午10:17
 */


namespace Cli\Controller;

class FixRegistrationController extends BaseController
{
    public function  __construct()
    {
        parent::__construct();
    }


    //Logic
    public function fixed()
    {


        echo "beginFixed..."."\r\n";

        $userlist = $this->getActivityUser();

        if (empty($userlist)) {
            echo "no data to fixed..." . "\r\n";
            return;
        }


        var_dump($userlist);

        foreach ($userlist as $v) {

            $result = M('ActivityUserTicket')
                ->where(array(
                    'userinfo_guid' => $v['userinfo_guid']
                ))
                ->save(array(
                    'real_name' => $v['real_name']
                ));

            if ($result === false) {
                echo "update data error... userinfo_guid: " . $v['userinfo_guid'] . "\r\n";
            } elseif ($result === 0) {
                echo "no row to update... userinfo_guid: " . $v['userinfo_guid'] . "\r\n";
            } else {
                echo "updated... userinfo_guid:" . $v["userinfo_guid"] . "\r\n";
            }
        }
        echo "endFixd ..."."\r\n";
    }

    //获取活动User  返回ActivityUser列表
    private function getActivityUser()
    {

        //未取消的报名用户
        $userlist = M('ActivityUserinfo')
            ->where(array(
                'is_del' => '0',
            ))
            ->field(array(
                'guid' => 'userinfo_guid',
                'real_name' => 'real_name'
            ))
            ->select();

        return $userlist;
    }

}