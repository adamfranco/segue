<?php

/**
 * Set up the MIME service for sniffing mime types
 *
 * USAGE: Copy this file to mime.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: mime_default.conf.php,v 1.2 2006/01/13 18:51:17 adamfranco Exp $
 */
 
// :: Set up the MIME service for sniffing mime types ::
	$configuration =& new ConfigurationProperties;
	Services::startManagerAsService("MIMEManager", $context, $configuration);