<?php
/**
 * @package      ImageProtection
 * @version      $Author: Flo $ $Revision: 6 $ $Modtime: 9.04.10 21:21 $ $Id: $
 * @author       Tree Florian
 * @link         https://github.com/ftree/ImageProtection
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

function ImageProtection_user_viewImage()
{
	$src 	   	= FormUtil::getPassedValue('src', null, 'GET');
	$extrapath 	= FormUtil::getPassedValue('extraPath', null, 'GET');
	$fullpath 	= FormUtil::getPassedValue('fullpath', false, 'GET');

	$modalbox = FormUtil::getPassedValue('modalbox', false, 'GET');

    $HTML = pnModAPIFunc('ImageProtection',
   					 	 'user',
   					 	 'ShowImage',
   					 	 array('src'   		=> $src,
    					 	   'extraPath' 	=> $extrapath,
    					 	   'fullpath' 	=> $fullpath));
	if ($modalbox) {
		echo $HTML;
		exit;
	} else {
		$pnRender = pnRender::getInstance('ImageProtection',false);
   		$pnRender->assign('Image', 		$HTML);
		return $pnRender->fetch('ImageProtection_user_viewImage.htm');
	}

}

function ImageProtection_user_showThumb()
{

	$src = FormUtil::getPassedValue('src', null, 'GET');

	header('content-type: image/jpeg');

	$image = imagecreatefromjpeg('F:\WAMP\DocumentRoot\TorD-Zikula1.2.0\IMPORT\bilder\loesungen\0002585_(1024664773__urlaub).jpg');
	imagejpeg($image);
	imagedestroy($image);

	exit;
}

function ImageProtection_user_LoadThickBox()
{
	//PageUtil::addVar('stylesheet', 'modules/ImageProtection/pnjavascript/ThickBox3.1/thickbox.css');
    //PageUtil::addVar('javascript', 'modules/ImageProtection/pnjavascript/ThickBox3.1/jquery-latest.js');
    //PageUtil::addVar('javascript', 'modules/ImageProtection/pnjavascript/ThickBox3.1/thickbox.js');

	//PageUtil::addVar('stylesheet', 'modules/ImageProtection/pnjavascript/lightbox/css/lightbox.css');
   	//PageUtil::addVar('stylesheet', 'modules/ImageProtection/pnjavascript/modalbox1.5.5/modalbox.css');

	//PageUtil::addVar('javascript', 'javascript/ajax/prototype.js');
   	//PageUtil::addVar('javascript', 'javascript/ajax/scriptaculous.js?load=builder,effects');

   	//PageUtil::addVar('javascript', 'modules/ImageProtection/pnjavascript/modalbox1.5.5/modalbox.js');

}



?>