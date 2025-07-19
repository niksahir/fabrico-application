<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Exception;

class MachineController extends Controller
{
    public function index()
    {
        try {
            $machines = Machine::where('user_id', Auth::id())->latest()->get();

            return response()->json([
                'status' => true,
                'data' => $machines
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'model_number' => 'required|string',
                'brand' => 'nullable|string',
                'notes' => 'nullable|string',
                'valid_till' => 'nullable|date',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $data = $request->only(['model_number', 'brand', 'notes', 'valid_till']);
            $data['user_id'] = Auth::id();

            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('machine_images', 'public');
            }

            $machine = Machine::create($data);

            return response()->json([
                'status' => true,
                'message' => 'Machine added successfully',
                'data' => $machine
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $machine = Machine::where('user_id', Auth::id())->findOrFail($id);

            return response()->json(['status' => true, 'data' => $machine]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Machine not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $machine = Machine::where('user_id', Auth::id())->findOrFail($id);

            $request->validate([
                'model_number' => 'required|string',
                'brand' => 'nullable|string',
                'notes' => 'nullable|string',
                'valid_till' => 'nullable|date',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $data = $request->only(['model_number', 'brand', 'notes', 'valid_till']);

            if ($request->hasFile('image')) {
                if ($machine->image_path) {
                    Storage::disk('public')->delete($machine->image_path);
                }

                $data['image_path'] = $request->file('image')->store('machine_images', 'public');
            }

            $machine->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Machine updated successfully',
                'data' => $machine
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $machine = Machine::where('user_id', Auth::id())->findOrFail($id);

            if ($machine->image_path) {
                Storage::disk('public')->delete($machine->image_path);
            }

            $machine->delete();

            return response()->json(['status' => true, 'message' => 'Machine deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Machine not found or already deleted'], 404);
        }
    }
}
