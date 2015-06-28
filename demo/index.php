<?php
/**
 * TOTP
 * Demo
 *
 * @author      MarQuis Knox <hire@marquisknox.com>
 * @copyright   2015 MarQuis Knox
 * @link        http://marquisknox.com
 * @license     GNU Affero General Public License v3
 *
 * @since       Sunday, June 28, 2015 / 13:19 GMT+1
 * @edited      $Date$
 * @version     $Id$
 *
 * @category    Demo
 * @package     TOTP
*/

require_once( dirname( dirname( __FILE__ ) ).'/totp.php');
require_once('includes/phpqrcode/qrlib.php');

// START:   change me
$tempDir    = 'tmp/phpqrcode';
$webUrl     = '//'.$_SERVER['HTTP_HOST'];
$issuedBy   = 'ACME Incorporated';
$label      = 'Example OTP';
// END:     change me

$secret = TOTP::genSecret();
$otp    = TOTP::getOTP( $secret['secret'] );
$uri    = TOTP::genURI( $issuedBy, $label, $secret['secret'] );

// output path
$outputPath = $tempDir.'/'.mt_rand().'.png';

// QR Code
QRcode::png( $uri['uri'], $outputPath, QR_ECLEVEL_L, 10 );

// output the QR Code
echo '<img src="'.$webUrl.'/'.$outputPath.'" />';
