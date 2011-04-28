<?php
/**
 * @package      ImageProtection
 * @version      $Author: Flo $ $Revision: 6 $ $Modtime: 8.02.10 21:30 $ $Id: $
 * @author       Tree Florian
 * @link         http://code.zikula.org/imageprotection/
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */


/**
 * get available admin panel links
 *
 * @return array array of admin links
 */
function ImageProtection_adminapi_getlinks()
{
	$dom = ZLanguage::getModuleDomain('ImageProtection');
    if (!SecurityUtil::checkPermission('ImageProtection::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
    }
        
    $links = array();
    $links[] = array('url' => pnModURL('ImageProtection', 'admin', 'Settings'), 'text' => __('Settings', $dom));
	$links[] = array('url' => pnModURL('ImageProtection', 'admin', 'Cache'), 	'text' => __('Cache Settings', $dom));    
    $links[] = array('url' => pnModURL('ImageProtection', 'admin', 'Test'),     'text' => __('Test', $dom));
    
    return $links;
}

/**
 * Delete the cache with use of the Settings
 *
 * @return array[]['count','size']
 */
function ImageProtection_adminapi_deleteCache()
{
    $settings = pnModGetVar('ImageProtection');    

   	$paths = array('CacheReWmImages',
			   'CacheProtected',
			   'CacheCroped',
			   'CacheWM',
			   'CacheTemp');
    
	foreach ($paths as $path) {
		if ($settings["age_$path"] > 0 ) {
			$Dels["path_$path"] = ImageProtection_adminapi_deleteFiles(array('path' => $settings["path_$path"],
															  	 	     	 'age'  => $settings["age_$path"]));
		}    
	}

	return $Dels;
}

/**
 * Delete the complete cache without use of the Settings
 *
 * @return array[]['count','size']
 */
function ImageProtection_adminapi_deleteCompleteCache()
{
	
	$settings = pnModGetVar('ImageProtection');    

	$paths = array('CacheReWmImages',
				   'CacheProtected',
				   'CacheCroped',
				   'CacheWM',
				   'CacheTemp');
	
	foreach ($paths as $path) {
		$Dels["path_$path"] = ImageProtection_adminapi_deleteFiles(array('path' => $settings["path_$path"],
															  	 	     'age'  => 0));    
	}
	return $Dels;
}

/**
 * Function for Deleting the Files
 *
 * @param string $args[path]
 * @param integer $args['age']
 * 
 * @return array('count','size')
 */
function ImageProtection_adminapi_deleteFiles($args)
{
	$path 	= $args['path'];
	$age 	= $args['age'];

	$comparedatestr = DateUtil::getDatetime_NextDay(intval($age)*-1);
	$comparedate=strtotime($comparedatestr);

	$result = ImageProtection_adminapi_doDeleteFiles(array('path'		 	=> $path, 
												  		   'comparedate'	=> $comparedate,
												 		   'doDateCheck' 	=> ($age==0) ? false : true));

	$result['size'] = ImageProtection_adminapi_sizeFormat($result['size']);
	return $result;
}

/**
 * Checks if a Path exists and is writeable
 * @param string $args[path]	The Path to check
 * 
 * @return Boolean
 */
function ImageProtection_adminapi_checkPath($args)
{
	$path = $args[path];
	$isDir = is_dir($path);
	$isWriteable = is_writeable($path);
	
	return $isDir & $isWriteable;
	
}

/**
 * Returns the amount of Files in a directory
 *
 * @param string $args[path]	The Path to count files
 * 
 * @return array('count', 'size', 'dircount')
 */
function ImageProtection_adminapi_getDirInfo($args)
{
	$path = $args['path'];
	
	$result = ImageProtection_adminapi_getDirectorySize($path);
	$result['size'] = ImageProtection_adminapi_sizeFormat($result['size']);

	return $result;
}


/**
 * Recursive Function for File deletion
 *
 * @param string 	$args[path]
 * @param datetime 	$args[comparedate]
 * @param boolean 	$args[doDateCheck]
 * 
 * @return array('count', 'size')
 */
function ImageProtection_adminapi_doDeleteFiles($args){

	$path 		 = $args['path']; 
	$comparedate = $args['comparedate']; 
	$doDateCheck = $args['doDateCheck'];
	
	$dir = opendir($path);
	$size 	= 0;
	$count 	= 0;
 	if(!$dir){ return 0; }
	while($entry = readdir($dir)){
		if(is_dir($path.$entry) && ($entry != ".." && $entry != ".")){                            
			$args['path'] = $path.$entry.'/'; 
			$result = ImageProtection_adminapi_doDeleteFiles($args);
			$size 	+= $result['size'];
			$count 	+= $result['count'];
		} else {
			if($entry != ".." && $entry != "." && $entry!='index.html') {
				$fulldir=$path.$entry;
				if ($doDateCheck) {
					$last_modified = filemtime($fulldir);
					//$last_modified_str= date("Y-m-d H:i:s", $last_modified);
					if($comparedate > $last_modified)  {
						//echo $fulldir.'=>'.$last_modified_str;
						//echo "<BR>";
						$size += filesize ($fulldir);
						$count += 1;	
						unlink($fulldir);				
					}
				} else {
					//echo $fulldir.'=>'.$last_modified_str;
					//echo "<BR>";
					$size += filesize ($fulldir);
					$count += 1;
					unlink($fulldir);					
				}
			}
		}
	}
	
	return array('size'=> $size, 'count' => $count);
}

/**
 * Recursive Function for File Count and size evaluation
 *
 * @param string $path
 * 
 * @return array('count', 'size', 'dircount')
 */
function ImageProtection_adminapi_getDirectorySize($path)
{
	$totalsize 	= 0;
	$totalcount = 0;
	$dircount 	= 0;
	$handle 	= opendir ($path);
	if ($handle) {
		while (false !== ($file = readdir($handle))) {
			$nextpath = $path . '/' . $file;
			if ($file != '.' && $file != '..' && !is_link ($nextpath) && $file != 'index.html') {
				if (is_dir ($nextpath)) {
					$dircount++;
					$result = ImageProtection_adminapi_getDirectorySize($nextpath);
					$totalsize += $result['size'];
					$totalcount += $result['count'];
					$dircount += $result['dircount'];
				} elseif (is_file ($nextpath)) {
					$totalsize += filesize ($nextpath);
					$totalcount++;
				}
			}
		}
	}
	closedir ($handle);
	$total['size'] = $totalsize;
	$total['count'] = $totalcount;
	$total['dircount'] = $dircount;
	return $total;
} 

/**
 * Formats a given File Size in a readable String
 *
 * @param integer $size
 * 
 * @return string
 */
function ImageProtection_adminapi_sizeFormat($size)
{
    if($size<1024)
    {
        return $size." bytes";
    }
    else if($size<(1024*1024))
    {
        $size=round($size/1024,1);
        return $size." KB";
    }
    else if($size<(1024*1024*1024))
    {
        $size=round($size/(1024*1024),1);
        return $size." MB";
    }
    else
    {
        $size=round($size/(1024*1024*1024),1);
        return $size." GB";
    }

}  


?>