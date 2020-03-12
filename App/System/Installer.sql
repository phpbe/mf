CREATE TABLE `system_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(60) NOT NULL COMMENT '应用名',
  `label` varchar(60) NOT NULL COMMENT '应用中文标识',
  `icon` varchar(60) NOT NULL COMMENT '应用图标',
  `install_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '安装时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用';

