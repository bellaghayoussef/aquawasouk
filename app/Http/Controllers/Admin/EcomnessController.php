<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssistantSetting;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\EcomnessService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EcomnessController extends Controller
{
    protected EcomnessService $service;

    public function __construct(EcomnessService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $vendor = $this->service->getVendor();
        $apiKey = $this->service->getApiKey();
        $ref = $this->service->fetchReference();
        $mapping = $this->service->getCategoryMapping();
        $categories = Category::select('id', 'name')->orderBy('name')->get();

        $totalProducts = Product::where('vendor_id', $vendor->id)
            ->orWhereNotNull('ecomness_code')
            ->count();

        $totalOrders = Order::whereNotNull('ecomness_order_code')->count();
        $pendingOrders = Order::whereNull('ecomness_order_code')
            ->whereHas('items.product', function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id)->orWhereNotNull('ecomness_code');
            })
            ->count();

        $recentOrders = Order::where(function ($q) use ($vendor) {
            $q->whereNotNull('ecomness_order_code')
                ->orWhereHas('items.product', function ($sub) use ($vendor) {
                    $sub->where('vendor_id', $vendor->id)->orWhereNotNull('ecomness_code');
                });
        })
            ->with(['items.product', 'user'])
            ->latest()
            ->take(20)
            ->get();

        return Inertia::render('Admin/Ecomness/Index', [
            'hasApiKey' => $this->service->hasApiKey(),
            'apiKey' => $apiKey ? (substr($apiKey, 0, 10) . '...' . substr($apiKey, -6)) : '',
            'rawApiKey' => $apiKey ?: '',
            'vendor' => $vendor,
            'categories' => $categories,
            'ecomnessCategories' => $ref['productCategories'] ?? array_keys(EcomnessService::DEFAULT_CATEGORY_MAPPING),
            'governorates' => $ref['governorates'] ?? EcomnessService::GOVERNORATES,
            'categoryMapping' => $mapping,
            'stats' => [
                'total_products' => $totalProducts,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'last_sync_at' => AssistantSetting::get('ecomness_last_sync_at'),
            ],
            'recentOrders' => $recentOrders,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string|min:10',
        ]);

        $this->service->setApiKey($request->api_key);

        $test = $this->service->testConnection();
        if ($test['success']) {
            return back()->with('success', 'Clé API enregistrée et test de connexion réussi !');
        }

        return back()->with('error', 'Clé enregistrée mais le test de connexion a échoué : ' . $test['message']);
    }

    public function testConnection()
    {
        $test = $this->service->testConnection();
        if ($test['success']) {
            return back()->with('success', $test['message']);
        }

        return back()->with('error', $test['message']);
    }

    public function updateCategoryMapping(Request $request)
    {
        $request->validate([
            'mapping' => 'required|array',
        ]);

        $this->service->saveCategoryMapping($request->mapping);

        return back()->with('success', 'Mapping des catégories Ecomness mis à jour avec succès !');
    }

    public function syncProducts()
    {
        if (!$this->service->hasApiKey()) {
            return back()->with('error', 'Veuillez d\'abord configurer votre clé API Ecomness.');
        }

        $res = $this->service->syncAllProducts();
        if ($res['success']) {
            return back()->with('success', "{$res['synced']} produits synchronisés avec succès dans la boutique 'Aquawasouk eco' !");
        }
        dd('error', 'Échec de synchronisation : ' . ($res['error'] ?? 'Inconnue'));

        return back()->with('error', 'Échec de synchronisation : ' . ($res['error'] ?? 'Inconnue'));
    }

    public function forwardOrder(Order $order)
    {
        $res = $this->service->forwardOrderToEcomness($order);

        if (!$res) {
            return back()->with('error', 'Cette commande ne contient aucun article provenant d\'Ecomness.');
        }

        if ($res['success']) {
            return back()->with('success', "Commande envoyée à Ecomness avec succès ! Code: {$res['code']}");
        }

        return back()->with('error', 'Échec lors de l\'envoi à Ecomness : ' . ($res['error'] ?? 'Inconnu'));
    }

    public function trackOrders()
    {
        $res = $this->service->syncTrackOrders();
        if ($res['success']) {
            return back()->with('success', "Suivi mis à jour : {$res['updated']} commande(s) actualisée(s).");
        }

        return back()->with('error', 'Échec du suivi : ' . ($res['error'] ?? 'Inconnu'));
    }
}

