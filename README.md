[![SensioLabsInsight](https://insight.sensiolabs.com/projects/0a685157-ea99-4554-b302-9e8879d05648/small.png)](https://insight.sensiolabs.com/projects/0a685157-ea99-4554-b302-9e8879d05648)
[![Build Status](https://travis-ci.org/odenktools/php-bca.svg?branch=master)](https://travis-ci.org/odenktools/php-bca)
[![codecov](https://codecov.io/gh/odenktools/php-bca/branch/master/graph/badge.svg)](https://codecov.io/gh/odenktools/php-bca)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/odenktools/php-bca/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/odenktools/php-bca/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/odenktools/php-bca/v/stable)](https://packagist.org/packages/odenktools/php-bca)
[![Latest Unstable Version](https://poser.pugx.org/odenktools/php-bca/v/unstable)](https://packagist.org/packages/odenktools/php-bca)
[![Total Downloads](https://poser.pugx.org/odenktools/php-bca/downloads)](https://packagist.org/packages/odenktools/php-bca)

# BCA (Bank Central Asia)

Native PHP library untuk mengintegrasikan Aplikasi Anda dengan sistem BCA (Bank Central Asia). Untuk dokumentasi lebih jelas dan lengkap, silahkan kunjungi website resminya di [Developer BCA](https://developer.bca.co.id/documentation).

Untuk Framework ```Laravel``` bisa menggunakan library [Odenktools Laravel BCA](https://github.com/odenktools/laravel-bca).

Jika merasa terbantu dengan adanya library ini, jangan lupa untuk kasih STAR untuk library ini.

## PHP Version Support

- [x] PHP 5.4.x
- [x] PHP 5.5.x
- [x] PHP 5.6.x
- [x] PHP HHVM
- [x] PHP 7.0.x
- [x] PHP 7.1.x
- [ ] PHP 7.2.x

Untuk lebih detail silahkan kunjungi [PHP BCA TravisCI](https://travis-ci.org/odenktools/php-bca)

## Fitur Library

* [Installasi](https://github.com/odenktools/php-bca#instalasi)
* [Setting](https://github.com/odenktools/php-bca#koneksi-dan-setting)
* [Login](https://github.com/odenktools/php-bca#login)
* [Informasi Saldo](https://github.com/odenktools/php-bca#balance-information)
* [Transfer](https://github.com/odenktools/php-bca#fund-transfer)
* [Mutasi Rekening](https://github.com/odenktools/php-bca#account-statement)
* [Info Kurs](https://github.com/odenktools/php-bca#foreign-exchange-rate)
* [Pencarian ATM Terdekat](https://github.com/odenktools/php-bca#nearest-atm-locator)
* [Deposit Rate](https://github.com/odenktools/php-bca#deposit-rate)
* [Generate Signature](https://github.com/odenktools/php-bca#generate-signature)
* [How to contribute](https://github.com/odenktools/php-bca#how-to-contribute)

### (NEW BCA API on December 2017)

Get balance information

```
/fire/accounts/balance
```

Get beneficiary account’s information

```
/fire/accounts
```

Get status of a transaction

```
/fire/transactions
```

Transfer funds directly to beneficiary account

```
/fire/transactions/to-account
```

Transfer funds to beneficiary to be taken personally

```
/fire/transactions/cash-transfer
```

Amend cash transfer transaction’s detail

```
/fire/transactions/cash-transfer/amend
```

Cancel cash transfer transaction

```
/fire/transactions/cash-transfer/cancel
```

Get status of payment by CompanyCode and CustomerNumber or RequestID

```
/va/payments?CompanyCode=&RequestID=
```

### INSTALASI

```bash
composer require "odenktools/php-bca"
```

### KONEKSI DAN SETTING

Sebelum masuk ke tahap ```LOGIN``` pastikan seluruh kebutuhan seperti ```CORP_ID, CLIENT_KEY, CLIENT_SECRET, APIKEY, SECRETKEY``` telah diketahui.

```php
    $options = array(
        'scheme'        => 'https',
        'port'          => 443,
        'host'          => 'sandbox.bca.co.id',
        'timezone'      => 'Asia/Jakarta',
        'timeout'       => 30,
        'debug'         => true,
        'development'   => true
    );

    // Setting default timezone Anda
    \Bca\BcaHttp::setTimeZone('Asia/Jakarta');

    // ATAU

    // \Bca\BcaHttp::setTimeZone('Asia/Singapore');

    $corp_id = "BCAAPI2016";
    $client_key = "NILAI-CLIENT-KEY-ANDA";
    $client_secret = "NILAI-CLIENT-SECRET-ANDA";
    $apikey = "NILAI-APIKEY-ANDA";
    $secret = "SECRETKEY-ANDA";

    $bca = new \Bca\BcaHttp($corp_id, $client_key, $client_secret, $apikey, $secret);

    // ATAU

    $bca = new \Bca\BcaHttp($corp_id, $client_key, $client_secret, $apikey, $secret, $options);
```

### LOGIN

```php
    $corp_id = "CORP_ID-ANDA";
    $client_key = "NILAI-CLIENT-KEY-ANDA";
    $client_secret = "NILAI-CLIENT-SECRET-ANDA";
    $apikey = "NILAI-APIKEY-ANDA";
    $secret = "SECRETKEY-ANDA";

    $bca = new \Bca\BcaHttp($corp_id, $client_key, $client_secret, $apikey, $secret);

    // Request Login dan dapatkan nilai OAUTH
    $response = $bca->httpAuth();

    // Cek hasil response berhasil atau tidak
    echo json_encode($response);
```

Setelah Login berhasil pastikan anda menyimpan nilai ```TOKEN``` di tempat yang aman, karena nilai ```TOKEN``` tersebut agar digunakan untuk tugas tugas berikutnya.

### BALANCE INFORMATION

Pastikan anda mendapatkan nilai ```TOKEN``` dan ```TOKEN``` tersebut masih berlaku (Tidak Expired).

```php
    // Ini adalah nilai token yang dihasilkan saat login
    $token = "MvXPqa5bQs5U09Bbn8uejBE79BjI3NNCwXrtMnjdu52heeZmw9oXgB";

    //Nomor akun yang akan di ambil informasi saldonya, menggunakan ARRAY
    $arrayAccNumber = array('0201245680', '0063001004', '1111111111');

    $response = $bca->getBalanceInfo($token, $arrayAccNumber);

    // Cek hasil response berhasil atau tidak
    echo json_encode($response);
```

### FUND TRANSFER

Pastikan anda mendapatkan nilai ```TOKEN``` dan ```TOKEN``` tersebut masih berlaku (Tidak Expired).

```php
    // Ini adalah nilai token yang dihasilkan saat login
    $token = "MvXPqa5bQs5U09Bbn8uejBE79BjI3NNCwXrtMnjdu52heeZmw9oXgB";

    $amount = '50000.00';

    // Nilai akun bank anda
    $nomorakun = '0201245680';

    // Nilai akun bank yang akan ditransfer
    $nomordestinasi = '0201245681';

    // Nomor PO, silahkan sesuaikan
    $nomorPO = '12345/PO/2017';

    // Nomor Transaksi anda, Silahkan generate sesuai kebutuhan anda
    $nomorTransaksiID = '00000001';

    $remark1 = 'Transfer Test Using Odenktools BCA';

    $remark2 = 'Online Transfer Using Odenktools BCA';

    $response = $bca->fundTransfers($token, 
                        $amount,
                        $nomorakun,
                        $nomordestinasi,
                        $nomorPO,
                        $remark1,
                        $remark2,
                        $nomorTransaksiID);

    // Cek hasil response berhasil atau tidak
    echo json_encode($response);
```

Untuk data ```remark1```, ```remark2```, ```nomorPO``` akan di replace menjadi ```lowercase``` dan dihapus ```whitespace```

### ACCOUNT STATEMENT

Pastikan anda mendapatkan nilai ```TOKEN``` dan ```TOKEN``` tersebut masih berlaku (Tidak Expired).

```php
    // Ini adalah nilai token yang dihasilkan saat login
    $token = "MvXPqa5bQs5U09Bbn8uejBE79BjI3NNCwXrtMnjdu52heeZmw9oXgB";

    // Nilai akun bank anda
    $nomorakun = '0201245680';

    // Tanggal start transaksi anda
    $startdate = '2016-08-29';

    // Tanggal akhir transaksi anda
    $enddate = '2016-09-01';

    $response = $bca->getAccountStatement($token, $nomorakun, $startdate, $enddate);

    // Cek hasil response berhasil atau tidak
    echo json_encode($response);
```

### FOREIGN EXCHANGE RATE

```php
    //Tipe rate :  bn, e-rate, tt, tc
    $rateType = 'e-rate';

    $mataUang = 'usd';

    $response = $bca->getForexRate($token, $rateType, $mataUang);

    // Cek hasil response berhasil atau tidak
    echo json_encode($response);
```

### NEAREST ATM LOCATOR

```php
    $latitude = '-6.1900718';

    $longitude = '106.797190';

    $totalAtmShow = '10';

    $radius = '20';

    $response = $bca->getAtmLocation($token, $latitude, $longitude, $totalAtmShow, $radius);

    // Cek hasil response berhasil atau tidak
    echo json_encode($response);
```

### DEPOSIT RATE

Pastikan anda mendapatkan nilai ```TOKEN``` dan ```TOKEN``` tersebut masih berlaku (Tidak Expired).

```php
    // Ini adalah nilai token yang dihasilkan saat login
    $token = "MvXPqa5bQs5U09Bbn8uejBE79BjI3NNCwXrtMnjdu52heeZmw9oXgB";

    $response       = $bca->getDepositRate($token);

    // Cek hasil response berhasil atau tidak
    echo json_encode($response);
```

### GENERATE SIGNATURE

Saat berguna untuk keperluan testing.

```php

    $secret = "NILAI-SECRET-ANDA";

    // Ini adalah nilai token yang dihasilkan saat login
    $token = "MvXPqa5bQs5U09Bbn8uejBE79BjI3NNCwXrtMnjdu52heeZmw9oXgB";

    $uriSign = "GET:/general/info-bca/atm";

    // Format timestamp harus dalam ISO8601 format (yyyy-MM-ddTHH:mm:ss.SSSTZD)
    $isoTime = "2016-02-03T10:00:00.000+07:00";

    $bodyData = array();

    //nilai body anda disini
    $bodyData['a'] = "BLAAA-BLLLAA";
    $bodyData['b'] = "BLEHH-BLLLAA";

    //ketentuan BCA array harus disort terlebih dahulu
    ksort($bodyData);

    $authSignature = \Bca\BcaHttp::generateSign($uriSign, $token, $secret, $isoTime, $bodyData);

    echo $authSignature;
```

# TESTING

Untuk melakukan testing lakukan ```command``` berikut ini

```bash
composer run-script test
```

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

# LICENSE

MIT License

Copyright (c) 2017 odenktools

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
