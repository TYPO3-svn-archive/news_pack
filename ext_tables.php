<?php

// diese Datei wird automatisch von Typo3 ausgeführt und ändert aktuelle Einstellungen in der 
// globalen Datei tables.php. Das Ergebnis ist im Backend unter Tools->Konfiguration->$TCA (tables.php) zu sehen.

// wenn Zugriff nicht über Typo3 erfolgt, abbrechen:
if (!defined("TYPO3_MODE"))    die("Access denied.");

// die Tabellendefinitionen tt_news werden geladen:
t3lib_div::loadTCA("tt_news");

// durch neue Typdefinitionen ersetzen (Feld Bodytext soll als RTE-Fenster angezeigt werden)
$TCA["tt_news"]["types"] = Array (
		"0" => Array("showitem" => "hidden;;;;1-1-1,type,title;;;;2-2-2,datetime,starttime;;1,archivedate,category,author,author_email,keywords,--div--,short;;;;3-3-3,bodytext;;9;richtext[*]:rte_transform[flag=rte_enabled|mode=ts];3-3-3,image;;;;4-4-4,imagecaption,--div--,links;;;;5-5-5,related"),
#		"0" => Array("showitem" => "hidden;;;;1-1-1,type,title;;;;2-2-2,datetime,starttime;;1,archivedate,category,author,author_email,keywords,--div--,short;;;;3-3-3,bodytext;;9;richtext[*]:rte_transform[flag=rte_enabled|mode=ts];3-3-3,image;;;;4-4-4,imagecaption,--div--,ext_url;;;;5-5-5,related"),
		"1" => Array("showitem" => "hidden;;;;1-1-1,type,page,title;;;;2-2-2,datetime,starttime;;1,archivedate,category,author,author_email,keywords,--div--,short;;;;3-3-3"),
		"2" => Array("showitem" => "hidden;;;;1-1-1,type,ext_url,title;;;;2-2-2,datetime,starttime;;1,archivedate,category,author,author_email,keywords,--div--,short;;;;3-3-3")
);

// neue Definitionen für datetime (als Default-Wert wird das aktuelle Systemdatum angezeigt):
$TCA["tt_news"]["columns"]["datetime"] = Array (
			"exclude" => 1,	
			"label" => "LLL:EXT:tt_news/locallang_tca.php:tt_news.datetime",
			"config" => Array (
				"type" => "input",
				"size" => "10",
				"max" => "20",
				"eval" => "datetime",
				"default" => mktime(date("h"),date("i"),0,date("m"),date("d"),date("Y"))
				)
);
$TCA["tt_news"]["columns"]["starttime"]["config"]["eval"] = "datetime";
$TCA["tt_news"]["columns"]["endtime"]["config"]["eval"] = "datetime";

$TCA["tt_news"]["columns"]["category"]["config"] = Array (
	"type" => "select",
	"foreign_table" => "tt_news_cat",
	"foreign_table_where" => "ORDER BY tt_news_cat.uid",
	"size" => 6,
	"minitems" => 0,
	"maxitems" => 100,
	"MM" => "tt_news_tx_dkdnewsmulticats_category_mm",
);


$tempColumns = Array (
	"tx_spnewscatimgs_image" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_pack/locallang_db.php:tt_news.tx_spnewscatimgs_image",		
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => "gif,png,jpeg,jpg",	
			"max_size" => 100,	
			"uploadfolder" => "uploads/pics",
			"show_thumbs" => 1,	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
		'tx_spnewscatimgs_shortcut' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.shortcut_page',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
					'allowed' => 'pages',
				'size' => '3',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		)
);

t3lib_div::loadTCA("tt_news_cat");
t3lib_extMgm::addTCAcolumns("tt_news_cat",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news_cat","tx_spnewscatimgs_image,tx_spnewscatimgs_shortcut;;;;1-1-1");


?>