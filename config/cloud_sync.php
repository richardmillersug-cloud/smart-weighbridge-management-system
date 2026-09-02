<?php

use App\Models\AuditLog;
use App\Models\CashSession;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WeighbridgeStation;
use App\Models\WeighbridgeTicket;
use App\Models\WeightInvoice;

return [

    /*
    |--------------------------------------------------------------------------
    | Cloud sync (local primary → DigitalOcean mirror)
    |--------------------------------------------------------------------------
    |
    | The station PC writes to local MySQL first. Queue jobs push changes to
    | mysql_cloud as soon as possible when CLOUD_SYNC_ENABLED=true.
    |
    */

    'enabled' => env('CLOUD_SYNC_ENABLED', false),

    'connection' => env('DB_CLOUD_CONNECTION', 'mysql_cloud'),

    'queue' => env('CLOUD_SYNC_QUEUE', 'default'),

    /*
    | Models synced automatically after local save/delete.
    | Keys are used for dependency resolution (parents before children).
    */
    'models' => [
        'users' => User::class,
        'weighbridge_stations' => WeighbridgeStation::class,
        'settings' => Setting::class,
        'customers' => Customer::class,
        'drivers' => Driver::class,
        'vehicles' => Vehicle::class,
        'products' => Product::class,
        'cash_sessions' => CashSession::class,
        'weighbridge_tickets' => WeighbridgeTicket::class,
        'weight_invoices' => WeightInvoice::class,
        'payments' => Payment::class,
        'audit_logs' => AuditLog::class,
    ],

    /*
    | Foreign-key dependencies synced before the main record.
    */
    'dependencies' => [
        WeighbridgeTicket::class => [
            WeighbridgeStation::class,
            Customer::class,
            Vehicle::class,
            Driver::class,
            Product::class,
            User::class,
        ],
        WeightInvoice::class => [
            WeighbridgeTicket::class,
            User::class,
        ],
        Payment::class => [
            WeightInvoice::class,
            CashSession::class,
            User::class,
        ],
        AuditLog::class => [
            User::class,
        ],
        CashSession::class => [
            User::class,
        ],
    ],

    /*
    | Map model foreign key columns to related model classes.
    */
    'foreign_keys' => [
        WeighbridgeTicket::class => [
            'station_id' => WeighbridgeStation::class,
            'customer_id' => Customer::class,
            'vehicle_id' => Vehicle::class,
            'driver_id' => Driver::class,
            'product_id' => Product::class,
            'created_by' => User::class,
            'completed_by' => User::class,
        ],
        WeightInvoice::class => [
            'ticket_id' => WeighbridgeTicket::class,
            'customer_id' => Customer::class,
            'created_by' => User::class,
        ],
        Payment::class => [
            'invoice_id' => WeightInvoice::class,
            'cash_session_id' => CashSession::class,
            'received_by' => User::class,
        ],
        AuditLog::class => [
            'user_id' => User::class,
        ],
        CashSession::class => [
            'user_id' => User::class,
        ],
    ],

    /*
    | Full sync order (parents first). Used by cloud:sync-full.
    */
    'full_sync_order' => [
        User::class,
        WeighbridgeStation::class,
        Setting::class,
        Customer::class,
        Driver::class,
        Vehicle::class,
        Product::class,
        CashSession::class,
        WeighbridgeTicket::class,
        WeightInvoice::class,
        Payment::class,
        AuditLog::class,
    ],

];
