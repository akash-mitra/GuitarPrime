<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\Purchase;
use App\Services\Payment\RazorpayPaymentService;
use App\Services\Payment\StripePaymentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Purchase::class);

        $purchases = Auth::user()->purchases()
            ->with(['purchasable'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Purchase::class);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['course', 'module'])],
            'id' => ['required', 'string'],
            'payment_provider' => ['required', Rule::in(['stripe', 'razorpay'])],
        ]);

        $purchasableClass = $validated['type'] === 'course' ? Course::class : Module::class;
        $purchasable = $purchasableClass::findOrFail($validated['id']);

        if ($purchasable->isFree()) {
            return back()->withErrors(['message' => 'This content is free and does not require purchase.']);
        }

        $existingPurchase = Auth::user()->purchases()
            ->where('purchasable_type', $purchasableClass)
            ->where('purchasable_id', $purchasable->id)
            ->where('status', 'completed')
            ->first();

        if ($existingPurchase) {
            return back()->withErrors(['message' => 'You have already purchased this content.']);
        }

        $purchase = Purchase::create([
            'user_id' => Auth::id(),
            'purchasable_type' => $purchasableClass,
            'purchasable_id' => $purchasable->id,
            'amount' => $purchasable->price,
            'currency' => 'USD',
            'payment_provider' => $validated['payment_provider'],
            'status' => 'pending',
        ]);

        if ($validated['payment_provider'] === 'stripe') {
            $service = new StripePaymentService;
            $result = $service->createPayment($purchase);

            if ($result['success']) {
                return Inertia::location($result['checkout_url']);
            }
        } else {
            $service = new RazorpayPaymentService;
            $result = $service->createPayment($purchase);

            if ($result['success']) {
                return Inertia::render('Purchases/Razorpay', [
                    'purchase' => $purchase,
                    'razorpayOptions' => $result,
                ]);
            }
        }

        $purchase->update(['status' => 'failed']);

        return back()->withErrors(['message' => $result['error'] ?? 'Payment initiation failed.']);
    }

    public function show(Purchase $purchase)
    {
        $this->authorize('view', $purchase);

        $purchase->load(['purchasable', 'user']);

        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase,
        ]);
    }

    public function success(Request $request, Purchase $purchase)
    {
        $this->authorize('view', $purchase);

        if ($purchase->payment_provider === 'stripe') {
            $sessionId = $request->get('session_id');
            if ($sessionId && $purchase->checkout_session_id === $sessionId) {
                $service = new StripePaymentService;
                $result = $service->retrievePayment($sessionId);

                if ($result['success'] && $result['session']->payment_status === 'paid') {
                    $purchase->update(['status' => 'completed']);
                }
            }
        }

        return Inertia::render('Purchases/Success', [
            'purchase' => $purchase->load('purchasable'),
        ]);
    }

    public function cancel(Purchase $purchase)
    {
        $this->authorize('view', $purchase);

        if ($purchase->isPending()) {
            $purchase->update(['status' => 'cancelled']);
        }

        return Inertia::render('Purchases/Cancel', [
            'purchase' => $purchase->load('purchasable'),
        ]);
    }

    public function verifyRazorpay(Request $request, Purchase $purchase)
    {
        $this->authorize('view', $purchase);

        $validated = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $service = new RazorpayPaymentService;

        if ($service->verifyPayment(
            $validated['razorpay_payment_id'],
            $validated['razorpay_order_id'],
            $validated['razorpay_signature']
        )) {
            $purchase->update([
                'status' => 'completed',
                'payment_id' => $validated['razorpay_payment_id'],
                'metadata' => array_merge($purchase->metadata ?? [], [
                    'razorpay_payment_id' => $validated['razorpay_payment_id'],
                    'razorpay_signature' => $validated['razorpay_signature'],
                ]),
            ]);

            return redirect()->route('purchase.success', $purchase);
        }

        $purchase->update(['status' => 'failed']);

        return back()->withErrors(['message' => 'Payment verification failed.']);
    }
}
