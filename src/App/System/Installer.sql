SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `system_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '应用名',
  `label` varchar(60) NOT NULL DEFAULT '' COMMENT '应用中文标识',
  `icon` varchar(60) NOT NULL DEFAULT '' COMMENT '应用图标',
  `install_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '安装时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用';

INSERT INTO `system_app` (`id`, `name`, `label`, `icon`, `install_time`) VALUES
(1, 'System', '系统', 'el-icon-s-tools', CURRENT_TIMESTAMP);

CREATE TABLE `system_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增编号',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户ID',
  `app` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '应用名',
  `controller` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '控制器名',
  `action` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '动作名',
  `content` VARCHAR(240) NOT NULL DEFAULT '' COMMENT '内容',
  `details` text NOT NULL DEFAULT '' COMMENT '明细',
  `ip` varchar(15) NOT NULL DEFAULT '' COMMENT 'IP',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='系统操作日志';

CREATE TABLE `system_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增编号',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '角色名',
  `note` varchar(120) NOT NULL DEFAULT '' COMMENT '备注',
  `permission` tinyint(4) NOT NULL COMMENT '权限',
  `permissions` text NOT NULL COMMENT '权限明细',
  `ordering` int(11) NOT NULL COMMENT '排序（越小越靠前）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户角色';
INSERT INTO `system_role` (`id`, `name`, `note`, `permission`, `permissions`, `ordering`) VALUES
(1, '超级管理员', '能执行所有操作', 1, '', 0);

CREATE TABLE `system_task`(
    `id` INT NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `driver` VARCHAR(240) NOT NULL DEFAULT '' COMMENT '驱动',
    `schedule` VARCHAR(30) NOT NULL DEFAULT '* * * * *' COMMENT '执行计划',
    `create_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_time` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY(`id`)
) ENGINE = InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='计划任务';

CREATE TABLE `system_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增编号',
  `username` varchar(120) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` char(40) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` char(32) NOT NULL DEFAULT '' COMMENT '密码盐值',
  `remember_me_token` char(32) NOT NULL DEFAULT '' COMMENT '记住我 Token',
  `role_id` INT NOT NULL DEFAULT '0' COMMENT '角色ID',
  `avatar` varchar(60) NOT NULL DEFAULT '' COMMENT '头像',
  `email` varchar(120) NOT NULL DEFAULT '' COMMENT '邮箱',
  `name` varchar(120) NOT NULL DEFAULT '' COMMENT '名称',
  `gender` tinyint(3) NOT NULL DEFAULT '-1' COMMENT '性别（0：女/1：男/-1：保密）',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '电话',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机',
  `is_enable` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否可用',
  `is_delete` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否已删除',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `last_login_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后一次登陆时间',
  `last_login_ip` VARCHAR(15) NOT NULL DEFAULT '' COMMENT '最后一次登录的IP',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户';

INSERT INTO `system_user` (`id`, `username`, `password`, `salt`, `remember_me_token`, `role_id`, `avatar`, `email`, `name`, `gender`, `phone`, `mobile`, `is_enable`, `is_delete`, `create_time`, `last_login_time`, `last_login_ip`) VALUES
(1, 'admin', 'a2ad3e6e3acf5b182324ed782f8a0556d43e59dd', 'ybFD7uzKMH8yvPHvuPNNT0vDv7uF2811', 'e3FLxEcsEd2DbLOQEpG8EhGkKj9p5k2J', 1, '', 'be@phpbe.com', '管理员', 0, '', '', 1, 0, CURRENT_TIMESTAMP, '0000-00-00 00:00:00', '127.0.0.1');

CREATE TABLE `system_user_login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增编号',
  `username` varchar(120) NOT NULL DEFAULT '' COMMENT '用户名',
  `success` tinyint(1) NOT NULL COMMENT '是否登录成功（0-不成功/1-成功）',
  `description` varchar(240) NOT NULL DEFAULT '' COMMENT '描述',
  `ip` varchar(15) NOT NULL DEFAULT '' COMMENT 'IP',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户登录日志';

