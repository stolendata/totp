<?php
/*
    TOTP v0.1.0 - a simple TOTP (RFC 6238) class using the SHA1 default

    (c) 2014 Robin Leffmann <djinn at stolendata dot net>

    https://github.com/stolendata/totp/

    Licensed under CC BY-NC-SA 4.0 - http://creativecommons.org/licenses/by-nc-sa/4.0/
*/

class TOTP
{
    private static $base32Map = 'abcdefghijklmnopqrstuvwxyz234567';

    private function base32Decode( $in )
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

    function getOTP( $secret, $digits = 6, $period = 30 )
    {
        if( strlen($secret) < 16 || strlen($secret) % 8 != 0 )
            return [ 'err'=>'length of secret must be a multiple of 8, and at least 16 characters' ];
        if( $digits < 6 || $digits > 8 )
            return [ 'err'=>'digits must be 6, 7 or 8' ];

        $seed = self::base32Decode( $secret );
        $time = intval( time() / $period );
        $time = str_pad( pack('N', $time), 8, "\x00", STR_PAD_LEFT );
        $hash = hash_hmac( 'sha1', $time, $seed, false );
        $otp = ( hexdec(substr($hash, hexdec($hash[39]) * 2, 8)) & 0x7fffffff ) % pow( 10, $digits );

        return [ 'otp'=>sprintf("%'0{$digits}u", $otp) ];
    }

    function genSecret( $length = 16 )
    {
        if( $length < 16 || $length % 8 != 0 )
            return [ 'err'=>'length must be a multiple of 8, and at least 16' ];

        while( $length-- )
        {
            $c = @gettimeofday();
            $c = $c['usec'] % 13;
            while( $c-- )
                mt_rand();
            @$secret .= self::$base32Map[mt_rand(0, 31)];
        }

        return [ 'secret'=>strtoupper($secret) ];
    }

    function genURI( $label, $secret, $digits, $period )
    {
        return 'otpauth://totp/' . rawurlencode( $label ) . "?secret=$secret&digits=$digits&period=$period";
    }
}
?>
