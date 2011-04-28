<?php
/**
 * @package      ImageProtection
 * @version      $Author: Flo $ $Revision: 11 $ $Modtime: 11.03.10 21:49 $ $Id: $
 * @author       Tree Florian
 * @link         http://code.zikula.org/imageprotection/
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */


/**
 * initialise the TorD module
 *
 * @author       Tree Florian
 * @return       bool       true on success, false otherwise
 */
function ImageProtection_init()
{
	$dom = ZLanguage::getModuleDomain('ImageProtection');
	    
	$settings = array('common_MaxWidth' 		=> 800,
					  'common_MaxHeight' 		=> 600,
					  'common_MaxThumbWidth' 	=> 100,
					  'common_MaxThumbHeight' 	=> 100,
					  'useModalbox'				=> 0,
					  'common_FuncMod' 			=> 'ImageProtection',
					  'common_FuncType' 		=> 'user',
					  'common_FuncFunc' 		=> 'CreateName',
					  'path_Original' 			=> '..../DATA/original',
					  'path_Thumbnail' 			=> '..../DATA/thumbnail',
					  'path_Watermarks' 		=> '..../DATA/watermarks',
					  'path_Cache' 				=> '..../DATA/cache',
					  'path_CacheWM' 			=> '..../DATA/cache/watermarks',
					  'path_CacheReWmImages' 	=> '..../DATA/cache/rewmimages',
					  'path_CacheProtected' 	=> '..../DATA/cache/protected',
					  'path_CacheCroped' 		=> '..../DATA/cache/croped',
					  'path_CacheTemp' 			=> '..../TEMP',
					  'wmi_enabled' 			=> 1,
					  'wmi_sizeperz' 			=> 15,
					  'wmi_pos' 				=> 'BR',
					  'wmi_opacity' 			=> 100,
					  'wmi_image'				=> '',
 					  'wmt_enabled' 			=> 1,
 					  'wmt_alignment'			=> '*',
					  'wmt_font' 				=> 'arial.ttf',
					  'wmt_margin' 				=> 5,
					  'wmt_opacity' 			=> 50,
					  'wmt_size' 				=> 20,
					  'wmt_text' 				=> '%uname% (%email%)',
					  'wmt_textcolor' 			=> '000000',
					  'wmt_angel'				=> 'auto',
					  'crop_enabled'			=> 1,
					  'crop_rows'				=> 5,
					  'crop_columns'			=> 1,
					  'cache_enabled'			=> 1,
					  'age_CacheWM'				=> 0,
					  'age_CacheReWmImages' 	=> 10,
					  'age_CacheProtected' 		=> 5,
					  'age_CacheCroped' 		=> 5,
					  'age_CacheTemp' 			=> 5
	);

	foreach ($settings as $key => $value) {
		pnModSetVar('ImageProtection', $key, $value);
	}

	// Register System Hook
    if (!pnModRegisterHook('zikula', 'systeminit', 'GUI', 'ImageProtection', 'user', 'LoadThickBox')) {
        return LogUtil::registerError(__('Error creating System Hook!', $dom));
    }	
	
    // Set up module hooks
    if (!pnModRegisterHook('item', 'transform', 'API', 'ImageProtection', 'user','FormatURL')) {
        return LogUtil::registerError(__('Error creating Module Hook!', $dom));
    }	
	
    // Initialisation successful

    return true;
}


/**
 * upgrade the module from an old version
 *
 * @author       Tree Florian
 * @return       bool       true on success, false otherwise
 */
function ImageProtection_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch($oldversion) {
		default:
        break;
    }

    // Update successful
    return true;
}


/**
 * delete the TorD module
 *
 *
 * @author       Tree Florian
 * @return       bool       false
 */
function ImageProtection_delete()
{
	$dom = ZLanguage::getModuleDomain('ImageProtection');
		
    pnModDelVar('ImageProtection');

    // delete the system init hook
    if (!pnModUnregisterHook('zikula', 'systeminit', 'GUI', 'ImageProtection', 'user', 'LoadThickBox')) {
        return LogUtil::registerError(__('Error deleting System Hook!', $dom));
    }
    
    // delete the system init hook
    if (!pnModUnregisterHook('item', 'transform', 'API', 'ImageProtection', 'user','FormatURL')) {
        return LogUtil::registerError(__('Error deleting Modul Hook!', $dom));
    }    
    
    // Deletion successful
    return true;


}
