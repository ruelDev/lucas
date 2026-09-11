<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

$defaultDateMY = date("Ym");
$defaultDateMYD = date("Ymd");
$defaultLogFileName = '/debug.log';
$offlineSearchLogPath = 'logs/OfflineSearchFacility/' . $defaultDateMY . '/' . $defaultDateMYD;
$defaultFilePermission = 0664;
$defaultFileLocking = true;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        // 'stack' => [
        //     'driver' => 'stack',
        //     'channels' => explode(',', env('LOG_STACK', 'single')),
        //     'ignore_exceptions' => false,
        // ],

        'stack' => [
            'driver' => 'stack',
            'channels' => [
                'user_management',
                'role_management',
                'branch_management',
                'division_management',
                'department_management',
                'section_management',
            ],
            'ignore_exceptions' => true,
        ],

        // -----PER SPECIFIC MODULE LOGS
        'queue_management' => [
            'driver' => 'single',
            'path' => storage_path('logs/Queue/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'session' => [
            'driver' => 'single',
            'path' => storage_path('logs/Sessions/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'audit_logs' => [
            'driver' => 'single',
            'path' => storage_path('logs/AuditLogs/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'user_management' => [
            'driver' => 'single',
            'path' => storage_path('logs/UserManagement/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'role_management' => [
            'driver' => 'single',
            'path' => storage_path('logs/RoleManagement/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'branch_management' => [
            'driver' => 'single',
            'path' => storage_path('logs/BranchManagement/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'dealer_management' => [
            'driver' => 'single',
            'path' => storage_path('logs/DealerManagement/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'group_management' => [
            'driver' => 'single',
            'path' => storage_path('logs/GroupManagement/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'division_management' => [
            'driver' => 'single',
            'path' => storage_path('logs/DivisionManagement/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'department_management' => [
            'driver' => 'single',
            'path' => storage_path('logs/DepartmentManagement/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'section_management' => [
            'driver' => 'single',
            'path' => storage_path('logs/SectionManagement/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'user_report' => [
            'driver' => 'single',
            'path' => storage_path('logs/UserReports/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'jasper_reports' => [
            'driver' => 'single',
            'path' => storage_path('logs/JasperReports/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'cfp' => [
            'driver' => 'single',
            'path' => storage_path('logs/CFP/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],
        'receipt_converter' => [
            'driver' => 'single',
            'path' => storage_path('logs/ReceiptConverter/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],
        'out_collection' => [
            'driver' => 'single',
            'path' => storage_path('logs/OutCollection/Deposit/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],
        'daily_collection' => [
            'driver' => 'single',
            'path' => storage_path('logs/OutCollectionReport/DailyCollection/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'authorized' => [
            'driver' => 'single',
            'path' => storage_path('logs/OutCollectionReport/Authorized/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        'unauthorized' => [
            'driver' => 'single',
            'path' => storage_path('logs/OutCollectionReport/Unauthorized/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],
        'finnone-qr' => [
            'driver' => 'single',
            'path' => storage_path('logs/OutCollectionReport/FinnoneQR/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],
        'finnone-ua' => [
            'driver' => 'single',
            'path' => storage_path('logs/OutCollectionReport/FinnoneUA/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],
        'newgen-qr' => [
            'driver' => 'single',
            'path' => storage_path('logs/OutCollectionReport/NewgenQR/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        // -------------------- Start Offline Search --------------------
        'offline_search_client_records' => [
            'driver' => 'single',
            'path' => storage_path($offlineSearchLogPath . '/offline_search_client_records.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'offline_search_client_accounts' => [
            'driver' => 'single',
            // 'path' => storage_path('logs/offline_search_client_accounts.log'),
            'path' => storage_path($offlineSearchLogPath . '/offline_search_client_accounts.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'offline_search_los_records' => [
            'driver' => 'single',
            // 'path' => storage_path('logs/offline_search_los_records.log'),
            'path' => storage_path($offlineSearchLogPath . '/offline_search_los_records.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'offline_search_lms_records' => [
            'driver' => 'single',
            // 'path' => storage_path('logs/offline_search_lms_records.log'),
            'path' => storage_path($offlineSearchLogPath . '/offline_search_lms_records.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        // -------------------- End Offline Search --------------------

        'worker' => [
            'driver' => 'single',
            'path' => storage_path('logs/workers/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],

        // ---------------Default Daily Logs --------------------------------------------------
        // 'daily_debug' => [
        //     'driver' => 'single',
        //     'path' => storage_path('logs/'. $defaultDateMY . '/'. $defaultDateMYD . $defaultLogFileName),
        //     'level' => 'debug',
        //     'replace_placeholders' => true,
        // ],

        // 'daily_info' => [
        //     'driver' => 'single',
        //     'path' => storage_path('logs/'. $defaultDateMY . '/'. $defaultDateMYD .'/info/info.log'),
        //     'level' => 'info',
        //     'days' => env('LOG_DAILY_DAYS', 7),
        //     'replace_placeholders' => true,
        // ],

        // 'daily_critical' => [
        //     'driver' => 'single',
        //     'path' => storage_path('logs/'. $defaultDateMY . '/'. $defaultDateMYD .'/critical.log'),
        //     'level' => 'critical',
        //     'replace_placeholders' => true,
        // ],

        // 'daily_error' => [
        //     'driver' => 'single',
        //     'path' => storage_path('logs/'. $defaultDateMY . '/'. $defaultDateMYD .'/error/error.log'),
        //     'level' => 'error',
        //     'replace_placeholders' => true,
        // ],




        // default logging
        'sso' => [
            'driver' => 'single',
            'path' => storage_path('logs/Sso/' . $defaultDateMY . '/' . $defaultDateMYD . $defaultLogFileName),
            'level' => 'debug',
            'permission' => $defaultFilePermission,
            'locking' => $defaultFileLocking,
            'replace_placeholders' => true,
        ],
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/daily-laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];
