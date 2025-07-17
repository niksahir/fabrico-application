<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $documents = Document::latest()->paginate($perPage);

            return response()->json($documents);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch documents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string',
                'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png',
                'sent_at' => 'nullable|date'
            ]);

            // Handle file upload
            $filePath = $request->file('file')->store('documents', 'public');

            $document = Document::create([
                'user_id' => $validated['user_id'],
                'title' => $validated['title'],
                'file_path' => $filePath,
                'sent_at' => $validated['sent_at'] ?? null,
            ]);

            return response()->json([
                'message' => 'Document created successfully',
                'document' => $document
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to create document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Document $document)
    {
        try {
            return response()->json($document);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Document $document)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string',
                'file' => 'sometimes|file|mimes:pdf,doc,docx,jpg,jpeg,png',
                'sent_at' => 'nullable|date'
            ]);

            // Replace file if new file is uploaded
            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }

                $filePath = $request->file('file')->store('documents', 'public');
                $document->file_path = $filePath;
            }

            $document->update([
                'title' => $validated['title'] ?? $document->title,
                'sent_at' => $validated['sent_at'] ?? $document->sent_at,
                'file_path' => $document->file_path
            ]);

            return response()->json([
                'message' => 'Document updated successfully',
                'document' => $document
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Document $document)
    {
        try {
            // Delete file from storage
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->delete();

            return response()->json(['message' => 'Document deleted successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete document',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
