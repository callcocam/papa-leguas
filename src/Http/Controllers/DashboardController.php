<?php

namespace Callcocam\PapaLeguas\Http\Controllers;

use App\Http\Controllers\Controller;
use Callcocam\Papaleguas\Http\Concerns\HasMenuMetadata;
use Callcocam\PapaLeguas\Models\Tenant;
use Callcocam\PapaLeguas\Services\DomainDetectionService;
use Callcocam\PapaLeguas\Support\Dashboard\DashboardLayout;
use Callcocam\PapaLeguas\Support\Dashboard\Widgets\ChartWidget;
use Callcocam\PapaLeguas\Support\Dashboard\Widgets\ListWidget;
use Callcocam\PapaLeguas\Support\Dashboard\Widgets\StatWidget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use HasMenuMetadata;

    protected string|null $navigationIcon = 'LayoutDashboard';

    
    protected string|null $componentIndexPath = 'views/Dashboard.vue';

    public function __construct(
        protected DomainDetectionService $domainDetectionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $context = $this->domainDetectionService->getContext($request);

        $dashboard = match ($context) {
            'landlord' => $this->getLandlordDashboard(),
            'tenant' => $this->getTenantDashboard(),
            default => $this->getBaseDashboard(),
        };
 

        return response()->json($dashboard->toArray());
    }
    
    public function getSluggedName(): ?string
    {
        return 'dashboard';
    }

    public function widgetData(Request $request, string $widgetId): JsonResponse
    {
        $context = $this->domainDetectionService->getContext($request);

        $dashboard = match ($context) {
            'landlord' => $this->getLandlordDashboard(),
            'tenant' => $this->getTenantDashboard(),
            default => $this->getBaseDashboard(),
        };

        $widget = $dashboard->getWidget($widgetId);

        if ($widget === null) {
            return response()->json([
                'error' => 'Widget não encontrado'
            ], 404);
        }

        try {
            $data = $widget->getData();

            return response()->json([
                'data' => $data,
                'widgetId' => $widgetId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao carregar dados do widget',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function getLandlordDashboard(): DashboardLayout
    {
        return DashboardLayout::make()
            ->columns(3)
            ->widgets([
                StatWidget::make('Total de Tenants')
                    ->id('total-tenants')
                    ->icon('Users')
                    ->color('primary')
                    ->value(fn() => Tenant::count())
                    ->description('Total de tenants cadastrados'),

                StatWidget::make('Tenants Ativos')
                    ->id('active-tenants')
                    ->icon('CheckCircle')
                    ->color('success')
                    ->value(fn() => Tenant::where('status', 'active')->count())
                    ->description('Tenants com status ativo'),

                StatWidget::make('Novos Este Mês')
                    ->id('new-tenants-month')
                    ->icon('TrendingUp')
                    ->color('info')
                    ->value(fn() => Tenant::whereMonth('created_at', now()->month)->count())
                    ->description('Tenants criados este mês')
                    ->trend('+12%', 'up'),

                ChartWidget::make('Crescimento de Tenants')
                    ->id('tenants-growth-chart')
                    ->chartType('line')
                    ->colSpan(3)
                    ->data(function() {
                        $lastSixMonths = collect(range(5, 0))->map(function($monthsAgo) {
                            $date = now()->subMonths($monthsAgo);
                            $count = Tenant::whereYear('created_at', $date->year)
                                ->whereMonth('created_at', $date->month)
                                ->count();

                            return [
                                'label' => $date->format('M/Y'),
                                'value' => $count,
                            ];
                        });

                        return [
                            'labels' => $lastSixMonths->pluck('label')->toArray(),
                            'datasets' => [
                                [
                                    'label' => 'Novos Tenants',
                                    'data' => $lastSixMonths->pluck('value')->toArray(),
                                ],
                            ],
                        ];
                    }),

                ListWidget::make('Tenants Recentes')
                    ->id('recent-tenants-list')
                    ->colSpan(3)
                    ->limit(5)
                    ->data(fn() => Tenant::latest()->take(5)->get()->toArray())
                    ->itemLabel(fn($item) => $item['name'] ?? 'Sem nome')
                    ->itemDescription(fn($item) => 'Criado em ' . now()->parse($item['created_at'])->format('d/m/Y'))
                    ->itemIcon(fn() => 'Building'),
            ]);
    }

    protected function getTenantDashboard(): DashboardLayout
    {
        return DashboardLayout::make()
            ->columns(3)
            ->widgets([
                StatWidget::make('Meus Usuários')
                    ->id('my-users')
                    ->icon('Users')
                    ->color('primary')
                    ->value(fn() => \App\Models\User::count())
                    ->description('Usuários cadastrados'),

                StatWidget::make('Projetos Ativos')
                    ->id('active-projects')
                    ->icon('FolderOpen')
                    ->color('success')
                    ->value(fn() => 0)
                    ->description('Projetos em andamento'),

                StatWidget::make('Atividades Hoje')
                    ->id('activities-today')
                    ->icon('Activity')
                    ->color('info')
                    ->value(fn() => 0)
                    ->description('Atividades registradas hoje'),
            ]);
    }

    protected function getBaseDashboard(): DashboardLayout
    {
        return DashboardLayout::make()
            ->columns(3)
            ->widgets([
                StatWidget::make('Bem-vindo')
                    ->id('welcome-stat')
                    ->icon('Home')
                    ->color('primary')
                    ->value(fn() => 'Papa Leguas')
                    ->description('Sistema de gerenciamento multi-tenant'),
            ]);
    }
}