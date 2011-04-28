<?php
/**
 * @package      ImageProtection
 * @version      $Author: Flo $ $Revision: 2 $ $Modtime: 14.08.09 19:15 $ $Id: $
 * @author       Tree Florian
 * @link         http://code.zikula.org/imageprotection/
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
/**
 * Deletes the Cache with the specific Settings
 *
 * @return HTML Output
 */
function ImageProtection_schedulerapi_deleteCache()
{
	pnModAPIFunc('ImageProtection',
			  	 'admin',
				 'deleteCache');
	return true;
}

/**
 * Deletes the complete Cache without the Age Settings
 *
 * @return HTML Output
 */
function ImageProtection_schedulerapi_deleteCompleteCache()
{
	pnModAPIFunc('ImageProtection',
			  	 'admin',
				 'deleteCompleteCache');
	return true;
}
?>