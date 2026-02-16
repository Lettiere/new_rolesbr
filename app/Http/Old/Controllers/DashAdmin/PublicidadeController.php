<?php

namespace App\Controllers\DashAdmin;

use App\Controllers\BaseController;
use App\Models\Publicidade\AdsBannerModel;

class PublicidadeController extends BaseController
{
    public function index()
    {
        $model = new AdsBannerModel();
        $banners = $model->orderBy('updated_at', 'DESC')->findAll();
        return view('dash_admin/publicidade/index', ['banners' => $banners]);
    }

    public function create()
    {
        return view('dash_admin/publicidade/create');
    }

    public function store()
    {
        $model = new AdsBannerModel();
        $data = [
            'title' => trim($this->request->getPost('title') ?? ''),
            'link_url' => trim($this->request->getPost('link_url') ?? ''),
            'placement' => trim($this->request->getPost('placement') ?? ''),
            'active' => (int)($this->request->getPost('active') ?? 0),
            'start_at' => $this->request->getPost('start_at') ?: null,
            'end_at' => $this->request->getPost('end_at') ?: null,
        ];

        // Uploads
        $uploadsRoot = ROOTPATH . 'public/uploads';
        if (!is_dir($uploadsRoot)) mkdir($uploadsRoot, 0777, true);
        $pubRoot = $uploadsRoot . '/publicidade';
        if (!is_dir($pubRoot)) mkdir($pubRoot, 0777, true);

        $desk = $this->request->getFile('image_desktop');
        if ($desk && $desk->isValid() && !$desk->hasMoved()) {
            $newName = $desk->getRandomName();
            if ($desk->move($pubRoot, $newName)) {
                $data['image_desktop_url'] = 'uploads/publicidade/' . $newName;
            }
        }
        $mob = $this->request->getFile('image_mobile');
        if ($mob && $mob->isValid() && !$mob->hasMoved()) {
            $newName = $mob->getRandomName();
            if ($mob->move($pubRoot, $newName)) {
                $data['image_mobile_url'] = 'uploads/publicidade/' . $newName;
            }
        }

        if (!$model->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Erro ao cadastrar banner.');
        }
        return redirect()->to('/dashboard/admin/publicidade')->with('success', 'Banner cadastrado.');
    }

    public function edit($id)
    {
        $model = new AdsBannerModel();
        $banner = $model->find($id);
        if (!$banner) {
            return redirect()->to('/dashboard/admin/publicidade')->with('error', 'Banner não encontrado.');
        }
        return view('dash_admin/publicidade/edit', ['banner' => $banner]);
    }

    public function update($id)
    {
        $model = new AdsBannerModel();
        $banner = $model->find($id);
        if (!$banner) {
            return redirect()->to('/dashboard/admin/publicidade')->with('error', 'Banner não encontrado.');
        }

        $data = [
            'title' => trim($this->request->getPost('title') ?? ''),
            'link_url' => trim($this->request->getPost('link_url') ?? ''),
            'placement' => trim($this->request->getPost('placement') ?? ''),
            'active' => (int)($this->request->getPost('active') ?? 0),
            'start_at' => $this->request->getPost('start_at') ?: null,
            'end_at' => $this->request->getPost('end_at') ?: null,
        ];

        // Uploads (substituir se enviados)
        $uploadsRoot = ROOTPATH . 'public/uploads';
        if (!is_dir($uploadsRoot)) mkdir($uploadsRoot, 0777, true);
        $pubRoot = $uploadsRoot . '/publicidade';
        if (!is_dir($pubRoot)) mkdir($pubRoot, 0777, true);

        $desk = $this->request->getFile('image_desktop');
        if ($desk && $desk->isValid() && !$desk->hasMoved()) {
            $newName = $desk->getRandomName();
            if ($desk->move($pubRoot, $newName)) {
                $data['image_desktop_url'] = 'uploads/publicidade/' . $newName;
            }
        }
        $mob = $this->request->getFile('image_mobile');
        if ($mob && $mob->isValid() && !$mob->hasMoved()) {
            $newName = $mob->getRandomName();
            if ($mob->move($pubRoot, $newName)) {
                $data['image_mobile_url'] = 'uploads/publicidade/' . $newName;
            }
        }

        if (!$model->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar banner.');
        }
        return redirect()->to('/dashboard/admin/publicidade')->with('success', 'Banner atualizado.');
    }

    public function show($id)
    {
        $model = new AdsBannerModel();
        $banner = $model->find($id);
        if (!$banner) {
            return redirect()->to('/dashboard/admin/publicidade')->with('error', 'Banner não encontrado.');
        }
        return view('dash_admin/publicidade/show', ['banner' => $banner]);
    }
}
