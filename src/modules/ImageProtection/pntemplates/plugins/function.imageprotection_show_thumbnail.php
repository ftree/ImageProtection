<?php
/**
 * @package      ImageProtection
 * @version      $Author: Flo $ $Revision: 1 $ $Modtime: 11.03.10 23:44 $ $Id: $
 * @author       Tree Florian
 * @link         http://code.zikula.org/imageprotection/
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

/**
 * Shows the Thumbnail
 *
 **/
function smarty_function_imageprotection_show_thumbnail ($params, &$smarty)
{
	
	$assign				= $params['assign'];
	$html = pnModAPIFunc('ImageProtection',
			  		 	 'user',
						 'createThumbURL',
						 array('src' 			=> $params['src'],
						 	   'fullpath'		=> $params['fullpath'],
							   'title' 	  		=> $params['title'],
							   'showImageLink'	=> $params['showImageLink'],
							   'extrapath'	  	=> $params['extrapath'],
						 	   'force'	  		=> $params['force'],
						 	   'width'	  		=> $params['width'],
						 	   'height'	  		=> $params['height']));
				 
    if ($assign) {
        $smarty->assign($assign, $html);
    } else {
        return $html;
    }
}


 ?>