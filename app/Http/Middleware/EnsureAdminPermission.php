<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    /**
     * @var array<string, string>
     */
    private const DOMAIN_ALIASES = [
        'advertisements' => 'marketing',
        'categories' => 'catalog',
        'payment-methods' => 'financials',
        'tickets' => 'support',
        'coupons' => 'financials',
        'disputes' => 'reviews',
    ];

    /**
     * @var array<string, string>
     */
    private const ROUTE_PERMISSIONS = [
        'admin.dashboard.view' => 'admin.dashboard',
        'admin.orders.view' => 'admin.orders.index',
        'admin.craftsmen.view' => 'admin.craftsmen.index',
        'admin.catalog.view' => 'admin.categories.index',
        'admin.stores.view' => 'admin.stores.index',
        'admin.clients.view' => 'admin.clients.index',
        'admin.locations.view' => 'admin.locations.index',
        'admin.marketing.view' => 'admin.advertisements.index',
        'admin.financials.view' => 'admin.financials.payouts',
        'admin.accounting.view' => 'admin.accounting.accounts',
        'admin.suppliers.view' => 'admin.suppliers.index',
        'admin.employees.view' => 'admin.employees.index',
        'admin.reviews.view' => 'admin.reviews.index',
        'admin.support.view' => 'admin.tickets.index',
        'admin.notifications.view' => 'admin.notifications.index',
        'admin.settings.view' => 'admin.settings.index',
        'admin.users.view' => 'admin.users.roles',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        if ($user->isAdmin() || $user->getAttribute('role') === 'admin') {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $segments = explode('.', (string) $routeName);
        $domain = $segments[1] ?? 'access';
        $domain = self::DOMAIN_ALIASES[$domain] ?? $domain;
        $action = in_array($request->method(), ['GET', 'HEAD'], true) ? 'view' : 'manage';

        // إذا كان المستخدم لا يملك صلاحية عرض الرئيسية وتم توجيهه إليها، ننقله تلقائياً لأول واجهة مصرح له بها
        if ($domain === 'dashboard' && ! $user->can('admin.dashboard.view')) {
            $fallbackRoute = $this->getFirstPermittedRoute($user);
            if ($fallbackRoute) {
                return redirect()->route($fallbackRoute);
            }
        }

        abort_unless($user->can("admin.{$domain}.{$action}"), 403);

        return $next($request);
    }

    private function getFirstPermittedRoute($user): ?string
    {
        foreach (self::ROUTE_PERMISSIONS as $perm => $route) {
            if ($user->can($perm)) {
                return $route;
            }
        }

        return null;
    }
}
