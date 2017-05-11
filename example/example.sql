 
create table zz_post(
    `Id` int(11) NOT NULL AUTO_INCREMENT ,
    `post_type_id` int(11) DEFAULT NULL ,
    `user_id` int(11) DEFAULT NULL ,
    `text` varchar(255) DEFAULT NULL ,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

create table zz_post_type(
    `Id` int(11) NOT NULL AUTO_INCREMENT ,
    `name` varchar(255) DEFAULT NULL ,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

create table zz_user(
    `Id` int(11) NOT NULL AUTO_INCREMENT ,
    `name` varchar(255) DEFAULT NULL ,
    `type_id` int,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

create table zz_user_type(
    `Id` int(11) NOT NULL AUTO_INCREMENT ,
    `name` varchar(255),
  PRIMARY KEY (`Id`)
)ENGINE=MyISAM DEFAULT CHARSET=latin1;

create table zz_friend( 
    `uid1` int(11) ,
    `uid2` int(11) ,
	`nickname` varchar(25),
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;