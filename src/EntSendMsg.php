<?php

namespace glacier\EntWechat;

use glacier\widgets\Curl;

class EntSendMsg
{
    protected $apiUrl="https://qyapi.weixin.qq.com/cgi-bin/message/send";
    protected $curl;

    public function __construct()
    {
        //多次调用就不会重复创建对象
        $this->curl=new Curl();
    }

    public function sendMsg($data)
    {
        $token=new EntAccessToken();
        $arr['access_token']=$token->getAccessToken();
        return $this->curl->post($this->apiUrl,$data,$arr);
    }

    public function sendTxt($content,$agentId,$user,$type='user')
    {
        $data=MsgFormater::sendTxt($content,$agentId,$user,$type);
        $token=new EntAccessToken();
        $arr['access_token']=$token->getAccessToken();
        return $this->curl->post($this->apiUrl,$data,$arr);
    }

    public function sendNews(array $news,$agentId,$user,$type='user')
    {
        $data=MsgFormater::sendNews($news,$user,$agentId);
        $token=new EntAccessToken();
        $arr['access_token']=$token->getAccessToken();
        return $this->curl->post($this->apiUrl,$data,$arr);
    }

}