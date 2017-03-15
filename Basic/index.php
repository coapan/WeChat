<?php

/**
 * 微信开发分为编辑模式和开发者模式，我们使用的是开发者模式
 * 入口文件的配置，服务器的配置
 * URL(服务器的地址)
 * token令牌的设置，可以由开发者自由设置
 * 过程中可以会使用到框架，这里先使用ThinkPHP
 */

// 获取参数 timestamp,nonce,signature,echostr,token(自定义)
$timestamp = $_GET['timestamp'];
$nonce = $_GET['nonce'];
$signature = $_GET['signature'];
$token = 'wchat';
$echostr = $_GET['echostr'];

// 对参数 timestamp,nonce,token进行字典排序
$tmpArr = array(
    $timestamp,
    $nonce,
    $token
);
sort($tmpArr);

// 将三个参数拼接成为一个字符串，并进行加加密
$tmpStr = implode('', $tmpArr);
$tmpStr = sha1($tmpStr);

// 开发者获得加密后的字符串与signature进行对比，判断该请求是否来源于微信
if ($tmpStr == $signature && $echostr) {
    // 当第一次访问的微信 api接口的时候才会校验，第二次就不会了
    echo $echostr;
    exit();
} else {
    // 如果是用在控制器里面，那么在后面应该会有一些方法，在这里进行调用就行了
    $this->responseMsg();
}

function responseMsg()
{
    // 获取到微信摄像头过来的数据
    $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
    // 获取xml对象数据
    $postObj = simplexml_load_string($postArr);
    
    // 判断该数据包是否是订阅的事件推送
    if (strtolower($postObj->MsgType) == 'event') {
        // 如果是关注 subscribe 事件
        if (strtolower($postObj->Event == 'subscribe')) {
            // 回复用户消息
            // 用户是开发者，跟前面恰好相反
            $toUser = $postObj->FromUserName;
            $fromUser = $postObj->ToUserName;
            $time = time();
            $msgType = 'text';
            $content = '欢迎关注潘朝志屌屌的公众号！' . "\n" . '您的开发者微信号为：' . $postObj->FromUserName . "\n" . '您的开发者openID为：' . $postObj->ToUserName;
            $template = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
            /*
             * ToUserName ---接收方账号(收到的openID)
             * FromUserName ---开发者微信号
             */
            $info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
            echo $info;
        }
    }
    
    // 纯文本回复
    /*
     * if (strtolower($postObj->MsgType) == 'text') {
     * switch (trim($postObj->Content)) {
     * case 1:
     * $content = '你输入的是1';
     * break;
     * case 2:
     * $content = '你输入的是2';
     * break;
     * case 3:
     * $content = "<a href='http://www.phpzhi.com'>极品PHP</a>";
     * break;
     * case '英语':
     * $content = 'this is a good children';
     * break;
     * }
     * $template = "<xml>
     * <ToUserName><![CDATA[%s]]></ToUserName>
     * <FromUserName><![CDATA[%s]]></FromUserName>
     * <CreateTime>%s</CreateTime>
     * <MsgType><![CDATA[%s]]></MsgType>
     * <Content><![CDATA[%s]]></Content>
     * </xml>";
     * $toUser = $postObj->FromUserName;
     * $fromUser = $postObj->ToUserName;
     * $time = time();
     * $msgType = 'text';
     * // $content = 'PHP无处不在，PHP是世界上最好的编程语言！';
     *
     * echo sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
     * }
     */
    
    // 回复图文消息，单图文的和多图文的
    if ((strtolower($postObj->MsgType) == 'text') && (trim($postObj->Content) == '123')) {
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
        $time = time();
        $array = array(
            array(
                'title' => 'PHP之家',
                'description' => 'PHP is the best program language',
                'picUrl' => 'http://www.phpzhi.com/wp-content/themes/Git-master/timthumb.php?src=http://www.phpzhi.com/wp-content/uploads/2016/12/2016121113262074.png&h=123&w=200&q=90&zc=1&ct=1',
                'url' => 'http://www.phpzhi.com'
            ),
            array(
                'title' => 'Java之家',
                'description' => 'Java is a good language',
                'picUrl' => 'https://www.baidu.com/img/bd_logo1.png',
                'url' => 'http://www.baidu.com'
            ),
            array(
                'title' => 'Python之家',
                'description' => 'Python is a bad language',
                'picUrl' => 'https://imgcache.qq.com/open_proj/proj_qcloud_v2/gateway/portal/css/img/home/qcloud-logo-dark.png',
                'url' => 'http://www.taobao.com'
            )
        );
        
        $template = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<ArticleCount>" . count($array) . "</ArticleCount>
						<Articles>";
        foreach ($array as $k => $v) {
            $template .= "<item>
							<Title><![CDATA[" . $v['title'] . "]]></Title>
							<Description><![CDATA[" . $v['description'] . "]]></Description>
							<PicUrl><![CDATA[" . $v['picUrl'] . "]]></PicUrl>
							<Url><![CDATA[" . $v['url'] . "]]></Url>
							</item>";
        }
        $template .= "</Articles>
						</xml>";
        echo sprintf($template, $toUser, $fromUser, $time, 'news');
    } else {
        switch (trim($postObj->Content)) {
            case 1:
                $content = '你输入的是1';
                break;
            case 2:
                $content = '你输入的是2';
                break;
            case 3:
                $content = "<a href='http://www.phpzhi.com'>极品PHP</a>";
                break;
            case '英语':
                $content = 'this is a good children';
                break;
        }
        $template = "<xml>
        	                <ToUserName><![CDATA[%s]]></ToUserName>
        	                <FromUserName><![CDATA[%s]]></FromUserName>
        	                <CreateTime>%s</CreateTime>
        	                <MsgType><![CDATA[%s]]></MsgType>
        	                <Content><![CDATA[%s]]></Content>
        	                </xml>";
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
        $time = time();
        $msgType = 'text';
        // $content = 'PHP无处不在，PHP是世界上最好的编程语言！';
        
        echo sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
    }
}
