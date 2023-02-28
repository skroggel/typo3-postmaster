#
# Table structure for table 'tx_postmaster_domain_model_queuemail'
#
CREATE TABLE tx_postmaster_domain_model_queuemail (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

  status tinyint(2) unsigned DEFAULT '1',
	type tinyint(2) unsigned DEFAULT '0',
  pipeline tinyint(1) unsigned DEFAULT '0',

	from_name varchar(255) DEFAULT '' NOT NULL,
	from_address varchar(255) DEFAULT '' NOT NULL,
	reply_to_name varchar(255) DEFAULT '' NOT NULL,
	reply_to_address varchar(255) DEFAULT '' NOT NULL,
	return_path varchar(255) DEFAULT '' NOT NULL,

	subject varchar(255) DEFAULT '' NOT NULL,
	body_text text NOT NULL,
	attachment_paths text NOT NULL,

	plaintext_template longtext NOT NULL,
	html_template longtext NOT NULL,
	calendar_template longtext NOT NULL,

	layout_paths text NOT NULL,
	partial_paths text NOT NULL,
	template_paths text NOT NULL,

	category varchar(255) DEFAULT '' NOT NULL,
	campaign_parameter varchar(255) DEFAULT '' NOT NULL,
	priority int(11) DEFAULT '0' NOT NULL,
	settings_pid int(11) unsigned DEFAULT '0',

	tstamp_fav_sending int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp_real_sending int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp_send_finish int(11) unsigned DEFAULT '0' NOT NULL,

	mailing_statistics int(11) DEFAULT '0' NOT NULL,

	reply_address varchar(255) DEFAULT '' NOT NULL,
	attachment blob,
	attachment_type varchar(255) DEFAULT '' NOT NULL,
	attachment_name varchar(255) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY status (status),
	KEY type (type),
	KEY status_type (status,type)
);

#
# Table structure for table 'tx_postmaster_domain_model_queuerecipient'
#
CREATE TABLE tx_postmaster_domain_model_queuerecipient (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	queue_mail int(11) unsigned DEFAULT '0',

	email varchar(255) DEFAULT '' NOT NULL,
	salutation tinyint(2) unsigned DEFAULT '0',
	title varchar(255) DEFAULT '' NOT NULL,
	first_name varchar(255) DEFAULT '' NOT NULL,
	last_name varchar(255) DEFAULT '' NOT NULL,
	subject varchar(255) DEFAULT '' NOT NULL,
	marker longtext NOT NULL,
	status tinyint(2) unsigned DEFAULT '1',
	language_code varchar(2) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	migrated int(1) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY email (email),
	KEY status (status),
	KEY queue_mail (queue_mail),
	KEY queue_mail_status (queue_mail,status),
	KEY email_status (email,status)
);


#
# Table structure for table 'tx_postmaster_domain_model_mailingstatistics'
#
CREATE TABLE tx_postmaster_domain_model_mailingstatistics (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	queue_mail int(11) DEFAULT '0' NOT NULL,
	queue_mail_uid int(11) DEFAULT '0' NOT NULL,
	subject varchar(255) DEFAULT '' NOT NULL,
	status tinyint(2) unsigned DEFAULT '1',
	type tinyint(2) unsigned DEFAULT '1',

	total_recipients int(11) DEFAULT '0' NOT NULL,
	total_sent int(11) DEFAULT '0' NOT NULL,
	delivered int(11) DEFAULT '0' NOT NULL,
	failed int(11) DEFAULT '0' NOT NULL,
	deferred int(11) DEFAULT '0' NOT NULL,
	bounced int(11) DEFAULT '0' NOT NULL,

	tstamp_fav_sending int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp_real_sending int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp_finished_sending int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
    UNIQUE KEY `queue_mail` (`queue_mail`),
    UNIQUE KEY `queue_mail_uid` (`queue_mail_uid`),
	KEY parent (pid)
);

#
# Table structure for table 'tx_postmaster_domain_model_clickstatistics'
#
CREATE TABLE tx_postmaster_domain_model_clickstatistics (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	queue_mail int(11) DEFAULT '0' NOT NULL,
	queue_mail_uid int(11) DEFAULT '0' NOT NULL,
	hash varchar(255) DEFAULT '' NOT NULL,
	link_hash varchar(255) DEFAULT '' NOT NULL,
	url text NOT NULL,
	counter int(11) DEFAULT '1' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	comment text NOT NULL,

	PRIMARY KEY (uid),
    UNIQUE KEY `hash` (`hash`, `queue_mail`),
	KEY parent (pid),
	KEY queue_mail (queue_mail),
);


#
# Table structure for table 'tx_postmaster_domain_model_openingstatistics'
#
CREATE TABLE tx_postmaster_domain_model_openingstatistics (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	queue_mail int(11) DEFAULT '0' NOT NULL,
	queue_mail_uid int(11) DEFAULT '0' NOT NULL,
	queue_recipient int(11) DEFAULT '0' NOT NULL,
	hash varchar(255) DEFAULT '' NOT NULL,
	counter int(11) DEFAULT '1' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	comment text NOT NULL,

	PRIMARY KEY (uid),
    UNIQUE KEY `hash` (`hash`, `queue_mail`),
	KEY parent (pid),
	KEY queue_mail (queue_mail),
    KEY queue_recipient (queue_recipient),
);


#
# Table structure for table 'tx_postmaster_domain_model_bouncemail'
#
CREATE TABLE tx_postmaster_domain_model_bouncemail (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	status tinyint(3) unsigned DEFAULT '0' NOT NULL,
	type varchar(255) DEFAULT '' NOT NULL,
	email varchar(255) DEFAULT '' NOT NULL,
	subject varchar(255) DEFAULT '' NOT NULL,

	rule_number int(11) unsigned DEFAULT '0' NOT NULL,
	rule_category varchar(255) DEFAULT '' NOT NULL,

	header text NOT NULL,
  body text NOT NULL,

  header_full longtext NOT NULL,
  body_full longtext NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY email (email),
	KEY status (status),
	KEY email_status (email, status)
);


#
# Table structure for table 'tx_postmaster_domain_model_link'
# DEPRECATED - but has to be kept
CREATE TABLE tx_postmaster_domain_model_link (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	queue_mail int(11) DEFAULT '0' NOT NULL,

	hash varchar(255) DEFAULT '' NOT NULL,
	url text NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
    UNIQUE KEY `hash` (`hash`),
	KEY parent (pid),
	KEY queue_mail (queue_mail),
);


