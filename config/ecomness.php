<?php

return [
    'base_url' => env('ECOMNESS_BASE_URL', 'https://ecomness.com/api/v1'),
    'api_key' => env('ECOMNESS_API_KEY', 'ecm_KsBRBcKyKhRPMyooZcTkHIvzJHXPTfamnafbzhAndUenpMySvdOLDAOBuNMbjFhi'),
    'vendor_shop_name' => 'Aquawasouk eco',
    'auto_forward_orders' => env('ECOMNESS_AUTO_FORWARD_ORDERS', true),
    'default_profit_margin_percent' => env('ECOMNESS_DEFAULT_MARGIN_PERCENT', 40), // 25    % margin on top of wholesale/min price
];

