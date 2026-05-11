<?= $this->include('template/header'); ?> 
     
<h2><?= $title; ?></h2> 
 
<form action="" method="post" enctype="multipart/form-data">  
    <p> 
        <label for="judul">Judul</label>
        <input type="text" name="judul" id="judul" required> 
    </p> 
    <p> 
        <label for="isi">Isi</label> 
        <textarea name="isi" id="isi" cols="50" rows="10"></textarea> 
    </p> 
    <p> 
        <label for="id_kategori">Kategori</label> 
        <select name="id_kategori" id="id_kategori" required> 
            <?php foreach($kategori as $k): ?> 
                <option value="<?= $k['id_kategori']; ?>"><?= $k['nama_kategori']; ?></option> 
            <?php endforeach; ?> 
        </select> 
    <p> 
        <input type="file" name="gambar"> 
    </p>
    </p> 
    <p><input type="submit" value="Kirim" class="btn btn-large"></p>
     
</form> 
 
<?= $this->include('template/footer'); ?>