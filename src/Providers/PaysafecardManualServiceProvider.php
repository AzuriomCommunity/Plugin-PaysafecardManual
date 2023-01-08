<?php

namespace Azuriom\Plugin\PaysafecardManual\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\Permission;
use Azuriom\Plugin\PaysafecardManual\PaysafecardManualMethod;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Models\PaymentItem;
use Exception;
use Azuriom\Plugin\PaysafecardManual\Models\PendingCode;

class PaysafecardManualServiceProvider extends BasePluginServiceProvider
{
    /**
     * Register any plugin services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerMiddlewares();
        //
    }

    /**
     * Bootstrap any plugin services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->registerPolicies();

        $this->loadViews();

        $this->loadTranslations();

        $this->loadMigrations();

        $this->registerRouteDescriptions();

        $this->registerAdminNavigation();

        Permission::registerPermissions([
            'paysafecardmanual.manage' => 'paysafecardmanual::admin.permissions.manage',
        ]);

        payment_manager()->registerPaymentMethod('paysafecardmanual', PaysafecardManualMethod::class);
    }

    /**
     * Returns the routes that should be able to be added to the navbar.
     *
     * @return array
     */
    protected function routeDescriptions()
    {
        return [
            //
        ];
    }

    /**
     * Return the admin navigations routes to register in the dashboard.
     *
     * @return array
     */
    protected function adminNavigation()
    {
        return [
            'paysafecardmanual' => [
                'name' => 'Paysafecard Manual ('.PendingCode::count().')',
                'type' => 'dropdown',
                'icon' => 'bi bi-bar-chart-line',
                'route' => 'paysafecardmanual.admin.*',
                'items' => [
                    'paysafecardmanual.admin.index' => trans('paysafecardmanual::messages.nav.pending'),
                ],
                'permission' => 'paysafecardmanual.manage'
            ]
        ];
    }
}
