<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBase;
use App\Models\ProductFamily;
use App\Models\ProductType;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $establishments = Establishment::where('user_id', Auth::id())->orderBy('nome')->get(['bares_id','nome']);
        $families = ProductFamily::where('ativo', 1)->orderBy('nome')->get(['familia_id','nome']);
        $types = ProductType::where('ativo', 1)->orderBy('nome')->get(['tipo_id','nome']);
        $selectedBares = request('bares_id');
        $selectedFamily = request('familia_id');
        $selectedType = request('tipo_id');
        $selectedBase = request('base_id');
        $query = Product::query()->whereIn('bares_id', $establishments->pluck('bares_id'));
        if ($selectedBares) $query->where('bares_id', $selectedBares);
        if ($selectedFamily) $query->where('familia_id', $selectedFamily);
        if ($selectedType) $query->where('tipo_id', $selectedType);
        if ($selectedBase) $query->where('base_id', $selectedBase);
        $products = $query->with(['establishment','base','family','type'])->orderBy('nome')->paginate(20);
        $bases = $selectedType ? ProductBase::where('tipo_id', $selectedType)->orderBy('nome')->get(['base_id','nome']) : collect();
        return view('dashboard.barista.products.index', compact('products', 'establishments', 'families', 'types', 'bases', 'selectedBares','selectedFamily','selectedType','selectedBase'));
    }

    public function create()
    {
        $establishments = Establishment::where('user_id', Auth::id())->orderBy('nome')->get(['bares_id','nome']);
        $families = ProductFamily::where('ativo', 1)->orderBy('nome')->get(['familia_id','nome']);
        $types = ProductType::where('ativo', 1)->orderBy('nome')->get(['tipo_id','nome']);
        return view('dashboard.barista.products.create', compact('establishments','families','types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bares_id' => 'required|exists:form_perfil_bares_tb,bares_id',
            'base_id' => 'nullable|exists:prod_base_produto_tb,base_id',
            'familia_id' => 'nullable|exists:prod_familia_produtos_tb,familia_id',
            'tipo_id' => 'nullable|exists:prod_tipo_produtos_tb,tipo_id',
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'tipo_produto' => 'required|in:bebida,alimento,lanche,salgado,sobremesa,outro',
            'subtipo_bebida' => 'nullable|in:alcoolica,nao_alcoolica,cafe,cha,outro',
            'preco' => 'nullable|numeric|min:0',
            'quantidade_estoque' => 'nullable|integer|min:0',
            'unidade' => 'nullable|string|max:50',
            'tags' => 'nullable|string',
            'status' => 'required|in:ativo,inativo',
            'fotos' => 'required|array|min:1|max:6',
            'fotos.*' => 'file|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        $data['status'] = $data['status'] ?? 'ativo';
        $prod = Product::create($data);
        if ($request->hasFile('fotos')) {
            $this->saveProductImages($request->file('fotos'), $prod->prod_id, $prod->nome);
        }
        return redirect()->route('dashboard.barista.products.index')->with('ok', 'Produto criado');
    }

    public function edit($id)
    {
        $product = Product::whereHas('establishment', function ($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);
        $establishments = Establishment::where('user_id', Auth::id())->orderBy('nome')->get(['bares_id','nome']);
        $families = ProductFamily::where('ativo', 1)->orderBy('nome')->get(['familia_id','nome']);
        $types = ProductType::where('ativo', 1)->orderBy('nome')->get(['tipo_id','nome']);
        $bases = $product->tipo_id ? ProductBase::where('tipo_id', $product->tipo_id)->orderBy('nome')->get(['base_id','nome']) : collect();
        $images = $this->listProductImages($product->prod_id);
        return view('dashboard.barista.products.edit', compact('product','establishments','families','types','bases','images'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::whereHas('establishment', function ($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);
        $data = $request->validate([
            'bares_id' => 'required|exists:form_perfil_bares_tb,bares_id',
            'base_id' => 'nullable|exists:prod_base_produto_tb,base_id',
            'familia_id' => 'nullable|exists:prod_familia_produtos_tb,familia_id',
            'tipo_id' => 'nullable|exists:prod_tipo_produtos_tb,tipo_id',
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'tipo_produto' => 'required|in:bebida,alimento,lanche,salgado,sobremesa,outro',
            'subtipo_bebida' => 'nullable|in:alcoolica,nao_alcoolica,cafe,cha,outro',
            'preco' => 'nullable|numeric|min:0',
            'quantidade_estoque' => 'nullable|integer|min:0',
            'unidade' => 'nullable|string|max:50',
            'tags' => 'nullable|string',
            'status' => 'required|in:ativo,inativo',
            'limpar_imagens' => 'nullable|boolean',
            'fotos' => 'nullable|array|max:6',
            'fotos.*' => 'file|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        $product->update($data);
        if ($request->hasFile('fotos')) {
            $clear = $request->boolean('limpar_imagens', false);
            $this->saveProductImages($request->file('fotos'), $product->prod_id, $product->nome, $clear);
        }
        return redirect()->route('dashboard.barista.products.index')->with('ok', 'Produto atualizado');
    }

    public function destroy($id)
    {
        $product = Product::whereHas('establishment', function ($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);
        $product->update(['status' => 'inativo']);
        $product->delete();
        return redirect()->route('dashboard.barista.products.index')->with('ok', 'Produto inativado');
    }

    public function basesPorTipo($tipoId)
    {
        return response()->json(ProductBase::where('tipo_id', (int)$tipoId)->orderBy('nome')->get(['base_id','nome']));
    }

    public function show($id)
    {
        $product = Product::whereHas('establishment', function ($q) {
            $q->where('user_id', Auth::id());
        })->with(['establishment','base','family','type'])->findOrFail($id);
        $images = $this->listProductImages($product->prod_id);
        return view('dashboard.barista.products.show', compact('product','images'));
    }

    protected function saveProductImages(array $files, int $prodId, string $name, bool $clear = false): void
    {
        $slug = Str::slug($name);
        $dir = public_path("uploads/produtos/{$prodId}_{$slug}");
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if ($clear) {
            $old = glob($dir . DIRECTORY_SEPARATOR . 'img_*.*');
            foreach ($old as $f) { @unlink($f); }
        }
        $existing = glob($dir . DIRECTORY_SEPARATOR . 'img_*.*');
        $start = count($existing) + 1;
        foreach ($files as $idx => $file) {
            $n = $start + $idx;
            if ($n > 6) break;
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) $ext = 'jpg';
            $tempPath = $file->getRealPath();
            $target = $dir . DIRECTORY_SEPARATOR . 'img_' . $n . '.' . $ext;
            $this->resizeTo($tempPath, $target, 800, 600);
        }
    }

    protected function listProductImages(int $prodId): array
    {
        $dirGlob = glob(public_path("uploads/produtos/{$prodId}_*"), GLOB_ONLYDIR);
        if (!$dirGlob) return [];
        $dir = $dirGlob[0];
        $files = glob($dir . DIRECTORY_SEPARATOR . 'img_*.*');
        $urls = [];
        foreach ($files as $f) {
            $rel = str_replace(public_path(), '', $f);
            $rel = str_replace('\\', '/', $rel);
            $urls[] = $rel[0] === '/' ? $rel : '/' . $rel;
        }
        return $urls;
    }

    protected function resizeTo(string $srcPath, string $dstPath, int $targetW, int $targetH): void
    {
        $info = @getimagesize($srcPath);
        if (!$info) { @copy($srcPath, $dstPath); return; }
        $w = (int)$info[0]; $h = (int)$info[1];
        $mime = strtolower($info['mime'] ?? 'image/jpeg');
        $src = null;
        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) $src = @imagecreatefromjpeg($srcPath);
        elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) $src = @imagecreatefrompng($srcPath);
        elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($srcPath);
        else { $data = @file_get_contents($srcPath); if ($data) $src = @imagecreatefromstring($data); }
        if (!$src) { @copy($srcPath, $dstPath); return; }
        $dst = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($dst, false); imagesavealpha($dst, true);
        $white = imagecolorallocate($dst, 255, 255, 255); imagefilledrectangle($dst, 0, 0, $targetW, $targetH, $white);
        $ratioSrc = $w / $h; $ratioDst = $targetW / $targetH;
        if ($ratioSrc > $ratioDst) {
            $newH = $targetH; $newW = (int) round($targetH * $ratioSrc);
        } else {
            $newW = $targetW; $newH = (int) round($targetW / $ratioSrc);
        }
        $tmp = imagecreatetruecolor($newW, $newH);
        imagealphablending($tmp, false); imagesavealpha($tmp, true);
        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
        $x = (int) floor(($newW - $targetW) / 2);
        $y = (int) floor(($newH - $targetH) / 2);
        imagecopy($dst, $tmp, 0, 0, $x, $y, $targetW, $targetH);
        $ext = strtolower(pathinfo($dstPath, PATHINFO_EXTENSION));
        if ($ext === 'png') @imagepng($dst, $dstPath, 6);
        elseif ($ext === 'webp' && function_exists('imagewebp')) @imagewebp($dst, $dstPath, 85);
        else @imagejpeg($dst, $dstPath, 85);
        imagedestroy($src); imagedestroy($tmp); imagedestroy($dst);
    }
}
