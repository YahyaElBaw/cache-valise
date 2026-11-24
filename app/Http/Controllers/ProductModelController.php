<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductModelRequest;
use App\Http\Requests\UpdateProductModelRequest;
use App\Models\ProductModel;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductModelController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $models = ProductModel::query()
            ->withCount('batches')
            ->when($search !== '', fn ($q) => $q->where(function ($sub) use ($search) {
                $sub->where('reference', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            }))
            ->orderBy('reference')
            ->paginate(15)
            ->withQueryString();

        return view('product-models.index', compact('models', 'search'));
    }

    public function create(): View
    {
        return view('product-models.create');
    }

    public function store(StoreProductModelRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $model = ProductModel::create($data);
        ActivityLogger::log('create', ProductModel::class, $model->id, "Création du modèle {$model->reference} ({$model->designation}).");

        return redirect()->route('product-models.index')->with('success', 'Modèle créé avec succès.');
    }

    public function edit(ProductModel $productModel): View
    {
        return view('product-models.edit', compact('productModel'));
    }

    public function update(UpdateProductModelRequest $request, ProductModel $productModel): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $productModel->update($data);
        ActivityLogger::log('update', ProductModel::class, $productModel->id, "Mise à jour du modèle {$productModel->reference}.");

        return redirect()->route('product-models.index')->with('success', 'Modèle mis à jour.');
    }

    public function toggle(ProductModel $productModel): RedirectResponse
    {
        $productModel->update(['is_active' => ! $productModel->is_active]);
        $state = $productModel->is_active ? 'activé' : 'désactivé';
        ActivityLogger::log('toggle', ProductModel::class, $productModel->id, "Modèle {$productModel->reference} {$state}.");

        return redirect()->route('product-models.index')->with('success', "Modèle {$state}.");
    }

    public function destroy(ProductModel $productModel): RedirectResponse
    {
        if ($productModel->batches()->exists()) {
            return back()->with('error', 'Impossible de supprimer un modèle lié à des lots.');
        }

        $ref = $productModel->reference;
        $productModel->delete();
        ActivityLogger::log('delete', ProductModel::class, null, "Suppression du modèle {$ref}.");

        return redirect()->route('product-models.index')->with('success', 'Modèle supprimé.');
    }
}
