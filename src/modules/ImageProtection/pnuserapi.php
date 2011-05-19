<?php
/**
 * @package      ImageProtection
 * @version      $Author: Flo $ $Revision: 20 $ $Modtime: 23.10.10 17:12 $ $Id: $
 * @author       Tree Florian
 * @link         https://github.com/ftree/ImageProtection
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */



/**
 * Shows a Image
 *
 * @return HTM Output from Template
 */
function ImageProtection_userapi_ShowImage ($args)
{

	//$src 	= $args['src'];
	//$force 	= $args['force'];

	$ret = pnModAPIFunc('ImageProtection',
	       			  	'user',
	       			  	'CreateImage',
	       			  	array('src'   		=> $args['src'],
	       			  		  'extraPath' 	=> $args['extraPath'],
							  'fullpath' 	=> isset($args['fullpath']) ? $args['fullpath'] : false,
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
	return $render->fetch('ImageProtection_plugin_showImage.htm');


}

function ImageProtection_userapi_generatePaths ($args)
{
	$srcFile 	= $args['src'];
	$extrapath 	= $args['extraPath'];
	$fullpath 	= isset($args['fullpath']) ? $args['fullpath'] : false;

	$settings = pnModGetVar('ImageProtection');
	$ret = array();

	if ($fullpath) {
		$ret['OrigFile'] = $srcFile;
		$pathifo = pathinfo($srcFile);
		if ($extrapath=="") {
			$ret['ThumbFile'] = $settings['path_Thumbnail'].$pathifo['basename'];
		} else {
			$ret['ThumbFile'] = $settings['path_Thumbnail'].$extrapath."/".$pathifo['basename'];
		}
	} else {
		if ($extrapath !== "") {
			$Opath =  $settings['path_Original'].$extrapath."/";
			if (!is_dir($Opath)) {
				mkdir_recursive($Opath);
				$fp=fopen($Opath.'index.html','w+');
				fclose($fp);
			}
			$Tpath =  $settings['path_Thumbnail'].$extrapath."/";
			if (!is_dir($Tpath)) {
				mkdir_recursive($Tpath);
				$fp=fopen($Opath.'index.html','w+');
				fclose($fp);
			}
			$ret['OrigFile'] = $Opath.$srcFile;
			$ret['ThumbFile'] = $Tpath.$srcFile;
		} else {
			$ret['OrigFile'] = $settings['path_Original'].$srcFile;
			$ret['ThumbFile'] = $settings['path_Thumbnail'].$srcFile;
		}

	}

	return $ret;
}

/**
 * Create the Image with all formating
 * This is the Main Function to create the Image
 *
 * @param string $args[src]	The Source File (Full Path)
 */
function ImageProtection_userapi_CreateImage ($args)
{
	ImageProtection_userapi_debug ("Input ImageProtection_userapi_CreateWatermark");
	ImageProtection_userapi_debug ($args);

	$srcFile 	= $args['src'];
	$extrapath 	= $args['extraPath'];
	$fullpath 	= isset($args['fullpath']) ? $args['fullpath'] : false;

	$paths = ImageProtection_userapi_generatePaths(array('src'   		=> $srcFile,
    					 	   							 'extraPath' 	=> $extrapath,
    					 	   							 'fullpath' 	=> $fullpath));

	$srcFile = $paths['OrigFile'];

	$settings = pnModGetVar('ImageProtection');

	ImageProtection_userapi_debug(array('Cache Enabled:' => $settings['cache_enabled'] == 1 ? "TRUE" : "FALSE"));

	// Set Caching Setting. If Cache is Enabled Forcing = false
	$force = $settings['cache_enabled'] == 1 ? false : true;
	ImageProtection_userapi_debug(array('force Setting:' => $force));

	// If force Parameter is set ovveride Caching ENABLED seting
	if (isset($args['force'])) {
		$force = ($args['force'] !== true) ? false : true;
	}
	ImageProtection_userapi_debug(array('force Parameter:' => $force));

   	$settings = pnModGetVar('ImageProtection');

   	$SrcImgInfo	= getimagesize($srcFile);

   	$SrcWidth	= $SrcImgInfo[0];
   	$SrcHeight  = $SrcImgInfo[1];

   	$files[] = $srcFile;
	$ret = array('files' 	=> $files,
				 'force' 	=> $force,
				 'width' 	=> $SrcWidth,
				 'height'	=> $SrcHeight);
   	// Check if we need an Image Watermark
	if ($settings['wmi_enabled'] == 1) {
	   	// Create the Watermark Image
		$ret = ImageProtection_userapi_CreateWatermark(
				 array('wmPathFile'	=> $settings['path_Watermarks'].$settings['wmi_image'],
					   'dstPath'	=> $settings['path_CacheWM'],
					   'SrcWidth' 	=> $SrcWidth,
					   'SrcHeight'	=> $SrcHeight,
					   'force' 		=> $force));
		ImageProtection_userapi_debug ("Ret CreateWatermark");
		ImageProtection_userapi_debug ($ret);
		$wmPathFile = $ret['dest'];
		$force   	= $ret['force'];
	}

	// Create and resize the Image with Watermark on it
	$ret = ImageProtection_userapi_WatermarkAndResizeImage(
				 array('srcPathFile'=> $srcFile,
					   'dstPath'	=> $settings['path_CacheReWmImages'],
				       'wmPathFile'	=> $wmPathFile,
			           'SrcWidth' 	=> $SrcWidth,
			           'SrcHeight'	=> $SrcHeight,
			           'force' 		=> $force));

	ImageProtection_userapi_debug ("Ret WatermarkImage");
	ImageProtection_userapi_debug ($ret);

	if ($settings['wmt_enabled'] == 1) {
		// Create the protected Image
		$ret = ImageProtection_userapi_CreateProtectedImage(
				  array('srcPathFile'	=> $ret['files'][0],
						'dstPath'		=> $settings['path_CacheProtected'],
						'SrcWidth' 		=> $ret['width'],
			  			'SrcHeight'		=> $ret['height'],
				  		'force' 		=> $ret['force']));

		ImageProtection_userapi_debug ("Ret CreateProtectedImage");
		ImageProtection_userapi_debug ($ret);
	}

	if ($settings['crop_enabled'] == true) {
		// Crop the Image into pieces
		$ret = ImageProtection_userapi_CropImage(
				array('srcPathFile'	=> $ret['files'][0],
					  'dstPath' 	=> $settings['path_CacheCroped'],
					  'SrcWidth' 	=> $ret['width'],
					  'SrcHeight'	=> $ret['height'],
		 	  		  'force' 		=> $ret['force']));

		ImageProtection_userapi_debug ("Ret CropImage");
		ImageProtection_userapi_debug ($ret);
	}

	// Remove temp. created files if Cache is enabled
	ImageProtection_userapi_removeDelCache($ret['files']);

	$ret = ImageProtection_userapi_FormatPaths(array('ret' => $ret));

	ImageProtection_userapi_debug ("Ret FormatPaths");
	ImageProtection_userapi_debug ($ret);

	return $ret;

} // function ImageProtection_userapi_CreateImage ($args)

/**
 * Creates the Watermark Logo depending on the File Size
 *
 * @param string 	$args[wmPathFile]	The Original Watermakr Image (with Path)
 * @param string	$args[dstPath]		The Destination Directory of the resized Watermark Image
 * @param integer 	$args[SrcWidth]		The Width of the Original Image
 * @param integer 	$args[SrcHeight]	The Height of the Original Image
 * @param boolean	$args['force']		The force the creation of the Image also if the Image already exists
 *
 * @return Path to the Watermark Image
 */
function ImageProtection_userapi_CreateWatermark ($args)
{
	ImageProtection_userapi_debug ("Input ImageProtection_userapi_CreateWatermark");
	ImageProtection_userapi_debug ($args);

	$wmPathFile	= $args['wmPathFile'];
	$dstPath	= $args['dstPath'];
	$SrcWidth 	= $args['SrcWidth'];
	$SrcHeight 	= $args['SrcHeight'];
	$force		= $args['force'];
	$settings 	= pnModGetVar('ImageProtection');

	$MaxWeight = $settings['common_MaxWidth'];
	$MaxHeight = $settings['common_MaxHeight'];

 	if ($SrcWidth <= $MaxWeight && $SrcHeight <= $MaxHeight) {
  		$nHeight = $SrcHeight;
  		$nWidth  = $SrcWidth;
 	}else{

  		if 	($MaxWeight/$SrcWidth < $MaxHeight/$SrcHeight) {
   			$nWidth  = $MaxWeight;
   			$nHeight = $SrcHeight*($MaxHeight/$SrcWidth);
  		}else{
   			$nWidth  = $SrcWidth*($MaxHeight/$SrcHeight);
   			$nHeight = $MaxHeight;
  		}
	}
	$SrcWidth = $nWidth;
	$SrcHeight = $nHeight;

	ImageProtection_userapi_debug (array('SrcWidth' => $SrcWidth, 'SrcHeight' => $SrcHeight));

	$WMImgInfo		= getimagesize($wmPathFile);
   	$OrigWMwidth	= $WMImgInfo[0];
   	$OrigWMheight  	= $WMImgInfo[1];

	if ($SrcWidth > $SrcHeight) {
		// Image is landscape
		$WMwidth  = intval($SrcWidth /100 * $settings['wmi_sizeperz']);
		$poz 	  = $WMwidth / $OrigWMwidth * 100;
		$WMheight = intval($OrigWMheight / 100 * $poz);
	} else {
		// Image is portrait
		$WMheight = intval($SrcHeight /100 * $settings['wmi_sizeperz']);
		$poz 	  = $WMheight / $OrigWMheight * 100;
		$WMwidth  = intval($OrigWMwidth / 100 * $poz);
	}

	ImageProtection_userapi_debug ("New WM Size");
	ImageProtection_userapi_debug (array('WMwidth' => $WMwidth, 'WMheight' => $WMheight, 'poz'=>$poz));

	$wmFile 	= basename($wmPathFile);
	$WMNewImage = $dstPath.$WMwidth."x".$WMheight."_".$wmFile;

	if (file_exists($WMNewImage) && $force == false) {
		ImageProtection_userapi_debug ("Watermark File $WMNewImage already exists and FORCE=FALSE");
	} else {
		$force = true;

		ImageProtection_userapi_resize($wmPathFile,$WMwidth,$WMheight,$WMNewImage);
/*
	$ret = pnModAPIFunc('Thumbnail',
       			  		'user',
       			  		'generateImage',
       			  		array('srcFilename' => $WMOrigImage,
       			  			  'dstFilename' => $WMNewImage,
       			  			  'w'			=> $WMsize,
       			  			  'h'			=> $WMsize));
*/
	}

	ImageProtection_userapi_add2DelCache($WMNewImage);

	$ret = array('dest'   => $WMNewImage,
				 'width'  => $WMwidth,
				 'height' => $WMheight,
				 'force'  => $force);
	return $ret;

} // function ImageProtection_userapi_CreateWatermark ($args)

/**
 * Watermarks and resizes an Image
 *
 * @param string 	$args[srcPathFile]	The Original File (with Path)
 * @param string	$args[dstPath]		The Destination Directory of the resized and watermakrd Image
 * @param string	$args[wmPathFile]	The resized Watermakr Image
 * @param integer 	$args[SrcWidth]		The Width of the Original Image
 * @param integer 	$args[SrcHeight]	The Height of the Original Image
 * @param boolean	$args[force]		The force the creation of the Image also if the Image already exists
 */
function ImageProtection_userapi_WatermarkAndResizeImage ($args)
{
	ImageProtection_userapi_debug ("Input ImageProtection_userapi_WatermarkAndResizeImage");
	ImageProtection_userapi_debug ($args);

	$srcPathFile 	= $args['srcPathFile'];
	$dstPath		= $args['dstPath'];
	$wmPathFile		= $args['wmPathFile'];
	$force			= $args['force'];
	$settings 		= pnModGetVar('ImageProtection');

	$srcFile = basename($srcPathFile);
	$dstPathFile = $dstPath.$srcFile;

	$files[] = $dstPathFile;
	ImageProtection_userapi_add2DelCache($dstPathFile);

	if (file_exists($dstPathFile) && $force == false) {
		ImageProtection_userapi_debug ("Watermarked and Resized File $dstPathFile already exists and FORCE=FALSE");
	} else {
		$force = true;
		if ($settings['wmi_enabled'] == true && $wmPathFile != "") {
			unset($filter);
			$filter[] = "wmi|$wmPathFile|".$settings['wmi_pos']."|".$settings['wmi_opacity'];
			ImageProtection_userapi_debug ("Filter Settings for Image Watermark");
			ImageProtection_userapi_debug ($filter);
		}

		pnModAPIFunc('Thumbnail',
  			  		 'user',
  			  		 'generateImage',
  			  		 array('srcFilename' => $srcPathFile,
  			  			   'dstFilename' => $dstPathFile,
  			  			   'fltr' 		 => $filter,
  			  			   'w'			 => $settings['common_MaxWidth'],
  			  			   'h'			 => $settings['common_MaxHeight']));

	}

	$imginfo 	= getimagesize($dstPathFile);
   	$DstWidth	= $imginfo[0];
   	$DstHeight  = $imginfo[1];

	$ret = array('files' 	=> $files,
				 'force' 	=> $force,
				 'width' 	=> $DstWidth,
				 'height'	=> $DstHeight);

	ImageProtection_userapi_debug ($ret);
	return $ret;
} // function ImageProtection_userapi_WatermarkAndResizeImage ($args)

/**
 * Creates the Protected Image with Userinformation over the Image
 *
 * @param string 	$args[srcPathFile]	The Original File (with Path)
 * @param string	$args[dstPath]		The Destination Directory of the protected Image
 * @param integer 	$args[SrcWidth]		The Width of the Original Image
 * @param integer 	$args[SrcHeight]	The Height of the Original Image
 * @param boolean	$args['force']		The force the creation of the Image also if the Image already exists
 *
 */
function ImageProtection_userapi_CreateProtectedImage ($args)
{
	ImageProtection_userapi_debug ("Input ImageProtection_userapi_CreateProtectedImage");
	ImageProtection_userapi_debug ($args);

	$srcPathFile 	= $args['srcPathFile'];
	$dstPath		= $args['dstPath'];
	$SrcWidth 		= $args['SrcWidth'];
	$SrcHeight 		= $args['SrcHeight'];
	$force			= $args['force'];
	$settings 		= pnModGetVar('ImageProtection');

	$username 	 = pnUserGetVar('uname');
	$srcFile 	 = basename($srcPathFile);
	$dstPath	 = $dstPath.$username."/";
	$dstPathFile = $dstPath.$srcFile;

	if (!is_dir($dstPath)) {
		mkdir($dstPath);
	}

	ImageProtection_userapi_add2DelCache($dstPathFile);

	if (file_exists($dstPathFile) && $force == false) {
		ImageProtection_userapi_debug ("Protected File $dstPathFile already exists and FORCE=FALSE");
	} else {

		$uid=pnUserGetVar('uid');
		$Vars = pnUserGetVars($uid);
		$needle = array();
		$str	= array();
		$str1	= array();
		foreach ($Vars as $key => $val) {
			if ($key != '__ATTRIBUTES__' && substr($key,0,3) != 'pn_') {
				$needle[] = '%'.$key.'%';
				$str[] = $val;
			}
		}

		$ProfVars = pnModAPIFunc('MyProfile','user','getProfile',array('uid'=>$uid));
		foreach ($ProfVars as $key => $val) {
			$fieldtype  	= $val['fieldtype'];
			$list 			= $val['list'];
			$dropdownitems  = $val['dropdownitems'];
			$radioitems     = $val['radioitems'];
			$radios = strpos($list,"@@");

			if ($fieldtype == "STRING" && $list!="" && $radios !== false) {
				$vals[$key] = $dropdownitems[$val['value']]['text'];
				$needle[] 	= '%'.$key.'%';
				$str[] 		= defined($vals[$key]) ? constant($vals[$key]) : $vals[$key];
			} else if ($fieldtype == "STRING" && $list!="" && $radios === false) {
				$vals[$key] = $radioitems[$val['value']]['text'];
				$needle[] 	= '%'.$key.'%';
				$str[] 		= defined($vals[$key]) ? constant($vals[$key]) : $vals[$key];
			} else {
				$vals[$key] = $val['value'];
				$needle[] 	= '%'.$key.'%';
				$str[] 		= defined($vals[$key]) ? constant($vals[$key]) : $vals[$key];
			}
		}

		for($i=0; $i<count($needle); $i++) {
			$PVars[$needle[$i]] = $str[$i];
		}

		ImageProtection_userapi_debug("Possible Variables for Text:");
		ImageProtection_userapi_debug($PVars);

		if (pnUserLoggedIn()) {
			$text = str_replace($needle,$str,$settings['wmt_text']);
		} else {
			$text = "";
		}
		$size 		= $settings['wmt_size'];
		$alignment 	= $settings['wmt_alignment'];
		$hex_color 	= $settings['wmt_textcolor'];
		$ttffont 	= $settings['wmt_font'];
		$opacity 	= $settings['wmt_opacity'];
		$margin 	= $settings['wmt_margin'];
		if ($settings['wmt_angel'] == "auto") {
			$angelrad = atan($SrcHeight/$SrcWidth);
			$angelgrad = $angelrad / (2 * pi()) * 360;
			$angle 	   = intval($angelgrad);
		} else {
			$angle = $settings['wmt_angel'];
		}
		//$bg_color 	= false;
		//$bg_opacity = 0;
		//$filter[] = "wmt|$text|$size|$alignment|$hex_color|$ttffont|$opacity|$margin|$angle|$bg_color|$bg_opacity";
		unset($filter);
		$filter[] = "wmt|$text|$size|$alignment|$hex_color|$ttffont|$opacity|$margin|$angle";
		ImageProtection_userapi_debug ("Filter Settings for Text Watermark");
		ImageProtection_userapi_debug ($filter);

		pnModAPIFunc('Thumbnail',
   			  		 'user',
   			  		 'generateImage',
   			  		 array('srcFilename' => $srcPathFile,
   			  			   'dstFilename' => $dstPathFile,
   			  			   'fltr' 		 => $filter));
	}

	$files[] = $dstPathFile;
	$ret = array('files' 	=> $files,
			 	 'force' 	=> $force,
			 	 'width' 	=> $SrcWidth,
			 	 'height'	=> $SrcHeight);
	return $ret;
} // function ImageProtection_userapi_CreateProtectedImage ($args)

/**
 * Cretes the Protected Image with Userinfomration over theImage
 *
 * @param string 	$args[src]			The Source File (Full Path)
 * @param boolean	$args['force']		The force the creation of the Image also if the Image already exists
 *
 */
function ImageProtection_userapi_CropImage ($args)
{
	ImageProtection_userapi_debug ("Input ImageProtection_userapi_CropImage");
	ImageProtection_userapi_debug ($args);

	$srcPathFile 	= $args['srcPathFile'];
	$dstPath		= $args['dstPath'];
	$SrcWidth 		= $args['SrcWidth'];
	$SrcHeight 		= $args['SrcHeight'];
	$force			= $args['force'];
	$settings 		= pnModGetVar('ImageProtection');

	$pathifo = pathinfo($srcPathFile);

	$srcFile 	= $pathifo['basename'];
	$srcExt 	= $pathifo['extension'];
	$srcName 	= substr($srcFile,0,strlen($srcFile)-strlen($srcExt)-1);


	$ret['force'] 	= $force;
	$ret['width'] 	= $SrcWidth;
	$ret['height'] 	= $SrcHeight;

	$username 	= pnUserGetVar('uname');

	$Rows 		= $settings['crop_rows'];
	$RowHeight 	= intval($SrcHeight/$Rows);

	for ($rcount=0; $rcount<$Rows; $rcount++) {
		$dstName = $srcName."_".$rcount.".".$srcExt;
		$hashname = md5($username.$dstName).".".$srcExt;

		$prefix = substr($hashname,0,2);
		$dstpath 	= $dstPath.$prefix."/";
		if (!is_dir($dstpath)) {
			mkdir($dstpath);
		}

		$dst 	 = $dstpath.$hashname;
		$files[] = $dst;
		ImageProtection_userapi_add2DelCache($dst);

		if (file_exists($dst) && $force == false) {
			ImageProtection_userapi_debug ("Croped File $dst already exists and FORCE=FALSE");
		} else {
			$top 	 = ($rcount * $RowHeight);
			$bottom	 = $SrcHeight - ($rcount*$RowHeight + $RowHeight);

			unset($filter);
			$filter[] = "crop|0|0|$top|$bottom";
			ImageProtection_userapi_debug ("Filter Settings for Cropping for:".$dst);
			ImageProtection_userapi_debug ($filter);

			$ret = pnModAPIFunc('Thumbnail',
	       			  			'user',
	       			  			'generateImage',
	       			  			array('srcFilename' => $srcPathFile,
	       			  			  	  'dstFilename' => $dst,
	       			  			  	  'fltr' 		=> $filter));
		}
	}

	$ret = array('files' 		=> $files,
		 	 	 'force' 		=> $force,
		 	 	 'width' 		=> $SrcWidth,
		 	 	 'height'		=> $SrcHeight,
				 'SingleWidth' 	=> $SrcWidth,
				 'SingleHeight' => $RowHeight);
	return $ret;

} // function ImageProtection_userapi_CropImage ($args)

/**
 * Check if the Thumb Folder is reachable by the Webser and formt the source,
 * so that the file can be shwon in the browser
 *
 * @param string $args[src]		The absolut path to the thumbnail
 */
function ImageProtection_userapi_checkThumbSource ($args)
{
	$file = $args['src'];

	$DocRoot 	= pnServerGetVar('DOCUMENT_ROOT');
	$http 	 	= pnServerGetProtocol();
	$host 	 	= pnServerGetVar('HTTP_HOST');
	$URL	 	= $http.'://'.$host;
	//$settings 	= pnModGetVar('ImageProtection');

	$lastchar = substr($URL,-1);
	if ($lastchar != "/") {
		$URL = $URL."/";
	}

	if (strpos($file,$DocRoot) !== false) {
		//Path over Document Root so we can use it
		$file = str_replace($DocRoot,$URL,$file);
	} else {
		// not yet implemented. Here we must send the thumbnail dynamically to the browser
	}

	return $file;

}

/**
 * Check if the Path is under the Document Root and format it
 */
function ImageProtection_userapi_FormatPaths ($args)
{
	ImageProtection_userapi_debug ("Input ImageProtection_userapi_FormatPaths");
	ImageProtection_userapi_debug ($args);

	$ret   = $args['ret'];
	$files = $ret['files'];
	$force = $ret['force'];

	$DocRoot 	= pnServerGetVar('DOCUMENT_ROOT');
	$http 	 	= pnServerGetProtocol();
	$host 	 	= pnServerGetVar('HTTP_HOST');
	$URL	 	= $http.'://'.$host;
	$settings 	= pnModGetVar('ImageProtection');

	$lastchar = substr($URL,-1);
	if ($lastchar != "/") {
		$URL = $URL."/";
	}

	$lastchar = substr($DocRoot,-1);
	if ($lastchar != "/") {
		$DocRoot = $DocRoot."/";
	}

	foreach ($files as $file) {

		if (strpos($file,$DocRoot) !== false) {
			//Path over Document Root so we can use it

			$file = str_replace($DocRoot,$URL,$file);
			$Ffiles[] = $file;
		} else {
			// Path is outside Document Root so copy it to temp Dir
			$pathinfo	=pathinfo($file);
			$filename 	= $pathinfo['basename'];
			$sufix 		= substr($filename,0,2);
			$tmpdir 	= $settings['path_CacheTemp'].$sufix."/";
			if (!is_dir($tmpdir)) {
				mkdir($tmpdir);
				$fp=fopen($tmpdir.'index.html','w+');
				fclose($fp);
			}
			$dst = $tmpdir.$filename;

			$file_exists = file_exists($dst);
			if (!$file_exists || $force === true ) {
				copy($file,$dst);
			}

			$file = str_replace($DocRoot,$URL,$dst);
			$Ffiles[]   = $file;
		}
	}

	$ret['files'] = $Ffiles;
	return $ret;
} //ImageProtection_userapi_FormatPaths


/**
 * Debug Functions
 *
 * @param variant $args
 */
function ImageProtection_userapi_debug ($args) {
	$debug = pnSessionGetVar("ImageProtectionDebug");
	//$debug = 1;
	if ($debug == 1) {
		prayer($args);
	}
} // function ImageProtection_userapi_debug ($args) {

/**
 * Adds Files to the internal Delete cache to be deleted after Image Creation
 *
 * @param string $file
 */
function ImageProtection_userapi_add2DelCache ($file) {
	$settings 	= pnModGetVar('ImageProtection');

	if ($settings['cache_enabled'] != 1) {
		$var = pnSessionGetVar('ImageProtection_DelCache',array());
		$var[] = $file;
		pnSessionSetVar('ImageProtection_DelCache',$var);
	}
}

function ImageProtection_userapi_removeDelCache ($noDeleteFiles) {

	$settings 	= pnModGetVar('ImageProtection');

	if ($settings['cache_enabled'] != 1) {
		$files = pnSessionGetVar('ImageProtection_DelCache');
		pnSessionDelVar('ImageProtection_DelCache');

		foreach ($files as $file) {
			if(!in_array($file,$noDeleteFiles)) {
				$delFiles[] = $file;
				@unlink($file);
			}
		}
		ImageProtection_userapi_debug("Temp Files Deleted");
		ImageProtection_userapi_debug($delFiles);
	}
}

/**
 * Resize an Image
 * We have to do this by our own, because the phpThumb looses the transparency during resizing.
 *
 * @param string 	$img			The Source File
 * @param integer 	$w				New Width
 * @param integer 	$h				New Height
 * @param string 	$newfilename	The Name of the new Image
 *
 * @return $newfilename
 */
function ImageProtection_userapi_resize($img, $w, $h, $newfilename) {

	//Check if GD extension is loaded
 	if (!extension_loaded('gd') && !extension_loaded('gd2')) {
  		trigger_error("GD is not loaded", E_USER_WARNING);
  		return false;
 	}

	//Get Image size info
 	$imgInfo = getimagesize($img);
 	switch ($imgInfo[2]) {
  		case 1: $im = imagecreatefromgif($img); break;
  		case 2: $im = imagecreatefromjpeg($img);  break;
  		case 3: $im = imagecreatefrompng($img); break;
  		default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
 	}

	//If image dimension is smaller, do not resize
 	if ($imgInfo[0] <= $w && $imgInfo[1] <= $h) {
  		$nHeight = $imgInfo[1];
  		$nWidth = $imgInfo[0];
 	}else{
    	//yeah, resize it, but keep it proportional
  		if ($w/$imgInfo[0] < $h/$imgInfo[1]) {
   			$nWidth = $w;
   			$nHeight = $imgInfo[1]*($w/$imgInfo[0]);
  		}else{
   			$nWidth = $imgInfo[0]*($h/$imgInfo[1]);
   			$nHeight = $h;
  		}
 	}

 	$nWidth = round($nWidth);
 	$nHeight = round($nHeight);

 	$newImg = imagecreatetruecolor($nWidth, $nHeight);

 	/* Check if this image is PNG or GIF, then set if Transparent*/
 	if(($imgInfo[2] == 1) OR ($imgInfo[2]==3)){
  		imagealphablending($newImg, false);
  		imagesavealpha($newImg,true);
  		$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
  		imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $transparent);
 	}
 	imagecopyresampled($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);

 	//	Generate the file, and rename it to $newfilename
 	switch ($imgInfo[2]) {
  		case 1: imagegif($newImg,$newfilename); break;
  		case 2: imagejpeg($newImg,$newfilename);  break;
  		case 3: imagepng($newImg,$newfilename); break;
  		default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
 	}

	return $newfilename;
} // function ImageProtection_userapi_resize($img, $w, $h, $newfilename) {


/**
 * Create a unique Filename
 *
 * @return
 */
function ImageProtection_userapi_CreateName ($args)
{

	$baseURI = FormUtil::getPassedValue("baseURI");
	$arrUrlFragments = parse_url($baseURI);

	$params = split("&",$arrUrlFragments['query']);
	foreach ($params as $param){
		$hlp = split("=",$param);
		$args[$hlp[0]]=$hlp[1];
	}

	$file = $args['fileinfo'];

	//$fileinfo = pathinfo($file['name']);
	$filename 	= $file['name'];
	//$srcFile 	= $fileinfo['basename'];
	//$srcExt 	= $fileinfo['extension'];
	//$srcName 	= substr($srcFile,0,strlen($srcFile)-strlen($srcExt)-1);
	$date 		= DateUtil::formatDatetime(DateUtil::makeTimestamp(),'%Y%m%d%H%M%S');

	$file['name'] = $date."_".$filename;
	if (strtoupper($args['module']) == "TORD") {
		$file['extraPath'] = "TorD2/".$args['type'];
		if ($args['type'] == "loesungen") {
			if (isset($args['id']) && $args['id'] != "") {
				$id = sprintf("%07d",$args['id']);
				$md5 = md5($date."_".$filename);
				$info = pathinfo($filename);
				$ext = $info['extension'];
				$file['name'] = $id."_".$md5.".".$ext;
			}
		}
	}

	//$file['extraPath'] = $path;

	return $file;
}

/**
 * Do some stuff after Uploading the Image
 *
 * @return
 */
function ImageProtection_userapi_AfterUploadHandling ($args)
{
	$fileinfo 	= $args['fileinfo'];

	$file 		= $fileinfo['name'];
	$extrapath 	= $fileinfo['extraPath'];
	$fullpath 	= isset($fileinfo['fullpath']) ? $fileinfo['fullpath'] : false;

	$paths = ImageProtection_userapi_generatePaths(array('src'   		=> $file,
    					 	   							 'extraPath' 	=> $extrapath,
    					 	   							 'fullpath' 	=> $fullpath));

	$upfile = $paths['OrigFile'];
	$thumfile = $paths['ThumbFile'];

	// Move uploaded File to Original Path
	$ret = move_uploaded_file($fileinfo['tmp_name'], $upfile);
	if (!$ret) {
		copy($fileinfo['tmp_name'],$upfile);
		$ret = file_exists($upfile);
	}

	if (!$ret) {
		return false;
	}

	$ret1 = ImageProtection_userapi_CreateThumbnail(array('extrapath' => $extrapath,
    					 	   							  'fullpath'  => $fullpath,
														  'src'  	  => $fileinfo['name']));

	if ($ret1 === false) {
		$ret = false;
	}

	$ThumbInfo	= getimagesize($ret1);
  	$ThumbW		= $ThumbInfo[0];
  	$ThumbH  	= $ThumbInfo[1];

  	$OrigSize = ImageProtection_userapi_getEstimatedSize(array('file'=>$upfile));

	if ($ret) {
		return array('OrigFile' 	=> $upfile,
					 'OrigName' 	=> $fileinfo['name'],
					 'OrigEName' 	=> $extrapath."/".$fileinfo['name'],
					 'OrigWidth' 	=> $OrigSize['Width'],
					 'OrigHeight'	=> $OrigSize['Height'],
					 'OrigSize'		=> $OrigSize['Size'],
					 'ThumbFile' 	=> $thumfile,
					 'ThumbName' 	=> $fileinfo['name'],
					 'ThumbEName' 	=> $extrapath."/".$fileinfo['name'],
					 'ThumbWidth' 	=> $ThumbW,
					 'ThumbHeight'	=> $ThumbH,
					 'ThumbSize' 	=> filesize($thumfile));
	} else {
		return false;
	}

}

function ImageProtection_userapi_existsThumbnail ($args)
{
	ImageProtection_userapi_debug ("Input ImageProtection_userapi_existsThumbnail");
	ImageProtection_userapi_debug ($args);

	$src 		= $args['src'];
	$extrapath 	= $args['extrapath'];
	$fullpath 	= $args['fullpath'];

	$paths = ImageProtection_userapi_generatePaths(array('src'   		=> $src,
    					 	   							 'extraPath' 	=> $extrapath,
    					 	   							 'fullpath' 	=> $fullpath));

	$thumfile = $paths['ThumbFile'];

	ImageProtection_userapi_debug(array('thumbfile'=>$thumfile));

	$exists =  file_exists($thumfile);
	if (!$exists) {
		ImageProtection_userapi_debug ("Thumbnail does not exists");
		return false;
	} else {
		ImageProtection_userapi_debug ("Thumbnail:".$thumfile);
		return $thumfile;
	}
}

function ImageProtection_userapi_CreateThumbnail ($args)
{
	ImageProtection_userapi_debug ("Input ImageProtection_userapi_CreateThumbnail");
	ImageProtection_userapi_debug ($args);

	$extrapath 	= $args['extrapath'];
	$filename	= $args['src'];
	$fullpath	= $args['fullpath'];
	$settings 	= pnModGetVar('ImageProtection');
	$w 			= isset($args['width'])  ? $args['width']  :  $settings['common_MaxThumbWidth'];
	$h 			= isset($args['height']) ? $args['height'] :  $settings['common_MaxThumbHeight'];

	$paths = ImageProtection_userapi_generatePaths(array('src'   		=> $filename,
    					 	   							 'extraPath' 	=> $extrapath,
    					 	   							 'fullpath' 	=> $fullpath));
	$origfile = $paths['OrigFile'];
	$thumfile = $paths['ThumbFile'];

	// Create Thumbnail and copy it ot the thumb Path
	$ret = pnModAPIFunc('Thumbnail',
					 	'user',
						'generateImage',
						array('srcFilename' => $origfile,
							  'dstFilename' => $thumfile,
							  'w'			=> $w,
							  'h'			=> $h));

	return $ret;

}

function ImageProtection_userapi_getEstimatedSize ($args)
{
	$file = $args['file'];

	$settings 		= pnModGetVar('ImageProtection');

	$ImgInfo= getimagesize($file);
  	$w		= $ImgInfo[0];
  	$h  	= $ImgInfo[1];
	$origpixel  = $w * $h;
	$origsize   = filesize($file);
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
 	$newpixel = $nWidth*$nHeight;
	$newsize = round(($origsize / $origpixel) * $newpixel,0);

 	return array('Width' => $nWidth, 'Height' => $nHeight, 'Size' => $newsize);

}

function ImageProtection_userapi_createThumbURL ($args)
{

	//$dom = ZLanguage::getModuleDomain('ImageProtection');
	$src 				= $args['src'];
	$title 				= $args['title'];
	$show				= isset($args['showImageLink']) ? $args['showImageLink'] : false;
	$fullpath			= $args['fullpath'];
	$extrapath			= $args['extrapath'];
	$force				= isset($args['force']) ? $args['force'] : false;

	$settings 	= pnModGetVar('ImageProtection');

	$width				= isset($args['width'])  ? $args['width']  : $settings['common_MaxThumbWidth'];
	$height				= isset($args['height']) ? $args['height'] : $settings['common_MaxThumbHeight'];
	if ($src != "" ) {
   		$exists = pnModAPIFunc('ImageProtection',
							 	'user',
								'existsThumbnail',
								array('extrapath' => $extrapath,
									  'fullpath'  => $fullpath,
									  'src' 	  => $src,
									  'width'	  => $width,
									  'height'	  => $height));
		$thumb = $exists;
   		if ($exists === false || $force) {

			$ret = pnModAPIFunc('ImageProtection',
							 	'user',
								'CreateThumbnail',
								array('extrapath' 	=> $extrapath,
									  'fullpath' 	=> $fullpath,
									  'src' 	  	=> $src,
									  'w'			=> $width,
									  'h'			=> $height));
   			$thumb = $ret;
   		}
   		if ($ret === false) {
   			$html = "error in ImageProtection_userapi_createThumbURL";
   		} else {

   			$thumbnail = pnModAPIFunc('ImageProtection',
							 		  'user',
									  'checkThumbSource',
									  array('src' 	  => $thumb));

   			$html = "<img src=\"$thumbnail\" title=\"$title\" />";
   		}
	} else {
		$html = "";
	}

	return $html;
}

function ImageProtection_userapi_FormatURL ($args)
{

	//$objectid  = $args['objectid'];
	$extrainfo = $args['extrainfo'];

	for ($i=0; $i<count($extrainfo); $i++) {
		$text = $extrainfo[$i];

		$values=array();
		$ret = preg_match_all ("|<img(.*)class=\"ImageProtectionImage\"(.*)/>|U", $text, $values);

		if ($ret > 0) {

			$images = $values[0];
		   	$settings = pnModGetVar('ImageProtection');
			$search=array();
			$replace=array();
			for ($j=0; $j<count($values[0]); $j++) {
				$img = $images[$j];
				$atts = trim(trim(trim($images[$j],"<img"),">"));

				$attribs = ParseHTML($atts);
				$src 	 = $attribs['id'];
				$style 	 = $attribs['style'];
				$width 	 = isset($attribs['width']) ? $attribs['width'] : (isset($style['width']) ? $style['width'] : 0);
				$height	 = isset($attribs['height']) ? $attribs['height'] : (isset($style['height']) ? $style['height'] : 0);

//prayer(array('width'=>$width, 'height'=>$height));
				if ($width != 0 && $height != 0) {
					$width 	= trim($width,"px");
					$height = trim($height,"px");
					$thumb 	= $src;
					$file 	= $settings['path_Thumbnail']."/".trim($thumb,"/");

					$ThumbInfo	= getimagesize($file);
				  	$W1			= $ThumbInfo[0];
				  	$H1  		= $ThumbInfo[1];

				  	if (!($W1-2 <= $width &&  $width <= $W1+2 &&
				  		  $H1-2 <= $height &&  $height <= $H1+2)) {

				  		$filename = strrchr($thumb,"/");
						if ($filename === false) {
							$filename = $thumb;
							$extrapath="";
						} else {
							$extrapath = str_replace($filename,"",$thumb);
							$filename=trim($filename,"/");
						}

						ImageProtection_userapi_CreateThumbnail(array('extrapath' => $extrapath,
					    			 	   							  'fullpath'  => false,
																	  'src'  	  => $filename,
																	  'width'	  => $width,
																	  'height'	  => $height));
				  	}
				}


				$pathifo = pathinfo($src);
			   	if ($pathifo['dirname'] == '' || $pathifo['dirname']=='.') {
   					$srcFile = $settings['path_Original'].$src;
   				} else {
   					$srcFile = $src;
   				}

				$size = ImageProtection_userapi_getEstimatedSize(array('file'=>$srcFile));

				$url = pnModURL('ImageProtection',
								'user',
								'viewImage',
								array('height' => $size['Height'],
									  'width'  => $size['Width'],
									  'src'	   => $src));

				$search[] = $img;
				$img = str_replace('class="ImageProtectionImage"','class="ImageProtectionThumb"',$img);
				//$replace[] = "<a href=\"$url\" class=\"modalbox\">$images[$j]</a>";
				$replace[] = "<a href=\"$url\">$img</a>";
			}
			$text = str_replace($search,$replace,$text);
		} else {
			$extrainfo[$i] = $text;
		}

		$extrainfo[$i] = $text;
	}


	return $extrainfo;
}

$num_recursions = 0;
// recursive function to create a directory
function mkdir_recursive($dir)
{
	global $num_recursions;
 	$num_recursions++;

 	$dir_res = @mkdir($dir);

 	if ($num_recursions > 100)
 	{
   		exit;
 	}

 	if ($dir_res === false)
 	{
   		$next_attempt_dir = substr($dir, 0, strrpos(substr($dir, 0, -1), '/'));
      	mkdir_recursive($next_attempt_dir);
	  	$dir_res = mkdir($dir);
 	}
 	else  // dir creation successful.  Reset the global recursion counter.
 	{
   		$num_recursions = 0;
 	}
}

function ParseHTML ($attribs) {

	$pattern = '/(\\w+)\s*=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';
	$matches = array();
	preg_match_all($pattern, $attribs, $matches, PREG_SET_ORDER);
	$attrs = array();
	foreach ($matches as $match) {
	    if (($match[2][0] == '"' || $match[2][0] == "'") && $match[2][0] == $match[2][strlen($match[2])-1]) {
	        $match[2] = substr($match[2], 1, -1);
	    }
	    $name = strtolower($match[1]);
	    $value = html_entity_decode($match[2]);
	    switch ($name) {
		    case 'class':
		        $attrs['class'] = preg_split('/\s+/', trim($value));
		        break;
		    case 'style':
		    	$values = split(";",$value);
		    	$styles = array();
		    	foreach ($values as $val) {
					$h = split (":",$val);
					if (trim($h[0]) != "") {
						$styles[trim($h[0])] = trim($h[1]);
					}
		    	}
		    	$attrs['style'] = $styles;
		        break;
		    default:
		        $attrs[$name] = $value;
	    }
	}
	return $attrs;
}

?>