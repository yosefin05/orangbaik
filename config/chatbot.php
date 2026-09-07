<?php

return [
    'route_prefix' => 'chatbot-admin',
    'public_route_prefix' => 'chatbot',
    'middleware' => ['web'],
    'admin_middleware' => ['web', 'chatbot.admin.auth'],
    'store_conversations' => true,
    'enable_widget' => true,
    'fallback_message' => 'Sorry, I could not understand your question.',
    'welcome_message' => 'Hello! How can I help you today?',
    'chatbot_name' => 'Support Bot',
    'widget_position' => 'bottom-right',
    'primary_color' => '#2563eb',
    'show_branding' => true,
    'collect_user_email' => false,
    'collect_user_name' => false,
    'engine' => 'rule_based',
];
