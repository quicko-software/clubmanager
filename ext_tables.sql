
CREATE TABLE fe_users (
	clubmanager_member int(11) unsigned DEFAULT '0' NULL,
	lastreminderemailsent int(11) unsigned DEFAULT '0' NOT NULL,

	KEY clubmanager_member (clubmanager_member)
);


#
# Table structure for table 'tx_clubmanager_domain_model_member'
#
CREATE TABLE tx_clubmanager_domain_model_member (
	person_type tinyint DEFAULT '0' NOT NULL,
	salutation tinyint DEFAULT '0' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	firstname varchar(255) DEFAULT '' NOT NULL,
	midname varchar(255) DEFAULT '' NOT NULL,
	lastname varchar(255) DEFAULT '' NOT NULL,
	dateofbirth bigint(20) DEFAULT '0' NOT NULL,
	nationality varchar(255) DEFAULT '' NOT NULL,
	# DEPRECATED: cancellation_wish wurde durch Journal-System ersetzt
	# Feld kann nach erfolgreicher Migration gelöscht werden mit: ALTER TABLE tx_clubmanager_domain_model_member DROP COLUMN cancellation_wish;
	# cancellation_wish int(11) unsigned DEFAULT '0',
	reduced_rate int(11) unsigned DEFAULT '0',
	company varchar(255) DEFAULT '' NOT NULL,
	add_address_info varchar(255) DEFAULT '' NOT NULL,
	street varchar(255) DEFAULT '' NOT NULL,
	zip varchar(255) DEFAULT '' NOT NULL,
	city varchar(255) DEFAULT '' NOT NULL,
  federal_state int(11) unsigned DEFAULT '0',
	country int(11) unsigned DEFAULT '0',
	
	ident varchar(255) DEFAULT '' NOT NULL,
	state int(11) DEFAULT '0' NOT NULL,
	email varchar(255) DEFAULT '' NOT NULL,
	phone varchar(255) DEFAULT '' NOT NULL,
	telefax varchar(255) DEFAULT '' NOT NULL,
	feuser int(11) unsigned DEFAULT '0',

	iban varchar(255) DEFAULT '' NOT NULL,
	bic varchar(255) DEFAULT '' NOT NULL,
	account varchar(255) DEFAULT '' NOT NULL,
	direct_debit int(11) unsigned DEFAULT '0',

	main_location int(11) unsigned DEFAULT '0' NOT NULL,
	sub_locations int(11) unsigned DEFAULT '0' NOT NULL,

	level int(11) DEFAULT '0' NOT NULL,

	alt_billing_name varchar(255) DEFAULT '' NOT NULL,
	alt_billing_street varchar(255) DEFAULT '' NOT NULL,
	alt_billing_zip varchar(255) DEFAULT '' NOT NULL,
	alt_billing_city varchar(255) DEFAULT '' NOT NULL,
	alt_billing_country int(11) unsigned DEFAULT '0',
	alt_email varchar(255) DEFAULT '' NOT NULL,

	customfield1 varchar(255) DEFAULT '' NOT NULL,
	customfield2 varchar(255) DEFAULT '' NOT NULL,
	customfield3 varchar(255) DEFAULT '' NOT NULL,
	customfield4 varchar(255) DEFAULT '' NOT NULL,
	customfield5 varchar(255) DEFAULT '' NOT NULL,
	customfield6 varchar(255) DEFAULT '' NOT NULL,

	categories int(11) DEFAULT '0' NOT NULL,

	club_function text NULL,
	found_via int(11) DEFAULT '0' NOT NULL,

	KEY state (state),
	KEY alt_billing_country (alt_billing_country),
	key memberstate (uid,state)
);



