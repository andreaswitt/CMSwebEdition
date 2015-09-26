<?php $GLOBALS['content'] = ""; ?>
<we:listview type="document" name="getContentofDocument" cfilter="false" contenttypes="text/webedition" id="\$weDocumentID" rows="1">
	<we:repeat>
	</we:repeat>
	<?php 
	$contentElements = unserialize($GLOBALS['lv']->f('ContentElement'));
	if($contentElements){
		foreach($contentElements as $value){
			$slideImgID = $GLOBALS['lv']->f('Slideblk_ContentElement_'.$value);
			$slideContent = $GLOBALS['lv']->f('SeitenInhaltblk_ContentElement_'.$value);

			$slideModeLearn = $GLOBALS['lv']->f('Lernenblk_ContentElement_'.$value);
			$slideModeLearn = ((bool) $slideModeLearn) ? 'learn' : '';
			
			$slideLearnTreat = $GLOBALS['lv']->f('Behandelnblk_ContentElement_'.$value);
			$slideLearnTreat = ((bool) $slideLearnTreat) ? 'treat' : '';
			
			if(!empty($slideImgID)){
				$GLOBALS['content'] .= '<div class="'.$slideModeLearn.' '.$slideLearnTreat.'"><img src="http://'.$_SERVER['HTTP_HOST'].id_to_path($slideImgID).'" alt="" class="pic folie"/></div>';
			}
			
			if(!empty($slideContent)){
				$GLOBALS['content'] .= '<div class="'.$slideModeLearn.' '.$slideLearnTreat.'">'.we_document::parseInternalLinks($slideContent,0).'</div>';
			}
			
			/**
			* use this for base encode image data instead of using http links to images
			*
			if(!empty($slideImgID)){
				$imgPath = $_SERVER['DOCUMENT_ROOT'].id_to_path($slideImgID);
				if (file_exists($imgPath)) {
					$extension = pathinfo($imgPath, PATHINFO_EXTENSION);
					switch ($extension) {
						case 'jpg':
						case 'jpeg':
						$mime = 'image/jpeg';
						break;
						case 'png':
						$mime = 'image/png';
						break;
						case 'gif':
						$mime = 'image/gif';
						break;
						default:
						$mime = 'image/jpeg';
						break;
					}
					$rawImg = file_get_contents($imgPath);
					$imgURI = 'data:'. $mime .';base64,' . base64_encode($rawImg);
					//$content = str_replace($search, $uri, $content);
					$GLOBALS['content'] .= '<div class="'.$slideModeLearn.' '.$slideLearnTreat.'"><img src="'.$imgURI.'" alt="" class="pic folie"/></div>';
					unset($rawImg);
					unset($imgURI);
				}
			}
			*/
		}
	}
	?>
</we:listview>