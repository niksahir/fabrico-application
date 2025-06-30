<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;

class DocumentController extends Controller
{
    public function index()
    {
        return Document::latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'file_path' => 'required|string',
            'sent_at' => 'nullable|date'
        ]);

        return Document::create($validated);
    }

    public function show(Document $document)
    {
        return $document;
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'file_path' => 'sometimes|string',
            'sent_at' => 'nullable|date'
        ]);

        $document->update($validated);
        return $document;
    }

    public function destroy(Document $document)
    {
        $document->delete();
        return response()->json(['message' => 'Document deleted successfully']);
    }
}
