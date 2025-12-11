<?php 

return [

    'allowed_users' => array_map('intval', explode(',', env('ALLOWED_USER_IDS', ''))),
    
];