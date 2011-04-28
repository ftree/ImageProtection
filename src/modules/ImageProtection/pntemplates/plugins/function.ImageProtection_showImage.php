<?php
/**
 * @package      ImageProtection
 * @version      $Author: Flo $ $Revision: 4 $ $Modtime: 8.02.10 21:32 $ $Id: $
 * @author       Tree Florian
 * @link         http://code.zikula.org/imageprotection/
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */


function smarty_function_imageprotection_showimage ($params, &$smarty)
{
	$src 		= $params['src'];
	$extrapath	= $params['extrapath'];
	$fullpath	= $params['fullpath'];	
	$force 		= $params['force'];
	$assign 	= $params['assign'];

    if ($src == "") {
        $smarty->trigger_error('imageprotection_showimage: SourceFile required');
        return false;
    }	
	
	$ret = pnModAPIFunc('ImageProtection',
	       			  	'user',
	       			  	'CreateImage',
	       			  	array('src'   		=> $src,
  							  'extraPath' 	=> $extrapath,
  							  'fullpath' 	=> $fullpath,
	       			  		  'force' 		=> $force));		
	       			  	
	// start a new pnRender display instance
	$render = & pnRender::getInstance('ImageProtection',false);
	
	$render->assign('files',  		$ret['files']);
	$render->assign('width',			$ret['width']);
	$render->assign('height', 		$ret['height']);
	$render->assign('SingleWidth',	$ret['SingleWidth']);
	$render->assign('SingleHeight',	$ret['SingleHeight']);	
	$render->assign('force',  		$ret['force'] ? "TRUE" : "FALSE");

	// fetch, process and display template
	$html = $render->fetch('ImageProtection_plugin_showImage.htm');	
	
    if ($assign) {
        $smarty->assign($assign, $html);
    } else {
        return $html;
    }
}



?>