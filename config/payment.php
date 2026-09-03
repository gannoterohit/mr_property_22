<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Razorpay Webhook Secret
    |--------------------------------------------------------------------------
    |
    | Set in Admin → Business Settings, or via RAZORPAY_WEBHOOK_SECRET in .env.
    | Create this secret in Razorpay Dashboard → Webhooks when adding your URL.
    |
    */

    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),

];
