<?php

namespace Be\App\System\Service;


use Be\System\Db\Tuple;
use Be\System\Service\ServiceException;
use Be\System\Be;

class AdminRole extends \Be\System\Service
{

    /**
     * 获取角色
     *
     * @return Tuple
     */
    public function getRole($roleId)
    {
        return Be::newTuple('system_admin_role')->load($roleId);
    }

    /**
     * 获取角色列表
     *
     * @return array
     */
    public function getRoles()
    {
        return Be::newTable('system_admin_role')->orderBy('ordering', 'ASC')->getObjects();
    }


    /**
     * 获取角色銉值对
     *
     * @return array
     */
    public function getRoleKeyValues()
    {
        return Be::newTable('system_admin_role')->orderBy('ordering', 'ASC')->getKeyValues('id', 'name');
    }


    /**
     * 更新所有角色缓存
     */
    public function updateAdminRoles()
    {
        $roles = $this->getRoles();
        foreach ($roles as $role) {
            $this->updateAdminRole($role->id);
        }
    }

    /**
     * 更新指定角色到文件缓存中
     *
     * @param $roleId
     * @throws ServiceException
     */
    public function updateAdminRole($roleId)
    {
        $tuple = Be::newTuple('system_admin_role');
        $tuple->load($roleId);
        if (!$tuple->id) {
            throw new ServiceException('未找到指定编号（#' . $roleId . '）的后台角色！');
        }

        $code = '<?php' . "\n";
        $code .= 'namespace Cache\\System\\AdminRole;' . "\n";
        $code .= "\n";
        $code .= 'class AdminRole' . $roleId . ' extends \\Be\\System\\AdminRole' . "\n";
        $code .= '{' . "\n";
        $code .= '  public $name = \'' . $tuple->name . '\';' . "\n";
        $code .= '  public $permission = \'' . $tuple->permission . '\';' . "\n";
        if ($tuple->permission == -1) {
            $code .= '  public $permissions = [\'' . implode('\',\'', explode(',', $tuple->permissions)) . '\'];' . "\n";
        } else {
            $code .= '  public $permissions = [];' . "\n";
        }
        $code .= '}' . "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/AdminRole/AdminRole' . $roleId . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);
    }

}
