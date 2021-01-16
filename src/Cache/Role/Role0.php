<?php
namespace Be\Mf\Cache\Role;

class Role0 extends \Be\Mf\User\Role
{
  public $name = '公共功能';
  public $permission = '-1';
  public $permissions = ['System.Installer.detect','System.Installer.installDb','System.Installer.testDb','System.Installer.installApp','System.Plugin.uploadFile','System.Plugin.uploadAvatar','System.Plugin.uploadImage','System.System.dashboard','System.System.historyBack','System.User.login','System.User.logout'];
}
