<?php

namespace glacier\EntWechat;


class EntMsgHandler implements MsgHandlerInterface
{
    protected $encodingAesKey;
    protected $token;
    protected $corpId;
    protected $wxcpt;
    protected $postObj;
    protected $responseFromUserName;
    protected $responseToUserName;
    protected $responseAgentID;
    protected $msgArray;

    public function __construct($encodingAesKey=null,$token=null,$corpId=null)
    {
        //获取配置
        $entConfig= include __DIR__."/EntConfig.php";
        // 企业号在公众平台上设置的参数如下
        $this->encodingAesKey = empty($encodingAesKey)?$entConfig['encodingAesKey']:$encodingAesKey;
        $this->token = empty($token)?$entConfig['token']:$token;
        $this->corpId = empty($corpId)?$entConfig['corpId']:$corpId;
        //实例化微信的解密
        $this->wxcpt = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->corpId);
        //解析微信发来的内容并返回数组，

        $postStr=$this->decrpyt();//自动解密微信来的密文
        $this->postObj = simplexml_load_string($postStr,'SimpleXMLElement', LIBXML_NOCDATA);
        $msg=MsgFormater::objectToArray($this->postObj);
        $this->responseFromUserName=$msg['ToUserName'];
        $this->responseToUserName=$msg['FromUserName'];
        $this->responseAgentID=$msg['AgentID'];
        $this->msgArray=$msg;

    }

    /**
     * @return string
     *自动解密微信来的密文成xml字符串
     */
    protected function decrpyt()
    {

        /*
        ------------使用示例二：对用户回复的消息解密---------------
        用户回复消息或者点击事件响应时，企业会收到回调消息，此消息是经过公众平台加密之后的密文以post形式发送给企业，密文格式请参考官方文档
        假设企业收到公众平台的回调消息如下：
        POST /cgi-bin/wxpush? msg_signature=477715d11cdb4164915debcba66cb864d751f3e6&timestamp=1409659813&nonce=1372623149 HTTP/1.1
        Host: qy.weixin.qq.com
        Content-Length: 613
        <xml>
        <ToUserName><![CDATA[wx5823bf96d3bd56c7]]></ToUserName><Encrypt><![CDATA[RypEvHKD8QQKFhvQ6QleEB4J58tiPdvo+rtK1I9qca6aM/wvqnLSV5zEPeusUiX5L5X/0lWfrf0QADHHhGd3QczcdCUpj911L3vg3W/sYYvuJTs3TUUkSUXxaccAS0qhxchrRYt66wiSpGLYL42aM6A8dTT+6k4aSknmPj48kzJs8qLjvd4Xgpue06DOdnLxAUHzM6+kDZ+HMZfJYuR+LtwGc2hgf5gsijff0ekUNXZiqATP7PF5mZxZ3Izoun1s4zG4LUMnvw2r+KqCKIw+3IQH03v+BCA9nMELNqbSf6tiWSrXJB3LAVGUcallcrw8V2t9EL4EhzJWrQUax5wLVMNS0+rUPA3k22Ncx4XXZS9o0MBH27Bo6BpNelZpS+/uh9KsNlY6bHCmJU9p8g7m3fVKn28H3KDYA5Pl/T8Z1ptDAVe0lXdQ2YoyyH2uyPIGHBZZIs2pDBS8R07+qN+E7Q==]]></Encrypt>
        <AgentID><![CDATA[218]]></AgentID>
        </xml>

        企业收到post请求之后应该
        1.解析出url上的参数，包括消息体签名(msg_signature)，时间戳(timestamp)以及随机数字串(nonce)
        2.验证消息体签名的正确性。
        3.将post请求的数据进行xml解析，并将<Encrypt>标签的内容进行解密，解密出来的明文即是用户回复消息的明文，明文格式请参考官方文档
        第2，3步可以用公众平台提供的库函数DecryptMsg来实现。
        */

// $sReqMsgSig = HttpUtils.ParseUrl("msg_signature");
        $sReqMsgSig = $_GET['msg_signature'];
// $sReqTimeStamp = HttpUtils.ParseUrl("timestamp");
        $sReqTimeStamp = $_GET['timestamp'];
// $sReqNonce = HttpUtils.$_GET("nonce");
        $sReqNonce = $_GET['nonce'];

        $sReqData =file_get_contents("php://input");
        /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
        the best way is to check the validity of xml by yourself */
        libxml_disable_entity_loader(true);
        $sMsg = "";  // 解析之后的明文
        $errCode = $this->wxcpt->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);
        if ($errCode == 0) {
            // 解密成功，sMsg即为xml格式的明文
           return $sMsg;
        } else {
            print("ERR: " . $errCode . "\n\n");
            //exit(-1);
        }

    }

    /**
     * @param $sRespData
     * @return string
     * 加密
     */
    protected function encrypt($sRespData){
        /*
------------使用示例三：企业回复用户消息的加密---------------
企业被动回复用户的消息也需要进行加密，并且拼接成密文格式的xml串。
假设企业需要回复用户的明文如下：
<xml>
<ToUserName><![CDATA[mycreate]]></ToUserName>
<FromUserName><![CDATA[wx5823bf96d3bd56c7]]></FromUserName>
<CreateTime>1348831860</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[this is a test]]></Content>
<MsgId>1234567890123456</MsgId>
<AgentID>128</AgentID>
</xml>

为了将此段明文回复给用户，企业应：
1.自己生成时间时间戳(timestamp),随机数字串(nonce)以便生成消息体签名，也可以直接用从公众平台的post url上解析出的对应值。
2.将明文加密得到密文。
3.用密文，步骤1生成的timestamp,nonce和企业在公众平台设定的token生成消息体签名。
4.将密文，消息体签名，时间戳，随机数字串拼接成xml格式的字符串，发送给企业号。
以上2，3，4步可以用公众平台提供的库函数EncryptMsg来实现。
*/

// 需要发送的明文
       // $sRespData = "<xml><ToUserName><![CDATA[mycreate]]></ToUserName><FromUserName><![CDATA[wx5823bf96d3bd56c7]]></FromUserName><CreateTime>1348831860</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[this is a test]]></Content><MsgId>1234567890123456</MsgId><AgentID>128</AgentID></xml>";
        $sEncryptMsg = ""; //xml格式的密文
        $sReqTimeStamp=time();
        $sReqNonce= (string)rand(1000000000,9999999999);
        $errCode =  $this->wxcpt->EncryptMsg($sRespData, $sReqTimeStamp, $sReqNonce, $sEncryptMsg);
        if ($errCode == 0) {
            // TODO:
            return $sEncryptMsg;
            // 加密成功，企业需要将加密之后的sEncryptMsg返回
            // HttpUtils.SetResponce($sEncryptMsg);  //回复加密之后的密文
        } else {
            print("ERR: " . $errCode . "\n\n");
            // exit(-1);
        }
    }
    /**
     * @return \SimpleXMLElement
     * 自动解密微信来的密文成xml字符串，并转换成对象
     */
    public function getMsgArray()
    {
        return $this->msgArray;
    }

    public function responseMsg(array $msg)
    {
        $msg['CreateTime']=time();
        $msg['FromUserName']=$this->responseFromUserName;
        $msg['ToUserName']=$this->responseToUserName;
        $xml=MsgFormater::xml($msg);
        return $this->encrypt($xml);
    }


}


