<?php
/**
 * @package      ImageProtection
 * @version      $Author: Flo $ $Revision: 6 $ $Modtime: 10.02.10 21:33 $ $Id: $
 * @author       Tree Florian
 * @link         http://code.zikula.org/imageprotection/
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

$dom = ZLanguage::getModuleDomain('ImageProtection');

$modversion['name']           = 'ImageProtection';
$modversion['version']        = '0.2';
$modversion['displayname']    = __('ImageProtection', $dom);
$modversion['description']    = __('Provides API Functions for Image protection', $dom);

$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['credits']        = 'pndocs/credits.txt';
$modversion['help']           = 'pndocs/help.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = 0;
$modversion['author']         = 'Florian Tree';
$modversion['contact']        = 'Send a PN to ftree on zikula';

$modversion['admin']          = 1;
$modversion['user']           = 0;

$modversion['securityschema'] = array('ImageProtection::' => '::');

$modversion['dependencies'] = array(array('modname'    => 'Thumbnail',
                                          'minversion' => '1.1',
                                          'maxversion' => '',
                                          'status'     => PNMODULE_DEPENDENCY_REQUIRED
                                         ),
                                    array('modname'    => 'Scheduler',
                                          'minversion' => '1.5',
                                          'maxversion' => '',
                                          'status'     => PNMODULE_DEPENDENCY_RECOMMENDED
                                         ));

?>