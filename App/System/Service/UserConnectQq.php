<?php
namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Service\ServiceException;
use Be\System\Session;
use Be\System\Request;

class UserConnectQq extends \Be\System\Service
{

    private $appId = '';
    private $appKey = '';

    // 构造函数
    public function __construct()
    {
        $config = Be::getConfig('System', 'User');
        $this->appId = $config->connectQqAppId;
        $this->appKey = $config->connectQqAppKey;
    }


    public function login()
    {
        $state = md5(uniqid(rand(), true));
        Session::set('user_connect_qq_state', $state);

        $url = 'https://graph.qq.com/oauth2.0/authorize';
        $url .= '?ResponseType=code';
        $url .= '&clientId=' . $this->appId;
        $url .= '&redirectUri=' . urlencode(url() . '/?app=System&controller=User&action=qqLoginCallback');
        $url .= '&state=' . $state;

        header("Location:$url");

        echo '<html>';
        echo '<head>';
        echo '<meta http-equiv="refresh" content="0; url=' . $url . '">';
        echo '<script language="javascript">';
        echo 'window.location.href="' . $url . '";';
        echo '</script>';
        echo '</head>';
        echo '<body></body>';
        echo '</html>';
    }

    public function callback()
    {
        if (Request::get('state', '') != Session::get('user_connect_qq_state')) {
            throw new \Exception('返回信息被篡改！');
        }

        $url = 'https://graph.qq.com/oauth2.0/token';
        $url .= '?grantType=authorizationCode';
        $url .= '&clientId=' . $this->appId;
        $url .= '&clientSecret=' . $this->appKey;
        $url .= '&code=' . Request::get('code', '');
        $url .= '&redirectUri=' . urlencode(url() . '/?app=System&controller=User&action=qqLoginCallback');

        $libHttp = Be::getLib('Http');
        $response = $libHttp->get($url);

        if (strpos($response, "callback") !== false) {

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
            $msg = json_decode($response);

            if (isset($msg->error)) {
                throw new ServiceException($msg->error . ': ' . $msg->errorDescription);
            }
        }

        $params = array();
        parse_str($response, $params);

        return $params['accessToken'];
    }

    public function getOpenid($accessToken)
    {
        $url = 'https://graph.qq.com/oauth2.0/me';
        $url .= '?accessToken=' . $accessToken;

        $libHttp = Be::getLib('Http');
        $response = $libHttp->get($url);

        //--------检测错误是否发生
        if (strpos($response, "callback") !== false) {

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
        }

        $response = json_decode($response);
        if (isset($response->error)) {
            throw new ServiceException($response->error . ': ' . $response->errorDescription);
        }

        return $response->openid;
    }

    public function getUserInfo($accessToken, $openid)
    {
        $url = 'https://graph.qq.com/user/getUserInfo';
        $url .= '?oauthConsumerKey=' . $this->appId;
        $url .= '&accessToken=' . $accessToken;
        $url .= '&openid=' . $openid;
        $url .= '&format=json';

        $libHttp = Be::getLib('Http');
        $response = $libHttp->get($url);

        $response = json_decode($response);

        if ($response->ret != 0) {
            throw new ServiceException($response->msg);
        }

        return $response;
    }

    public function register($userInfo)
    {
        $configUser = Be::getConfig('System', 'User');

        $t = time();
        $tupleUser = Be::newTuple('system_user');
        $tupleUser->connect = 'qq';
        $tupleUser->name = $userInfo->nickname;
        $tupleUser->register_time = $t;
        $tupleUser->last_visit_time = $t;
        $tupleUser->block = 0;
        $tupleUser->save();

        $libHttp = Be::getLib('Http');
        $response = $libHttp->get($userInfo->figureurl_qq_2 ? $userInfo->figureurl_qq_1 : $userInfo->figureurl_qq_2);

        $t = date('YmdHis', $t);

        $tmpAvatar = Be::getRuntime()->getDataPath() . '/System/Tmp/user_connect_qq_' . $t . '_' . $tupleUser->id;
        file_put_contents($tmpAvatar, $response);

        $libImage = Be::getLib('image');
        $libImage->open($tmpAvatar);
        if ($libImage->isImage()) {

            $libImage->resize($configUser->avatar_l_w, $configUser->avatar_l_h, 'north');
            $libImage->save(Be::getRuntime()->getDataPath() . '/System/User/Avatar/' . $tupleUser->id . '_' . $t . '_l.' . $libImage->getType());
            $tupleUser->avatar_l = $tupleUser->id . '_' . $t . '_l.' . $libImage->getType();

            $libImage->resize($configUser->avatar_m_w, $configUser->avatar_m_h, 'north');
            $libImage->save(Be::getRuntime()->getDataPath() . '/System/User/Avatar/' . $tupleUser->id . '_' . $t . '_m.' . $libImage->getType());
            $tupleUser->avatar_m = $tupleUser->id . '_' . $t . '_m.' . $libImage->getType();

            $libImage->resize($configUser->avatar_s_w, $configUser->avatar_s_h, 'north');
            $libImage->save(Be::getRuntime()->getDataPath() . '/System/User/Avatar/' . $tupleUser->id . '_' . $t . '_s.' . $libImage->getType());
            $tupleUser->avatar_s = $tupleUser->id . '_' . $t . '_s.' . $libImage->getType();

            $tupleUser->save();
        }

        unlink($tmpAvatar);

        return $tupleUser;
    }

    // 用户登陆到BE系统
    public function systemLogin($userId)
    {
        Be::getService('System', 'User')->makeLogin($userId);
    }

}
