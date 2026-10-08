<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * GET /products — list of user's services/products.
     */
    public function index(Request $request): View
    {
        $user  = auth()->user();
        $query = Product::where('user_id', $user->id);

        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%');
            });
        }

        $products = $query->orderBy('name')->paginate(12)->withQueryString();

        return view('products.index', compact('products', 'q'));
    }

    /**
     * GET /products/create — empty service form.
     */
    public function create(): View
    {
        return view('products.create', ['product' => new Product()]);
    }

    /**
     * POST /products — validate and save a new service.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user      = auth()->user();

        $priceCents = (int) round(((float) $validated['price']) * 100);

        Product::create([
            'user_id'     => $user->id,
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price'       => $priceCents,
        ]);

        session()->flash('success', "Service &ldquo;{$validated['name']}&rdquo; added.");

        return redirect()->route('products.index');
    }

    /**
     * GET /products/{product}/edit — edit form.
     */
    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('products.edit', compact('product'));
    }

    /**
     * PUT /products/{product} — update existing service.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $validated = $request->validated();

        $priceCents = (int) round(((float) $validated['price']) * 100);

        $product->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price'       => $priceCents,
        ]);

        session()->flash('success', "Service &ldquo;{$product->name}&rdquo; updated.");

        return redirect()->route('products.index');
    }

    /**
     * DELETE /products/{product} — remove a saved service.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $name = $product->name;
        $product->delete();

        session()->flash('success', "Service &ldquo;{$name}&rdquo; deleted.");

        return redirect()->route('products.index');
    }
}
