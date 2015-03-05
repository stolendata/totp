TOTP (RFC 6238)
===============

Copyright (c) 2014 Robin Leffmann

TOTP is a simple, compact and bare-bones class for calculating [TOTP](https://en.wikipedia.org/wiki/Time-based_One-time_Password_Algorithm) tokens using the SHA1 default, commonly used for two-factor authentication in mobile apps such as Google Authenticator. It comprises three public functions of which only one is necessary to call to get a token.


Usage
-----

Simply call `TOTP::getOTP( $secret [, $digits = 6 [, $period = 30 ]] )` which returns an array with the key `otp` holding the authentication token, or the key `err` describing an eventual error.

The other two functions are meant to be convenient utilities:

`TOTP::genSecret( [ $length = 24 ] )` generates a TOTP-compatible secret in Base32 ASCII, returning an array with the key `secret` holding the random secret or the key `err` describing an eventual error.

`TOTP::genURI( $label, $secret [, $digits = false [, $period = false ]] )` returns an array with the key `uri` holding an `otpauth://` style URI providing the supplied parameters, which can f.e. be embedded in a QR code image, or the key `err` describing an eventual error.


Donations
---------

BTC: 1EDhbo9ejdKUxNW3GPBh1UmocC1ea1TvE5  
LTC: LaDuRFwEt1V26pmJJH94auDvxqN3rRFqPj  
DOGE: DJ7vQ1dNRfebb1umVHsHxoMcd2Zq5L6LKp  
VTC: VwDmyMR5udPkEwTJhxDsUB2C3mk8NKCSD8  
DRK: XvHfibq2f1xU6rYqAULVXHLPuRhBawzTZs  


License
-------

TOTP is released under the Creative Commons BY-NC-SA 4.0 license: http://creativecommons.org/licenses/by-nc-sa/4.0/
