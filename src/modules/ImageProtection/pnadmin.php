<?php
/**
 * @package      ImageProtection
 * @version      $Author: Flo $ $Revision: 12 $ $Modtime: 11.03.10 22:01 $ $Id: $
 * @author       Tree Florian
 * @link         https://github.com/ftree/ImageProtection
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

/**
 * Admin main page
 *
 * @return array
 */
function ImageProtection_admin_main()
{
	return pnRedirect(pnModURL('ImageProtection','admin','Settings'));
}


/**
 * Shows the Settings
 *
 * @return HTML Output
 */
function ImageProtection_admin_Settings()
{
	$dom = ZLanguage::getModuleDomain('ImageProtection');
    if (!SecurityUtil::checkPermission('ImageProtection::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
    }

    $settings = pnModGetVar('ImageProtection');

	$TTFdir = pnModGetVar('Thumbnail','ttf_directory');
	if (is_dir($settings['path_Watermarks'])) {
		$d = dir($TTFdir);
		while($TTFfile=$d->read()) {
			$hlp = explode('.', $TTFfile);
			if (strtoupper($hlp[count($hlp)-1]) == 'TTF') {
				$TTFfiles[] = $TTFfile;
			}
		}
	}
	if (is_dir($settings['path_Watermarks'])) {
		$d = dir($settings['path_Watermarks']);
		while($WMfile=$d->read()) {
			$hlp = explode('.', $WMfile);
			$ext = strtoupper($hlp[count($hlp)-1]);
			if ($ext == 'GIF' || $ext == 'PNG' || $ext == 'JPG' || $ext == 'JPEG' || $ext == 'BMP') {
				$WMfiles[] = $WMfile;
			}
		}
	}

	$Vars = pnUserGetVars(pnUserGetVar('uid'));
	$userneedle = array();
	$profileneedle = array();

	foreach ($Vars as $key => $val) {
		if ($key != '__ATTRIBUTES__' && substr($key,0,3) != 'pn_') {
			$userneedle[] = '%'.$key.'%';
		}
	}

	$ProfVars = pnModAPIFunc('MyProfile','user','getProfile',array('uid'=>pnUserGetVar('uid')));
	foreach ($ProfVars as $key => $val) {
		$profileneedle[] = '%'.$key.'%';
	}

	$Access['path_Original'] 		= pnModAPIFunc('ImageProtection','admin','checkPath',array('path'=>$settings['path_Original']));
	$Access['path_Thumbnail'] 		= pnModAPIFunc('ImageProtection','admin','checkPath',array('path'=>$settings['path_Thumbnail']));
	$Access['path_Watermarks'] 		= pnModAPIFunc('ImageProtection','admin','checkPath',array('path'=>$settings['path_Watermarks']));
	$Access['path_Cache'] 			= pnModAPIFunc('ImageProtection','admin','checkPath',array('path'=>$settings['path_Cache']));
	$Access['path_CacheReWmImages'] = pnModAPIFunc('ImageProtection','admin','checkPath',array('path'=>$settings['path_CacheReWmImages']));
	$Access['path_CacheProtected'] 	= pnModAPIFunc('ImageProtection','admin','checkPath',array('path'=>$settings['path_CacheProtected']));
	$Access['path_CacheCroped'] 	= pnModAPIFunc('ImageProtection','admin','checkPath',array('path'=>$settings['path_CacheCroped']));
	$Access['path_CacheWM'] 		= pnModAPIFunc('ImageProtection','admin','checkPath',array('path'=>$settings['path_CacheWM']));
	$Access['path_CacheTemp'] 		= pnModAPIFunc('ImageProtection','admin','checkPath',array('path'=>$settings['path_CacheTemp']));


	// get all mods
    $modules = pnModGetAllMods();

    $Mods = array();
    foreach ($modules as $module) {
		if ($module['type'] == 2 || $module['type'] ==3) {
	        $Mods[$module['name']] = $module['displayname'];
		}
    }

	$settings['wmt_textuservariables'] 	 = implode($userneedle,", ");
	$settings['wmt_textprofilvariables'] = implode($profileneedle,", ");

	// start a new pnRender display instance
	$render = & pnRender::getInstance('ImageProtection',false);

	// Write the settings to the template
	$render->assign('Settings', $settings);
	$render->assign('Modules', $Mods);

	$render->assign('DirAccess', $Access);

	$render->assign('TTFfiles', $TTFfiles);
	$render->assign('WMfiles', $WMfiles);
	// fetch, process and display template
	return $render->fetch('ImageProtection_admin_Settings.htm');
}


/**
 * Writes the settings to the DB
 *
 * @return HTML Output
 */
function ImageProtection_admin_editSettings()
{
	$dom = ZLanguage::getModuleDomain('ImageProtection');
    if (!SecurityUtil::checkPermission('ImageProtection::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError (pnModURL('ImageProtection', 'admin', 'main'));
    }

    $settings = FormUtil::getPassedValue('settings');
	$settings['wmi_enabled']  = $settings['wmi_enabled']  == 1 ? $settings['wmi_enabled']  : 0;
	$settings['wmt_enabled']  = $settings['wmt_enabled']  == 1 ? $settings['wmt_enabled']  : 0;
	$settings['crop_enabled'] = $settings['crop_enabled'] == 1 ? $settings['crop_enabled'] : 0;
	$settings['useModalbox']  = $settings['useModalbox'] == 1 ? $settings['useModalbox'] : 0;

	$settings['path_Original']   	  = ImageProtection_admin_FormatPath(array('path'=>$settings['path_Original']));
	$settings['path_Thumbnail']   	  = ImageProtection_admin_FormatPath(array('path'=>$settings['path_Thumbnail']));
	$settings['path_Watermarks'] 	  = ImageProtection_admin_FormatPath(array('path'=>$settings['path_Watermarks']));
	$settings['path_Cache'] 	 	  = ImageProtection_admin_FormatPath(array('path'=>$settings['path_Cache']));
	$settings['path_CacheReWmImages'] = ImageProtection_admin_FormatPath(array('path'=>$settings['path_CacheReWmImages']));
	$settings['path_CacheProtected']  = ImageProtection_admin_FormatPath(array('path'=>$settings['path_CacheProtected']));
	$settings['path_CacheCroped'] 	  = ImageProtection_admin_FormatPath(array('path'=>$settings['path_CacheCroped']));
	$settings['path_CacheWM'] 	 	  = ImageProtection_admin_FormatPath(array('path'=>$settings['path_CacheWM']));
	$settings['path_CacheTemp'] 	  = ImageProtection_admin_FormatPath(array('path'=>$settings['path_CacheTemp']));

	foreach ($settings as $key => $value) {
		pnModSetVar('ImageProtection', $key, $value);
	}

	// the module configuration has been updated successfuly
    LogUtil::registerStatus (__('Done! Module configuration updated.', $dom));

	return pnRedirect(pnModURL('ImageProtection','admin','Settings'));

}

/**
 * Shows the Test Page
 *
 * @return HTML Output
 */
function ImageProtection_admin_Test()
{
	$dom = ZLanguage::getModuleDomain('ImageProtection');
    if (!SecurityUtil::checkPermission('ImageProtection::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
    }

	$test_image = FormUtil::getPassedValue('test_image');
    $test_show  = FormUtil::getPassedValue('test_show');
    $test_debug = FormUtil::getPassedValue('test_debug');
    $test_cache = FormUtil::getPassedValue('test_cache') == 1 ? false : true;

    if ($test_debug == 1) {
    	pnSessionSetVar("ImageProtectionDebug",1);
    } else {
    	pnSessionDelVar("ImageProtectionDebug");
    }

    //$settings = pnModGetVar('ImageProtection');
	$docRoot = pnServerGetVar('DOCUMENT_ROOT');
	$lastchar = substr($docRoot,-1);
	if ($lastchar != "\\" && $lastchar != "/" && $path != "") {
		$docRoot = $docRoot."/";
	}
	$dir = $docRoot.pnModGetBaseDir('ImageProtection').'/pnimages/test';
	if (is_dir($dir)) {
		$d = dir($dir);
		while($TESTfile=$d->read()) {
			$hlp = explode('.', $TESTfile);
			$ext = strtoupper($hlp[count($hlp)-1]);
			if ($ext == 'GIF' || $ext == 'PNG' || $ext == 'JPG' || $ext == 'JPEG' || $ext == 'BMP') {
				$TESTfiles[] = array('file'=>$dir.'/'.$TESTfile,'display'=>$TESTfile);
			}
		}
	}

	if ($test_show == 1) {
		pnModAPIFunc('ImageProtection','user','LoadThickBox');
	}

	// start a new pnRender display instance
	$render = & pnRender::getInstance('ImageProtection',false);
	$render->assign('TESTfiles',  $TESTfiles);
	$render->assign('test_image', $test_image);
	$render->assign('test_debug', $test_debug);
	$render->assign('test_cache', $test_cache ? 0 : 1);

	$render->assign('showimage', 	$test_show);
	$render->assign('force', 		$test_cache);
	$render->assign('src',   		$test_image);

	// fetch, process and display template
	return $render->fetch('ImageProtection_admin_Test.htm');
}

/**
 * Formats a Path with trailing slash and creates the dirs
 *
 * @param string $args[path]	Path
 */
function ImageProtection_admin_FormatPath($args)
{
	$path = $args['path'];
	$path = str_replace("\\","/",$path);
	$lastchar = substr($path,-1);
	if ($lastchar != "\\" && $lastchar != "/" && $path != "") {
		$path = $path."/";
	}
	mkdir($path,null,true);
	$fp=fopen($path.'index.html','w+');
	fclose($fp);

	return $path;
}

/**
 * Shows the Cache Settings
 *
 * @return HTML Output
 */
function ImageProtection_admin_Cache()
{
	$dom = ZLanguage::getModuleDomain('ImageProtection');
	if (!SecurityUtil::checkPermission('ImageProtection::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
    }

	$settings = pnModGetVar('ImageProtection');

	$DirInfo['path_CacheReWmImages'] = pnModAPIFunc('ImageProtection','admin','getDirInfo',array('path'=>$settings['path_CacheReWmImages']));
	$DirInfo['path_CacheProtected']  = pnModAPIFunc('ImageProtection','admin','getDirInfo',array('path'=>$settings['path_CacheProtected']));
	$DirInfo['path_CacheCroped'] 	 = pnModAPIFunc('ImageProtection','admin','getDirInfo',array('path'=>$settings['path_CacheCroped']));
	$DirInfo['path_CacheWM'] 		 = pnModAPIFunc('ImageProtection','admin','getDirInfo',array('path'=>$settings['path_CacheWM']));
	$DirInfo['path_CacheTemp'] 	     = pnModAPIFunc('ImageProtection','admin','getDirInfo',array('path'=>$settings['path_CacheTemp']));

	$Dels = pnSessionGetVar('IMPRO_DELS');
	pnSessionDelVar('IMPRO_DELS');

	// start a new pnRender display instance
	$render = & pnRender::getInstance('ImageProtection',false);

	// Write the settings to the template
	$render->assign('Settings', $settings);

	$render->assign('DirInfo', $DirInfo);
	$render->assign('DelInfo', $Dels);

	// fetch, process and display template
	return $render->fetch('ImageProtection_admin_Cache.htm');

}

/**
 * Writes the settings to the DB
 *
 * @return HTML Output
 */
function ImageProtection_admin_editCache()
{
	$dom = ZLanguage::getModuleDomain('ImageProtection');
	if (!SecurityUtil::checkPermission('ImageProtection::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError (pnModURL('ImageProtection', 'admin', 'main'));
    }

    $settings = FormUtil::getPassedValue('settings');
	$settings['cache_enabled']  = $settings['cache_enabled']  == 1 ? $settings['cache_enabled']  : 0;

	foreach ($settings as $key => $value) {
		pnModSetVar('ImageProtection', $key, $value);
	}

	// the module configuration has been updated successfuly
    LogUtil::registerStatus (__('Done! Module configuration updated.', $dom));

	return pnRedirect(pnModURL('ImageProtection','admin','Cache'));

}

/**
 * Deletes the Cache with the specific Settings
 *
 * @return HTML Output
 */
function ImageProtection_admin_deleteCache()
{
	$dom = ZLanguage::getModuleDomain('ImageProtection');
	if (!SecurityUtil::checkPermission('ImageProtection::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
    }

	$Dels = pnModAPIFunc('ImageProtection',
			 		     'admin',
						 'deleteCache');

	pnSessionSetVar('IMPRO_DELS',$Dels);

    return pnRedirect(pnModURL('ImageProtection','admin','Cache'));
}

/**
 * Deletes the complete Cache without the Age Settings
 *
 * @return HTML Output
 */
function ImageProtection_admin_deleteCompleteCache()
{
	$dom = ZLanguage::getModuleDomain('ImageProtection');
	if (!SecurityUtil::checkPermission('ImageProtection::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
    }

   	$Dels = pnModAPIFunc('ImageProtection',
			 		     'admin',
						 'deleteCompleteCache');

	pnSessionSetVar('IMPRO_DELS',$Dels);


    return pnRedirect(pnModURL('ImageProtection','admin','Cache'));
}



?>