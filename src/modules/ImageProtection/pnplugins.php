<?php
/**
 * @package      ImageProtection
 * @version      $Author: Flo $ $Revision: 3 $ $Modtime: 8.02.10 21:32 $ $Id: $
 * @author       Tree Florian
 * @link         https://github.com/ftree/ImageProtection
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */


function ImageProtection_plugins_Images()
{

	$render = & pnRender::getInstance('ImageProtection');
	echo $render->fetch('ImageProtection_plugin_Images.htm');

	return true;
}

function ImageProtection_plugins_UploadImage()
{

	$file  = FormUtil::getPassedValue('uploadFile',null,'FILES');
	$title = FormUtil::getPassedValue('title',null,'POST');
	$align = FormUtil::getPassedValue('align',null,'POST');
	$baseURI = FormUtil::getPassedValue('baseURI',null,'POST');

	$settings = pnModGetVar('ImageProtection');

	$fileinfo = pnModAPIFunc($settings['common_FuncMod'],
							 $settings['common_FuncType'],
							 $settings['common_FuncFunc'],
							 array('fileinfo' => $file));
	$OrigFile = $fileinfo['name'];
	$files = pnModAPIFunc('ImageProtection',
							 'user',
							 'AfterUploadHandling',
							 array('fileinfo' => $fileinfo));

	$render = & pnRender::getInstance('ImageProtection');

	if ($files !== false) {
		$file['files'][] = $files['ThumbFile'];

		$ret = pnModAPIFunc('ImageProtection',
						    'user',
							'FormatPaths',
							 array('ret' => $file));

		$Size = pnModAPIFunc('ImageProtection',
						     'user',
							 'getEstimatedSize',
							  array('file' => $files['OrigFile']));

		$ThumbURL = $ret['files'][0];

		$render->assign('ThumbURL', $ThumbURL);
		$render->assign('OrigFile', $OrigFile);
		$render->assign('baseURI', $baseURI);
		$render->assign('extraPath', $fileinfo['extraPath']);
		$render->assign('OrigWidth', $Size['Width']);
		$render->assign('OrigHeight', $Size['Height']);
	}

	$render->assign('title', $title);
	$render->assign('align', $align);

	echo $render->fetch('ImageProtection_plugin_Images.htm');

	return true;
}

?>