<?php
// Working RSS Feeds Configuration
return [
    'feeds' => [
        'BBC News' => [
            'url' => 'https://feeds.bbci.co.uk/news/rss.xml',
            'active' => true,
            'last_updated' => null,
            'category' => 'International'
        ]
    ],
    'settings' => [
        'timeout' => 60,
        'connect_timeout' => 15,
        'max_retries' => 3,
        'cache_duration' => 300 // 5 minutes
    ]
];
?>
