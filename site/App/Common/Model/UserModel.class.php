<?php
namespace Common\Model;

use Common\Model\BaseModel;

/**
 * 用户模型
 *
 * CT: 2014-09-15 15:00 by YLX
 */
class UserModel extends BaseModel{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 用户模型验证条件
     *
     * @author wangleiming<wangleiming@yunmai365.com> 
     */
    protected $_validate = array(

        array('guid','require','{%l_ym_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%l_ym_model_guid_len_error}', self::EXISTS_VALIDATE, 'length', self::MODEL_INSERT),

        array('email', 'email_check', '邮箱格式不对!', self::EXISTS_VALIDATE, 'callback'),
        array('email', 'email_unique', '邮箱已经注册!', self::EXISTS_VALIDATE, 'callback', self::MODEL_INSERT),


        array('mobile', 'require', '手机号码必须填写!', self::EXISTS_VALIDATE),
        array('mobile', 'mobile_check', '手机号码格式不正确!', self::EXISTS_VALIDATE, 'callback'),
        array('mobile', 'mobile_unique', '手机号码已经注册!', self::EXISTS_VALIDATE, 'callback', self::MODEL_INSERT),
        
        array('password', 'require', '密码必须填写!', self::EXISTS_VALIDATE),

    );

    public function email_check($email){
        return preg_match('/^([\w\.\_]{2,12})@(\w{1,}).([a-z]{2,4})$/i', $email) ? true : false; 
    }

    public function mobile_check($mobile){
        return preg_match('/^1[34587]{1}\d{9}$/', $mobile) ? true : false; 
    }


    public function mobile_unique($mobile){
        $exist = $this->where(array('mobile' => trim($mobile), 'is_del' => 0))->find(); 
        return $exist ? false : true;
    }
    public function email_unique($email){
        $exist = $this->where(array('email' => trim($email), 'is_del' => 0, 'email_verify' => 1))->find(); 
        return $exist ? false : true;
    }

    /**
     * 获取用户详细信息
     *
     * CT: 2014-09-11 15:00 by YLX
     */
    public function getUserInfo($u_guid)
    {
        return $this->where(array('guid' => $u_guid))->find();
    }


    /**
     * 获取用户详细信息
     *
     * $param $guid 用户guid
     * $param $guid_2 用户2 GUid
     * $param $c_limit 共同好友显示数量
     *
     * CT: 2014-09-11 15:00 by YLX
     * UT: 2014-12-31 10:00 by Qiu
     */
    public function getDetail($guid, $guid_2 = '', $c_limit = 5)
    {
        // 获取用户信息
        $user_info = $this->getUserInfo($guid);
        if (empty($user_info)) return false;

        $return                     = array();
        $return['mobile']           = $user_info['mobile'];
        $return['real_name']        = $user_info['real_name'];
        $return['sex']              = $user_info['sex'];
        $return['photo']            = $user_info['photo_240'];
        $return['birthday']         = isset($user_info['birthday']) ? $user_info['birthday'] : '';
        $return['home_area']        = isset($user_info['home_areaid_1']) ? api_get_full_area($user_info['home_areaid_1']) . ',' . api_get_full_area($user_info['home_areaid_2']) : '';
        $return['area']             = isset($user_info['areaid_1']) ? api_get_full_area($user_info['areaid_1']) . ',' . api_get_full_area($user_info['areaid_2']) : '';
        $return['interest']         = $this->getInterest($guid);
        $return['edu']              = isset($user_info['edu']) ? $user_info['edu'] . ',' . $this->getSchool($guid) : '';
        $industry_name              = D('Industry')->getName($user_info['main_industry_guid']);
        $industry_name              = empty($industry_name) ? '' : $industry_name;
        $return['industry']         = isset($user_info['main_industry_guid']) ? $user_info['main_industry_guid'] . ',' . $industry_name : '';
        $return['company']          = M('UserCompany')->alias('c')->join('ym_industry i ON c.industry_guid=i.guid', 'left')
            ->field('c.guid, c.name, c.tel, c.position, i.guid as industry_guid, i.name as industry_name')
            ->where(array('c.user_guid' => $guid, 'c.is_verify' => '1', 'c.is_del' => '0'))
            ->select();
        $return['remark']           = isset($user_info['remark']) ? $user_info['remark'] : '';
        $return['updated_at']       = $user_info['updated_at'];
        $return['moblie_verify']    = $user_info['moblie_verify'];
        $return['email_verify']     = $user_info['email_verify'];
        $return['real_name_verify'] = $user_info['real_name_verify'];

        if (!empty($guid_2)) {
            $return['c_friends'] = D('Contacts')->get_related_friends($guid_2, $guid, $c_limit, 'guid, real_name');
        }

        return $return;
    }

