<?php
namespace controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Db;

class Setup extends \Be\System\Controller
{

	public function __construct()
	{
		$action = Request::_('action');
		if ($action!='complete') {
			db::connect();
			if (!db::hasError()) {
				$tuples = db::getTables();
				//printR($tuples);
				//if (in_array($tuples, 'beUser')) $this->redirect(url('controller=setup&action=complete'));
			}
		}
	}

	public function index()
	{
		$this->setting();
	}


	public function setting()	// 配置数据库
	{
		$template = Be::getTemplate('setup.setting');
		$template->setTitle('配置数据库');
		$template->display();
	}


	public function settingSave()	// 保存配置
	{
		$configDb = Be::getConfig('db');
		$configDb->dbHost = Request::post('dbHost', '');
		$configDb->dbUser = Request::post('dbUser', '');
		$configDb->dbPass = Request::post('dbPass', '');
		$configDb->dbName = Request::post('dbName', '');

		$serviceSetup = Be::getService('setup');
		$serviceSetup->saveConfig($configDb, Be::getRuntime()->getRootPath() . '/configs/db.php');

		db::connect();
		if (db::hasError()) {
			$this->setMessage(db::getError(), 'error');
			$this->redirect(url('controller=setup&action=setting'));
		} else {
			$serviceSetup->install();
			$this->redirect(url('controller=setup&action=complete'));
		}
	}


	public function complete()
	{
		$template = Be::getTemplate('setup.complete');
		$template->setTitle('完成配置');
		$template->display();
		
		/*
		$path = BONE_ROOT'/setup.html';
		if (file_exists($path)) @unlink($path);

		$path = BONE_ROOT'/apps/setup';
		if (file_exists($path)) {
			$fso = Be::getLib('fso');
			$fso->rmDir($path);
		}
		*/

	}




}
