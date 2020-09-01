<?php

namespace Be\App\System\Service;


use Be\App\System\Helper\DocComment;
use Be\System\Db\Tuple;
use Be\System\Exception\ServiceException;
use Be\System\Be;

class Role extends \Be\System\Service
{

    /**
     * 获取角色
     *
     * @return Tuple
     */
    public function getRole($roleId)
    {
        return Be::newTuple('system_role')->load($roleId);
    }

    /**
     * 获取角色列表
     *
     * @return array
     */
    public function getRoles()
    {
        return Be::newTable('system_role')->orderBy('ordering', 'ASC')->getObjects();
    }

    /**
     * 获取角色銉值对
     *
     * @return array
     */
    public function getRoleKeyValues()
    {
        return Be::newTable('system_role')->orderBy('ordering', 'ASC')->getKeyValues('id', 'name');
    }

    /**
     * 更新所有角色缓存
     */
    public function updateRoles()
    {
        $roles = $this->getRoles();
        foreach ($roles as $role) {
            $this->updateRole($role->id);
        }
    }

    /**
     * 更新指定角色到文件缓存中
     *
     * @param $roleId
     * @throws ServiceException
     */
    public function updateRole($roleId)
    {
        if ($roleId == 0) {
            $this->updateRole0();
            return;
        }

        $tuple = Be::newTuple('system_role');
        $tuple->load($roleId);
        if (!$tuple->id) {
            throw new ServiceException('未找到指定编号（#' . $roleId . '）的角色！');
        }

        $code = '<?php' . "\n";
        $code .= 'namespace Be\\Cache\\System\\Role;' . "\n";
        $code .= "\n";
        $code .= 'class Role' . $roleId . ' extends \\Be\\System\\Role' . "\n";
        $code .= '{' . "\n";
        $code .= '  public $name = \'' . $tuple->name . '\';' . "\n";
        $code .= '  public $permission = \'' . $tuple->permission . '\';' . "\n";
        if ($tuple->permission == -1) {
            $code .= '  public $permissions = [\'' . implode('\',\'', explode(',', $tuple->permissions)) . '\'];' . "\n";
        } else {
            $code .= '  public $permissions = [];' . "\n";
        }
        $code .= '}' . "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/Role/Role' . $roleId . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);
    }

    /**
     * 公共用户权限
     */
    public function updateRole0()
    {
        $permissions = [];

        $apps = Be::getService('System.App')->getApps();
        foreach ($apps as $app) {
            $appName = $app->name;
            $appProperty = Be::getProperty('App.'.$appName);
            $controllerDir = Be::getRuntime()->getRootPath() . $appProperty->getPath(). '/Controller';
            if (!file_exists($controllerDir) && !is_dir($controllerDir)) continue;
            $controllers = scandir($controllerDir);
            foreach ($controllers as $controller) {
                if ($controller == '.' || $controller == '..' || is_dir($controllerDir . '/' . $controller)) continue;

                $controller = substr($controller, 0, -4);
                $className = 'Be\\App\\' . $appName . '\\Controller\\' . $controller;
                if (!class_exists($className)) continue;

                $reflection = new \ReflectionClass($className);
                $classMenuGroup = [];

                // 类注释
                $classComment = $reflection->getDocComment();
                $parseClassComments = DocComment::parse($classComment);

                $permission = 0;
                foreach ($parseClassComments as $key => $val) {
                    if ($key == 'BePermissionGroup') {
                        if (is_array($val[0]) && isset($val[0]['value']) && $val[0]['value'] == '*') {
                            $permission = 1;
                            break;
                        }
                    }
                }

                $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($methods as &$method) {
                    $methodName = $method->getName();
                    if ($permission == 1) {
                        $permissions[] = $appName . '.' . $controller . '.' . $methodName;
                    } else {
                        $methodComment = $method->getDocComment();
                        $methodComments = DocComment::parse($methodComment);
                        foreach ($methodComments as $key => $val) {
                            if ($key == 'BePermission') {
                                if (is_array($val[0]) && isset($val[0]['value']) && $val[0]['value'] == '*') {
                                    $permissions[] = $appName . '.' . $controller . '.' . $methodName;
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        $code = '<?php' . "\n";
        $code .= 'namespace Be\\Cache\\System\\Role;' . "\n";
        $code .= "\n";
        $code .= 'class Role0 extends \\Be\\System\\Role' . "\n";
        $code .= '{' . "\n";
        $code .= '  public $name = \'公共功能\';' . "\n";
        $code .= '  public $permission = \'-1\';' . "\n";
        $code .= '  public $permissions = [\'' . implode('\',\'',  $permissions) . '\'];' . "\n";
        $code .= '}' . "\n";

        $path = Be::getRuntime()->getCachePath() . '/System/Role/Role0.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);
    }

}
