<?php

namespace glacier\EntWechat;

use glacier\widgets\FileCache;
use glacier\widgets\Curl;
class EntAccessToken
{
    protected $queryString=array();
    protected $accessToken;
    public $errorMsg;

    public function __construct($corpId=null,$secret=null)
    {
        $this->apiUrl="https://qyapi.weixin.qq.com/cgi-bin/gettoken";//?corpid=id&corpsecret=secrect
        //获取配置
        $mpConfig= include __DIR__."/EntConfig.php";
        $this->queryString['corpId']=empty($corpId)?$mpConfig['corpId']:$corpId;
        $this->queryString['corpsecret']=empty($secret)?$mpConfig['corpsecret']:$secret;
        if($this->queryString['corpId']=='你的信息'||$this->queryString['corpsecret']=='你的信息'){
            throw new \Exception('请配置EntConfig.php文件');
        }
        $this->appID=$this->queryString['corpId'];
        $this->useCache=$mpConfig['useCache'];
        $this->cachePath=$mpConfig['cachePath'];
        $this->expireTime=$mpConfig['expire'];
        //开启缓存，注意目录写权限
        if($this->useCache){
            $this->cache= new FileCache($this->cachePath);
        }
    }

    protected function setCache()
    {
        //没开启缓存不做事
        if(!$this->useCache){
            return ;
        }
        $cache=$this->cache;
        $cache->set($this->appID.'access_token',$this->accessToken,$this->expireTime);
    }

    protected function getCache()
    {
        //没开启缓存不做事
        if(!$this->useCache){
            return false;
        }
        $cache=$this->cache;
        return $cache->get($this->appID.'access_token');
    }

    public function getAccessToken()
    {
        $token=$this->getCache();
        if(!$token) $token=$this->requestAccessToken();
        return $token;
    }

    public function requestAccessToken()
    {
        $curl=new Curl();
        $res=$curl->get($this->apiUrl,$this->queryString);
        if($res){
            $data=json_decode($res,true);
            if(isset($data['access_token'])){
                $this->accessToken=$data['access_token'];
                $this->expireTime=$data['expires_in']<$this->expireTime?$data['expires_in']:$this->expireTime;
                $this->setCache();
                return $this->accessToken;
            }elseif(isset($data['errcode'])){
                $this->errorMsg="errcode:".$data['errcode']." errMsg:".$data['errmsg'];
                return false;
            }
        }else{
            throw new \Exception($curl->error_message);
        }
        unset($curl);
    }
}