#
# Table structure for table 'tt_news_tx_dkdnewsmulticats_category_mm'
#
#
CREATE TABLE tt_news_tx_dkdnewsmulticats_category_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
	category int(11) unsigned DEFAULT '0' NOT NULL
);

#
# Table structure for table 'tt_news_cat'
#
CREATE TABLE tt_news_cat (
	tx_spnewscatimgs_image tinyblob NOT NULL,
	tx_spnewscatimgs_shortcut int(11) DEFAULT '0' NOT NULL
);
