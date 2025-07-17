<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Quotation;
use App\Models\QuotationAttachment;
use Illuminate\Support\Facades\Validator;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Quotation::with(['attachments', 'user']);

            if ($search = $request->input('search')) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            }

            $quotations = $query->latest()->paginate($request->get('per_page', 10));

            return response()->json($quotations);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching quotations', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'product_name' => 'required|string',
                'price' => 'required|numeric',
                'description' => 'nullable|string',
                'status' => 'in:pending,approved,sent',
                'attachments.*' => 'nullable|file|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            $quotation = Quotation::create($request->only([
                'user_id', 'product_name', 'price', 'description', 'status'
            ]));

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('quotation_attachments', 'public');
                    QuotationAttachment::create([
                        'quotation_id' => $quotation->id,
                        'file_path' => $path
                    ]);
                }
            }

            DB::commit();
            return response()->json($quotation->load('attachments'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create quotation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Quotation $quotation)
    {
        try {
            return response()->json($quotation->load('attachments'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch quotation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Quotation $quotation)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_name' => 'sometimes|string',
                'price' => 'sometimes|numeric',
                'description' => 'nullable|string',
                'status' => 'in:pending,approved,sent',
                'attachments.*' => 'nullable|file|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            $quotation->update($request->only([
                'product_name', 'price', 'description', 'status'
            ]));

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('quotation_attachments', 'public');
                    QuotationAttachment::create([
                        'quotation_id' => $quotation->id,
                        'file_path' => $path
                    ]);
                }
            }

            DB::commit();
            return response()->json($quotation->load('attachments'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update quotation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Quotation $quotation)
    {
        try {
            foreach ($quotation->attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment->delete();
            }

            $quotation->delete();

            return response()->json(['message' => 'Quotation deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete quotation',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
