<?php

namespace glacier\EntWechat;


class MsgFormater
{
    static public function xml(array $array, $flag = 0)
    {
        if ($flag == 0) {
            $xml = "<xml>";
        } else {
            $xml = "";
        }
        foreach ($array as $key => $val) {
            if(is_int($key)) $key="item";
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . self::xml($val, 1) . "</" . $key . ">";
            } else {
                if (is_int($val)) {
                    $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
                }
                if (is_string($val)) {
                    $xml .= "<" . $key . ">" . "<![CDATA[{$val}]]>" . "</" . $key . ">";
                }
            }
        }
        if ($flag == 0) {
            $xml .= "</xml>";
        }
        return $xml;
    }

    /**
     * 将对象转换为多维数组
     *
     **/
   static public function objectToArray($d)
    {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }

        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(__METHOD__, $d);
        } else {
            // Return array
            return $d;
        }
    }

    /*
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[你好]]></Content>
*/
    static public function text($content)
    {
        $msg=is_string($content) ?$content:"错误：传入的content不是字符串类型";
        return array(
            'MsgType'=>'text',
            'Content'=>$msg
        );
    }

    /**
     * @param array $content 二维数组 array( ['Title'=>'','Description'=>'','PicUrl'=>'','Url'=>''] )
     * @return array
     * @param $content
    <MsgType><![CDATA[news]]></MsgType>
    <ArticleCount>2</ArticleCount>
    <Articles>
    <item>
    <Title><![CDATA[title1]]></Title>
    <Description><![CDATA[description1]]></Description>
    <PicUrl><![CDATA[picurl]]></PicUrl>
    <Url><![CDATA[url]]></Url>
    </item>
    <item>
    <Title><![CDATA[title]]></Title>
    <Description><![CDATA[description]]></Description>
    <PicUrl><![CDATA[picurl]]></PicUrl>
    <Url><![CDATA[url]]></Url>
    </item>
    </Articles>
     */
    static public function news(array $content)
    {
        return array(
            'MsgType'=>'news',
            'ArticleCount'=>count($content),
            'Articles'=>$content
        );

    }

    /**
     * @param array $user
     * @param $content
     * {
     * "touser": "UserID1|UserID2|UserID3",
     * "toparty": " PartyID1 | PartyID2 ",
     * "totag": " TagID1 | TagID2 ",
     * "msgtype": "text",
     * "agentid": 1,
     * "text": {
     * "content": "Holiday Request For Pony(http://xxxxx)"
     * }
     * }
     * @return string
     */
    static public function sendTxt($content,$agentId,$user,$type)
    {
        $txt=[];
        $typeList=['user','party','tag'];
        if(!in_array($type,$typeList)){
            throw new \Exception("type类型不正确应该为'user','party','tag'中的一种");
        }
        if(is_array($user)){
            $txt["to{$type}"]=implode("|",$user);
        }elseif(is_string($user)||is_numeric($user)){
            if($user=='all'){
                $txt['touser']='@all';
            }else{
                $txt["to{$type}"]=(string)$user;
            }
        }else{
            throw new \Exception("user数据类型不正确");
        }
        $txt['msgtype']='text';
        $txt['agentid']=$agentId;
        $txt['text']=array('content'=>$content);
        return json_encode($txt,JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array $user
     * @param array $news
     * @param $agentid
     * {
    "touser": "UserID1|UserID2|UserID3",
    "toparty": " PartyID1 | PartyID2 ",
    "totag": " TagID1 | TagID2 ",
    "msgtype": "news",
    "agentid": 1,
    "news": {
    "articles":[
    {
    "title": "Title",
    "description": "Description",
    "url": "URL",
    "picurl": "PIC_URL"
    },
    {
    "title": "Title",
    "description": "Description",
    "url": "URL",
    "picurl": "PIC_URL"
    }
    ]
    }
    }
     */
    static public function sendNews(array $news,$agentId,$user,$type)
    {
        $txt=array();
        $typeList=['user','party','tag'];
        if(!in_array($type,$typeList)){
            throw new \Exception("type类型不正确应该为'user','party','tag'中的一种");
        }
        if(is_array($user)){
            $txt["to{$type}"]=implode("|",$user);
        }elseif(is_string($user)||is_numeric($user)){
            if($user=='all'){
                $txt['touser']='@all';
            }else{
                $txt["to{$type}"]=$user;
            }
        }else{
            throw new \Exception("user数据类型不正确");
        }
        $txt['touser']=implode("|",$user);
        $txt['msgtype']='news';
        $txt['agentid']=$agentId;
        $txt['articles']=$news;
        return json_encode($txt,JSON_UNESCAPED_UNICODE);

    }

/*
 * {
   "userid": "zhangsan", 必填
   "name": "张三", 必填
   "department": [1, 2], 必填
   "position": "产品经理",
   "mobile": "15913215421",  3选一必填一
   "gender": "1", //1为男 2为女
   "email": "zhangsan@gzdev.com",  3选一必填一
   "weixinid": "zhangsan4dev",  3选一必填一
   "avatar_mediaid": "2-G6nrLmr5EC3MNb_-zL1dDdzkd0p7cNliYu9V5w7o8K0",
   "extattr": {"attrs":[{"name":"爱好","value":"旅游"},{"name":"卡号","value":"1234567234"}]}
}
*/
    static public function entUser(array $user){
        $user['department']=isset($user['department'])?$user['department']:[1];
        return json_encode($user,JSON_UNESCAPED_UNICODE);
    }

}