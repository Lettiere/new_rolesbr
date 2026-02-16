<?php

namespace App\Http\Controllers;

use App\Models\ProductBase;
use App\Models\ProductFamily;
use App\Models\ProductType;
use Illuminate\Http\Request;

class ProductCatalogController extends Controller
{
    public function families(Request $request)
    {
        $q = $request->get('q');
        $rows = ProductFamily::when($q, fn($qq)=>$qq->where('nome','like',"%$q%"))
            ->orderBy('nome')->paginate(20);
        return view('dashboard.barista.products.catalog.families', compact('rows','q'));
    }

    public function types(Request $request)
    {
        $q = $request->get('q');
        $rows = ProductType::when($q, fn($qq)=>$qq->where('nome','like',"%$q%"))
            ->orderBy('nome')->paginate(20);
        return view('dashboard.barista.products.catalog.types', compact('rows','q'));
    }

    public function bases(Request $request)
    {
        $q = $request->get('q');
        $rows = ProductBase::with('type')
            ->when($q, fn($qq)=>$qq->where('nome','like',"%$q%"))
            ->orderBy('nome')->paginate(20);
        return view('dashboard.barista.products.catalog.bases', compact('rows','q'));
    }
}