#
# Table structure for table 'tx_clubmanager_domain_model_location'
#
CREATE TABLE tx_clubmanager_domain_model_location (

	member int(11) DEFAULT '0' NOT NULL,
	slug varchar(255) DEFAULT '' NOT NULL,
	kind int(11) DEFAULT '0' NOT NULL,

	salutation tinyint DEFAULT '0' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	firstname varchar(255) DEFAULT '' NOT NULL,
	midname varchar(255) DEFAULT '' NOT NULL,
	lastname varchar(255) DEFAULT '' NOT NULL,

	company varchar(255) DEFAULT '' NOT NULL,
	street varchar(255) DEFAULT '' NOT NULL,
	add_address_info varchar(255) DEFAULT '' NOT NULL,
	zip varchar(255) DEFAULT '' NOT NULL,
	city varchar(255) DEFAULT '' NOT NULL,
	state int(11) unsigned DEFAULT '0',
	country int(11) unsigned DEFAULT '0',

	latitude varchar(255) DEFAULT '' NOT NULL,
	longitude varchar(255) DEFAULT '' NOT NULL,
	search_location int(11) unsigned DEFAULT '0' NOT NULL,

	image int(11) unsigned DEFAULT '0',
	info text NULL,
	categories int(11) DEFAULT '0' NOT NULL,

	phone varchar(255) DEFAULT '' NOT NULL,
	mobile varchar(255) DEFAULT '' NOT NULL,
	fax varchar(255) DEFAULT '' NOT NULL,
	email varchar(255) DEFAULT '' NOT NULL,
	website varchar(255) DEFAULT '' NOT NULL,

	socialmedia  int(11) unsigned DEFAULT '0' NOT NULL,
	youtube_video varchar(1024) DEFAULT '' NOT NULL,

	KEY member (member),
	KEY country (country),
	KEY categories (categories),
	KEY kind (member,kind),
);


#
# Table structure for table 'tx_clubmanager_sanitizevalue_mapping'
#
CREATE TABLE tx_clubmanager_sanitizevalue_mapping (
	uid int(11) unsigned DEFAULT 0 NOT NULL auto_increment,
	sanitized_value varchar(255) DEFAULT '' NOT NULL,
	original_value varchar(512) DEFAULT '' NOT NULL,
	table_name varchar(255) DEFAULT '' NOT NULL,
	column_name varchar(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (uid),
	INDEX sanitizedValueIdx (sanitized_value(191), table_name(100), column_name(100)),
	INDEX originalValueIdx (original_value(191), table_name(100), column_name(100))
);

#
# Table structure for table 'sys_category'
#
CREATE TABLE sys_category (
	slug varchar(2048),
);

#
# Table structure for table 'tx_clubmanager_mail_task'
#
CREATE TABLE tx_clubmanager_domain_model_mail_task (
	priority_level int(11) DEFAULT '0' NOT NULL, 
	send_state int(11) DEFAULT '0' NOT NULL, 
	generator_class varchar(512) DEFAULT '' NOT NULL,
	generator_arguments longtext,
	processed_time datetime default NULL,
	error_time datetime default NULL,
	error_message text DEFAULT '' NOT NULL,
	open_tries int(11) DEFAULT '0' NOT NULL, 
);

#
# Table structure for table 'tx_clubmanager_domain_model_socialmedia'
#
CREATE TABLE tx_clubmanager_domain_model_socialmedia (
	location int(11) unsigned DEFAULT '0' NOT NULL,
	type int(11) DEFAULT '0' NOT NULL,
	url varchar(1024) DEFAULT '' NOT NULL,
	KEY location (location),
);

#
# Table structure for table 'tx_clubmanager_domain_model_memberjournalentry'
#
CREATE TABLE tx_clubmanager_domain_model_memberjournalentry (
	member int(11) unsigned DEFAULT '0' NOT NULL,
	
	entry_type varchar(50) DEFAULT 'status_change' NOT NULL,
	entry_date bigint(20) NOT NULL,
	
	-- Gemeinsame Felder
	note text,
	creator_type tinyint DEFAULT '0' NOT NULL,
	processed bigint(20) DEFAULT NULL,
	effective_date bigint(20) DEFAULT NULL,
	
	-- Für entry_type = 'status_change'
	target_state int(11) DEFAULT NULL,
	
	-- Für entry_type = 'level_change'
	old_level int(11) DEFAULT NULL,
	new_level int(11) DEFAULT NULL,

	KEY member_date (member, entry_date DESC),
	KEY entry_type (entry_type),
	KEY effective_processed (effective_date, processed, entry_type)
);