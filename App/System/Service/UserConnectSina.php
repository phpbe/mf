<?php
namespace App\System\Service;

use Be\System\Be;
use Be\System\Service\ServiceException;
use Be\System\Session;
use Be\System\Request;

class UserConnectSina extends \Be\System\Service
{
	
	private $appKey = '';
	private $appSecret = '';

    // 构造函数
    public function __construct()
    {
		$config = Be::getConfig('System', 'User');
        $this->appKey = $config->connectSinaAppKey;
		$this->appSecret = $config->connectSinaAppSecret;
    }

	public function login()
	{
		$state = md5(uniqid(rand(), true));
		Session::set('user_connect_sina_state', $state);

        $url = 'https://api.weibo.com/oauth2/authorize';
		$url .= '?clientId='.$this->appKey;
		$url .= '&ResponseType=code';
		$url .= '&redirectUri='.urlencode(url().'/?app=System&controller=User&action=sinaLoginCallback');
		$url .= '&state='.$state;

        header("Location:$url");

		echo '<html>';
		echo '<head>';
		echo '<meta http-equiv="refresh" content="0; url='.$url.'">';
		echo '<script language="javascript">';
		echo 'window.location.href="'.$url.'";';
		echo '</script>';
		echo '</head>';
		echo '<body></body>';
		echo '</html>';
	}

	public function callback()
	{
		if (Request::get('state', '')!=Session::get('user_connect_sina_state')) {
            throw new ServiceException('返回信息被篡改！');
		}

        $url = 'https://api.weibo.com/oauth2/accessToken';

		$data = array();
		$data['clientId'] = $this->appKey;
		$data['clientSecret'] = $this->appSecret;
		$data['grantType'] = 'authorizationCode';
		$data['redirectUri'] = url().'/?app=System&controller=User&action=sinaLoginCallback';
		$data['code'] = Request::get('code','');

		$libHttp = Be::getLib('Http');
		$response = $libHttp->post($url, $data); // 本步骤比较特殊，用 POST 发送
		$response = json_decode($response);

		if (isset($response->error)) {
            throw new ServiceException($response->errorCode.': '.$response->error);
		}

		return $response->accessToken;
	}

	public function getUid($accessToken)
	{
		$url = 'https://api.weibo.com/2/account/getUid.json';
		$url .= '?accessToken='.$accessToken;

		$libHttp = Be::getLib('Http');
		$response = $libHttp->get($url);
        $response = json_decode($response);

        if (isset($response->error)) {
            throw new ServiceException($response->errorCode.': '.$response->error);
        }

        return $response->uid;
	}


	public function getUserInfo($accessToken, $uid)
	{
		$url = 'https://api.weibo.com/2/users/show.json';
		$url .= '?accessToken='.$accessToken;
		$url .= '&uid='.$uid;

		$libHttp = Be::getLib('Http');
		$response = $libHttp->get($url);

		$response = json_decode($response);

        if (isset($response->error)) {
            throw new ServiceException($response->errorCode.': '.$response->error);
        }

		return $response;
	}

	public function register($userInfo)
	{
		$configUser = Be::getConfig('System', 'User');

		$t = time();
		$tupleUser = Be::newTuple('system_user');
		$tupleUser->connect = 'sina';
		$tupleUser->name = $userInfo->name;
		$tupleUser->register_time = $t;
		$tupleUser->last_visit_time = $t;
		$tupleUser->block = 0;
		$tupleUser->save();

		$libHttp = Be::getLib('Http');
		$response = $libHttp->get($userInfo->avatarLarge);

		$t = date('YmdHis', $t);
		
        $tmpAvatar = Be::getRuntime()->getDataPath().'/System/Tmp/user_connect_sina_'.$t.'_'.$tupleUser->id;
        file_put_contents($tmpAvatar, $response);

		$libImage = Be::getLib('Image');
		$libImage->open($tmpAvatar);
		if ($libImage->isImage()) {
			$libImage->resize($configUser->avatar_l_w, $configUser->avatar_l_h, 'north');
			$libImage->save(Be::getRuntime()->getDataPath().'/System/User/Avatar/'.$tupleUser->id.'_'.$t.'_l.'.$libImage->getType());
			$tupleUser->avatar_l = $tupleUser->id.'_'.$t.'_l.'.$libImage->getType();

			$libImage->resize($configUser->avatar_m_w, $configUser->avatar_m_h, 'north');
			$libImage->save(Be::getRuntime()->getDataPath().'/System/User/Avatar/'.$tupleUser->id.'_'.$t.'_m.'.$libImage->getType());
			$tupleUser->avatar_m= $tupleUser->id.'_'.$t.'_m.'.$libImage->getType();

			$libImage->resize($configUser->avatar_s_w, $configUser->avatar_s_h, 'north');
			$libImage->save(Be::getRuntime()->getDataPath().'/System/User/Avatar/'.$tupleUser->id.'_'.$t.'_s.'.$libImage->getType());
			$tupleUser->avatar_s = $tupleUser->id.'_'.$t.'_s.'.$libImage->getType();

			$tupleUser->save();
		}
		
		unlink($tmpAvatar);

		return $tupleUser;
	}

	public function systemLogin($userId)
	{
        Be::getService('System', 'User')->makeLogin($userId);
	}

}
