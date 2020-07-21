SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `system_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(60) NOT NULL COMMENT '应用名',
  `label` varchar(60) NOT NULL COMMENT '应用中文标识',
  `icon` varchar(60) NOT NULL COMMENT '应用图标',
  `install_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '安装时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用';

CREATE TABLE `system_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(240) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `system_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `note` varchar(120) NOT NULL,
  `permission` tinyint(4) NOT NULL,
  `permissions` text NOT NULL,
  `ordering` int(11) NOT NULL COMMENT '排序（越小越靠前）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
INSERT INTO `system_role` (`id`, `name`, `note`, `permission`, `permissions`, `ordering`) VALUES
(1, '超级管理员', '能执行所有操作', 1, '', 0);

CREATE TABLE `system_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增编号',
  `username` varchar(120) NOT NULL COMMENT '用户名',
  `password` char(40) NOT NULL COMMENT '密码',
  `salt` char(32) NOT NULL COMMENT '密码盐值',
  `remember_me_token` char(32) NOT NULL COMMENT '记住我 Token',
  `avatar_s` varchar(60) NOT NULL COMMENT '大头像',
  `avatar_m` varchar(60) NOT NULL COMMENT '中头像',
  `avatar_l` varchar(60) NOT NULL COMMENT '小头像',
  `email` varchar(120) NOT NULL COMMENT '邮箱',
  `name` varchar(120) NOT NULL COMMENT '名称',
  `gender` tinyint(3) NOT NULL DEFAULT '-1' COMMENT '性别（0：女/1：男/-1：保密）',
  `phone` varchar(20) NOT NULL COMMENT '电话',
  `mobile` varchar(20) NOT NULL COMMENT '手机',
  `block` tinyint(3) unsigned NOT NULL COMMENT '是否屏蔽',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `last_login_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后一次登陆时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `system_user` (`id`, `username`, `password`, `salt`, `remember_me_token`, `avatar_s`, `avatar_m`, `avatar_l`, `email`, `name`, `gender`, `phone`, `mobile`, `block`, `create_time`, `last_login_time`) VALUES
(1, 'admin', 'a2ad3e6e3acf5b182324ed782f8a0556d43e59dd', 'ybFD7uzKMH8yvPHvuPNNT0vDv7uF2811', 'L0IfMTHH1CziwVAb3w7m5Hoi3NMhNwMZ', '1_20140407160313_s.jpg', '1_20140407160313_m.jpg', '1_20140407160313_l.jpg', 'iua1024@gmail.com', '谁谁谁', 0, '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00');


CREATE TABLE `system_user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(120) NOT NULL,
  `success` tinyint(1) NOT NULL,
  `description` varchar(240) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `system_user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(11) NOT NULL COMMENT '后台管理员ID',
  `role_id` int(11) NOT NULL COMMENT '后台角色ID',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `system_user_role` (`id`, `user_id`, `role_id`) VALUES
(1, 1, 1);