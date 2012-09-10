CREATE TABLE IF NOT EXISTS wcf1_user_soundcloud_connect (
  userID INT(10) NOT NULL,
  soundcloudID INT(10) NOT NULL,
  accessToken VARCHAR(255) NOT NULL,
  UNIQUE(userID),
  UNIQUE(soundcloudID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;