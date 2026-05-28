<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryHistory;
use App\Models\DELIVERY_STATUS;
use App\Models\Pbf;
use Illuminate\Http\Request;

class DeliveryDashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with('pbf');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_invoice', 'like', "%{$search}%")
                  ->orWhere('kode_pbf', 'like', "%{$search}%")
                  ->orWhere('no_kendaraan', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $deliveries = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('delivery.index', compact('deliveries'));
    }

    public function create()
    {
        $pbfs = Pbf::orderBy('nama_pbf')->get();
        $nextInvoice = $this->generateInvoiceNumber();

        return view('delivery.create', compact('pbfs', 'nextInvoice'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_invoice'           => 'required|string|max:255|unique:tbpengiriman,no_invoice',
            'kode_pbf'             => 'required|string|exists:tbpbf,kode_pbf',
            'jumlah_barang_besar'  => 'required|integer|min:0',
            'jumlah_barang_sedang' => 'required|integer|min:0',
            'jumlah_barang_kecil'  => 'required|integer|min:0',
            'no_kendaraan'         => 'nullable|string|max:255',
        ]);

        Delivery::create(array_merge(
            $request->only(['no_invoice', 'kode_pbf', 'jumlah_barang_besar', 'jumlah_barang_sedang', 'jumlah_barang_kecil', 'no_kendaraan']),
            ['status' => DELIVERY_STATUS::PROCESS->value]
        ));

        return redirect()->route('delivery.index')->with('success', 'Delivery berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $delivery = Delivery::with('pbf')->findOrFail($id);
        $history = DeliveryHistory::where('no_invoice', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('delivery.show', compact('delivery', 'history'));
    }

    public function edit(string $id)
    {
        $delivery = Delivery::findOrFail($id);
        $pbfs = Pbf::orderBy('nama_pbf')->get();
        $statuses = DELIVERY_STATUS::cases();

        return view('delivery.edit', compact('delivery', 'pbfs', 'statuses'));
    }

    public function update(Request $request, string $id)
    {
        $delivery = Delivery::findOrFail($id);

        $request->validate([
            'kode_pbf'             => 'required|string|exists:tbpbf,kode_pbf',
            'jumlah_barang_besar'  => 'required|integer|min:0',
            'jumlah_barang_sedang' => 'required|integer|min:0',
            'jumlah_barang_kecil'  => 'required|integer|min:0',
            'status'               => 'required|string|in:' . implode(',', array_map(fn($s) => $s->value, DELIVERY_STATUS::cases())),
            'no_kendaraan'         => 'nullable|string|max:255',
        ]);

        $delivery->update($request->only([
            'kode_pbf', 'jumlah_barang_besar', 'jumlah_barang_sedang',
            'jumlah_barang_kecil', 'status', 'no_kendaraan',
        ]));

        return redirect()->route('delivery.index')->with('success', 'Delivery berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $delivery = Delivery::findOrFail($id);
        $delivery->delete();

        return redirect()->route('delivery.index')->with('success', 'Delivery berhasil dihapus.');
    }

    public function statusCheck(string $id)
    {
        $delivery = Delivery::select('status')->findOrFail($id);
        return response()->json(['status' => $delivery->status]);
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Y-m-d') . '-';

        $last = Delivery::where('no_invoice', 'like', $prefix . '%')
            ->orderBy('no_invoice', 'desc')
            ->value('no_invoice');

        $next = 1;
        if ($last) {
            $parts = explode('-', $last);
            $next = (int) end($parts) + 1;
        }

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
