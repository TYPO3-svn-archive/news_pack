<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

/**
 * Example of how to configure a class for extension of another class:
 */


#**************   hier wird die ursprüngliche PHP-Class erweitert:
#   ##extensionkey## muss durch den Extension-Schlüssel der _"alten"_ Extension ersetzt werden (fängt mit "tx_" an, z.B. tx_tt_products)
$TYPO3_CONF_VARS["FE"]["XCLASS"]["ext/tt_news/pi/class.tx_ttnews.php"]=t3lib_extMgm::extPath($_EXTKEY)."class.ux_tx_ttnews.php";
# $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tt_news/pi/class.tx_ttnews.php"] = t3lib_extMgm::extPath("dkd_newsmulticats")."class.ux_tx_ttnews.php";
?>
