<?php

namespace glacier\EntWechat;

use glacier\widgets\Curl;

class EntSendMsg
{
    protected $apiUrl="https://qyapi.weixin.qq.com/cgi-bin/message/send";
    protected $curl;
    public $errorMsg;

    public function __construct()
    {
        //多次调用就不会重复创建对象
        $this->curl=new Curl();
    }

    public function sendMsg($data)
    {
        $token=new EntAccessToken();
        $arr['access_token']=$token->getAccessToken();
        if($arr['access_token']==false){
            //如果access_token获取失败
            throw new \Exception("获取access_token失败! ".$token->errorMsg);
        }
        $result= $this->curl->post($this->apiUrl,$data,$arr);
        return $this->checkResult($result);
    }

    /**
     * @param $content string 文本消息
     * @param $agentId int 应用ID
     * @param $user string|int|array 发送对象，多个可以用数组
     * @param string $type string 'user','tag','party'
     * @return bool|null|string http请求成功返回微信送回的json字符串,错误返回false
     * @throws \Exception
     */
    public function sendTxt($content,$agentId,$user,$type='user')
    {
        $data=MsgFormater::sendTxt($content,$agentId,$user,$type);
        $token=new EntAccessToken();
        $arr['access_token']=$token->getAccessToken();
        if($arr['access_token']==false){
            //如果access_token获取失败
            throw new \Exception("获取access_token失败! ".$token->errorMsg);
        }
        $result= $this->curl->post($this->apiUrl,$data,$arr);
        return $this->checkResult($result);

    }

    /**
     * @param $news array 图文消息的article数组
     * @param $agentId int 应用ID
     * @param $user string|int|array 发送对象，多个可以用数组
     * @param string $type string 'user','tag','party'
     * @return bool|null|string http请求成功返回微信送回的json字符串,错误返回false
     * @throws \Exception
     */
    public function sendNews(array $news,$agentId,$user,$type='user')
    {
        $data=MsgFormater::sendNews($news,$agentId,$user,$type);
        $token=new EntAccessToken();
        $arr['access_token']=$token->getAccessToken();
        if($arr['access_token']==false){
            //如果access_token获取失败
            throw new \Exception("获取access_token失败! ".$token->errorMsg);
        }
        $result= $this->curl->post($this->apiUrl,$data,$arr);
        return $this->checkResult($result);
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

}