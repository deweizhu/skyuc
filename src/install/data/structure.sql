SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


DROP TABLE IF EXISTS `skyuc_account_log`;
CREATE TABLE IF NOT EXISTS `skyuc_account_log` (
  `log_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `user_money` decimal(10,2) NOT NULL,
  `pay_point` mediumint(9) NOT NULL,
  `change_time` int(10) unsigned NOT NULL,
  `change_desc` varchar(255) NOT NULL,
  `change_type` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_ad`;
CREATE TABLE IF NOT EXISTS `skyuc_ad` (
  `ad_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `position_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `media_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ad_name` varchar(60) NOT NULL DEFAULT '',
  `ad_link` varchar(255) NOT NULL DEFAULT '',
  `ad_code` text NOT NULL,
  `start_date` int(10) unsigned NOT NULL,
  `end_date` int(10) unsigned NOT NULL,
  `link_man` varchar(60) NOT NULL DEFAULT '',
  `link_email` varchar(60) NOT NULL DEFAULT '',
  `link_phone` varchar(60) NOT NULL DEFAULT '',
  `click_count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`ad_id`),
  KEY `position_id` (`position_id`),
  KEY `enabled` (`enabled`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_admin`;
CREATE TABLE IF NOT EXISTS `skyuc_admin` (
  `user_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `password` varchar(32) NOT NULL,
  `join_time` int(10) unsigned NOT NULL DEFAULT '0',
  `last_time` int(10) unsigned NOT NULL DEFAULT '0',
  `last_ip` varchar(15) NOT NULL,
  `action_list` text NOT NULL,
  `nav_list` text NOT NULL,
  `lang_type` varchar(50) NOT NULL,
  `todolist` longtext NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_adminutil`;
CREATE TABLE IF NOT EXISTS `skyuc_adminutil` (
  `title` varchar(50) NOT NULL DEFAULT '',
  `text` mediumtext,
  PRIMARY KEY (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_admin_action`;
CREATE TABLE IF NOT EXISTS `skyuc_admin_action` (
  `action_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `action_code` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`action_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=53 ;

DROP TABLE IF EXISTS `skyuc_admin_log`;
CREATE TABLE IF NOT EXISTS `skyuc_admin_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_time` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `log_info` varchar(255) NOT NULL DEFAULT '',
  `ip_address` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`log_id`),
  KEY `log_time` (`log_time`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_admin_message`;
CREATE TABLE IF NOT EXISTS `skyuc_admin_message` (
  `message_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `receiver_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `send_date` int(10) unsigned NOT NULL,
  `read_date` int(10) unsigned NOT NULL,
  `readed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `title` varchar(150) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `sender_id` (`sender_id`,`receiver_id`),
  KEY `receiver_id` (`receiver_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_adsense`;
CREATE TABLE IF NOT EXISTS `skyuc_adsense` (
  `from_ad` smallint(5) NOT NULL DEFAULT '0',
  `referer` varchar(255) NOT NULL DEFAULT '',
  `clicks` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `from_ad` (`from_ad`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_ad_position`;
CREATE TABLE IF NOT EXISTS `skyuc_ad_position` (
  `position_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `position_name` varchar(60) NOT NULL DEFAULT '',
  `ad_width` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ad_height` smallint(5) unsigned NOT NULL DEFAULT '0',
  `position_desc` varchar(255) NOT NULL DEFAULT '',
  `position_style` text NOT NULL,
  PRIMARY KEY (`position_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_article`;
CREATE TABLE IF NOT EXISTS `skyuc_article` (
  `article_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `title` varchar(150) NOT NULL DEFAULT '',
  `content` longtext NOT NULL,
  `author` varchar(30) NOT NULL DEFAULT '',
  `author_email` varchar(60) NOT NULL DEFAULT '',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `article_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_open` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `file_url` varchar(255) NOT NULL DEFAULT '',
  `open_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `link` varchar(255) NOT NULL,
  PRIMARY KEY (`article_id`),
  KEY `cat_id` (`cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

DROP TABLE IF EXISTS `skyuc_article_cat`;
CREATE TABLE IF NOT EXISTS `skyuc_article_cat` (
  `cat_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(255) NOT NULL,
  `cat_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `cat_desc` varchar(255) NOT NULL DEFAULT '',
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `show_in_nav` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `parent_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  KEY `cat_type` (`cat_type`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

DROP TABLE IF EXISTS `skyuc_card`;
CREATE TABLE IF NOT EXISTS `skyuc_card` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cardid` varchar(30) DEFAULT NULL,
  `cardpass` varchar(30) DEFAULT NULL,
  `rank_id` smallint(1) unsigned DEFAULT NULL,
  `cardvalue` smallint(5) unsigned DEFAULT NULL,
  `money` smallint(5) unsigned DEFAULT NULL,
  `addtime` int(10) unsigned NOT NULL,
  `endtime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cardtype` (`rank_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_card_log`;
CREATE TABLE IF NOT EXISTS `skyuc_card_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cardid` varchar(30) DEFAULT NULL,
  `cardpass` varchar(30) DEFAULT NULL,
  `rank_id` smallint(1) unsigned DEFAULT NULL,
  `cardvalue` smallint(5) unsigned DEFAULT NULL,
  `money` smallint(5) unsigned DEFAULT NULL,
  `addtime` int(10) unsigned DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `userip` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cardtype` (`rank_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_category`;
CREATE TABLE IF NOT EXISTS `skyuc_category` (
  `cat_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(90) NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `style` varchar(150) NOT NULL,
  `cat_desc` varchar(255) NOT NULL,
  `parent_id` smallint(5) unsigned DEFAULT NULL,
  `sort_order` tinyint(1) NOT NULL,
  `is_show` tinyint(1) unsigned NOT NULL,
  `show_in_nav` tinyint(1) NOT NULL,
  PRIMARY KEY (`cat_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=33 ;

DROP TABLE IF EXISTS `skyuc_comment`;
CREATE TABLE IF NOT EXISTS `skyuc_comment` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comment_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id_value` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `email` varchar(60) NOT NULL DEFAULT '',
  `user_name` varchar(60) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_address` varchar(15) NOT NULL DEFAULT '',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `agree` int(10) unsigned NOT NULL DEFAULT '0',
  `against` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`),
  KEY `parent_id` (`parent_id`),
  KEY `id_value` (`id_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_co_html`;
CREATE TABLE IF NOT EXISTS `skyuc_co_html` (
  `aid` int(10) NOT NULL AUTO_INCREMENT,
  `nid` int(10) NOT NULL DEFAULT '0',
  `title` varchar(60) NOT NULL DEFAULT '',
  `litpic` varchar(100) NOT NULL,
  `url` varchar(150) NOT NULL DEFAULT '',
  `dtime` int(10) NOT NULL DEFAULT '0',
  `isdown` smallint(6) NOT NULL DEFAULT '0',
  `isexport` smallint(6) NOT NULL DEFAULT '0',
  `result` mediumtext NOT NULL,
  PRIMARY KEY (`aid`),
  KEY `nid` (`nid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_co_listen`;
CREATE TABLE IF NOT EXISTS `skyuc_co_listen` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nid` smallint(5) NOT NULL DEFAULT '0',
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_co_media`;
CREATE TABLE IF NOT EXISTS `skyuc_co_media` (
  `nid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `hash` char(32) NOT NULL DEFAULT '',
  `tofile` char(60) NOT NULL DEFAULT '',
  KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_co_note`;
CREATE TABLE IF NOT EXISTS `skyuc_co_note` (
  `nid` int(10) NOT NULL AUTO_INCREMENT,
  `gathername` varchar(50) NOT NULL DEFAULT '',
  `language` varchar(10) NOT NULL DEFAULT 'utf-8',
  `player` varchar(20) NOT NULL,
  `cat_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `server_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `savetime` int(10) NOT NULL DEFAULT '0',
  `lasttime` int(10) NOT NULL DEFAULT '0',
  `noteinfo` text NOT NULL,
  PRIMARY KEY (`nid`),
  KEY `conote` (`gathername`,`lasttime`,`savetime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_cpsession`;
CREATE TABLE IF NOT EXISTS `skyuc_cpsession` (
  `adminid` int(10) unsigned NOT NULL DEFAULT '0',
  `hash` varchar(32) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`adminid`,`hash`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_datastore`;
CREATE TABLE IF NOT EXISTS `skyuc_datastore` (
  `title` varchar(50) NOT NULL DEFAULT '',
  `data` mediumtext,
  `unserialize` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_feedback`;
CREATE TABLE IF NOT EXISTS `skyuc_feedback` (
  `msg_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_name` varchar(60) NOT NULL DEFAULT '',
  `user_email` varchar(60) NOT NULL DEFAULT '',
  `msg_title` varchar(200) NOT NULL DEFAULT '',
  `msg_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `msg_content` text NOT NULL,
  `msg_time` int(10) unsigned NOT NULL DEFAULT '0',
  `message_img` varchar(255) NOT NULL DEFAULT '0',
  `msg_area` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`msg_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_friend_link`;
CREATE TABLE IF NOT EXISTS `skyuc_friend_link` (
  `link_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `link_name` varchar(255) NOT NULL DEFAULT '',
  `link_url` varchar(255) NOT NULL DEFAULT '',
  `link_logo` varchar(255) NOT NULL DEFAULT '',
  `show_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`link_id`),
  KEY `show_order` (`show_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_humanverify`;
CREATE TABLE IF NOT EXISTS `skyuc_humanverify` (
  `hash` char(32) NOT NULL DEFAULT '',
  `answer` mediumtext,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `viewed` smallint(5) unsigned NOT NULL DEFAULT '0',
  KEY `hash` (`hash`),
  KEY `dateline` (`dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_keywords`;
CREATE TABLE IF NOT EXISTS `skyuc_keywords` (
  `date` int(10) unsigned NOT NULL,
  `searchengine` varchar(20) NOT NULL DEFAULT '',
  `keyword` varchar(90) NOT NULL DEFAULT '',
  `count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`searchengine`,`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_mailqueue`;
CREATE TABLE IF NOT EXISTS `skyuc_mailqueue` (
  `mailqueueid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `toemail` mediumtext,
  `fromemail` mediumtext,
  `subject` mediumtext,
  `message` mediumtext,
  `header` mediumtext,
  PRIMARY KEY (`mailqueueid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_nav`;
CREATE TABLE IF NOT EXISTS `skyuc_nav` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ctype` varchar(10) DEFAULT NULL,
  `cid` smallint(5) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `ifshow` tinyint(1) NOT NULL,
  `vieworder` tinyint(1) NOT NULL,
  `opennew` tinyint(1) NOT NULL,
  `url` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `ifshow` (`ifshow`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

DROP TABLE IF EXISTS `skyuc_netbar`;
CREATE TABLE IF NOT EXISTS `skyuc_netbar` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `snum` bigint(20) DEFAULT NULL,
  `enum` bigint(20) DEFAULT NULL,
  `sip` varchar(15) DEFAULT NULL,
  `eip` varchar(15) DEFAULT NULL,
  `content` varchar(50) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `userpass` varchar(50) NOT NULL,
  `addtime` int(10) unsigned DEFAULT NULL,
  `endtime` int(10) unsigned DEFAULT NULL,
  `lasttime` int(10) unsigned NOT NULL,
  `maxuser` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_ok` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lasttime` (`lasttime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_order_info`;
CREATE TABLE IF NOT EXISTS `skyuc_order_info` (
  `order_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(20) NOT NULL,
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `order_time` int(10) unsigned NOT NULL,
  `pay_status` tinyint(1) NOT NULL DEFAULT '0',
  `order_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pay_amount` decimal(10,2) NOT NULL,
  `order_count` mediumint(8) NOT NULL DEFAULT '0',
  `surplus` decimal(10,2) NOT NULL,
  `integral` int(10) NOT NULL,
  `usertype` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `rank_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pay_id` tinyint(3) NOT NULL DEFAULT '0',
  `pay_name` varchar(120) NOT NULL,
  `pay_time` int(10) NOT NULL DEFAULT '0',
  `user_ip` varchar(15) NOT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_payment`;
CREATE TABLE IF NOT EXISTS `skyuc_payment` (
  `pay_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `pay_code` varchar(20) NOT NULL DEFAULT '',
  `pay_name` varchar(120) NOT NULL DEFAULT '',
  `pay_fee` varchar(10) NOT NULL DEFAULT '0',
  `pay_desc` text NOT NULL,
  `pay_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `pay_config` text NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_cod` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pay_id`),
  UNIQUE KEY `pay_code` (`pay_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

DROP TABLE IF EXISTS `skyuc_pay_log`;
CREATE TABLE IF NOT EXISTS `skyuc_pay_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `order_amount` decimal(10,2) unsigned NOT NULL,
  `order_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_paid` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_player`;
CREATE TABLE IF NOT EXISTS `skyuc_player` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `tag` char(30) NOT NULL,
  `player_code` text,
  `sort_order` tinyint(3) NOT NULL DEFAULT '0',
  `user_rank` varchar(30) NOT NULL,
  `is_show` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

DROP TABLE IF EXISTS `skyuc_play_log`;
CREATE TABLE IF NOT EXISTS `skyuc_play_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(225) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `time` int(10) unsigned NOT NULL,
  `minute` int(10) unsigned NOT NULL,
  `host` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  `counts` smallint(3) NOT NULL DEFAULT '1',
  `player` varchar(50) NOT NULL,
  `mov_id` int(10) unsigned NOT NULL DEFAULT '0',
  `url_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mov_id` (`mov_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_searchcore_text`;
CREATE TABLE IF NOT EXISTS `skyuc_searchcore_text` (
  `searchcoreid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `searchid` int(10) unsigned NOT NULL,
  `cat_id` smallint(5) unsigned DEFAULT NULL,
  `keywordtext` mediumtext,
  `title` varchar(254) NOT NULL DEFAULT '',
  PRIMARY KEY (`searchcoreid`),
  KEY `searchid` (`searchid`),
  KEY `contenttypeid` (`cat_id`),
  FULLTEXT KEY `text` (`title`,`keywordtext`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_searchengine`;
CREATE TABLE IF NOT EXISTS `skyuc_searchengine` (
  `date` int(10) unsigned NOT NULL,
  `searchengine` varchar(20) NOT NULL DEFAULT '',
  `count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`date`,`searchengine`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_server`;
CREATE TABLE IF NOT EXISTS `skyuc_server` (
  `server_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `server_name` varchar(60) NOT NULL DEFAULT '',
  `server_desc` text NOT NULL,
  `server_url` varchar(255) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`server_id`),
  KEY `is_show` (`is_show`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `skyuc_session`;
CREATE TABLE IF NOT EXISTS `skyuc_session` (
  `sessionhash` char(32) NOT NULL DEFAULT '',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `host` char(15) NOT NULL DEFAULT '',
  `adminid` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `idhash` char(32) NOT NULL DEFAULT '',
  `lastactivity` int(10) unsigned NOT NULL DEFAULT '0',
  `location` char(255) NOT NULL DEFAULT '',
  `useragent` char(100) NOT NULL DEFAULT '',
  `loggedin` smallint(5) unsigned NOT NULL DEFAULT '0',
  `bypass` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sessionhash`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_setting`;
CREATE TABLE IF NOT EXISTS `skyuc_setting` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` smallint(5) unsigned NOT NULL,
  `code` varchar(30) NOT NULL,
  `type` varchar(10) NOT NULL,
  `site_range` varchar(255) NOT NULL,
  `site_dir` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1017 ;

DROP TABLE IF EXISTS `skyuc_show`;
CREATE TABLE IF NOT EXISTS `skyuc_show` (
  `show_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `director` varchar(255) NOT NULL,
  `actor` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `title_alias` varchar(255) NOT NULL,
  `title_english` varchar(255) NOT NULL,
  `title_style` varchar(60) NOT NULL DEFAULT '+',
  `status` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `thumb` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `data` mediumtext NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `detail` mediumtext,
  `pubdate` varchar(20) NOT NULL,
  `runtime` tinyint(3) unsigned DEFAULT '0',
  `click_count` int(10) unsigned DEFAULT NULL,
  `click_time` int(10) unsigned DEFAULT '0',
  `click_month` int(10) unsigned DEFAULT '0',
  `click_week` int(10) unsigned DEFAULT '0',
  `cat_id` tinyint(5) unsigned DEFAULT NULL,
  `is_show` tinyint(1) unsigned DEFAULT NULL,
  `area` varchar(20) DEFAULT NULL,
  `lang` varchar(20) NOT NULL,
  `player` varchar(255) NOT NULL DEFAULT '0',
  `add_time` int(10) unsigned NOT NULL,
  `points` tinyint(2) unsigned DEFAULT NULL,
  `moviepoint` int(10) unsigned NOT NULL DEFAULT '5',
  `userspoint` int(10) unsigned NOT NULL DEFAULT '1',
  `server_id` varchar(255) DEFAULT NULL,
  `attribute` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`show_id`),
  KEY `click_count` (`click_count`),
  KEY `cat_id` (`cat_id`),
  KEY `is_show` (`is_show`),
  KEY `attribute` (`attribute`),
  KEY `area` (`area`),
  KEY `lang` (`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_show_article`;
CREATE TABLE IF NOT EXISTS `skyuc_show_article` (
  `show_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `article_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`show_id`,`article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_show_cat`;
CREATE TABLE IF NOT EXISTS `skyuc_show_cat` (
  `show_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `cat_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`show_id`,`cat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_stats`;
CREATE TABLE IF NOT EXISTS `skyuc_stats` (
  `access_time` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_address` varchar(15) NOT NULL DEFAULT '',
  `visit_times` smallint(5) unsigned NOT NULL DEFAULT '1',
  `browser` varchar(60) NOT NULL DEFAULT '',
  `system` varchar(20) NOT NULL DEFAULT '',
  `language` varchar(20) NOT NULL DEFAULT '',
  `area` varchar(30) NOT NULL DEFAULT '',
  `referer_domain` varchar(100) NOT NULL DEFAULT '',
  `referer_path` varchar(200) NOT NULL DEFAULT '',
  `access_url` varchar(255) NOT NULL DEFAULT '',
  KEY `access_time` (`access_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_subject`;
CREATE TABLE IF NOT EXISTS `skyuc_subject` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `thumb` varchar(255) NOT NULL,
  `poster` varchar(255) NOT NULL,
  `intro` text NOT NULL,
  `detail` text NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `recom` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `link` varchar(255) NOT NULL,
  `uselink` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `recom` (`recom`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `skyuc_tag`;
CREATE TABLE IF NOT EXISTS `skyuc_tag` (
  `tag_id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `show_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tag_words` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tag_id`),
  KEY `user_id` (`user_id`),
  KEY `show_id` (`show_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=44 ;

DROP TABLE IF EXISTS `skyuc_template`;
CREATE TABLE IF NOT EXISTS `skyuc_template` (
  `filename` varchar(30) NOT NULL DEFAULT '',
  `region` varchar(40) NOT NULL DEFAULT '',
  `library` varchar(40) NOT NULL DEFAULT '',
  `sort_order` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `number` tinyint(1) unsigned NOT NULL DEFAULT '5',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `theme` varchar(60) NOT NULL DEFAULT '',
  `remarks` varchar(30) NOT NULL,
  KEY `filename` (`filename`,`region`),
  KEY `theme` (`theme`),
  KEY `remarks` (`remarks`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skyuc_template_mail`;
CREATE TABLE IF NOT EXISTS `skyuc_template_mail` (
  `template_id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `template_code` varchar(30) NOT NULL DEFAULT '',
  `is_html` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `template_subject` varchar(200) NOT NULL DEFAULT '',
  `template_content` text NOT NULL,
  `last_modify` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`template_id`),
  UNIQUE KEY `template_code` (`template_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

DROP TABLE IF EXISTS `skyuc_users`;
CREATE TABLE IF NOT EXISTS `skyuc_users` (
  `user_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(60) NOT NULL,
  `password` varchar(32) NOT NULL,
  `gender` tinyint(1) NOT NULL DEFAULT '0',
  `birthday` int(10) unsigned NOT NULL,
  `email` varchar(60) NOT NULL,
  `reg_time` int(10) NOT NULL,
  `user_rank` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `unit_date` int(10) unsigned NOT NULL DEFAULT '0',
  `user_point` int(10) unsigned NOT NULL DEFAULT '0',
  `pay_point` int(10) unsigned NOT NULL DEFAULT '0',
  `usertype` smallint(1) unsigned NOT NULL DEFAULT '0',
  `user_money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `lastvisit` int(10) unsigned NOT NULL,
  `lastactivity` int(10) unsigned NOT NULL,
  `last_ip` varchar(15) NOT NULL,
  `visit_count` smallint(5) unsigned NOT NULL DEFAULT '0',
  `qq` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `msn` varchar(60) NOT NULL,
  `firstname` varchar(60) NOT NULL,
  `is_validated` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `salt` char(3) NOT NULL DEFAULT '0',
  `flag` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `alias` varchar(60) NOT NULL,
  `minute` int(10) unsigned NOT NULL,
  `playcount` int(10) unsigned NOT NULL,
  `referrer` varchar(60) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  KEY `email` (`email`),
  KEY `flag` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_user_account`;
CREATE TABLE IF NOT EXISTS `skyuc_user_account` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `admin_user` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `add_time` int(10) NOT NULL DEFAULT '0',
  `paid_time` int(10) NOT NULL DEFAULT '0',
  `admin_note` varchar(255) NOT NULL,
  `user_note` varchar(255) NOT NULL,
  `process_type` tinyint(1) NOT NULL DEFAULT '0',
  `payment` varchar(90) NOT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_paid` (`is_paid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_user_rank`;
CREATE TABLE IF NOT EXISTS `skyuc_user_rank` (
  `rank_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `rank_name` varchar(30) NOT NULL,
  `rank_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `day_play` int(10) unsigned NOT NULL DEFAULT '0',
  `day_down` int(10) unsigned NOT NULL DEFAULT '0',
  `allow_cate` varchar(255) NOT NULL DEFAULT '0',
  `allow_hours` varchar(255) NOT NULL,
  `count` smallint(5) unsigned NOT NULL DEFAULT '0',
  `money` smallint(5) unsigned NOT NULL DEFAULT '0',
  `content` varchar(255) NOT NULL,
  PRIMARY KEY (`rank_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `skyuc_vote`;
CREATE TABLE IF NOT EXISTS `skyuc_vote` (
  `vote_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `vote_name` varchar(250) NOT NULL DEFAULT '',
  `begin_date` int(10) unsigned NOT NULL,
  `end_date` int(10) unsigned NOT NULL,
  `can_multi` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `vote_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`vote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_vote_log`;
CREATE TABLE IF NOT EXISTS `skyuc_vote_log` (
  `log_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `vote_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ip_address` varchar(15) NOT NULL DEFAULT '',
  `vote_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_id`),
  KEY `vote_id` (`vote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `skyuc_vote_option`;
CREATE TABLE IF NOT EXISTS `skyuc_vote_option` (
  `option_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `vote_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `option_name` varchar(250) NOT NULL DEFAULT '',
  `option_count` int(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`option_id`),
  KEY `vote_id` (`vote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


