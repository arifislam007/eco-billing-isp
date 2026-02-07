<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Auth');
$routes->setDefaultMethod('login');
$routes->setTranslateURIDashes(false);
$routes->set404Override(function ($message = null) {
    echo view('errors/html/error_404');
});

// Public Routes
$routes->get('/', 'Auth::login');
$routes->get('/login', 'Auth::login');
$routes->post('/auth/login', 'Auth::attemptLogin');
$routes->get('/logout', 'Auth::logout');

// Admin Routes (Protected)
$routes->group('admin', ['filter' => 'auth'], function (RouteCollection $routes) {
    // Dashboard
    $routes->get('dashboard', 'Dashboard::index');
    
    // Customers
    $routes->get('customers', 'Customer::index');
    $routes->get('customers/create', 'Customer::create');
    $routes->post('customers/store', 'Customer::store');
    $routes->get('customers/edit/(:num)', 'Customer::edit/$1');
    $routes->post('customers/update/(:num)', 'Customer::update/$1');
    $routes->get('customers/view/(:num)', 'Customer::view/$1');
    $routes->get('customers/delete/(:num)', 'Customer::delete/$1');
    $routes->post('customers/status/(:num)', 'Customer::updateStatus/$1');
    $routes->get('customers/export', 'Customer::export');
    
    // Packages
    $routes->get('packages', 'Package::index');
    $routes->get('packages/create', 'Package::create');
    $routes->post('packages/store', 'Package::store');
    $routes->get('packages/edit/(:num)', 'Package::edit/$1');
    $routes->post('packages/update/(:num)', 'Package::update/$1');
    $routes->get('packages/delete/(:num)', 'Package::delete/$1');
    $routes->get('packages/sync-radius/(:num)', 'Package::syncRadius/$1');
    
    // Routers
    $routes->get('routers', 'Router::index');
    $routes->get('routers/create', 'Router::create');
    $routes->post('routers/store', 'Router::store');
    $routes->get('routers/edit/(:num)', 'Router::edit/$1');
    $routes->post('routers/update/(:num)', 'Router::update/$1');
    $routes->get('routers/delete/(:num)', 'Router::delete/$1');
    $routes->get('routers/test/(:num)', 'Router::test/$1');
    
    // Invoices
    $routes->get('invoices', 'Invoice::index');
    $routes->get('invoices/create', 'Invoice::create');
    $routes->post('invoices/store', 'Invoice::store');
    $routes->get('invoices/view/(:num)', 'Invoice::view/$1');
    $routes->get('invoices/print/(:num)', 'Invoice::print/$1');
    $routes->get('invoices/pdf/(:num)', 'Invoice::pdf/$1');
    $routes->post('invoices/mark-paid/(:num)', 'Invoice::markAsPaid/$1');
    $routes->get('invoices/delete/(:num)', 'Invoice::delete/$1');
    
    // Payments
    $routes->get('payments', 'Payment::index');
    $routes->get('payments/create', 'Payment::create');
    $routes->post('payments/store', 'Payment::store');
    $routes->get('payments/view/(:num)', 'Payment::view/$1');
    $routes->get('payments/delete/(:num)', 'Payment::delete/$1');
    
    // Reports
    $routes->get('reports', 'Report::index');
    $routes->get('reports/revenue', 'Report::revenue');
    $routes->get('reports/customers', 'Report::customers');
    $routes->get('reports/payments', 'Report::payments');
    $routes->get('reports/online', 'Report::online');
    
    // Tickets
    $routes->get('tickets', 'Ticket::index');
    $routes->get('tickets/create', 'Ticket::create');
    $routes->post('tickets/store', 'Ticket::store');
    $routes->get('tickets/view/(:num)', 'Ticket::view/$1');
    $routes->post('tickets/reply/(:num)', 'Ticket::reply/$1');
    $routes->post('tickets/status/(:num)', 'Ticket::updateStatus/$1');
    
    // Settings
    $routes->get('settings', 'Setting::index');
    $routes->post('settings/company', 'Setting::updateCompany');
    $routes->post('settings/billing', 'Setting::updateBilling');
    $routes->post('settings/smtp', 'Setting::updateSmtp');
    
    // RADIUS
    $routes->get('radius/groups', 'Radius::groups');
    $routes->get('radius/replies', 'Radius::replies');
    $routes->get('radius/test', 'Radius::test');
});

// Customer Portal Routes
$routes->group('portal', function (RouteCollection $routes) {
    $routes->get('login', 'Portal::login');
    $routes->post('auth', 'Portal::authenticate');
    $routes->get('dashboard', 'Portal::dashboard', ['filter' => 'customer']);
    $routes->get('invoices', 'Portal::invoices', ['filter' => 'customer']);
    $routes->get('tickets', 'Portal::tickets', ['filter' => 'customer']);
    $routes->post('tickets/store', 'Portal::storeTicket', ['filter' => 'customer']);
    $routes->get('profile', 'Portal::profile', ['filter' => 'customer']);
    $routes->post('profile/update', 'Portal::updateProfile', ['filter' => 'customer']);
    $routes->get('logout', 'Portal::logout');
});

// API Routes
$routes->group('api', function (RouteCollection $routes) {
    $routes->get('dashboard/stats', 'Api::dashboardStats');
    $routes->get('customers/list', 'Api::customers');
    $routes->get('packages/list', 'Api::packages');
    $routes->get('invoices/list', 'Api::invoices');
    $routes->get('payments/list', 'Api::payments');
});
