<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Part;
class PartController extends Controller
{
    public function index(Request $request)
    {
        $query = Part::with('systems');
        
        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('part_number', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('filter_brand')) {
            $query->where('brand', $request->filter_brand);
        }
        
        if ($request->filled('filter_system')) {
            $query->whereHas('systems', function($q) use ($request) {
                $q->where('systems.id', $request->filter_system);
            });
        }
        
        $parts = $query->orderBy('name', 'asc')->paginate(12)->withQueryString();
        
        // Get filter options
        $brands = Part::distinct()->whereNotNull('brand')->orderBy('brand')->pluck('brand')->toArray();
        $systems = \App\Models\System::orderBy('nama_sistem')->get();
        
        return view('parts.index', compact('parts', 'brands', 'systems'));
    }

    public function create()
    {
        $systems = \App\Models\System::orderBy('nama_sistem')->get();
        return view('parts.create', compact('systems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|max:255|unique:parts,part_number',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'stock' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'systems' => 'nullable|array',
            'systems.*' => 'exists:systems,id',
        ]);
        
        $part = Part::create($validated);
        
        if ($request->filled('systems')) {
            $part->systems()->sync($request->input('systems', []));
        }
        
        return redirect()->route('parts.index')->with('success', 'Part created successfully.');
    }

    public function edit($id)
    {
        $part = Part::with('systems')->findOrFail($id);
        $systems = \App\Models\System::orderBy('nama_sistem')->get();
        return view('parts.edit', compact('part', 'systems'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|max:255|unique:parts,part_number,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'stock' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'systems' => 'nullable|array',
            'systems.*' => 'exists:systems,id',
        ]);
        
        $part = Part::findOrFail($id);
        $part->update($validated);
        
        if ($request->has('systems')) {
            $part->systems()->sync($request->input('systems', []));
        }
        
        return redirect()->route('parts.index')->with('success', 'Part updated successfully.');
    }

    public function destroy($id)
    {
        $part = Part::findOrFail($id);
        $part->delete();
        return redirect()->route('parts.index')->with('success', 'Part deleted successfully.');
    }
}
