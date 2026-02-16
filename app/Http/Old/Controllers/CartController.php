<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Cart\CartModel;
use App\Models\Cart\CartItemModel;
use App\Models\Perfil\PerfilBarClientModel;

class CartController extends BaseController
{
    protected function getSessionId(): string
    {
        $sid = session()->get('cart_session_id');
        if (!$sid) {
            $sid = bin2hex(random_bytes(16));
            session()->set('cart_session_id', $sid);
        }
        return $sid;
    }

    protected function getOrCreateCart(int $baresId): array
    {
        $cartModel = new CartModel();
        $userId = (int) (session()->get('user_id') ?? 0) ?: null;
        $sid = $this->getSessionId();
        $currentId = (int) (session()->get('current_cart_id') ?? 0);
        $current = $currentId ? $cartModel->find($currentId) : null;
        if ($current && (int) $current['bares_id'] !== $baresId) {
            $itemModel = new CartItemModel();
            $itemModel->where('cart_id', (int) $current['cart_id'])->delete();
            $cartModel->update((int) $current['cart_id'], ['bares_id' => $baresId, 'status' => 'open']);
            $current = $cartModel->find((int) $current['cart_id']);
            return $current;
        }
        if ($current) {
            return $current;
        }
        $cartId = $cartModel->insert([
            'session_id' => $sid,
            'user_id'    => $userId,
            'bares_id'   => $baresId,
            'status'     => 'open',
        ], true);
        session()->set('current_cart_id', (int) $cartId);
        return $cartModel->find((int) $cartId);
    }

    public function index()
    {
        $cartId = (int) (session()->get('current_cart_id') ?? 0);
        if (!$cartId) {
            return view('cart/index', ['cart' => null, 'items' => [], 'bar' => null]);
        }
        $cartModel = new CartModel();
        $itemModel = new CartItemModel();
        $barModel = new PerfilBarClientModel();
        $cart = $cartModel->find($cartId);
        $items = $itemModel->where('cart_id', $cartId)->findAll();
        $bar = $cart ? $barModel->find((int) $cart['bares_id']) : null;
        return view('cart/index', ['cart' => $cart, 'items' => $items, 'bar' => $bar]);
    }

    public function json()
    {
        $cartId = (int) (session()->get('current_cart_id') ?? 0);
        if (!$cartId) {
            return $this->response->setJSON(['cart' => null, 'items' => []]);
        }
        $cartModel = new CartModel();
        $itemModel = new CartItemModel();
        $cart = $cartModel->find($cartId);
        $items = $itemModel->where('cart_id', $cartId)->findAll();
        return $this->response->setJSON(['cart' => $cart, 'items' => $items]);
    }

    public function add()
    {
        $baresId = (int) ($this->request->getPost('bares_id') ?? 0);
        $name = trim((string) ($this->request->getPost('name') ?? ''));
        $price = (float) ($this->request->getPost('price') ?? 0);
        $qty = (int) ($this->request->getPost('qty') ?? 1);
        $produtoId = (int) ($this->request->getPost('produto_id') ?? 0) ?: null;
        if ($baresId <= 0 || $name === '' || $qty <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Dados inválidos']);
        }
        $cart = $this->getOrCreateCart($baresId);
        $itemModel = new CartItemModel();
        $existing = $itemModel->where('cart_id', (int) $cart['cart_id'])->where('name', $name)->where('price', $price)->first();
        if ($existing) {
            $itemModel->update((int) $existing['item_id'], ['qty' => (int) $existing['qty'] + $qty]);
        } else {
            $itemModel->insert([
                'cart_id'    => (int) $cart['cart_id'],
                'produto_id' => $produtoId,
                'name'       => $name,
                'price'      => $price,
                'qty'        => $qty,
            ]);
        }
        $items = $itemModel->where('cart_id', (int) $cart['cart_id'])->findAll();
        $total = 0;
        $count = 0;
        foreach ($items as $it) {
            $total += ((float) $it['price']) * ((int) $it['qty']);
            $count += (int) $it['qty'];
        }
        return $this->response->setJSON(['success' => true, 'count' => $count, 'total' => $total]);
    }

    public function remove()
    {
        $itemId = (int) ($this->request->getPost('item_id') ?? 0);
        if ($itemId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Item inválido']);
        }
        $itemModel = new CartItemModel();
        $itemModel->delete($itemId);
        return $this->response->setJSON(['success' => true]);
    }

    public function clear()
    {
        $cartId = (int) (session()->get('current_cart_id') ?? 0);
        if ($cartId <= 0) {
            return $this->response->setJSON(['success' => true]);
        }
        $itemModel = new CartItemModel();
        $itemModel->where('cart_id', $cartId)->delete();
        return $this->response->setJSON(['success' => true]);
    }

    public function update()
    {
        $itemId = (int) ($this->request->getPost('item_id') ?? 0);
        $qty = (int) ($this->request->getPost('qty') ?? 0);
        if ($itemId <= 0 || $qty < 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Dados inválidos']);
        }
        $itemModel = new CartItemModel();
        if ($qty === 0) {
            $itemModel->delete($itemId);
        } else {
            $itemModel->update($itemId, ['qty' => $qty]);
        }
        $cartId = (int) (session()->get('current_cart_id') ?? 0);
        $items = $cartId ? $itemModel->where('cart_id', $cartId)->findAll() : [];
        $total = 0;
        $count = 0;
        foreach ($items as $it) {
            $total += ((float) $it['price']) * ((int) $it['qty']);
            $count += (int) $it['qty'];
        }
        return $this->response->setJSON(['success' => true, 'count' => $count, 'total' => $total, 'items' => $items]);
    }
}
