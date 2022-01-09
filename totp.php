<?php
/*
    TOTP v0.3.0 - a simple TOTP (RFC 6238) class

    (c) 2014 Robin Leffmann <djinn at stolendata dot net>

    https://github.com/stolendata/totp/

    Licensed under CC BY-NC-SA 4.0 - http://creativecommons.org/licenses/by-nc-sa/4.0/
*/

class TOTP
{
    private static $base32Map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private static function base32Decode( $in )
    {
        $l = strlen( $in );
        $n = $bs = 0;

        for( $i = 0; $i < $l; $i++ )
        {
            $n <<= 5;
            $n += stripos( self::$base32Map, $in[$i] );
            $bs = ( $bs + 5 ) % 8;
            @$out .= $bs < 5 ? chr( ($n & (255 << $bs)) >> $bs ) : null;
        }

        return $out;
    }

    public static function getOTP( $secret, $digits = 6, $period = 30, $offset = 0, $algo = 'sha1' )
    {
        if( strlen($secret) < 16 || strlen($secret) % 8 != 0 )
            return [ 'err'=>'length of secret must be a multiple of 8, and at least 16 characters' ];
        if( preg_match('/[^a-z2-7]/i', $secret) === 1 )
            return [ 'err'=>'secret contains non-base32 characters' ];
        $digits = intval( $digits );
        if( $digits < 6 || $digits > 8 )
            return [ 'err'=>'digits must be 6, 7 or 8' ];
        if( in_array(strtolower($algo), ['sha1', 'sha256', 'sha512']) === false )
            return [ 'err'=>'algo must be SHA1, SHA256 or SHA512' ];

        $seed = self::base32Decode( $secret );
        $time = str_pad( pack('N', intval($offset + time() / $period)), 8, "\x00", STR_PAD_LEFT );
        $hash = hash_hmac( strtolower($algo), $time, $seed, false );
        $otp = ( hexdec(substr($hash, hexdec($hash[-1]) * 2, 8)) & 0x7fffffff ) % pow( 10, $digits );

        return [ 'otp'=>sprintf("%'0{$digits}u", $otp) ];
    }

    public static function genSecret( $length = 24 )
    {
        if( $length < 16 || $length % 8 !== 0 )
            return [ 'err'=>'length must be a multiple of 8, and at least 16' ];

        while( $length-- )
        {
            $c = @gettimeofday()['usec'] % 53;
            while( $c-- )
                mt_rand();
            @$secret .= self::$base32Map[mt_rand(0, 31)];
        }

        return [ 'secret'=>$secret ];
    }

    public static function genURI( $account, $secret, $digits = null, $period = null, $issuer = null, $algo = null )
    {
        if( empty($account) || empty($secret) )
            return [ 'err'=>'you must provide at least an account and a secret' ];
        if( mb_strpos($account . $issuer, ':') !== false )
            return [ 'err'=>'neither account nor issuer can contain a colon (:) character' ];

        $account = rawurlencode( $account );
        $issuer = rawurlencode( $issuer );
        $label = empty( $issuer ) ? $account : "$issuer:$account";

        return [ 'uri'=>'otpauth://totp/' . $label . "?secret=$secret" .
                        (is_null($algo) ? '' : "&algorithm=$algo") .
                        (is_null($digits) ? '' : "&digits=$digits") .
                        (is_null($period) ? '' : "&period=$period") .
                        (empty($issuer) ? '' : "&issuer=$issuer") ];
    }
}
?>
