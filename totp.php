<?php
/**
 * TOTP
 * A simple TOTP (RFC 6238) class using the SHA1 default
 *
 * @author      Robin Leffmann <djinn at stolendata dot net>
 * @copyright   2014 Robin Leffmann
 * @link        https://github.com/stolendata/totp
 * @license     Creative Commons BY-NC-SA 4.0
 * @link        http://creativecommons.org/licenses/by-nc-sa/4.0
 *
 * @since       March 2015
 * @edited      $Date$
 * @version     $Id$
 *
 * @category    PHP Class
 * @package     TOTP 
*/

class TOTP
{
    private static $base32Map = 'abcdefghijklmnopqrstuvwxyz234567';

    /**
     * Base32 Decode
     *
     * @param   string  $in
     * @return  string
    */    
    private static function base32Decode( $in )
    {
        $l = strlen( $in );
        $n = $bs = 0;

        for( $i = 0; $i < $l; $i++ ) {
            $n <<= 5;
            $n += stripos( self::$base32Map, $in[$i] );
            $bs = ( $bs + 5 ) % 8;
            @$out .= $bs < 5 ? chr( ($n & (255 << $bs)) >> $bs ) : null;
        }

        return $out;
    }

    /**
     * Generate an OTP
     *
     * @param   string  $secret
     * @param   int     $digits
     * @param   int     $period
     * @return  array
    */    
    public static function getOTP( $secret, $digits = 6, $period = 30 )
    {
        if( strlen($secret) < 16 || strlen($secret) % 8 != 0 )
            return [ 'err'=>'length of secret must be a multiple of 8, and at least 16 characters' ];
        if( preg_match('/[^a-z2-7]/i', $secret) === 1 )
            return [ 'err'=>'secret contains non-base32 characters' ];
        if( $digits < 6 || $digits > 8 )
            return [ 'err'=>'digits must be 6, 7 or 8' ];

        $seed = self::base32Decode( $secret );
        $time = intval( time() / $period );
        $time = str_pad( pack('N', $time), 8, "\x00", STR_PAD_LEFT );
        $hash = hash_hmac( 'sha1', $time, $seed, false );
        $otp = ( hexdec(substr($hash, hexdec($hash[39]) * 2, 8)) & 0x7fffffff ) % pow( 10, $digits );

        return [ 'otp'=>sprintf("%'0{$digits}u", $otp) ];
    }

    /**
     * Generate a secret
     *
     * @param   int  $length
     * @return  array
    */    
    public static function genSecret( $length = 24 )
    {
        if( $length < 16 || $length % 8 != 0 )
            return [ 'err'=>'length must be a multiple of 8, and at least 16' ];

        while( $length-- ) {
            $c = @gettimeofday()['usec'] % 53;
            while( $c-- )
                mt_rand();
            @$secret .= self::$base32Map[mt_rand(0, 31)];
        }

        return [ 'secret'=>strtoupper($secret) ];
    }

    /**
     * Generate an OTP URI
     * 
     * @param   string  $issuedBy
     * @param   string  $label
     * @param   string  $secret
     * @param   boolean $digits
     * @param   int     $period
     * @return  array
    */
    public static function genURI( $issuedBy = '', $label, $secret, $digits = false, $period = null )
    {
        if( empty($label) OR empty($secret) ) {
            return [ 'err'=>'you must provide at least a label and a secret' ];            
        }

        if( strlen( trim( $issuedBy ) ) ) {
            $issuedBy = rawurlencode( $issuedBy ).':';
        } else {
            $issuedBy = '';
        }
        
        
        return [ 'uri'=>'otpauth://totp/' .$issuedBy . rawurlencode( $label ) . "?secret=$secret" .
                        ( !strlen($digits) ? '' : "&digits=$digits") .
                        ( !strlen($period) ? '' : "&period=$period") ];
    }
}
