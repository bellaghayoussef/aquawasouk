<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssistantSetting;
use App\Models\Order;
use App\Models\SmsLog;
use App\Models\Vendor;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class SmsController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index()
    {
        // Auto-migrate sms_logs table if not present
        if (!Schema::hasTable('sms_logs')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                // Ignore if migration already ran
            }
        }

        $logs = Schema::hasTable('sms_logs') 
            ? SmsLog::with('sender:id,name,email')->latest()->paginate(15)
            : collect([]);

        $totalSent = Schema::hasTable('sms_logs') 
            ? SmsLog::where('status', 'sent')->sum('recipients_count') 
            : 0;
            
        $totalFailed = Schema::hasTable('sms_logs') 
            ? SmsLog::where('status', 'failed')->sum('recipients_count') 
            : 0;

        $vendorsCount = Vendor::whereNotNull('phone')->where('phone', '!=', '')->distinct('phone')->count();
        $customersCount = Order::whereNotNull('phone')->where('phone', '!=', '')->distinct('phone')->count();

        $deviceId = $this->smsService->getDeviceId();
        $apiKey = $this->smsService->getApiKey();

        $userCols = ['id', 'name', 'email'];
        if (Schema::hasColumn('users', 'phone')) {
            $userCols[] = 'phone';
        }

        $vendorsList = Vendor::with(['user' => fn($q) => $q->select($userCols)])
            ->select('id', 'user_id', 'shop_name', 'phone', 'status', 'city', 'created_at')
            ->latest()
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'shop_name' => $v->shop_name,
                    'phone' => $v->phone ?: ($v->user && isset($v->user->phone) ? $v->user->phone : null),
                    'status' => $v->status,
                    'city' => $v->city ?? '',
                    'owner_name' => $v->user ? $v->user->name : 'N/A',
                    'owner_email' => $v->user ? $v->user->email : '',
                    'created_at' => $v->created_at ? $v->created_at->format('d/m/Y') : '',
                ];
            });

        return Inertia::render('Admin/Sms/Index', [
            'logs' => $logs,
            'vendorsList' => $vendorsList,
            'stats' => [
                'total_sent' => (int) $totalSent,
                'total_failed' => (int) $totalFailed,
                'vendors_count' => (int) $vendorsCount,
                'customers_count' => (int) $customersCount,
            ],
            'config' => [
                'is_configured' => !empty($deviceId) && !empty($apiKey),
                'device_id' => $deviceId ? substr($deviceId, 0, 8) . '...' . substr($deviceId, -4) : '',
                'api_key' => $apiKey ? substr($apiKey, 0, 7) . '...' . substr($apiKey, -4) : '',
                'full_device_id' => $deviceId ?? '',
                'full_api_key' => $apiKey ?? '',
            ],
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'recipient_type' => 'required|in:custom,selected_vendors,vendors,customers,all',
            'custom_recipients' => 'required_if:recipient_type,custom|nullable|string',
            'selected_vendor_ids' => 'required_if:recipient_type,selected_vendors|nullable|array',
            'selected_vendor_ids.*' => 'integer|exists:vendors,id',
            'activate_vendors' => 'nullable|boolean',
            'message' => 'required|string|max:1000',
        ]);

        $recipients = [];

        switch ($request->input('recipient_type')) {
            case 'selected_vendors':
                $vendorIds = (array) $request->input('selected_vendor_ids', []);
                if (empty($vendorIds)) {
                    return back()->with('error', 'Veuillez sélectionner au moins un vendeur.');
                }

                $selectedVendors = Vendor::with('user')->whereIn('id', $vendorIds)->get();
                $activatedCount = 0;

                foreach ($selectedVendors as $v) {
                    $phone = $v->phone ?: ($v->user && isset($v->user->phone) ? $v->user->phone : null);
                    if (!empty($phone)) {
                        $recipients[] = $phone;
                    }

                    // Automatically activate vendor account if requested
                    if ($request->boolean('activate_vendors') && $v->status !== 'active') {
                        $v->update(['status' => 'active']);
                        $activatedCount++;
                    }
                }
                break;

            case 'vendors':
                $recipients = Vendor::whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->pluck('phone')
                    ->toArray();
                break;

            case 'customers':
                $recipients = Order::whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->pluck('phone')
                    ->unique()
                    ->toArray();
                break;

            case 'all':
                $vendorPhones = Vendor::whereNotNull('phone')->where('phone', '!=', '')->pluck('phone')->toArray();
                $customerPhones = Order::whereNotNull('phone')->where('phone', '!=', '')->pluck('phone')->toArray();
                $recipients = array_unique(array_merge($vendorPhones, $customerPhones));
                break;

            case 'custom':
            default:
                $input = (string) $request->input('custom_recipients');
                $parts = preg_split('/[\r\n,;]+/', $input);
                $recipients = array_filter(array_map('trim', $parts));
                break;
        }

        if (empty($recipients)) {
            return back()->with('error', 'Aucun numéro de téléphone trouvé pour ce groupe de destinataires.');
        }

        $result = $this->smsService->send(
            recipients: $recipients,
            message: $request->input('message'),
            adminId: auth()->id()
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|max:100',
            'api_key' => 'required|string|max:150',
        ]);

        AssistantSetting::set('textbee_device_id', trim($request->input('device_id')));
        AssistantSetting::set('textbee_api_key', trim($request->input('api_key')));

        return back()->with('success', 'Paramètres TextBee mis à jour avec succès !');
    }

    public function testConnection(Request $request)
    {
        $request->validate([
            'test_phone' => 'required|string|max:30',
        ]);

        $result = $this->smsService->send(
            recipients: $request->input('test_phone'),
            message: 'AquawaSouk Test: Connexion TextBee SMS réussie ! 🎉',
            adminId: auth()->id()
        );

        if ($result['success']) {
            return back()->with('success', 'SMS de test envoyé avec succès au ' . $request->input('test_phone'));
        }

        return back()->with('error', 'Échec du test : ' . $result['message']);
    }
}
