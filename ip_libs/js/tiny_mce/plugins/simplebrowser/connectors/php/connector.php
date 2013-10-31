<?php
/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * This is the File Manager Connector for PHP.
 */

 
 /*ImpressPages security*/





	error_reporting(E_ALL|E_STRICT);
	ini_set('display_errors', '1');
	define("BACKEND", "true");  // make sure files are accessed through admin.

    $msConfigPath = '../../../../../../../../../ms_config.php';

    if(inOpenBasedir($msConfigPath) && file_exists($msConfigPath)) {
        require_once ($msConfigPath);
    } elseif (is_file('../../../../../../../ip_config.php')) {
        require_once ('../../../../../../../ip_config.php');
    } else {
        require_once ('../../../../../../../../ip_config.php');
    }
	
	

        session_name(\Ip\Config::getRaw('SESSION_NAME'));
        session_start();
	$admin = false;
  if(isset($_SESSION['backend_session']) && isset($_SESSION['backend_session']['user_id']) && isset($_SESSION['backend_session']['user_id']) != null){
    $admin = true;
  }

	if(!$admin)
		exit;

	

 
 /*eof ImpressPages security*/ 
 
ob_start() ;

require('./config.php') ;
require('./util.php') ;
require('./io.php') ;
require('./basexml.php') ;
require('./commands.php') ;
require('./phpcompat.php') ;

if ( !$Config['Enabled'] )
	SendError( 1, 'This connector is disabled. Please check the "editor/filemanager/connectors/php/config.php" file' ) ;

DoResponse() ;

function DoResponse()
{
    if (!isset($_GET)) {
        global $_GET;
    }
	if ( !isset( $_GET['Command'] ) || !isset( $_GET['Type'] ) || !isset( $_GET['CurrentFolder'] ) )
		return ;

	// Get the main request informaiton.
	$sCommand		= $_GET['Command'] ;
	$sResourceType	= $_GET['Type'] ;
	$sCurrentFolder	= GetCurrentFolder() ;

	// Check if it is an allowed command 
	if ( ! IsAllowedCommand( $sCommand ) ) 
		SendError( 1, 'The "' . $sCommand . '" command isn\'t allowed' ) ;

	// Check if it is an allowed type.
	if ( !IsAllowedType( $sResourceType ) )
		SendError( 1, 'Invalid type specified' ) ;

	// File Upload doesn't have to Return XML, so it must be intercepted before anything.
	if ( $sCommand == 'FileUpload' )
	{
		FileUpload( $sResourceType, $sCurrentFolder, $sCommand ) ;
		return ;
	}

	CreateXmlHeader( $sCommand, $sResourceType, $sCurrentFolder ) ;

	// Execute the required command.
	switch ( $sCommand )
	{
		case 'GetFolders' :
			GetFolders( $sResourceType, $sCurrentFolder ) ;
			break ;
		case 'GetFoldersAndFiles' :
			GetFoldersAndFiles( $sResourceType, $sCurrentFolder ) ;
			break ;
		case 'CreateFolder' :
			CreateFolder( $sResourceType, $sCurrentFolder ) ;
			break ;
	}

	CreateXmlFooter() ;

	exit ;
}


function inOpenBasedir($dir) {
    $openBasedir = ini_get('open_basedir');
    if (empty($openBasedir)) {
        return true;
    }

    foreach (explode(':', $openBasedir) as $basedir)
    {
        if( strlen($basedir) > strlen($dir) )
        {
            // Check, if only a '\' is needed at the end of $dir
            if( $basedir == ($dir . "/") )
            {
                return true;
            }
        }
        else
        {
            // Check if basedir and dir are the same..
            if( $basedir == $dir )
            {
                return true;
            }
            else
            {
                // open_basedir can be a prefix -> checking whether
                // dir starts with basedir or not
                if( strncmp($basedir, $dir, strlen($basedir)) == 0)
                {
                    return true;
                }

            }
        }
    }
    return false;
}

?>