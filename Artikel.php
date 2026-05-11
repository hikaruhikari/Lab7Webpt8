<?php
namespace App\Controllers;
use App\Models\ArtikelModel;
use App\Models\KategoriModel;
class Artikel extends BaseController
{
    public function index()
    {
        $title = 'Daftar Artikel';
        $model = new ArtikelModel();
        $artikel = $model->getArtikelDenganKategori(); // Use the new method
        return view('artikel/index', compact('artikel', 'title'));
    }

    public function admin_index()
    {
        $title = 'Daftar Artikel (Admin)';
        $model = new ArtikelModel();
        // Get search keyword
        $q = $this->request->getVar('q') ?? '';
        // Get category filter
        $kategori_id = $this->request->getVar('kategori_id') ?? '';
        $data = [
            'title' => $title,
            'q' => $q,
            'kategori_id' => $kategori_id,
        ];
        // Building the query
        $builder = $model->table('artikel')
        ->select('artikel.*, kategori.nama_kategori')
        ->join('kategori', 'kategori.id_kategori = artikel.id_kategori');
        // Apply search filter if keyword is provided
        if ($q != '') {
            $builder->like('artikel.judul', $q);
        }
        // Apply category filter if category_id is provided
        if ($kategori_id != '') {
            $builder->where('artikel.id_kategori', $kategori_id);
        }
        // Apply pagination
        $data['artikel'] = $builder->paginate(10);
        $data['pager'] = $model->pager;
        // Fetch all categories for the filter dropdown
        $kategoriModel = new KategoriModel();
        $data['kategori'] = $kategoriModel->findAll();
        return view('artikel/admin_index', $data);
    }
    // ... (methods add, edit, delete remain largely the same, but update to handle id_kategori)
    public function add()  
    { 
        $db = \Config\Database::connect();

        // 1. Ambil data kategori untuk pilihan select di form
        $kategori = $db->table('kategori')->get()->getResultArray();

        // Validasi data
        $validation = \Config\Services::validation(); 
        $validation->setRules(['judul' => 'required']); 
        $isDataValid = $validation->withRequest($this->request)->run(); 

        if ($isDataValid) 
        { 
            $artikel = new ArtikelModel(); 
            $artikel->insert([ 
                'judul' => $this->request->getPost('judul'), 
                'isi' => $this->request->getPost('isi'), 
                'id_kategori' => $this->request->getPost('id_kategori'), // Pastikan ini disimpan
                'slug' => url_title($this->request->getPost('judul'), '-', true), 
            ]); 
            return redirect('admin/artikel'); 
        } 

        $title = "Tambah Artikel"; 
        
        // 2. Kirim variabel $kategori ke view agar tidak "Undefined"
        return view('artikel/form_add', compact('title', 'kategori')); 
    }
    public function edit($id)  
    { 
        $artikelModel = new ArtikelModel(); 
        $db = \Config\Database::connect();

        // 1. Ambil data kategori untuk pilihan select
        $kategori = $db->table('kategori')->get()->getResultArray();

        // Validasi data
        $validation = \Config\Services::validation(); 
        $validation->setRules(['judul' => 'required']); 
        $isDataValid = $validation->withRequest($this->request)->run(); 

        if ($isDataValid) 
        { 
            $artikelModel->update($id, [ 
                'judul' => $this->request->getPost('judul'), 
                'isi' => $this->request->getPost('isi'), 
                'id_kategori' => $this->request->getPost('id_kategori'), // Update kategorinya juga
            ]); 
            return redirect('admin/artikel'); 
        } 

        // Ambil data artikel yang sedang diedit
        $data = $artikelModel->where('id', $id)->first(); 
        $title = "Edit Artikel"; 

        // 2. Kirim $data (artikel) dan $kategori ke view
        return view('artikel/form_edit', [
            'title' => $title,
            'data' => $data,
            'kategori' => $kategori
        ]); 
    }
    public function delete($id)
    {
        $model = new ArtikelModel();
        $model->delete($id);
        return redirect()->to('/admin/artikel');
    }
    public function view($slug)
    {
        $model = new ArtikelModel();
        // Melakukan join ke tabel kategori untuk mendapatkan nama_kategori
        $artikel = $model->select('artikel.*, kategori.nama_kategori')
                        ->join('kategori', 'kategori.id_kategori = artikel.id_kategori', 'left')
                        ->where('artikel.slug', $slug)
                        ->first();

        if (!$artikel) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $title = $artikel['judul'];
        return view('artikel/detail', compact('artikel', 'title'));
    }
    public function kategori($id_kategori)
    {
        $model = new ArtikelModel();
        $db = \Config\Database::connect();

        // Ambil nama kategori untuk judul halaman
        $kategori = $db->table('kategori')->where('id_kategori', $id_kategori)->get()->getRowArray();
        
        if (!$kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Kategori tidak ditemukan");
        }

        // Ambil artikel yang sesuai dengan id_kategori saja
        $artikel = $model->select('artikel.*, kategori.nama_kategori')
                        ->join('kategori', 'kategori.id_kategori = artikel.id_kategori', 'left')
                        ->where('artikel.id_kategori', $id_kategori)
                        ->findAll();

        $data = [
            'title' => 'Kategori: ' . $kategori['nama_kategori'],
            'artikel' => $artikel
        ];

        // Kita gunakan view index artikel yang sudah ada untuk menampilkan hasilnya
        return view('artikel/index', $data);
}
}