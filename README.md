# EntWechat
微信企业号SDK
安装：composer require glacier/ent-wechat

先配置好EntConfig
## EntSendMsg:发送企业号消息
发送文本消息：`sendTxt($content,$agentId,$user,$type='user')`

* $content string 文本消息
* $agentId int 应用ID
* $user string|int|array 发送对象，多个可以用数组
* string $type string 'user','tag','party'
* 返回值 bool|null|string http请求成功返回微信送回的json（已解码成数组）,错误返回false，通过`errorMsg`属性可以查看失败原因


例子：
```
use glacier\EntWechat\EntSendMsg;

$a=new EntSendMsg();
$a->sendTxt('test',1,'user1');
$a->sendTxt('test',1,'@all');
$a->sendTxt('test',1,['user1','user2']);
$msg=$a->sendTxt('test',1,['tag1','tag2'],'tag');
var_dump($msg);
//string(27) "{"errcode":0,"errmsg":"ok"}"

```

发送图文消息：
`sendNews(array $news,$agentId,$user,$type='user')` 

发送消息内容自定义的json内容（文本，图文其他都可以结构参看微信文档）：
`sendMsg($data)` 

## EntUser:用户管理
增加:`create(array $user)`
更新：`update(array $user)`
具体数组`$user`的结构参看`MsgFormater::entUser`的注释
删除：`delete($userid)`
获取user信息：`listUser($status=0)`
验证用户是否会企业号成员：`oauth($code)`
**返回值：** 成功返回微信返回的json(已经解码成数组格式)，失败返回`false`，通过`errorMsg`属性可以查看失败原因

例子：
```
use glacier\EntWechat\EntUser;
$a=new EntUser();
$res=$a->listUser();
//失败打印出错误信息
if(!$res){
var_dump($a->errorMsg);
}
var_dump($res)
/**
array(16) {
  [0]=>
  array(6) {
    ["userid"]=>
    string(11) "userid1"
    ["name"]=>
    string(9) "xx"
    ..此处省略X字
*/
```

验证用户
```
    /**
     * 原理：
     * 员工要点击的连接URL构造，点击后会跳转到redirect_uri
     * https://open.weixin.qq.com/connect/oauth2/authorize?appid=CORPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
     * 员工点击后，页面将跳转至 redirect_uri?code=CODE&state=STATE，微信加上了queryString企业可根据code参数获得员工的userid。
     * 脚本根据获取code 找微信验证登录的用户信息
     * https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=ACCESS_TOKEN&code=CODE
     */
//获取code,这个是微信生成的
$code=$_GET['code'];
//如果code获取不到可能不是通过微信来的访问，做点什么。。。。
$a=new EntUser();
$res=$a->oauth($code);
//验证失败返回false,成功返回用户信息数组。
```

## EntMsgHandler:解析用户在应用里发送的消息
具体看代码吧，主要是接口定义的2个方法。这里不写了
