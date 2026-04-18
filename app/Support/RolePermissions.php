<?php

namespace App\Support;

/**
 * Defines the permission matrix for each role.
 * No Spatie required — uses the existing role string column on users.
 */
class RolePermissions
{
    public const PERMISSIONS = [
        // Users & Settings
        'users.view', 'users.create', 'users.update', 'users.delete',
        'roles.view', 'roles.manage',
        'settings.company.manage', 'settings.sms_otp.manage',

        // Customer Management
        'customers.view', 'customers.create', 'customers.update', 'customers.delete',
        'customers.approve', 'customers.reject',
        'zones.view', 'zones.manage',
        'customer_prices.view', 'customer_prices.manage',
        'subscriptions.view', 'subscriptions.manage',

        // Dealer Management
        'dealers.view', 'dealers.create', 'dealers.update', 'dealers.delete',
        'dealers.approve', 'dealers.reject',
        'dealer_prices.view', 'dealer_prices.manage',

        // Orders & Delivery
        'orders.view', 'orders.create', 'orders.update', 'orders.delete',
        'orders.confirm', 'orders.cancel', 'orders.review_recurring',
        'deliveries.view', 'deliveries.create', 'deliveries.update', 'deliveries.delete',
        'deliveries.assign', 'deliveries.mark_delivered', 'deliveries.mark_failed',

        // Billing & Payment
        'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete',
        'invoices.issue', 'invoices.cancel', 'invoices.print',
        'payments.view', 'payments.create', 'payments.update', 'payments.delete',
        'payments.print', 'collections.reconcile',

        // Inventory
        'products.view', 'products.create', 'products.update', 'products.delete',
        'stock_transactions.view', 'stock_transactions.create', 'stock_transactions.update', 'stock_transactions.delete',
        'jar_deposits.view', 'jar_deposits.create', 'jar_deposits.update', 'jar_deposits.delete',

        // Reports
        'reports.dashboard.view', 'reports.sales.view', 'reports.delivery.view',
        'reports.due.view', 'reports.stock.view', 'reports.customer_ledger.view',
    ];

    public const ROLE_PERMISSIONS = [
        'super_admin' => '*', // all

        'admin' => [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.view',
            'settings.company.manage', 'settings.sms_otp.manage',
            'customers.view', 'customers.create', 'customers.update', 'customers.delete',
            'customers.approve', 'customers.reject',
            'zones.view', 'zones.manage',
            'customer_prices.view', 'customer_prices.manage',
            'subscriptions.view', 'subscriptions.manage',
            'dealers.view', 'dealers.create', 'dealers.update', 'dealers.delete',
            'dealers.approve', 'dealers.reject',
            'dealer_prices.view', 'dealer_prices.manage',
            'orders.view', 'orders.create', 'orders.update', 'orders.delete',
            'orders.confirm', 'orders.cancel', 'orders.review_recurring',
            'deliveries.view', 'deliveries.create', 'deliveries.update', 'deliveries.delete',
            'deliveries.assign', 'deliveries.mark_delivered', 'deliveries.mark_failed',
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete',
            'invoices.issue', 'invoices.cancel', 'invoices.print',
            'payments.view', 'payments.create', 'payments.update', 'payments.delete',
            'payments.print', 'collections.reconcile',
            'products.view', 'products.create', 'products.update', 'products.delete',
            'stock_transactions.view', 'stock_transactions.create', 'stock_transactions.update', 'stock_transactions.delete',
            'jar_deposits.view', 'jar_deposits.create', 'jar_deposits.update', 'jar_deposits.delete',
            'reports.dashboard.view', 'reports.sales.view', 'reports.delivery.view',
            'reports.due.view', 'reports.stock.view', 'reports.customer_ledger.view',
        ],

        'delivery_manager' => [
            'reports.dashboard.view',
            'customers.view', 'dealers.view', 'zones.view',
            'subscriptions.view',
            'orders.view', 'orders.update', 'orders.confirm', 'orders.cancel',
            'deliveries.view', 'deliveries.create', 'deliveries.update',
            'deliveries.assign', 'deliveries.mark_delivered', 'deliveries.mark_failed',
            'reports.delivery.view',
        ],

        'billing_officer' => [
            'reports.dashboard.view',
            'customers.view', 'dealers.view',
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete',
            'invoices.issue', 'invoices.cancel', 'invoices.print',
            'payments.view', 'payments.create', 'payments.update', 'payments.delete',
            'payments.print', 'collections.reconcile',
            'reports.sales.view', 'reports.due.view', 'reports.customer_ledger.view',
        ],

        'stock_manager' => [
            'reports.dashboard.view',
            'customers.view', 'dealers.view',
            'products.view', 'products.create', 'products.update',
            'stock_transactions.view', 'stock_transactions.create', 'stock_transactions.update', 'stock_transactions.delete',
            'jar_deposits.view', 'jar_deposits.create', 'jar_deposits.update', 'jar_deposits.delete',
            'reports.stock.view',
        ],

        // No Filament permissions for these roles
        'customer'       => [],
        'dealer'         => [],
        'delivery_staff' => [],
    ];

    /**
     * Check if a role has a specific permission.
     */
    public static function roleHas(string $role, string $permission): bool
    {
        $perms = self::ROLE_PERMISSIONS[$role] ?? [];

        if ($perms === '*') {
            return true;
        }

        return in_array($permission, $perms);
    }
}
