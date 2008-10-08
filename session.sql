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
