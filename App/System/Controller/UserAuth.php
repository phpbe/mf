<?php
namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\System\Controller;

class UserAuth extends Controller
{
    public function __construct()
    {
		$my = Be::getUser();
        if ($my->isGuest()) {
            Response::error('登陆超时，请重新登陆！', url('System.User.login', ['return'=>Request::url()]), -1);
		}
    }
}
