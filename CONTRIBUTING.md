# How to contribute


* Lakukan Fork pada GitHub
* Tambahkan fork pada git remote anda

Untuk contoh commandline nya :

```bash
git remote add fork git@github.com:$USER/php-bca.git  # Tambahkan fork pada remote, $USER adalah username GitHub anda
```

contohnya :

```bash
git remote add fork git@github.com:johndoe/php-bca.git
```

* Buat feature ```branch``` dengan cara

```bash
git checkout -b feature/my-new-feature origin/develop 
```

* Lakukan pekerjaan pada repository anda tersebut. 
* Sebelum melakukan commit lakukan ```Reformat kode``` anda menggunakan sesuai [PSR-2 Coding Style Guide](https://github.com/odenktools/php-bca#guidelines)
* Setelah selesai lakukan commit

```bash
git commit -am 'Menambahkan fitur xxx'
```

* Lakukan ```Push``` ke branch yang telah dibuat

```bash
git push fork feature/my-new-feature
```

* Lakukan PullRequest pada GitHub, setelah pekerjaan anda akan kami review. Selesai.

## Guidelines

* Koding berstandart [PSR-2 Coding Style Guide](http://www.php-fig.org/psr/psr-2/)
* Pastikan seluruh test yang dilakukan telah pass, jika anda menambahkan fitur baru, anda diharus kan untuk membuat unit test terkait dengan fitur tersebut.
* Pergunakan [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) untuk menghindari conflict dan merge kode
* Jika anda menambahkan fitur, mungkin anda juga harus mengupdate halaman dokumentasi pada repository ini.
