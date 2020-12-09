<?php

namespace Be\App\System\Controller;

use Be\Plugin\Detail\Item\DetailItemIcon;
use Be\Plugin\Form\Item\FormItemAutoComplete;
use Be\Plugin\Form\Item\FormItemCustom;
use Be\Plugin\Form\Item\FormItemInputNumberInt;
use Be\Plugin\Form\Item\FormItemInputPassword;
use Be\System\Be;
use Be\System\Request;
use Be\System\Response;
use Be\Util\Random;

class Installer
{

    private $steps = null;

    public function __construct()
    {
        $config = Be::getConfig('System.System');
        if (!$config->developer || !$config->installable) {
            Response::end('请先开启系统配置中的 "开发者模式" 和 "可安装及重装" 配置项！');
        }

        $this->steps = ['环境检测', '配置数据库', '安装应用', '配置系统', '完成'];
    }

    public function index()
    {
        Response::redirect(beUrl('System.Installer.detect'));
    }

    /**
     * 检测环境
     *
     * @BePermission("*")
     */
    public function detect()
    {
        if (Request::isPost()) {
            Response::redirect(beUrl('System.Installer.installDb'));
        } else {
            $runtime = Be::getRuntime();
            $value = [];
            $value['isPhpVersionGtMatch'] = version_compare(PHP_VERSION, '7.0.0') >= 0 ? 1 : 0;
            $value['isPdoMysqlInstalled'] = extension_loaded('pdo_mysql') ? 1 : 0;
            $value['isCacheDirWritable'] = is_writable($runtime->getCachePath()) ? 1 : 0;
            $value['isDataDirWritable'] = is_writable($runtime->getDataPath()) ? 1 : 0;
            $isAllPassed = array_sum($value) == count($value);

            Response::set('steps', $this->steps);
            Response::set('step', 1);

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
                                    'icon' => [
                                        'style' => 'color:' . ($value['isPhpVersionGtMatch'] ? '#67C23A' : '#F56C6C')
                                    ]
                                ]
                            ],
                            [
                                'label' => 'PDO Mysql 扩展',
                                'driver' => DetailItemIcon::class,
                                'value' => $value['isPdoMysqlInstalled'] ? 'el-icon-check' : 'el-icon-close',
                                'ui' => [
                                    'icon' => [
                                        'style' => 'color:' . ($value['isPdoMysqlInstalled'] ? '#67C23A' : '#F56C6C')
                                    ]
                                ]
                            ],
                            [
                                'label' => 'Cache 目录可写',
                                'driver' => DetailItemIcon::class,
                                'value' => $value['isCacheDirWritable'] ? 'el-icon-check' : 'el-icon-close',
                                'ui' => [
                                    'icon' => [
                                        'style' => 'color:' . ($value['isCacheDirWritable'] ? '#67C23A' : '#F56C6C')
                                    ]
                                ]
                            ],
                            [
                                'label' => 'Data 目录可写',
                                'driver' => DetailItemIcon::class,
                                'value' => $value['isDataDirWritable'] ? 'el-icon-check' : 'el-icon-close',
                                'ui' => [
                                    'icon' => [
                                        'style' => 'color:' . ($value['isDataDirWritable'] ? '#67C23A' : '#F56C6C')
                                    ]
                                ]
                            ],
                        ],
                        'actions' => [
                            [
                                'label' => '继续安装',
                                'target' => 'self',
                                'ui' => [
                                    'button' => [
                                        'type' => $isAllPassed ? 'success' : 'danger',
                                        ':disabled' => $isAllPassed ? 'false' : 'true',
                                    ]
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
        if (Request::isPost()) {

            $postData = Request::json();
            $formData = $postData['formData'];

            $configDb = Be::getConfig('System.Db');
            foreach ($configDb->master as $k => $v) {
                if (isset($formData[$k])) {
                    $configDb->master->$k = $formData[$k];
                }
            }

            Be::getService('System.Config')->save('System.Db', $configDb);

            Response::redirect(beUrl('System.Installer.installApp'));

        } else {
            Response::set('steps', $this->steps);
            Response::set('step', 1);

            Be::getPlugin('Form')
                ->setting([
                    'title' => '系统数据库配置',
                    'theme' => 'Installer',
                    'form' => [
                        'ui' => [
                            'label-width' => '200px',
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
                                'name' => '',
                                'label' => '继续安装',
                                '@click' => 'submit',
                                'ui' => [
                                    'button' => [
                                        'type' => 'success'
                                    ]
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
                                        _this.formItems.db_name.suggestions = suggestions;
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
        try {
            $postData = Request::json();
            $databases = Be::getService('System.Installer')->testDb($postData['formData']);
            Response::set('success', true);
            Response::set('data', [
                'databases' => $databases,
            ]);
            Response::json();
        } catch (\Exception $e) {
            Response::set('success', false);
            Response::set('message', $e->getMessage());
            Response::json();
        }
    }

    /**
     * 安装应用
     *
     * @BePermission("*")
     */
    public function installApp()
    {
        if (Request::isPost()) {
            $postData = Request::json();
            $formData = $postData['formData'];

            $service = Be::getService('System.Installer');
            if (isset($formData['appNames']) && is_array($formData['appNames']) && count($formData['appNames'])) {
                foreach ($formData['appNames'] as $appName) {
                    $service->installApp($appName);
                }
            }

            Response::set('success', true);
            Response::set('message', '安装完成');
            Response::set('data', [
                'redirectUrl' => beUrl('System.Installer.setting'),
            ]);
            Response::json();
        } else {
            Response::set('steps', $this->steps);
            Response::set('step', 3);

            $appProperties = [];
            $appProperties['System'] = Be::getProperty('App.System');
            $appNames = Be::getService('System.Installer')->getAppNames();
            foreach ($appNames as $appName) {
                $appProperties[$appName] = Be::getProperty('App.' . $appName);
            }

            Response::set('appProperties', $appProperties);
            Response::display();
        }
    }


    /**
     * 配置系统
     */
    public function setting()
    {
        $tuple = Be::getTuple('system_user');
        $tuple->load(1);

        if (Request::isPost()) {
            $postData = Request::json();
            $formData = $postData['formData'];

            $tuple->username = $formData['username'];
            $tuple->salt = Random::complex(32);
            $tuple->password = Be::getService('System.User')->encryptPassword($formData['password'], $tuple->salt);
            $tuple->name = $formData['name'];
            $tuple->email = $formData['email'];
            $tuple->update_time = date('Y-m-d H:i:s');
            $tuple->update();

            Response::redirect(beUrl('System.Installer.complete'));
        } else {
            Response::set('steps', $this->steps);
            Response::set('step', 4);

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
                                '@click' => 'submit',
                                'ui' => [
                                    'button' => [
                                        'type' => 'success'
                                    ]
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
        $config = Be::getConfig('System.System');
        $config->installable = false;
        Be::getService('System.Config')->save('System.System', $config);

        Response::set('steps', $this->steps);
        Response::set('step', 5);
        Response::set('url', beUrl());
        Response::display();
    }


}