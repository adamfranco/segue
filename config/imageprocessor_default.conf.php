<?php

/**
 * Set up the ImageProcessor service for generating thumbnails
 *
 * USAGE: Copy this file to imageprocessor.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: imageprocessor_default.conf.php,v 1.1 2006/01/13 18:30:22 adamfranco Exp $
 */
 
// :: Set up the ImageProcessor service for generating thumbnails ::
	$configuration =& new ConfigurationProperties;
	$configuration->addProperty('thumbnail_format', $arg0 = "image/jpeg");
	$configuration->addProperty('use_gd', $arg1 = FALSE);
	$configuration->addProperty('gd_formats', $arg2 = array());
	$configuration->addProperty('use_imagemagick', $arg3 = TRUE);
	$configuration->addProperty('imagemagick_path', $arg4 = "/usr/local/bin");
	$configuration->addProperty('imagemagick_temp_dir', $arg5 = "/tmp");
	$configuration->addProperty('imagemagick_formats', $arg2 = array());
	unset ($arg0, $arg1, $arg2, $arg3, $arg4, $arg5);
	Services::startManagerAsService("ImageProcessingManager", $context, $configuration);