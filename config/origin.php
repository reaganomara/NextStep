<?php

/*
|--------------------------------------------------------------------------
| CloudFront Origin Verification
|--------------------------------------------------------------------------
|
| Visitors should only reach NextStep through CloudFront. CloudFront adds a
| secret header to every request it sends to the server; when a secret is
| set here, any request without it is refused, so the server's direct
| Elastic Beanstalk address cannot be used to bypass CloudFront.
|
| OFF until ORIGIN_VERIFY_SECRET is set. Set it in the Elastic Beanstalk
| console only, after CloudFront is already sending the header. Never
| commit it.
|
*/

return [

    'header' => 'X-Origin-Verify',

    'secret' => (string) env('ORIGIN_VERIFY_SECRET', ''),

    // Optional. During a secret change, the old value keeps working until
    // CloudFront has switched to the new one.
    'previous_secret' => (string) env('ORIGIN_VERIFY_SECRET_PREVIOUS', ''),

    // Paths allowed without the header. Laravel's built-in health check
    // ("/up") reveals nothing and may be used by AWS health checks.
    'exempt_paths' => ['up'],

];
