<?php

namespace App\Http\Controllers;

use App\Models\TransactionHeader;
use App\Models\TransactionDetail;
use App\Http\Requests\TransactionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ApiResponse;


class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         try {
            $transactions = TransactionHeader::with('details.category')
                ->orderBy('date_paid', 'desc')
                ->get();

            return ApiResponse::success(
                $transactions,
                'Transactions retrieved successfully'
            );
        } catch (\Exception $e) {
            \Log::error('Failed to fetch transactions: '.$e->getMessage());

            return ApiResponse::error(
                'Failed to retrieve transactions',
                ['exception' => [$e->getMessage()]],
                500
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request)
    {
        DB::beginTransaction();

        try {
            $header = TransactionHeader::create(
                $request->only(['desc', 'code', 'rate_euro', 'date_paid'])
            );

            foreach ($request->details as $detail) {
                $header->details()->create($detail);
            }

            DB::commit();

            $header->load('details.category');

            return ApiResponse::success(
                $header,
                'Transaction created successfully',
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to save transaction: ' . $e->getMessage());

            return ApiResponse::error(
                'Failed to save transaction',
                ['exception' => [$e->getMessage()]],
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = TransactionHeader::with('details.category')->find($id);
        
        if (!$transaction) {
            return ApiResponse::error(
                'Transaction not found',
                ['id' => ['Transaction with ID ' . $id . ' not found.']],
                404
            );
        }

        return ApiResponse::success(
            $transaction,
            'Transaction retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        try {
            $header = TransactionHeader::with('details')->findOrFail($id);

            $header->update($request->only(['desc', 'code', 'rate_euro', 'date_paid']));

            if ($request->has('details') && is_array($request->details)) {
                $existingDetailIds = $header->details->pluck('id')->toArray();

                foreach ($request->details as $detail) {
                    if (isset($detail['id'])) {
                        if (!in_array($detail['id'], $existingDetailIds)) {
                            DB::rollBack();
                            return ApiResponse::error(
                                'Detail produk tidak cocok dengan transaksi ini.',
                                null,
                                422
                            );
                        }

                        if (isset($detail['destroy']) && $detail['destroy'] === true) {
                            $header->details()->where('id', $detail['id'])->delete();
                            continue;
                        }
                        
                        $header->details()->where('id', $detail['id'])->update([
                            'transaction_category_id' => $detail['transaction_category_id'],
                            'name' => $detail['name'],
                            'value_idr' => $detail['value_idr'],
                        ]);
                    } else {
                        $header->details()->create([
                            'transaction_category_id' => $detail['transaction_category_id'],
                            'name' => $detail['name'],
                            'value_idr' => $detail['value_idr'],
                        ]);
                    }
                }
            }

            DB::commit();

            $header->load('details.category');

            return ApiResponse::success($header, 'Transaction updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update failed: ' . $e->getMessage());

            return ApiResponse::error('Update failed', ['exception' => [$e->getMessage()]], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $transaction = TransactionHeader::findOrFail($id);
            $transaction->details()->delete();
            $transaction->delete();

            return ApiResponse::success(null, 'Deleted successfully');
        } catch (\Exception $e) {
            \Log::error('Delete failed: ' . $e->getMessage());

            return ApiResponse::error('Delete failed', ['exception' => [$e->getMessage()]], 500);
        }
    }
    
    public function filter(Request $request)
    {
        try {
            $query = TransactionHeader::with('details.category');

            if ($request->has('category_id')) {
                $query->whereHas('details', function ($q) use ($request) {
                    $q->where('transaction_category_id', $request->category_id);
                });
            }

            if ($request->has('date_from') && $request->has('date_to')) {
                $query->whereBetween('date_paid', [$request->date_from, $request->date_to]);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('desc', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%");
                });
            }

            if ($request->has('sort_by') && $request->has('sort_order')) {
                $query->orderBy($request->sort_by, $request->sort_order);
            } else {
                $query->orderBy('date_paid', 'desc');
            }

            $results = $query->get();

            return ApiResponse::success($results, 'Filtered transactions retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('Filter failed: ' . $e->getMessage());

            return ApiResponse::error('Failed to filter transactions', ['exception' => [$e->getMessage()]], 500);
        }
    }

    public function rekap(Request $request)
    {
        try {
        $results = TransactionHeader::select(
                DB::raw('date_paid as date'),
                'details.transaction_category_id',
                DB::raw('categories.name as category'),
                DB::raw('SUM(details.value_idr) as nominal')
            )
            ->join('transaction_details as details', 'transaction_headers.id', '=', 'details.transaction_id')
            ->join('categories', 'details.transaction_category_id', '=', 'categories.id')
            ->when($request->category_id, function($query) use ($request) {
                $query->where('details.transaction_category_id', $request->category_id);
            })
            ->when($request->date_from && $request->date_to, function($query) use ($request) {
                $query->whereBetween('date_paid', [$request->date_from, $request->date_to]);
            })
            ->groupBy('date_paid', 'details.transaction_category_id', 'categories.name')
            ->orderBy('date_paid', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->date,
                    'category' => $item->category,
                    'nominal' => (int) $item->nominal,
                ];
            });

        return ApiResponse::success($results, 'Rekap transaksi');
    } catch (\Exception $e) {
        \Log::error('Rekap by date failed: ' . $e->getMessage());
        return ApiResponse::error('Gagal mengambil rekap transaksi', ['exception' => [$e->getMessage()]], 500);
    }
    }

}

