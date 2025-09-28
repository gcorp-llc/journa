<?php return array (
  'broadcasting' => 
  array (
    'default' => 'log',
    'connections' => 
    array (
      'reverb' => 
      array (
        'driver' => 'reverb',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'host' => NULL,
          'port' => 443,
          'scheme' => 'https',
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'cluster' => NULL,
          'host' => 'api-mt1.pusher.com',
          'port' => 443,
          'scheme' => 'https',
          'encrypted' => true,
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'ably' => 
      array (
        'driver' => 'ably',
        'key' => NULL,
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
    ),
  ),
  'concurrency' => 
  array (
    'default' => 'process',
  ),
  'cors' => 
  array (
    'paths' => 
    array (
      0 => 'api/*',
      1 => 'sanctum/csrf-cookie',
    ),
    'allowed_methods' => 
    array (
      0 => '*',
    ),
    'allowed_origins' => 
    array (
      0 => '*',
    ),
    'allowed_origins_patterns' => 
    array (
    ),
    'allowed_headers' => 
    array (
      0 => '*',
    ),
    'exposed_headers' => 
    array (
    ),
    'max_age' => 0,
    'supports_credentials' => false,
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => '12',
      'verify' => true,
      'limit' => NULL,
    ),
    'argon' => 
    array (
      'memory' => 65536,
      'threads' => 1,
      'time' => 4,
      'verify' => true,
    ),
    'rehash_on_login' => true,
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\resources\\views',
    ),
    'compiled' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\storage\\framework\\views',
  ),
  'activitylog' => 
  array (
    'enabled' => true,
    'delete_records_older_than_days' => 365,
    'default_log_name' => 'default',
    'default_auth_driver' => NULL,
    'subject_returns_soft_deleted_models' => false,
    'activity_model' => 'Spatie\\Activitylog\\Models\\Activity',
    'table_name' => 'activity_log',
    'database_connection' => NULL,
  ),
  'app' => 
  array (
    'name' => 'Journa',
    'env' => 'local',
    'debug' => true,
    'url' => 'https://journa-core.test',
    'frontend_url' => 'http://localhost:3000',
    'asset_url' => NULL,
    'timezone' => 'UTC',
    'locale' => 'fa',
    'fallback_locale' => 'fa',
    'faker_locale' => 'fa_IR',
    'cipher' => 'AES-256-CBC',
    'key' => 'base64:14M9pXhwOemCjiskboqzrLdgHMo9eYtBz0nNf6qB9rM=',
    'previous_keys' => 
    array (
    ),
    'maintenance' => 
    array (
      'driver' => 'file',
      'store' => 'database',
    ),
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Concurrency\\ConcurrencyServiceProvider',
      6 => 'Illuminate\\Cookie\\CookieServiceProvider',
      7 => 'Illuminate\\Database\\DatabaseServiceProvider',
      8 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      9 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      10 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      11 => 'Illuminate\\Hashing\\HashServiceProvider',
      12 => 'Illuminate\\Mail\\MailServiceProvider',
      13 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      14 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      15 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      16 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      17 => 'Illuminate\\Queue\\QueueServiceProvider',
      18 => 'Illuminate\\Redis\\RedisServiceProvider',
      19 => 'Illuminate\\Session\\SessionServiceProvider',
      20 => 'Illuminate\\Translation\\TranslationServiceProvider',
      21 => 'Illuminate\\Validation\\ValidationServiceProvider',
      22 => 'Illuminate\\View\\ViewServiceProvider',
      23 => 'App\\Providers\\AppServiceProvider',
      24 => 'App\\Providers\\Filament\\AdminPanelProvider',
      25 => 'App\\Providers\\VoltServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Benchmark' => 'Illuminate\\Support\\Benchmark',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Concurrency' => 'Illuminate\\Support\\Facades\\Concurrency',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Context' => 'Illuminate\\Support\\Facades\\Context',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Js' => 'Illuminate\\Support\\Js',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Number' => 'Illuminate\\Support\\Number',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Process' => 'Illuminate\\Support\\Facades\\Process',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schedule' => 'Illuminate\\Support\\Facades\\Schedule',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'Uri' => 'Illuminate\\Support\\Uri',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Vite' => 'Illuminate\\Support\\Facades\\Vite',
    ),
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'sanctum' => 
      array (
        'driver' => 'sanctum',
        'provider' => NULL,
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
      ),
    ),
    'password_timeout' => 10800,
  ),
  'blade-heroicons' => 
  array (
    'prefix' => 'heroicon',
    'fallback' => '',
    'class' => '',
    'attributes' => 
    array (
    ),
  ),
  'cache' => 
  array (
    'default' => 'database',
    'stores' => 
    array (
      'array' => 
      array (
        'driver' => 'array',
        'serialize' => false,
      ),
      'session' => 
      array (
        'driver' => 'session',
        'key' => '_cache',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'cache',
        'lock_connection' => NULL,
        'lock_table' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\storage\\framework/cache/data',
        'lock_path' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\storage\\framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
      ),
      'dynamodb' => 
      array (
        'driver' => 'dynamodb',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'table' => 'cache',
        'endpoint' => NULL,
      ),
      'octane' => 
      array (
        'driver' => 'octane',
      ),
    ),
    'prefix' => 'journa_cache_',
  ),
  'crawler' => 
  array (
    'sites' => 
    array (
      'The New York Times' => 
      array (
        'category_selectors' => 
        array (
          'links' => 'a.css-1u3p7j1',
          'filter' => '/\\/2025\\/[0-1][0-9]\\/[0-3][0-9]\\/world\\//',
        ),
        'news_selectors' => 
        array (
          'title' => 'h1.css-v3oks',
          'content' => '.StoryBodyCompanionColumn .css-53u6y8',
          'cover' => 'img.css-1ibr5m9',
          'cover_alt' => 'meta[property="og:image"]',
        ),
        'rate_limit' => 2,
      ),
      'Guardian' => 
      array (
        'category_selectors' => 
        array (
          'links' => '.fc-item__link',
          'filter' => 'article',
        ),
        'news_selectors' => 
        array (
          'title' => 'h1',
          'content' => 'div#content-body',
          'cover' => 'figure img',
          'cover_alt' => 'meta[property="og:image"]',
        ),
        'rate_limit' => 2,
      ),
      'Al Jazeera' => 
      array (
        'category_selectors' => 
        array (
          'links' => '.u-clickable-card__link',
          'filter' => 'article',
        ),
        'news_selectors' => 
        array (
          'title' => 'h1',
          'content' => '.wysiwyg--all-content',
          'cover' => '.article-featured-image img',
          'cover_alt' => 'meta[property="og:image"]',
        ),
        'rate_limit' => 2,
      ),
      'Associated Press' => 
      array (
        'category_selectors' => 
        array (
          'links' => '.Link',
          'filter' => 'article',
        ),
        'news_selectors' => 
        array (
          'title' => 'h1.Page-headline',
          'content' => '.RichTextStoryBody',
          'cover' => '.Page-lead .Carousel-slide img.Image',
          'cover_alt' => 'meta[property="og:image"]',
        ),
        'rate_limit' => 2,
      ),
      'Financial Times' => 
      array (
        'category_selectors' => 
        array (
          'links' => '.fc-item__link',
          'filter' => 'article',
        ),
        'news_selectors' => 
        array (
          'title' => 'h1',
          'content' => 'div#content-body',
          'cover' => 'figure img',
          'cover_alt' => 'meta[property="og:image"]',
        ),
        'rate_limit' => 2,
      ),
      'bloomberg' => 
      array (
        'category_selectors' => 
        array (
          'links' => '.u-clickable-card__link',
          'filter' => 'article',
        ),
        'news_selectors' => 
        array (
          'title' => 'h1',
          'content' => '.wysiwyg--all-content',
          'cover' => '.article-featured-image img',
          'cover_alt' => 'meta[property="og:image"]',
        ),
        'rate_limit' => 2,
      ),
      'economist' => 
      array (
        'category_selectors' => 
        array (
          'links' => '.css-4svvz1 .eb97p610',
          'filter' => '',
        ),
        'news_selectors' => 
        array (
          'title' => 'a.css-1u3p7j1',
          'content' => '.meteredContent',
          'cover' => 'img.css-119ags5',
          'cover_alt' => 'meta[property="og:image"]',
        ),
        'rate_limit' => 2,
      ),
      'sky news' => 
      array (
        'category_selectors' => 
        array (
          'links' => '.fc-item__link',
          'filter' => 'article',
        ),
        'news_selectors' => 
        array (
          'title' => 'h1',
          'content' => 'div#content-body',
          'cover' => 'figure img',
          'cover_alt' => 'meta[property="og:image"]',
        ),
        'rate_limit' => 2,
      ),
      'nationalgeographic' => 
      array (
        'category_selectors' => 
        array (
          'links' => '.u-clickable-card__link',
          'filter' => 'article',
        ),
        'news_selectors' => 
        array (
          'title' => 'h1',
          'content' => '.wysiwyg--all-content',
          'cover' => '.article-featured-image img',
          'cover_alt' => 'meta[property="og:image"]',
        ),
        'rate_limit' => 2,
      ),
      'wired' => 
      array (
        'category_selectors' => 
        array (
          'links' => '.css-4svvz1 .eb97p610',
          'filter' => '',
        ),
        'news_selectors' => 
        array (
          'title' => 'a.css-1u3p7j1',
          'content' => '.meteredContent',
          'cover' => 'img.css-119ags5',
          'cover_alt' => 'meta[property="og:image"]',
        ),
        'rate_limit' => 2,
      ),
      'techcrunch' => 
      array (
        'category_selectors' => 
        array (
          'links' => '.fc-item__link',
          'filter' => 'article',
        ),
        'news_selectors' => 
        array (
          'title' => 'h1',
          'content' => 'div#content-body',
          'cover' => 'figure img',
          'cover_alt' => 'meta[property="og:image"]',
        ),
        'rate_limit' => 2,
      ),
    ),
  ),
  'database' => 
  array (
    'default' => 'mariadb',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'url' => NULL,
        'database' => 'journa',
        'prefix' => '',
        'foreign_key_constraints' => true,
        'busy_timeout' => NULL,
        'journal_mode' => NULL,
        'synchronous' => NULL,
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'journa',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'mariadb' => 
      array (
        'driver' => 'mariadb',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'journa',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'journa',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'journa',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
      ),
    ),
    'migrations' => 
    array (
      'table' => 'migrations',
      'update_date_on_publish' => true,
    ),
    'redis' => 
    array (
      'client' => 'phpredis',
      'options' => 
      array (
        'cluster' => 'redis',
        'prefix' => 'journa_database_',
        'persistent' => false,
      ),
      'default' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '0',
      ),
      'cache' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '1',
      ),
    ),
  ),
  'filament-activitylog' => 
  array (
    'resources' => 
    array (
      'label' => 'Activity Log',
      'plural_label' => 'Activity Logs',
      'navigation_item' => true,
      'navigation_group' => NULL,
      'navigation_icon' => 'heroicon-o-shield-check',
      'navigation_sort' => NULL,
      'default_sort_column' => 'id',
      'default_sort_direction' => 'desc',
      'navigation_count_badge' => false,
      'resource' => 'Rmsramos\\Activitylog\\Resources\\ActivitylogResource',
    ),
    'datetime_format' => 'd/m/Y H:i:s',
  ),
  'filament-jobs-monitor' => 
  array (
    'resources' => 
    array (
      'enabled' => true,
      'label' => 'Job',
      'plural_label' => 'Jobs',
      'navigation_group' => 'Settings',
      'navigation_icon' => 'heroicon-o-cpu-chip',
      'navigation_sort' => NULL,
      'navigation_count_badge' => false,
      'resource' => 'Croustibat\\FilamentJobsMonitor\\Resources\\QueueMonitorResource',
      'cluster' => NULL,
    ),
    'pruning' => 
    array (
      'enabled' => true,
      'retention_days' => 7,
    ),
    'queues' => 
    array (
      0 => 'default',
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\storage\\app/private',
        'serve' => true,
        'throw' => false,
        'report' => false,
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\storage\\app/public',
        'url' => 'https://journa-core.test/storage',
        'visibility' => 'public',
        'throw' => false,
        'report' => false,
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'bucket' => '',
        'url' => NULL,
        'endpoint' => NULL,
        'use_path_style_endpoint' => false,
        'throw' => false,
        'report' => false,
      ),
    ),
    'links' => 
    array (
      'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\public\\storage' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\storage\\app/public',
    ),
  ),
  'firewall' => 
  array (
    'enabled' => true,
    'whitelist' => 
    array (
      0 => '',
    ),
    'models' => 
    array (
      'user' => '\\App\\Models\\User',
    ),
    'log' => 
    array (
      'max_request_size' => 2048,
    ),
    'cron' => 
    array (
      'enabled' => true,
      'expression' => '* * * * *',
    ),
    'responses' => 
    array (
      'block' => 
      array (
        'view' => NULL,
        'redirect' => NULL,
        'abort' => false,
        'code' => 403,
      ),
    ),
    'notifications' => 
    array (
      'mail' => 
      array (
        'enabled' => false,
        'name' => 'Laravel Firewall',
        'from' => 'firewall@mydomain.com',
        'to' => 'admin@mydomain.com',
        'queue' => 'default',
      ),
      'slack' => 
      array (
        'enabled' => false,
        'emoji' => ':fire:',
        'from' => 'Laravel Firewall',
        'to' => NULL,
        'channel' => NULL,
        'queue' => 'default',
      ),
    ),
    'all_middleware' => 
    array (
      0 => 'firewall.ip',
      1 => 'firewall.agent',
      2 => 'firewall.bot',
      3 => 'firewall.geo',
      4 => 'firewall.lfi',
      5 => 'firewall.php',
      6 => 'firewall.referrer',
      7 => 'firewall.rfi',
      8 => 'firewall.session',
      9 => 'firewall.sqli',
      10 => 'firewall.swear',
      11 => 'firewall.xss',
    ),
    'middleware' => 
    array (
      'ip' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'all',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
      ),
      'agent' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'all',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'browsers' => 
        array (
          'allow' => 
          array (
          ),
          'block' => 
          array (
          ),
        ),
        'platforms' => 
        array (
          'allow' => 
          array (
          ),
          'block' => 
          array (
          ),
        ),
        'devices' => 
        array (
          'allow' => 
          array (
          ),
          'block' => 
          array (
          ),
        ),
        'properties' => 
        array (
          'allow' => 
          array (
          ),
          'block' => 
          array (
          ),
        ),
        'auto_block' => 
        array (
          'attempts' => 5,
          'frequency' => 60,
          'period' => 1800,
        ),
      ),
      'bot' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'all',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'crawlers' => 
        array (
          'allow' => 
          array (
          ),
          'block' => 
          array (
          ),
        ),
        'auto_block' => 
        array (
          'attempts' => 5,
          'frequency' => 60,
          'period' => 1800,
        ),
      ),
      'geo' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'all',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'continents' => 
        array (
          'allow' => 
          array (
          ),
          'block' => 
          array (
          ),
        ),
        'regions' => 
        array (
          'allow' => 
          array (
          ),
          'block' => 
          array (
          ),
        ),
        'countries' => 
        array (
          'allow' => 
          array (
          ),
          'block' => 
          array (
          ),
        ),
        'cities' => 
        array (
          'allow' => 
          array (
          ),
          'block' => 
          array (
          ),
        ),
        'service' => 'ipapi',
        'auto_block' => 
        array (
          'attempts' => 3,
          'frequency' => 300,
          'period' => 1800,
        ),
      ),
      'lfi' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'get',
          1 => 'delete',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'inputs' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'patterns' => 
        array (
          0 => '#\\.\\/#is',
        ),
        'auto_block' => 
        array (
          'attempts' => 3,
          'frequency' => 300,
          'period' => 1800,
        ),
      ),
      'login' => 
      array (
        'enabled' => true,
        'auto_block' => 
        array (
          'attempts' => 5,
          'frequency' => 60,
          'period' => 1800,
        ),
      ),
      'php' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'get',
          1 => 'post',
          2 => 'delete',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'inputs' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'patterns' => 
        array (
          0 => 'bzip2://',
          1 => 'expect://',
          2 => 'glob://',
          3 => 'phar://',
          4 => 'php://',
          5 => 'ogg://',
          6 => 'rar://',
          7 => 'ssh2://',
          8 => 'zip://',
          9 => 'zlib://',
        ),
        'auto_block' => 
        array (
          'attempts' => 3,
          'frequency' => 300,
          'period' => 1800,
        ),
      ),
      'referrer' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'all',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'blocked' => 
        array (
        ),
        'auto_block' => 
        array (
          'attempts' => 3,
          'frequency' => 300,
          'period' => 1800,
        ),
      ),
      'rfi' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'get',
          1 => 'post',
          2 => 'delete',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'inputs' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'patterns' => 
        array (
          0 => '#(http|ftp){1,1}(s){0,1}://.*#i',
        ),
        'exceptions' => 
        array (
        ),
        'auto_block' => 
        array (
          'attempts' => 3,
          'frequency' => 300,
          'period' => 1800,
        ),
      ),
      'session' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'get',
          1 => 'post',
          2 => 'delete',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'inputs' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'patterns' => 
        array (
          0 => '@[\\|:]O:\\d{1,}:"[\\w_][\\w\\d_]{0,}":\\d{1,}:{@i',
          1 => '@[\\|:]a:\\d{1,}:{@i',
        ),
        'auto_block' => 
        array (
          'attempts' => 3,
          'frequency' => 300,
          'period' => 1800,
        ),
      ),
      'sqli' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'get',
          1 => 'delete',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'inputs' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'patterns' => 
        array (
          0 => '#[\\d\\W](union select|union join|union distinct)[\\d\\W]#is',
          1 => '#[\\d\\W](union|union select|insert|from|where|concat|into|cast|truncate|select|delete|having)[\\d\\W]#is',
        ),
        'auto_block' => 
        array (
          'attempts' => 3,
          'frequency' => 300,
          'period' => 1800,
        ),
      ),
      'swear' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'post',
          1 => 'put',
          2 => 'patch',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'inputs' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'words' => 
        array (
        ),
        'auto_block' => 
        array (
          'attempts' => 3,
          'frequency' => 300,
          'period' => 1800,
        ),
      ),
      'url' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'all',
        ),
        'inspections' => 
        array (
        ),
        'auto_block' => 
        array (
          'attempts' => 5,
          'frequency' => 60,
          'period' => 1800,
        ),
      ),
      'whitelist' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'all',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
      ),
      'xss' => 
      array (
        'enabled' => true,
        'methods' => 
        array (
          0 => 'post',
          1 => 'put',
          2 => 'patch',
        ),
        'routes' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'inputs' => 
        array (
          'only' => 
          array (
          ),
          'except' => 
          array (
          ),
        ),
        'patterns' => 
        array (
          0 => '#(<[^>]+[\\x00-\\x20\\"\'\\/])(form|formaction|on\\w*|style|xmlns|xlink:href)[^>]*>?#iUu',
          1 => '!((java|live|vb)script|mocha|feed|data):(\\w)*!iUu',
          2 => '#-moz-binding[\\x00-\\x20]*:#u',
          3 => '#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base|img)[^>]*>?#i',
        ),
        'auto_block' => 
        array (
          'attempts' => 3,
          'frequency' => 300,
          'period' => 1800,
        ),
      ),
    ),
  ),
  'laravellocalization' => 
  array (
    'supportedLocales' => 
    array (
      'en' => 
      array (
        'name' => 'English',
        'script' => 'Latn',
        'native' => 'English',
        'regional' => 'en_GB',
      ),
      'es' => 
      array (
        'name' => 'Spanish',
        'script' => 'Latn',
        'native' => 'español',
        'regional' => 'es_ES',
      ),
      'ar' => 
      array (
        'name' => 'Arabic',
        'script' => 'Arab',
        'native' => 'العربية',
        'regional' => 'ar_AE',
      ),
      'fa' => 
      array (
        'name' => 'Persian',
        'script' => 'Arab',
        'native' => 'فارسی',
        'regional' => 'fa_IR',
      ),
    ),
    'useAcceptLanguageHeader' => true,
    'hideDefaultLocaleInURL' => false,
    'localesOrder' => 
    array (
    ),
    'localesMapping' => 
    array (
    ),
    'utf8suffix' => '.UTF-8',
    'urlsIgnored' => 
    array (
      0 => '/skipped',
    ),
    'httpMethodsIgnored' => 
    array (
      0 => 'POST',
      1 => 'PUT',
      2 => 'PATCH',
      3 => 'DELETE',
    ),
  ),
  'logging' => 
  array (
    'default' => 'stack',
    'deprecations' => 
    array (
      'channel' => NULL,
      'trace' => false,
    ),
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\storage\\logs/laravel.log',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\storage\\logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
        'replace_placeholders' => true,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'papertrail' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\SyslogUdpHandler',
        'handler_with' => 
        array (
          'host' => NULL,
          'port' => NULL,
          'connectionString' => 'tls://:',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'handler_with' => 
        array (
          'stream' => 'php://stderr',
        ),
        'formatter' => NULL,
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
        'facility' => 8,
        'replace_placeholders' => true,
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'null' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\NullHandler',
      ),
      'emergency' => 
      array (
        'path' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\storage\\logs/laravel.log',
      ),
    ),
  ),
  'mail' => 
  array (
    'default' => 'log',
    'mailers' => 
    array (
      'smtp' => 
      array (
        'transport' => 'smtp',
        'scheme' => NULL,
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '2525',
        'username' => NULL,
        'password' => NULL,
        'timeout' => NULL,
        'local_domain' => 'journa-core.test',
      ),
      'ses' => 
      array (
        'transport' => 'ses',
      ),
      'postmark' => 
      array (
        'transport' => 'postmark',
      ),
      'resend' => 
      array (
        'transport' => 'resend',
      ),
      'sendmail' => 
      array (
        'transport' => 'sendmail',
        'path' => '/usr/sbin/sendmail -bs -i',
      ),
      'log' => 
      array (
        'transport' => 'log',
        'channel' => NULL,
      ),
      'array' => 
      array (
        'transport' => 'array',
      ),
      'failover' => 
      array (
        'transport' => 'failover',
        'mailers' => 
        array (
          0 => 'smtp',
          1 => 'log',
        ),
        'retry_after' => 60,
      ),
      'roundrobin' => 
      array (
        'transport' => 'roundrobin',
        'mailers' => 
        array (
          0 => 'ses',
          1 => 'postmark',
        ),
        'retry_after' => 60,
      ),
    ),
    'from' => 
    array (
      'address' => 'hello@example.com',
      'name' => 'Journa',
    ),
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\resources\\views/vendor/mail',
      ),
    ),
  ),
  'queue' => 
  array (
    'default' => 'database',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 0,
        'after_commit' => false,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => '',
        'secret' => '',
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'default',
        'suffix' => NULL,
        'region' => 'us-east-1',
        'after_commit' => false,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
        'after_commit' => false,
      ),
    ),
    'batching' => 
    array (
      'database' => 'mariadb',
      'table' => 'job_batches',
    ),
    'failed' => 
    array (
      'driver' => 'database-uuids',
      'database' => 'mariadb',
      'table' => 'failed_jobs',
    ),
  ),
  'sanctum' => 
  array (
    'stateful' => 
    array (
      0 => 'localhost',
      1 => 'localhost:3000',
      2 => '127.0.0.1',
      3 => '127.0.0.1:8000',
      4 => '::1',
      5 => 'journa-core.test',
    ),
    'guard' => 
    array (
      0 => 'web',
    ),
    'expiration' => NULL,
    'token_prefix' => '',
    'middleware' => 
    array (
      'authenticate_session' => 'Laravel\\Sanctum\\Http\\Middleware\\AuthenticateSession',
      'encrypt_cookies' => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
      'validate_csrf_token' => 'Illuminate\\Foundation\\Http\\Middleware\\ValidateCsrfToken',
    ),
  ),
  'seotools' => 
  array (
    'inertia' => false,
    'meta' => 
    array (
      'defaults' => 
      array (
        'title' => 'Journa News!',
        'titleBefore' => false,
        'description' => '',
        'separator' => ' - ',
        'keywords' => 
        array (
        ),
        'canonical' => false,
        'robots' => false,
      ),
      'webmaster_tags' => 
      array (
        'google' => NULL,
        'bing' => NULL,
        'alexa' => NULL,
        'pinterest' => NULL,
        'yandex' => NULL,
        'norton' => NULL,
      ),
      'add_notranslate_class' => false,
    ),
    'opengraph' => 
    array (
      'defaults' => 
      array (
        'title' => 'Over 9000 Thousand!',
        'description' => 'For those who helped create the Genki Dama',
        'url' => false,
        'type' => false,
        'site_name' => false,
        'images' => 
        array (
        ),
      ),
    ),
    'twitter' => 
    array (
      'defaults' => 
      array (
      ),
    ),
    'json-ld' => 
    array (
      'defaults' => 
      array (
        'title' => 'Over 9000 Thousand!',
        'description' => 'For those who helped create the Genki Dama',
        'url' => false,
        'type' => 'WebPage',
        'images' => 
        array (
        ),
      ),
    ),
  ),
  'services' => 
  array (
    'postmark' => 
    array (
      'token' => NULL,
    ),
    'resend' => 
    array (
      'key' => NULL,
    ),
    'ses' => 
    array (
      'key' => '',
      'secret' => '',
      'region' => 'us-east-1',
    ),
    'slack' => 
    array (
      'notifications' => 
      array (
        'bot_user_oauth_token' => NULL,
        'channel' => NULL,
      ),
    ),
  ),
  'session' => 
  array (
    'driver' => 'database',
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\storage\\framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'journa_session',
    'path' => '/',
    'domain' => NULL,
    'secure' => NULL,
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
  ),
  'blade-icons' => 
  array (
    'sets' => 
    array (
    ),
    'class' => '',
    'attributes' => 
    array (
    ),
    'fallback' => '',
    'components' => 
    array (
      'disabled' => false,
      'default' => 'icon',
    ),
  ),
  'blade-solar-icons' => 
  array (
    'prefix' => 'solar',
    'fallback' => '',
    'class' => '',
    'attributes' => 
    array (
    ),
  ),
  'filament' => 
  array (
    'broadcasting' => 
    array (
    ),
    'default_filesystem_disk' => 'public',
    'assets_path' => NULL,
    'cache_path' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\bootstrap/cache/filament',
    'livewire_loading_delay' => 'default',
    'system_route_prefix' => 'filament',
  ),
  'icon-picker' => 
  array (
    'sets' => NULL,
    'columns' => 1,
    'layout' => 'floating',
    'cache' => 
    array (
      'enabled' => true,
      'duration' => '7 days',
    ),
  ),
  'localization' => 
  array (
    'inline' => false,
    'align' => true,
    'aliases' => 
    array (
    ),
    'smart_punctuation' => 
    array (
      'enable' => false,
      'common' => 
      array (
        'double_quote_opener' => '“',
        'double_quote_closer' => '”',
        'single_quote_opener' => '‘',
        'single_quote_closer' => '’',
      ),
      'locales' => 
      array (
        'fr' => 
        array (
          'double_quote_opener' => '«&nbsp;',
          'double_quote_closer' => '&nbsp;»',
          'single_quote_opener' => '‘',
          'single_quote_closer' => '’',
        ),
        'ru' => 
        array (
          'double_quote_opener' => '«',
          'double_quote_closer' => '»',
          'single_quote_opener' => '‘',
          'single_quote_closer' => '’',
        ),
        'uk' => 
        array (
          'double_quote_opener' => '«',
          'double_quote_closer' => '»',
          'single_quote_opener' => '‘',
          'single_quote_closer' => '’',
        ),
        'be' => 
        array (
          'double_quote_opener' => '«',
          'double_quote_closer' => '»',
          'single_quote_opener' => '‘',
          'single_quote_closer' => '’',
        ),
      ),
    ),
    'routes' => 
    array (
      'names' => 
      array (
        'parameter' => 'locale',
        'header' => 'Accept-Language',
        'cookie' => 'Accept-Language',
        'session' => 'Accept-Language',
        'column' => 'locale',
      ),
      'name_prefix' => 'localized.',
      'redirect_default' => false,
      'hide_default' => false,
      'group' => 
      array (
        'middlewares' => 
        array (
          'default' => 
          array (
            0 => 'LaravelLang\\Routes\\Middlewares\\LocalizationByCookie',
            1 => 'LaravelLang\\Routes\\Middlewares\\LocalizationByHeader',
            2 => 'LaravelLang\\Routes\\Middlewares\\LocalizationBySession',
            3 => 'LaravelLang\\Routes\\Middlewares\\LocalizationByModel',
          ),
          'prefix' => 
          array (
            0 => 'LaravelLang\\Routes\\Middlewares\\LocalizationByParameterPrefix',
            1 => 'LaravelLang\\Routes\\Middlewares\\LocalizationByCookie',
            2 => 'LaravelLang\\Routes\\Middlewares\\LocalizationByHeader',
            3 => 'LaravelLang\\Routes\\Middlewares\\LocalizationBySession',
            4 => 'LaravelLang\\Routes\\Middlewares\\LocalizationByModel',
          ),
        ),
      ),
    ),
    'models' => 
    array (
      'suffix' => 'Translation',
      'filter' => 
      array (
        'enabled' => true,
      ),
      'helpers' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\vendor/_laravel_lang',
    ),
    'translators' => 
    array (
      'channels' => 
      array (
        'google' => 
        array (
          'translator' => '\\LaravelLang\\Translator\\Integrations\\Google',
          'enabled' => true,
          'priority' => 1,
        ),
        'deepl' => 
        array (
          'translator' => '\\LaravelLang\\Translator\\Integrations\\Deepl',
          'enabled' => false,
          'priority' => 2,
          'credentials' => 
          array (
            'key' => '',
          ),
        ),
        'yandex' => 
        array (
          'translator' => '\\LaravelLang\\Translator\\Integrations\\Yandex',
          'enabled' => false,
          'priority' => 3,
          'credentials' => 
          array (
            'key' => '',
            'folder' => '',
          ),
        ),
      ),
      'options' => 
      array (
        'preserve_parameters' => true,
      ),
    ),
  ),
  'localization-private' => 
  array (
    'plugins' => 
    array (
      'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\vendor\\laravel-lang\\lang' => 
      array (
        0 => 'LaravelLang\\Lang\\Plugins\\Breeze\\Master',
        1 => 'LaravelLang\\Lang\\Plugins\\Breeze\\V2',
        2 => 'LaravelLang\\Lang\\Plugins\\Cashier\\Stripe\\Master',
        3 => 'LaravelLang\\Lang\\Plugins\\Cashier\\Stripe\\V15',
        4 => 'LaravelLang\\Lang\\Plugins\\Fortify\\Master',
        5 => 'LaravelLang\\Lang\\Plugins\\Fortify\\V1',
        6 => 'LaravelLang\\Lang\\Plugins\\Jetstream\\Master',
        7 => 'LaravelLang\\Lang\\Plugins\\Jetstream\\V5',
        8 => 'LaravelLang\\Lang\\Plugins\\Laravel\\Master',
        9 => 'LaravelLang\\Lang\\Plugins\\Laravel\\V11',
        10 => 'LaravelLang\\Lang\\Plugins\\Laravel\\V12',
        11 => 'LaravelLang\\Lang\\Plugins\\Nova\\DuskSuite\\Main',
        12 => 'LaravelLang\\Lang\\Plugins\\Nova\\LogViewer\\Main',
        13 => 'LaravelLang\\Lang\\Plugins\\Nova\\V4',
        14 => 'LaravelLang\\Lang\\Plugins\\Nova\\V5',
        15 => 'LaravelLang\\Lang\\Plugins\\Spark\\Paddle',
        16 => 'LaravelLang\\Lang\\Plugins\\Spark\\Stripe',
        17 => 'LaravelLang\\Lang\\Plugins\\UI\\Master',
        18 => 'LaravelLang\\Lang\\Plugins\\UI\\V4',
      ),
    ),
    'packages' => 
    array (
      'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\vendor\\laravel-lang\\lang' => 
      array (
        'class' => 'LaravelLang\\Lang\\Plugin',
        'name' => 'laravel-lang/lang',
      ),
    ),
    'models' => 
    array (
      'directory' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\app',
    ),
    'map' => 
    array (
      'af' => 
      array (
        'type' => 'Latn',
        'regional' => 'af_ZA',
      ),
      'sq' => 
      array (
        'type' => 'Latn',
        'regional' => 'sq_AL',
      ),
      'am' => 
      array (
        'type' => 'Ethi',
        'regional' => 'am_ET',
      ),
      'ar' => 
      array (
        'type' => 'Arab',
        'regional' => 'ar_AE',
        'direction' => 
        \LaravelLang\LocaleList\Direction::RightToLeft,
      ),
      'hy' => 
      array (
        'type' => 'Armn',
        'regional' => 'hy_AM',
      ),
      'as' => 
      array (
        'type' => 'Beng',
        'regional' => 'as_IN',
      ),
      'az' => 
      array (
        'type' => 'Latn',
        'regional' => 'az_AZ',
      ),
      'bm' => 
      array (
        'type' => 'Latn',
        'regional' => 'bm_ML',
      ),
      'bho' => 
      array (
        'type' => 'Deva',
        'regional' => 'bho_IN',
      ),
      'eu' => 
      array (
        'type' => 'Latn',
        'regional' => 'eu_ES',
      ),
      'be' => 
      array (
        'type' => 'Cyrl',
        'regional' => 'be_BY',
      ),
      'bn' => 
      array (
        'type' => 'Beng',
        'regional' => 'bn_BD',
      ),
      'bs' => 
      array (
        'type' => 'Latn',
        'regional' => 'bs_BA',
      ),
      'bg' => 
      array (
        'type' => 'Cyrl',
        'regional' => 'bg_BG',
      ),
      'en_CA' => 
      array (
        'type' => 'Latn',
        'regional' => 'en_CA',
      ),
      'ca' => 
      array (
        'type' => 'Latn',
        'regional' => 'ca_ES',
      ),
      'ceb' => 
      array (
        'type' => 'Latn',
        'regional' => 'ceb_PH',
      ),
      'km' => 
      array (
        'type' => 'Khmr',
        'regional' => 'km_KH',
      ),
      'zh_CN' => 
      array (
        'type' => 'Hans',
        'regional' => 'zh_CN',
      ),
      'zh_HK' => 
      array (
        'type' => 'Hans',
        'regional' => 'zh_HK',
      ),
      'zh_TW' => 
      array (
        'type' => 'Hans',
        'regional' => 'zh_TW',
      ),
      'hr' => 
      array (
        'type' => 'Latn',
        'regional' => 'hr_HR',
      ),
      'cs' => 
      array (
        'type' => 'Latn',
        'regional' => 'cs_CZ',
      ),
      'da' => 
      array (
        'type' => 'Latn',
        'regional' => 'da_DK',
      ),
      'doi' => 
      array (
        'type' => 'Deva',
        'regional' => 'doi_IN',
      ),
      'nl' => 
      array (
        'type' => 'Latn',
        'regional' => 'nl_NL',
      ),
      'en' => 
      array (
        'type' => 'Latn',
        'regional' => 'en_GB',
      ),
      'eo' => 
      array (
        'type' => 'Latn',
        'regional' => 'eo_001',
      ),
      'et' => 
      array (
        'type' => 'Latn',
        'regional' => 'et_EE',
      ),
      'ee' => 
      array (
        'type' => 'Latn',
        'regional' => 'ee_GH',
      ),
      'fi' => 
      array (
        'type' => 'Latn',
        'regional' => 'fi_FI',
      ),
      'fr' => 
      array (
        'type' => 'Latn',
        'regional' => 'fr_FR',
      ),
      'fy' => 
      array (
        'type' => 'Latn',
        'regional' => 'fy_NL',
      ),
      'gl' => 
      array (
        'type' => 'Latn',
        'regional' => 'gl_ES',
      ),
      'ka' => 
      array (
        'type' => 'Geor',
        'regional' => 'ka_GE',
      ),
      'de' => 
      array (
        'type' => 'Latn',
        'regional' => 'de_DE',
      ),
      'de_CH' => 
      array (
        'type' => 'Latn',
        'regional' => 'de_CH',
      ),
      'el' => 
      array (
        'type' => 'Grek',
        'regional' => 'el_GR',
      ),
      'gu' => 
      array (
        'type' => 'Gujr',
        'regional' => 'gu_IN',
      ),
      'ha' => 
      array (
        'type' => 'Latn',
        'regional' => 'ha_NG',
      ),
      'haw' => 
      array (
        'type' => 'Latn',
        'regional' => 'haw',
      ),
      'he' => 
      array (
        'type' => 'Hebr',
        'regional' => 'he_IL',
        'direction' => 
        \LaravelLang\LocaleList\Direction::RightToLeft,
      ),
      'hi' => 
      array (
        'type' => 'Deva',
        'regional' => 'hi_IN',
      ),
      'hu' => 
      array (
        'type' => 'Latn',
        'regional' => 'hu_HU',
      ),
      'is' => 
      array (
        'type' => 'Latn',
        'regional' => 'is_IS',
      ),
      'ig' => 
      array (
        'type' => 'Latn',
        'regional' => 'ig_NG',
      ),
      'id' => 
      array (
        'type' => 'Latn',
        'regional' => 'id_ID',
      ),
      'ga' => 
      array (
        'type' => 'Latn',
        'regional' => 'ga_IE',
      ),
      'it' => 
      array (
        'type' => 'Latn',
        'regional' => 'it_IT',
      ),
      'ja' => 
      array (
        'type' => 'Jpan',
        'regional' => 'ja_JP',
      ),
      'kn' => 
      array (
        'type' => 'Knda',
        'regional' => 'kn_IN',
      ),
      'kk' => 
      array (
        'type' => 'Cyrl',
        'regional' => 'kk_KZ',
      ),
      'rw' => 
      array (
        'type' => 'Latn',
        'regional' => 'rw_RW',
      ),
      'ko' => 
      array (
        'type' => 'Hang',
        'regional' => 'ko_KR',
      ),
      'ku' => 
      array (
        'type' => 'Latn',
        'regional' => 'ku_TR',
      ),
      'ckb' => 
      array (
        'type' => 'Arab',
        'regional' => 'ckb_IQ',
        'direction' => 
        \LaravelLang\LocaleList\Direction::RightToLeft,
      ),
      'ky' => 
      array (
        'type' => 'Cyrl',
        'regional' => 'ky_KG',
      ),
      'lo' => 
      array (
        'type' => 'Laoo',
        'regional' => 'lo_LA',
      ),
      'lv' => 
      array (
        'type' => 'Latn',
        'regional' => 'lv_LV',
      ),
      'ln' => 
      array (
        'type' => 'Latn',
        'regional' => 'ln_CD',
      ),
      'lt' => 
      array (
        'type' => 'Latn',
        'regional' => 'lt_LT',
      ),
      'lg' => 
      array (
        'type' => 'Latn',
        'regional' => 'lg_UG',
      ),
      'lb' => 
      array (
        'type' => 'Latn',
        'regional' => 'lb_LU',
      ),
      'mk' => 
      array (
        'type' => 'Cyrl',
        'regional' => 'mk_MK',
      ),
      'mai' => 
      array (
        'type' => 'Deva',
        'regional' => 'mai_IN',
      ),
      'mg' => 
      array (
        'type' => 'Latn',
        'regional' => 'mg_MG',
      ),
      'ml' => 
      array (
        'type' => 'Mlym',
        'regional' => 'ml_IN',
      ),
      'ms' => 
      array (
        'type' => 'Latn',
        'regional' => 'ms_MY',
      ),
      'mt' => 
      array (
        'type' => 'Latn',
        'regional' => 'mt_MT',
      ),
      'mr' => 
      array (
        'type' => 'Deva',
        'regional' => 'mr_IN',
      ),
      'mi' => 
      array (
        'type' => 'Latn',
        'regional' => 'mi_NZ',
      ),
      'mni_Mtei' => 
      array (
        'type' => 'Beng',
        'regional' => 'mni_IN',
      ),
      'mn' => 
      array (
        'type' => 'Mong',
        'regional' => 'mn_MN',
      ),
      'my' => 
      array (
        'type' => 'Mymr',
        'regional' => 'my_MM',
      ),
      'ne' => 
      array (
        'type' => 'Deva',
        'regional' => 'ne',
      ),
      'nb' => 
      array (
        'type' => 'Latn',
        'regional' => 'nb_NO',
      ),
      'nn' => 
      array (
        'type' => 'Latn',
        'regional' => 'nn_NO',
      ),
      'oc' => 
      array (
        'type' => 'Latn',
        'regional' => 'oc_FR',
      ),
      'or' => 
      array (
        'type' => 'Orya',
        'regional' => 'or_IN',
      ),
      'om' => 
      array (
        'type' => 'Latn',
        'regional' => 'om_ET',
      ),
      'ps' => 
      array (
        'type' => 'Arab',
        'regional' => 'ps_AF',
        'direction' => 
        \LaravelLang\LocaleList\Direction::RightToLeft,
      ),
      'fa' => 
      array (
        'type' => 'Arab',
        'regional' => 'fa_IR',
        'direction' => 
        \LaravelLang\LocaleList\Direction::RightToLeft,
      ),
      'fil' => 
      array (
        'type' => 'Latn',
        'regional' => 'fil_PH',
      ),
      'pl' => 
      array (
        'type' => 'Latn',
        'regional' => 'pl_PL',
      ),
      'pt' => 
      array (
        'type' => 'Latn',
        'regional' => 'pt_PT',
      ),
      'pt_BR' => 
      array (
        'type' => 'Latn',
        'regional' => 'pt_BR',
      ),
      'pa' => 
      array (
        'type' => 'Guru',
        'regional' => 'pa_IN',
      ),
      'qu' => 
      array (
        'type' => 'Latn',
        'regional' => 'qu_PE',
      ),
      'ro' => 
      array (
        'type' => 'Latn',
        'regional' => 'ro_RO',
      ),
      'ru' => 
      array (
        'type' => 'Cyrl',
        'regional' => 'ru_RU',
      ),
      'sa' => 
      array (
        'type' => 'Deva',
        'regional' => 'sa_IN',
      ),
      'sc' => 
      array (
        'type' => 'Latn',
        'regional' => 'sc_IT',
      ),
      'gd' => 
      array (
        'type' => 'Latn',
        'regional' => 'gd_GB',
      ),
      'sr_Cyrl' => 
      array (
        'type' => 'Cyrl',
        'regional' => 'sr_RS',
      ),
      'sr_Latn' => 
      array (
        'type' => 'Latn',
        'regional' => 'sr_RS',
      ),
      'sr_Latn_ME' => 
      array (
        'type' => 'Latn',
        'regional' => 'sr_Latn_ME',
      ),
      'sn' => 
      array (
        'type' => 'Latn',
        'regional' => 'sn_ZW',
      ),
      'sd' => 
      array (
        'type' => 'Arab',
        'regional' => 'sd_PK',
        'direction' => 
        \LaravelLang\LocaleList\Direction::RightToLeft,
      ),
      'si' => 
      array (
        'type' => 'Sinh',
        'regional' => 'si_LK',
      ),
      'sk' => 
      array (
        'type' => 'Latn',
        'regional' => 'sk_SK',
      ),
      'sl' => 
      array (
        'type' => 'Latn',
        'regional' => 'sl_SI',
      ),
      'so' => 
      array (
        'type' => 'Latn',
        'regional' => 'so_SO',
      ),
      'es' => 
      array (
        'type' => 'Latn',
        'regional' => 'es_ES',
      ),
      'su' => 
      array (
        'type' => 'Latn',
        'regional' => 'su_ID',
      ),
      'sw' => 
      array (
        'type' => 'Latn',
        'regional' => 'sw_KE',
      ),
      'sv' => 
      array (
        'type' => 'Latn',
        'regional' => 'sv_SE',
      ),
      'tl' => 
      array (
        'type' => 'Latn',
        'regional' => 'tl_PH',
      ),
      'tg' => 
      array (
        'type' => 'Cyrl',
        'regional' => 'tg_TJ',
      ),
      'ta' => 
      array (
        'type' => 'Taml',
        'regional' => 'ta_IN',
      ),
      'tt' => 
      array (
        'type' => 'Cyrl',
        'regional' => 'tt_RU',
      ),
      'te' => 
      array (
        'type' => 'Telu',
        'regional' => 'te_IN',
      ),
      'ti' => 
      array (
        'type' => 'Ethi',
        'regional' => 'ti_ET',
      ),
      'th' => 
      array (
        'type' => 'Thai',
        'regional' => 'th_TH',
      ),
      'tr' => 
      array (
        'type' => 'Latn',
        'regional' => 'tr_TR',
      ),
      'tk' => 
      array (
        'type' => 'Cyrl',
        'regional' => 'tk_TM',
      ),
      'ak' => 
      array (
        'type' => 'Latn',
        'regional' => 'ak_GH',
      ),
      'ug' => 
      array (
        'type' => 'Arab',
        'regional' => 'ug_CN',
        'direction' => 
        \LaravelLang\LocaleList\Direction::RightToLeft,
      ),
      'uk' => 
      array (
        'type' => 'Cyrl',
        'regional' => 'uk_UA',
      ),
      'ur' => 
      array (
        'type' => 'Arab',
        'regional' => 'ur_PK',
        'direction' => 
        \LaravelLang\LocaleList\Direction::RightToLeft,
      ),
      'uz_Cyrl' => 
      array (
        'type' => 'Cyrl',
        'regional' => 'uz_UZ',
      ),
      'uz_Latn' => 
      array (
        'type' => 'Latn',
        'regional' => 'uz_UZ',
      ),
      'vi' => 
      array (
        'type' => 'Latn',
        'regional' => 'vi_VN',
      ),
      'cy' => 
      array (
        'type' => 'Latn',
        'regional' => 'cy_GB',
      ),
      'xh' => 
      array (
        'type' => 'Latn',
        'regional' => 'xh_ZA',
      ),
      'yi' => 
      array (
        'type' => 'Hebr',
        'regional' => 'yi_001',
      ),
      'yo' => 
      array (
        'type' => 'Latn',
        'regional' => 'yo_NG',
      ),
      'zu' => 
      array (
        'type' => 'Latn',
        'regional' => 'zu_ZA',
      ),
    ),
  ),
  'livewire' => 
  array (
    'class_namespace' => 'App\\Livewire',
    'view_path' => 'C:\\Users\\GCORPLLC\\Desktop\\Project\\journa\\resources\\views/livewire',
    'layout' => 'components.layouts.app',
    'lazy_placeholder' => NULL,
    'temporary_file_upload' => 
    array (
      'disk' => NULL,
      'rules' => NULL,
      'directory' => NULL,
      'middleware' => NULL,
      'preview_mimes' => 
      array (
        0 => 'png',
        1 => 'gif',
        2 => 'bmp',
        3 => 'svg',
        4 => 'wav',
        5 => 'mp4',
        6 => 'mov',
        7 => 'avi',
        8 => 'wmv',
        9 => 'mp3',
        10 => 'm4a',
        11 => 'jpg',
        12 => 'jpeg',
        13 => 'mpga',
        14 => 'webp',
        15 => 'wma',
      ),
      'max_upload_time' => 5,
      'cleanup' => true,
    ),
    'render_on_redirect' => false,
    'legacy_model_binding' => false,
    'inject_assets' => true,
    'navigate' => 
    array (
      'show_progress_bar' => true,
      'progress_bar_color' => '#2299dd',
    ),
    'inject_morph_markers' => true,
    'pagination_theme' => 'tailwind',
  ),
  'sitemap' => 
  array (
    'guzzle_options' => 
    array (
      'cookies' => true,
      'connect_timeout' => 10,
      'timeout' => 10,
      'allow_redirects' => false,
    ),
    'execute_javascript' => false,
    'chrome_binary_path' => NULL,
    'crawl_profile' => 'Spatie\\Sitemap\\Crawler\\Profile',
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'alias' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
);
