<?php
	 
	class ux_tx_ttnews extends tx_ttnews {
		 
		var $conf2;
		var $prefixId;
		 
		function ux_tx_ttnews() {
			$this->prefixId = 'news_pack';
			$this->conf2 = $GLOBALS['TSFE']->tmpl->setup['plugin.'][$this->prefixId.'.'];
			#  $this->pi_loadLL();
		}
		 
		// Funktionen, die geändert werden müssen, werden aus tt_news kopiert:
		/**
		* Displaying single news/ the news list / searching
		*
		* @return [type]  ...
		*/
		function news_list() {
			 
			$theCode = $this->theCode;
			/*  $this->setPidlist($this->config['pid_list']);    // The list of pid's we're operation on. All tt_products records must be in the pidlist in order to be selected.
			$this->initRecursive($this->config['recursive']);
			$this->initCategories();
			$this->generatePageArray();
			 
			debug($this->pageArray);
			debug($this->categories);
			 
			*/
			 
			$this->initCategories();
			switch($theCode) {
				case 'LATEST':
				$prefix_display = 'displayLatest';
				$templateName = 'TEMPLATE_LATEST';
				$this->arcExclusive = -1; // Only latest, non archive news
				if (intval($this->conf['latestLimit'])) $this->config['limit'] = intval($this->conf['latestLimit']);
					break;
				case 'LIST':
				case 'SEARCH':
				$prefix_display = 'displayList';
				$templateName = 'TEMPLATE_LIST';
				break;
				default:
				$prefix_display = 'displaySingle';
				$templateName = 'TEMPLATE_SINGLE';
				break;
			}
			 
			if ($this->tt_news_uid) {
				// performing query:
				$query = 'SELECT * FROM tt_news WHERE uid='.intval($this->tt_news_uid).' AND type=0'.$this->enableFields; // type=0->only real news.
				$res = mysql(TYPO3_db, $query);
				echo mysql_error();
				 
				$row = mysql_fetch_assoc($res);
				if ($this->config['displayCurrentRecord'] || is_array($row)) {
					$this->setPidlist(intval($row['pid']));
					$this->generatePageArray();
					 
					// Get the subpart code
					$item = '';
					if ($this->config['displayCurrentRecord']) {
						$row = $this->cObj->data;
						$item = trim($this->cObj->getSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SINGLE_RECORDINSERT###')));
					}
					if (!$item) {
						$item = $this->cObj->getSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SINGLE###'));
					}
					 
					// Fill marker arrays
					$wrappedSubpartArray = array();
					$wrappedSubpartArray['###LINK_ITEM###'] = array('<A href="'.$this->getLinkUrl($this->conf['backPid']?$this->conf['backPid']:t3lib_div::GPvar('backPID')).'">', '</A>');
					$markerArray = $this->getItemMarkerArray($row, 'displaySingle');
					// Substitute
					$content = $this->cObj->substituteMarkerArrayCached($item, $markerArray, array(), $wrappedSubpartArray);
				}
			} elseif ($theCode == 'SINGLE') {
				$content .= 'Wrong parameters, GET/POST var \'tt_news\' was missing.';
			} elseif ($this->arcExclusive > 0 && !t3lib_div::GPvar('pS') && $theCode != 'SEARCH') {
				// periodStart must be set when listing from the archive.
				$content .= '';
			} else {
				$content = '';
				// List news:
				$where = '';
				if ($theCode == 'SEARCH') {
					// Get search subpart
					$t['search'] = $this->cObj->getSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SEARCH###'));
					// Substitute a few markers
					$out = $t['search'];
					$out = $this->cObj->substituteMarker($out, '###FORM_URL###', $this->getLinkUrl($this->conf['PIDsearch']));
					$out = $this->cObj->substituteMarker($out, '###SWORDS###', htmlspecialchars(t3lib_div::GPvar('swords')));
					// Add to content
					$content .= $out;
					if (t3lib_div::GPvar('swords')) {
						$where = $this->searchWhere(trim(t3lib_div::GPvar('swords')));
					}
				}
				$begin_at = t3lib_div::intInRange(t3lib_div::GPvar('begin_at'), 0, 100000);
				if (($theCode != 'SEARCH' && !t3lib_div::GPvar('swords')) || $where) {
					 
					$selectConf = $this->getSelectConf($where);
					 
					// performing query to count all news (we need to know it for browsing):
					$query = eregi_replace('^[\t ]*SELECT.+FROM', 'SELECT count(*) FROM', $this->cObj->getQuery('tt_news', $selectConf));
					$res = mysql(TYPO3_db, $query);
					echo mysql_error();
					$row = mysql_fetch_row($res);
					$newsCount = $row[0];
					 
					// range check to current newsCount
					$begin_at = t3lib_div::intInRange(($begin_at >= $newsCount) ? ($newsCount-$this->config['limit']) : $begin_at, 0);
					 
					// performing query for display:
					$selectConf['orderBy'] = 'datetime DESC';
					 
					$query = $this->cObj->getQuery('tt_news', $selectConf);
					$query .= ' LIMIT '.$begin_at.','.($this->config['limit']+1);
					 
					//    debug($query);
					$res = mysql(TYPO3_db, $query);
					echo mysql_error();
					 
					// Getting elements
					$itemsOut = '';
					$t = array();
					$t['total'] = $this->cObj->getSubpart($this->templateCode, $this->spMarker('###'.$templateName.'###'));
					$t['item'] = $this->getLayouts($t['total'], $this->alternativeLayouts, 'NEWS');
					$cc = 0;
					 
					// Andreas Schwarzkopf
					// der Wert itemLinkTarget aus TypoScript Setup wird ausgelesen, wenn vorhanden
					 
					$itemLinkTarget = $this->conf['itemLinkTarget'] ? 'target="'.$this->conf['itemLinkTarget'].'"' : "";
					 
					//
					while ($row = mysql_fetch_assoc($res)) {
						// Print Item Title
						$wrappedSubpartArray = array();
						if ($row['type'] == 1 || $row['type'] == 2) {
							$this->local_cObj->setCurrentVal($row['type'] == 1 ? $row['page'] : $row['ext_url']);
							$wrappedSubpartArray['###LINK_ITEM###'] = $this->local_cObj->typolinkWrap($this->conf['pageTypoLink.']);
						} else {
							// Andreas Schwarzkopf      $wrappedSubpartArray['###LINK_ITEM###']= array('<A href="'.$this->getLinkUrl($this->conf['PIDitemDisplay']).'&tt_news='.$row['uid'].'">','</A>');
							// der Link für den Marker LINK_ITEM wird um die Target-Definition aus TypoScript erweitert
							$wrappedSubpartArray['###LINK_ITEM###'] = array('<A href="'.$this->getLinkUrl($this->conf['PIDitemDisplay']).'&tt_news='.$row['uid'].'" '.$itemLinkTarget.'>', '</A>');
							//
						}
						$markerArray = $this->getItemMarkerArray($row, $prefix_display);
						 
						$itemsOut .= $this->cObj->substituteMarkerArrayCached($t['item'][($cc%count($t['item']))], $markerArray, array(), $wrappedSubpartArray);
						$cc++;
						if ($cc == $this->config['limit']) {
							break;
						}
					}
					$out = $itemsOut;
				}
				if ($out) {
					// next / prev:
					$url = $this->getLinkUrl('', 'begin_at');
					// Reset:
					$subpartArray = array();
					$wrappedSubpartArray = array();
					$markerArray = array();
					 
					if ($newsCount > $begin_at+$this->config['limit']) {
						$next = ($begin_at+$this->config['limit'] > $newsCount) ? $newsCount-$this->config['limit'] :
						 $begin_at+$this->config['limit'];
						$wrappedSubpartArray['###LINK_NEXT###'] = array('<A href="'.$url.'&begin_at='.$next.'">', '</A>');
					} else {
						$subpartArray['###LINK_NEXT###'] = '';
					}
					if ($begin_at) {
						$prev = ($begin_at-$this->config['limit'] < 0) ? 0 :
						 $begin_at-$this->config['limit'];
						$wrappedSubpartArray['###LINK_PREV###'] = array('<A href="'.$url.'&begin_at='.$prev.'">', '</A>');
					} else {
						$subpartArray['###LINK_PREV###'] = '';
					}
					$markerArray['###BROWSE_LINKS###'] = '';
					if ($newsCount > $this->config['limit'] ) {
						// there is more than one page, so let's browse
						for ($i = 0 ; $i < ($newsCount/$this->config['limit']); $i++) {
							if (($begin_at >= $i * $this->config['limit']) && ($begin_at < $i * $this->config['limit']+$this->config['limit'])) {
								$markerArray['###BROWSE_LINKS###'] .= ' <b>'.(string)($i+1).'</b> ';
								// you may use this if you want to link to the current page also
								// $markerArray['###BROWSE_LINKS###'].= ' <A href="'.$url.'&begin_at='.(string)($i * $this->config['limit']).'"><b>'.(string)($i+1).'</b></A> ';
							} else {
								$markerArray['###BROWSE_LINKS###'] .= ' <A href="'.$url.'&begin_at='.(string)($i * $this->config['limit']).'">'.(string)($i+1).'</A> ';
							}
						}
					}
					 
					$subpartArray['###CONTENT###'] = $out;
					$markerArray['###CATEGORY_TITLE###'] = ''; // Something here later...
					$wrappedSubpartArray['###LINK_ARCHIVE###'] = $this->local_cObj->typolinkWrap($this->conf['archiveTypoLink.']);
					 
					$content .= $this->cObj->substituteMarkerArrayCached($t['total'], $markerArray, $subpartArray, $wrappedSubpartArray);
				} elseif ($where) {
					$content .= $this->cObj->getSubpart($this->templateCode, $this->spMarker('###ITEM_SEARCH_EMPTY###'));
				}
			}
			return $content;
		}
		 
		/**
		* Returns a url for use in forms and links
		*
		* @param [type]  $id: ...
		* @param [type]  $excludeList: ...
		* @return [type]  ...
		*/
		function getLinkUrl($id = '', $excludeList = '') {
			$queryString = array();
			$queryString['id'] = 'id='.($id ? $id : $GLOBALS['TSFE']->id);
			// Andreas Schwarzkopf  $queryString['type']= $GLOBALS['TSFE']->type ? 'type='.$GLOBALS['TSFE']->type : "";
			// der TypoScript-Setup-Wert itemLinkType wird ausgelesen, wenn nicht vorhanden, der aktuelle Type-Wert des Fensters
			if ($this->conf['itemLinkType'])
				$itemLinkType = 'type='.$this->conf['itemLinkType'];
			else
				if ($GLOBALS['TSFE']->type)
				$itemLinkType = 'type='.$GLOBALS['TSFE']->type;
			else
				$itemLinkType = '';
			 
			// als Type für die Linkbildung aufnehmen
			$queryString['type'] = $itemLinkType;
			// Andreas Schwarzkopf Ende
			$queryString['backPID'] = 'backPID='.$GLOBALS['TSFE']->id;
			$queryString['begin_at'] = t3lib_div::GPvar('begin_at') ? 'begin_at='.t3lib_div::GPvar('begin_at') : '';
			$queryString['swords'] = t3lib_div::GPvar('swords') ? 'swords='.rawurlencode(t3lib_div::GPvar('swords')) : '';
			$queryString['pS'] = t3lib_div::GPvar('pS') ? 'pS='.intval(t3lib_div::GPvar('pS')) : ''; // period start
			$queryString['pL'] = t3lib_div::GPvar('pL') ? 'pL='.intval(t3lib_div::GPvar('pL')) : ''; // Period length
			$queryString['arc'] = t3lib_div::GPvar('arc') ? 'arc='.intval(t3lib_div::GPvar('arc')) : ''; // Archive flag: 0 = don't care, -1 = latest, 1 = archive
			$queryString['cat'] = t3lib_div::GPvar('cat') ? 'cat='.intval(t3lib_div::GPvar('cat')) : ''; // Category uid, 0 = any
			 
			reset($queryString);
			while (list($key, $val) = each($queryString)) {
				if (!$val || ($excludeList && t3lib_div::inList($excludeList, $key))) {
					unset($queryString[$key]);
				}
			}
			return $GLOBALS['TSFE']->absRefPrefix.'index.php?'.implode($queryString, '&');
		}
		 
		/**
		* [Describe function...]
		*
		* @param [type]  $where: ...
		* @param [type]  $noPeriod: ...
		* @return [type]  ...
		*/
		function getSelectConf($where, $noPeriod = 0) {
			$this->setPidlist($this->config['pid_list']);
			$this->initRecursive($this->config['recursive']);
			$this->generatePageArray();
			 
			// Get news
			$selectConf = Array();
			$selectConf['pidInList'] = $this->pid_list;
			$selectConf['where'] = '1=1 '.$where;
			 
			// Archive
			if (intval(t3lib_div::GPvar('arc'))) {
				$this->arcExclusive = intval(t3lib_div::GPvar('arc'));
			}
			if ($this->arcExclusive) {
				if ($this->conf['enableArchiveDate']) {
					if ($this->arcExclusive < 0) {
						// latest
						$selectConf['where'] .= ' AND (tt_news.archivedate=0 OR tt_news.archivedate>'.$GLOBALS['SIM_EXEC_TIME'].')';
					} elseif ($this->arcExclusive > 0) {
						$selectConf['where'] .= ' AND tt_news.archivedate<'.$GLOBALS['SIM_EXEC_TIME'];
					}
				}
				if ($this->conf['datetimeDaysToArchive']) {
					$theTime = $GLOBALS['SIM_EXEC_TIME']-intval($this->conf['datetimeDaysToArchive']) * 3600 * 24;
					if ($this->arcExclusive < 0) {
						// latest
						$selectConf['where'] .= ' AND (tt_news.datetime=0 OR tt_news.datetime>'.$theTime.')';
					} elseif ($this->arcExclusive > 0) {
						$selectConf['where'] .= ' AND tt_news.datetime<'.$theTime;
					}
				}
			}
			// Category
			if (intval(t3lib_div::GPvar('cat'))) {
				$this->catExclusive = intval(t3lib_div::GPvar('cat'));
			}
			$codes = t3lib_div::trimExplode(',', $this->config['code']?$this->config['code']:$this->conf['defaultCode'], 1);
			if (!count($codes)) $codes = array('');
				while (list(, $theCode) = each($codes)) {
				list($theCode, $cat, $aFlag) = explode('/', $theCode);
			}
			$this->catExclusive2 = explode(';', $cat);
			//lav det sådan at hvis det f.eks. er minus-tal, så betyder at denne category ikke kommer med.
			if ($this->catExclusive) {
				$selectConf['join'] = 'tt_news_tx_dkdnewsmulticats_category_mm';
				$selectConf['where'] .= ' AND tt_news_tx_dkdnewsmulticats_category_mm.uid_foreign IN ('.implode(',', $this->catExclusive2).')';
				$selectConf['where'] .= ' AND tt_news_tx_dkdnewsmulticats_category_mm.uid_local=tt_news.uid';
			}
			// Period
			if (!$noPeriod && intval(t3lib_div::GPvar('pS'))) {
				$selectConf['where'] .= ' AND tt_news.datetime>'.intval(t3lib_div::GPvar('pS'));
				if (intval(t3lib_div::GPvar('pL'))) {
					$selectConf['where'] .= ' AND tt_news.datetime<'.(intval(t3lib_div::GPvar('pS'))+intval(t3lib_div::GPvar('pL')));
				}
			}
			$selectConf['groupBy'] = 'uid';
			return $selectConf;
		}
		 
		/**
		* [Describe function...]
		*
		* @return [type]  ...
		*/
		function initCategories() {
			// Fetching catagories:
			$query = 'select * from tt_news_tx_dkdnewsmulticats_category_mm LEFT JOIN tt_news_cat ON tt_news_tx_dkdnewsmulticats_category_mm.uid_foreign = tt_news_cat.uid where 1=1'.$this->cObj->enableFields('tt_news_cat');
			$res = mysql(TYPO3_db, $query);
			echo mysql_error();
			$this->categories = array();
			$this->categorieImages = array();
			while ($row = mysql_fetch_assoc($res)) {
				$this->categories[$row['uid_local']][] = array(
				'title' => $row['title'],
					'image' => $row['tx_spnewscatimgs_image'],
					'shortcut' => $row['tx_spnewscatimgs_shortcut'] );
			}
		}
		 
		/**
		* [Describe function...]
		*
		* @param [type]  $row: ...
		* @param [type]  $textRenderObj: ...
		* @return [type]  ...
		*/
		function getItemMarkerArray ($row, $textRenderObj = 'displaySingle') {
			$markerArray = array();
			$markerArray = parent::getItemMarkerArray($row, $textRenderObj = 'displaySingle');
			$news_category = array();
			$catimgs = array();
			$theCatImgCode = '';
			$theCatImgCodeArray = array();
			if ($this->conf2['showcategorytext'] == 1 or $this->conf2['showcategoryimage'] == 1) {
				while (list ($key, $val) = each ($this->categories[$row['uid']])) {
					$news_category[] = $this->local_cObj->stdWrap($this->cObj->getTypoLink($this->categories[$row['uid']][$key]['title'], $this->categories[$row['uid']][$key]['shortcut']), $lConf['category_stdWrap.']);
					$catimgs[] = $this->categories[$row['uid_local']][$key]['image'];
					if (!empty($this->categories[$row['uid_local']][$key]['image'])) {
						$lConf['image.']['file'] = 'uploads/pics/'.$this->categories[$row['uid_local']][$key]['image'];
						$lConf['image.']['file.']['maxW'] = $this->conf2['categoryimagemaxwidth'];
						$lConf['image.']['file.']['maxH'] = $this->conf2['categoryimagemaxheight'];
						$lConf['image.']['stdWrap.']['typolink.']['parameter'] = $this->categories[$row['uid_local']][$key]['shortcut'];
						$theCatImgCodeArray[] = $this->local_cObj->IMAGE($lConf['image.']);
					}
				}
				if ($this->conf2['showcategorytext'] == 1) {
					$news_category = implode(', ', array_slice($news_category, 0, $this->conf2['maxnumberofcategorytexts']));
					$markerArray['###NEWS_CATEGORY###'] = (strlen($news_category) < $this->conf2['categorytextmaxcharlength']?$news_category:substr($news_category, 0, $this->conf2['categorytextmaxcharlength']).'...');
				} else {
					$markerArray['###NEWS_CATEGORY###'] = '';
				}
				if ($this->conf2['showcategoryimage'] == 1) {
					$theCatImgCode = implode('', array_slice($theCatImgCodeArray, 0, $this->conf2['maxnumberofcategoryimages']));
					$markerArray['###NEWS_CATEGORY_IMAGE###'] = $this->local_cObj->wrap(trim($theCatImgCode), $lConf['imageWrapIfAny']);
				} else {
					$markerArray['###NEWS_CATEGORY_IMAGE###'] = '';
				}
			} else {
				$markerArray['###NEWS_CATEGORY_IMAGE###'] = '';
				$markerArray['###NEWS_CATEGORY###'] = '';
			}
			 
			return $markerArray;
		}
		 
	}
?>
