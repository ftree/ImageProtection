<?php
/**
 * @package      ImageProtection
 * @version      $Author: Flo $ $Revision: 6 $ $Modtime: 11.03.10 22:00 $ $Id: $
 * @author       Tree Florian
 * @link         http://code.zikula.org/imageprotection/
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

/**
 * Shows the Link to the Image if its an old Loesung and an Image exists
 *
 **/
function smarty_function_imageprotection_show_imagelink ($params, &$smarty)
{
	
	//$dom = ZLanguage::getModuleDomain('ImageProtection');
	$src 				= $params['src'];
	$extrapath			= $params['extrapath'];
	$fullpath			= $params['fullpath'];
	
	$title 				= $params['title'];
	$text				= $params['text'];
	$assign				= $params['assign'];

	if ($src != "" ) {

	   	$settings = pnModGetVar('ImageProtection');
	   	
	   	$paths = ImageProtection_userapi_generatePaths(array('src'   		=> $src,
    					 	   							 	 'extraPath' 	=> $extrapath,
    					 	   							 	 'fullpath' 	=> $fullpath));
	
		$file = $paths['OrigFile'];
/*	   	
	   	$pathifo = pathinfo($src);
   		if ($pathifo['dirname'] == '' || $pathifo['dirname']=='.') {
			// No path is given so use the one from the settings
   			$file = $settings['path_Original'].$src;	
   		} else {
   			$file = $src;
   		}
*/		
		$ImgInfo= getimagesize($file);
		
   		$w		= $ImgInfo[0];
   		$h  	= $ImgInfo[1];
		$MaxW   = $settings['common_MaxWidth'];
		$MaxH   = $settings['common_MaxHeight'];   		

	
		//If image dimension is smaller, do not resize
	 	if ($w <= $MaxW && $h <= $MaxH) {
	  		$nHeight 	= $h;
	  		$nWidth 	= $w;
	 	}else{
	 		//yeah, resize it, but keep it proportional
	  		if ($MaxW/$w < $MaxH/$h) {
	   			$nWidth 	= $MaxW;
	   			$nHeight 	= intval($h*($MaxW/$w));
	  		}else{
	   			$nWidth 	= intval($w*($MaxH/$h));
	   			$nHeight 	= $MaxH;
	  		}
	 	}   		

		$modalbox = pnModGetVar('ImageProtection','useModalbox', false);
	 	if ($modalbox) {
			pnModAPIFunc('ImageProtection','user','LoadThickBox');		 		
			$url =pnModURL('ImageProtection','user','viewImage', array('height'	  => $nHeight + 10,
																 	   'width' 	  => $nWidth,
																 	   'src'	  => urlencode($src),
																	   'extraPath'=> $extrapath,
    					 	   							 	 		   'fullpath' => $fullpath,
																	   'modalbox' => $modalbox));
	 	} else {
			$url =pnModURL('ImageProtection','user','viewImage', array('src'	  => urlencode($src),
																	   'extraPath'=> $extrapath,
    					 	   							 	 		   'fullpath' => $fullpath,			
																	   'modalbox' => $modalbox)); 		
	 	}
		if ($modalbox) {
			$nWidth = $nWidth + 18;
			$nHeight = $nHeight + 42;
			$html="<a href=\"$url\" class=\"modalbox\" title=\"$title\" onclick=\"Modalbox.show(this.href, {title: this.title, width: $nWidth, height: $nHeight }); return false;\">$text</a>";
		} else {
			$html="<a href=\"$url\" title=\"$title\">$text</a>";	
		}	 	
		
	} else {
		$html = "";
	}
	
    if ($assign) {
        $smarty->assign($assign, $html);
    } else {
        return $html;
    }
}


 ?>