#sqlite

CREATE TABLE 'session'
(
'PHPSESSID' CHAR(40) PRIMARY KEY,
'update_time' INTEGER(10),
'client_ip' CHAR(15),
'user_agent' CHAR(255),
'data' TEXT
);

CREATE INDEX 'session_update_time_idx' ON 'session'('update_time');

#mysql

 CREATE TABLE `session` (
`PHPSESSID` CHAR( 40 ) NOT NULL ,
`update_time` INT( 10 ) UNSIGNED NOT NULL ,
`client_ip` VARCHAR( 15 ) NOT NULL ,
`user_agent` VARCHAR( 255 ) NOT NULL ,
`data` TEXT NOT NULL ,
PRIMARY KEY ( `PHPSESSID` ) ,
INDEX ( `update_time` )
) ENGINE = MYISAM;