    /**
     * 获取用户详细信息
     *
     * $guid 用户guid
     * $cfriend 是否显示用户共同好友
     *
     * CT: 2014-11-01 11:00 by YLX
     */
    public function getDetailForWeb($guid, $cfriend = false, $climit = null)
    {
        // 获取用户信息
        $user_info = $this->getUserInfo($guid);
        if (empty($user_info)) return false;

        $return                  = $user_info;
        $return['guid']          = $user_info['guid'];
        $return['real_name']     = $user_info['real_name'];
        $return['sex']           = $user_info['sex'];
        $return['photo']         = $user_info['photo'];
        $return['birthday']      = $user_info['birthday'];
        $return['address']       = $user_info['address'];
        $return['home_area']     = isset($user_info['home_areaid_2']) ? get_full_area($user_info['home_areaid_1'], $user_info['home_areaid_2']) : '';
        $return['area']          = isset($user_info['areaid_2']) ? get_full_area($user_info['areaid_1'], $user_info['areaid_2']) : '';
        $return['interest']      = $this->getInterest($guid);
        $return['edu']           = isset($user_info['edu']) ? D('School')->getName($user_info['edu']) : '';
        $industry_name           = D('Industry')->getName($user_info['main_industry_guid']);
        $return['industry']      = isset($user_info['main_industry_guid']) ? $industry_name : '';
        $return['company']       = M('UserCompany')->alias('c')->join('ym_industry i ON c.industry_guid=i.guid', 'left')
            ->field('c.guid, c.name, c.tel, c.position, i.guid as industry_guid, i.name as industry_name')
            ->where(array('c.user_guid' => $guid, 'c.is_verify' => '1', 'c.is_del' => '0'))
            ->select();
        $return['org']           = $this->getOrgNames($guid);
        $return['remark']        = $user_info['remark'];
        $return['updated_at']    = $user_info['updated_at'];
        $return['created_at']    = $user_info['created_at'];
        $return['email']         = $user_info['email'];
        $return['mobile']        = $user_info['mobile'];
        $return['moblie_verify'] = $user_info['moblie_verify'];
        $return['email_verify']  = $user_info['email_verify'];
        $return['real_name']     = $user_info['real_name'];

        if ($cfriend) {
            $return['c_friends'] = D('Contacts')->get_related_friends(I('post.user_guid'), $guid, $climit, 'guid, real_name');
        }

        return $return;
    }

    /**
     * 通过字段搜索用户
     *
     * @param $field 要搜索的字段
     * @param $keyword 要搜索的关键字
     * @param $mid 当前用户GUID
     * @param $is_blacklist 是否增加黑名单过滤
     *
     * @return 用户列表
     *
     * CT: 2014-10-28 10:50 by YLX
     */
    public function searchBy($field, $keyword, $page = 1, $mid = null, $is_blacklist = true)
    {
        if (empty($field) || empty($keyword)) return false;

        $where = array('is_del' => '0', 'is_active' => '1', 'type' => '1',
            $field   => array('LIKE', "%$keyword%"),
            'guid'   => array('NEQ', $mid)
        );

        $blacklist = array();
        if ($is_blacklist && $mid) {
            $blacklist = M('Contacts')->where(array('user_guid_2' => $mid, 'status' => '4'))->getField('user_guid_1', true);
            if (!empty($blacklist)) {
                $where['guid'] = array('NOT IN', $blacklist);
            }
        }

        $res = $this->field('guid, real_name, photo, remark')
            ->where($where)
            ->page($page . ',' . C('NUM_PER_PAGE', null, '15'))
            ->select();
        return $res;
    }
    public function getInterest($u_guid)
    {
        return $this->getInterestSql($u_guid)->select();
    }

    /**
     * 获取用户兴趣IDS
     *
     * CT: 2014-10-27 18:00 by YLX
     */
    public function getInterestIds($u_guid)
    {
        return $this->getInterestSql($u_guid)->getField('interest_id', true);
    }
}
