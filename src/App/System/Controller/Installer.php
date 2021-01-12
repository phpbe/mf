<?php

namespace Be\Mf\App\System\Controller;

use Be\F\Plugin\Detail\Item\DetailItemIcon;
use Be\F\Plugin\Form\Item\FormItemAutoComplete;
use Be\F\Plugin\Form\Item\FormItemCustom;
use Be\F\Plugin\Form\Item\FormItemInputNumberInt;
use Be\F\Plugin\Form\Item\FormItemInputPassword;
use Be\Mf\Be;
use Be\F\Request;
use Be\F\Response;
use Be\F\Util\Random;

class Installer
{

    private $steps = null;

    public function __construct()
    {
        $response = Be::getResponse();

        $config = Be::getConfig('System.System');
        if (!$config->developer || !$config->installable) {
            $response->end('请先开启系统配置中的 "开发者模式" 和 "可安装及重装" 配置项！');
        }

        $this->steps = ['环境检测', '配置数据库', '安装应用', '配置系统', '完成'];
    }

    public function index()
    {
        $response = Be::getResponse();
        $response->redirect(beUrl('System.Installer.detect'));
    }

    /**
     * 检测环境
     *
     * @BePermission("*")
     */
    public function detect()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isPost()) {
            $response->redirect(beUrl('System.Installer.installDb'));
        } else {
            $runtime = Be::getRuntime();
            $value = [];
            $value['isPhpVersionGtMatch'] = version_compare(PHP_VERSION, '7.0.0') >= 0 ? 1 : 0;
            $value['isPdoMysqlInstalled'] = extension_loaded('pdo_mysql') ? 1 : 0;
            $value['isCacheDirWritable'] = is_writable($runtime->getCachePath()) ? 1 : 0;
            $value['isDataDirWritable'] = is_writable($runtime->getDataPath()) ? 1 : 0;
            $isAllPassed = array_sum($value) == count($value);

            $response->set('steps', $this->steps);
            $response->set('step', 0);

            Be::getPlugin('Detail')
                ->setting([
                    'title' => '系统数据库配置',
                    'theme' => 'Installer',
                    'form' => [
                        'ui' => [
                            'label-width' => '300px',
                        ],
                        'items' => [
                            [
                                'label' => 'PHP版本（7.0+）',
                                'driver' => DetailItemIcon::class,
                                'value' => $value['isPhpVersionGtMatch'] ? 'el-icon-check' : 'el-icon-close',
                                'ui' => [
                                    'style' => 'color:' . ($value['isPhpVersionGtMatch'] ? '#67C23A' : '#F56C6C')
                                ]
                            ],
                            [
                                'label' => 'PDO Mysql 扩展',
                                'driver' => DetailItemIcon::class,
                                'value' => $value['isPdoMysqlInstalled'] ? 'el-icon-check' : 'el-icon-close',
                                'ui' => [
                                    'style' => 'color:' . ($value['isPdoMysqlInstalled'] ? '#67C23A' : '#F56C6C')
                                ]
                            ],
                            [
                                'label' => 'Cache 目录可写',
                                'driver' => DetailItemIcon::class,
                                'value' => $value['isCacheDirWritable'] ? 'el-icon-check' : 'el-icon-close',
                                'ui' => [
                                    'style' => 'color:' . ($value['isCacheDirWritable'] ? '#67C23A' : '#F56C6C')
                                ]
                            ],
                            [
                                'label' => 'Data 目录可写',
                                'driver' => DetailItemIcon::class,
                                'value' => $value['isDataDirWritable'] ? 'el-icon-check' : 'el-icon-close',
                                'ui' => [
                                    'style' => 'color:' . ($value['isDataDirWritable'] ? '#67C23A' : '#F56C6C')
                                ]
                            ],
                        ],
                        'actions' => [
                            [
                                'label' => '继续安装',
                                'target' => 'self',
                                'ui' => [
                                    'type' => $isAllPassed ? 'primary' : 'danger',
                                    ':disabled' => $isAllPassed ? 'false' : 'true',
                                ]
                            ]
                        ]

                    ],
                ])
                ->execute();
        }
    }

    /**
     * 系统安装
     *
     * @BePermission("*")
     */
    public function installDb()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isPost()) {

            $postData = $request->post('data', '', '');
            $postData = json_decode($postData, true);
            $formData = $postData['formData'];

            $configDb = Be::getConfig('System.Db');
            foreach ($configDb->master as $k => $v) {
                if (isset($formData[$k])) {
                    $configDb->master[$k] = $formData[$k];
                }
            }

            Be::getService('System.Config')->save('System.Db', $configDb);

            $response->redirect(beUrl('System.Installer.installApp'));

        } else {
            $response->set('steps', $this->steps);
            $response->set('step', 1);

            Be::getPlugin('Form')
                ->setting([
                    'title' => '系统数据库配置',
                    'theme' => 'Installer',
                    'form' => [
                        'ui' => [
                            'label-width' => '200px',
                            'style' => 'width: 600px',
                        ],
                        'items' => [
                            [
                                'name' => 'host',
                                'label' => '主机名',
                                'required' => true,
                            ],
                            [
                                'name' => 'port',
                                'label' => '端口号',
                                'driver' => FormItemInputNumberInt::class,
                                'required' => true,
                                'value' => 3306
                            ],
                            [
                                'name' => 'username',
                                'label' => '用户名',
                                'required' => true,
                            ],
                            [
                                'name' => 'password',
                                'label' => '密码',
                                'required' => true,
                            ],
                            [
                                'name' => 'testDb',
                                'driver' => FormItemCustom::class,
                                'html' => '<el-form-item><el-button type="success" @click="testDb" v-loading="testDbLoading" size="mini" plain>测试连接，并获取库名列表</el-button></el-form-item>'
                            ],
                            [
                                'name' => 'name',
                                'label' => '库名',
                                'driver' => FormItemAutoComplete::class,
                                'required' => true,
                            ],
                        ],
                        'actions' => [
                            [
                                'label' => '继续安装',
                                'target' => 'self',
                                'ui' => [
                                    'type' => 'primary',
                                    '@click' => 'submit',
                                ]
                            ]
                        ]
                    ],
                    'vueData' => [
                        'testDbLoading' => false, // 测试数据库连接中
                    ],
                    'vueMethods' => [
                        'testDb' => 'function() {
                        var _this = this;
                        this.testDbLoading = true;
                        this.$http.post("./?route=System.Installer.testDb", {
                                formData: _this.formData
                            }).then(function (response) {
                                _this.testDbLoading = false;
                                //console.log(response);
                                if (response.status == 200) {
                                    var responseData = response.data;
                                    if (responseData.success) {
                                        var message;
                                        if (responseData.message) {
                                            message = responseData.message;
                                        } else {
                                            message = \'连接成功！\';
                                        }
                                        _this.$message.success(message);
                                        var suggestions = [];
                                        for(var x in responseData.data.databases) {
                                            suggestions.push({
                                                "value" : responseData.data.databases[x]
                                            });
                                        }
                                        _this.formItems.name.suggestions = suggestions;
                                    } else {
                                        if (responseData.message) {
                                            _this.$message.error(responseData.message);
                                        }
                                    }
                                }
                            }).catch(function (error) {
                                _this.testDbLoading = false;
                                _this.$message.error(error);
                            });
                    }',
                    ],
                ])
                ->setValue(Be::getConfig('System.Db')->master)
                ->execute();
        }
    }

    /**
     * @BePermission("*")
     */
    public function testDb()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        try {
            $postData = $request->json();
            $databases = Be::getService('System.Installer')->testDb($postData['formData']);
            $response->set('success', true);
            $response->set('data', [
                'databases' => $databases,
            ]);
            $response->json();
        } catch (\Exception $e) {
            $response->set('success', false);
            $response->set('message', $e->getMessage());
            $response->json();
        }
    }

    /**
     * 安装应用
     *
     * @BePermission("*")
     */
    public function installApp()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        if ($request->isPost()) {
            $postData = $request->post('data', '', '');
            $postData = json_decode($postData, true);
            $formData = $postData['formData'];

            $service = Be::getService('System.Installer');
            if (isset($formData['appNames']) && is_array($formData['appNames']) && count($formData['appNames'])) {
                foreach ($formData['appNames'] as $appName) {
                    $service->installApp($appName);
                }
            }

            $response->set('success', true);
            $response->set('message', '安装完成');
            $response->set('data', [
                'redirectUrl' => beUrl('System.Installer.setting'),
            ]);
            $response->json();
        } else {
            $response->set('steps', $this->steps);
            $response->set('step', 2);

            $appProperties = [];
            $appProperties[] = (array)Be::getProperty('App.System');
            $appNames = Be::getService('System.Installer')->getAppNames();
            foreach ($appNames as $appName) {
                $appProperties[] = (array)Be::getProperty('App.' . $appName);
            }

            $response->set('appProperties', $appProperties);
            $response->display('App.System.Installer.installApp', 'Installer');
        }
    }


    /**
     * 配置系统
     */
    public function setting()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $tuple = Be::getTuple('system_user');
        $tuple->load(1);

        if ($request->isPost()) {
            $postData = $request->post('data', '', '');
            $postData = json_decode($postData, true);
            $formData = $postData['formData'];

            $tuple->username = $formData['username'];
            $tuple->salt = Random::complex(32);
            $tuple->password = Be::getService('System.User')->encryptPassword($formData['password'], $tuple->salt);
            $tuple->name = $formData['name'];
            $tuple->email = $formData['email'];
            $tuple->update_time = date('Y-m-d H:i:s');
            $tuple->update();

            $response->redirect(beUrl('System.Installer.complete'));
        } else {
            $response->set('steps', $this->steps);
            $response->set('step', 4);

            Be::getPlugin('Form')
                ->setting([
                    'title' => '后台账号',
                    'theme' => 'Installer',
                    'form' => [
                        'ui' => [
                            'label-width' => '200px',
                        ],
                        'items' => [
                            [
                                'name' => 'username',
                                'label' => '超级管理员账号',
                                'required' => true,
                            ],
                            [
                                'name' => 'password',
                                'label' => '密码',
                                'required' => true,
                                'value' => 'admin',
                            ],
                            [
                                'name' => 'name',
                                'label' => '名称',
                            ],
                            [
                                'name' => 'email',
                                'label' => '邮箱',
                            ],
                        ],
                        'actions' => [
                            [
                                'label' => '完成安装',
                                'ui' => [
                                    'type' => 'success',
                                    '@click' => 'submit',
                                ]
                            ]
                        ]
                    ],
                ])
                ->setValue($tuple)
                ->execute();
        }
    }


    /**
     * 安装完成
     */
    public function complete()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $config = Be::getConfig('System.System');
        $config->installable = false;
        Be::getService('System.Config')->save('System.System', $config);

        $response->set('steps', $this->steps);
        $response->set('step', 5);
        $response->set('url', beUrl());
        $response->display();
    }


}