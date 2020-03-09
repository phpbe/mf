<?php
namespace App\System\Controller;

use Be\System\Be;
use Be\System\Response;
use Be\System\Controller;

class Home extends Controller
{
	public function home()
	{
		$configSystem = Be::getConfig('System', 'System');
		Response::setTitle($configSystem->homeTitle);
        Response::setMetaKeywords($configSystem->homeMetaKeywords);
        Response::setMetaDescription($configSystem->homeMetaDescription);
        Response::display();
	}
}
