<?php

namespace glacier\EntWechat;

use glacier\widgets\Curl;

class EntUser
{
    protected $createApiUrl="https://qyapi.weixin.qq.com/cgi-bin/user/create";
    protected $updateApiUrl="https://qyapi.weixin.qq.com/cgi-bin/user/update";
    protected $deleteApiUrl="https://qyapi.weixin.qq.com/cgi-bin/user/delete"; //get
    protected $userInfoApiUrl="https://qyapi.weixin.qq.com/cgi-bin/user/get"; //get
    protected $listUserApiUrl="https://qyapi.weixin.qq.com/cgi-bin/user/list";//get
    protected $oauthApiUrl="https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo";//get
    protected $curl;
    protected $access_token;
    public $errorMsg;

    public function __construct()
    {
        $this->curl=new Curl();
        $a=new EntAccessToken();
        $this->access_token=$a->getAccessToken();
        if($this->access_token==false){
            //如果access_token获取失败
            $this->errorMsg=$a->errorMsg;
        }
    }

    public function create(array $user)
    {
        $queryString['access_token']=$this->access_token;
        $user=MsgFormater::entUser($user);
        $res=$this->curl->post($this->createApiUrl,$user,$queryString);
        return $this->checkResult($res);
    }

    public function update(array $user)
    {
        $queryString['access_token']=$this->access_token;
        $user=MsgFormater::entUser($user);
        $res=$this->curl->post($this->updateApiUrl,$user,$queryString);
        return $this->checkResult($res);
    }

    public function delete($userid)
    {
        $queryString=array();
        $queryString['userid']=$userid;
        $queryString['access_token']=$this->access_token;
        $res=$this->curl->get($this->deleteApiUrl,$queryString);
        return $this->checkResult($res);
    }

    public function listUser($status=0)
    {
        $queryString=[];
        $queryString['department_id']=1;
        $queryString['fetch_child']=1;
        $queryString['status']=$status;
        $queryString['access_token']=$this->access_token;
        $res=$this->curl->get($this->listUserApiUrl,$queryString);
        $users=$this->checkResult($res);
        if($users){
            return $users['userlist'];
        }else{
            return false;
        }

    }

    protected function checkResult($result)
    {
        $resArray=json_decode($result,true);
        if(!$resArray) throw new \Exception($result);
        if(isset($resArray['errcode'])&&$resArray['errcode']==0){
            return $resArray;
        }else{
            $this->errorMsg=$result;
            return false;
        }
    }

    /**
     * 企业如果需要员工在跳转到企业网页时带上员工的身份信息，需构造如下的链接：
     * https://open.weixin.qq.com/connect/oauth2/authorize?appid=CORPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
     * 员工点击后，页面将跳转至 redirect_uri?code=CODE&state=STATE，企业可根据code参数获得员工的userid。
     * 获取code 找微信验证登录的用户信息
     * https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=ACCESS_TOKEN&code=CODE
     */
    public function oauth($code)
    {
        $queryString['access_token']=$this->access_token;
        $queryString['code']=$code;
        $res=$this->curl->get($this->oauthApiUrl,$queryString);
        $resArray=json_decode($res,true);
        if(!$resArray) throw new \Exception("登录微信验证时错误，无法解析：".$res);
        if(isset($resArray['UserId'])){
            return $resArray;
        }else{
            $this->errorMsg=$res;
            return false;
        }
    }

}