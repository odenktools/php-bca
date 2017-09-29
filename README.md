# BCA (Bank Central Asia)


# Example

#### Constructor

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
	BcaHttp::setTimeZone('Asia/Jakarta');

	$corp_id = "BCAAPI2016";
	$client_key = "NILAI-CLIENT-KEY-ANDA";
	$client_secret = "NILAI-CLIENT-SECRET-ANDA";
	$apikey = "NILAI-APIKEY-ANDA";
	$secret = "SECRETKEY-ANDA";

	$bca = new BcaHttp($corp_id, $client_key, $client_secret, $apikey, $secret);

	//or

	$bca = new BcaHttp($corp_id, $client_key, $client_secret, $apikey, $secret, $options);
```

#### Login

```php
	$corp_id = "BCAAPI2016";
	$client_key = "NILAI-CLIENT-KEY-ANDA";
	$client_secret = "NILAI-CLIENT-SECRET-ANDA";
	$apikey = "NILAI-APIKEY-ANDA";
	$secret = "SECRETKEY-ANDA";

	$bca = new BcaHttp($corp_id, $client_key, $client_secret, $apikey, $secret);

	// Request Login dan dapatkan nilai OAUTH
	$response = $bca->httpAuth();

	// LIHAT HASIL OUTPUT
	echo json_encode($response);
```

#### Balance Information

```php
	// Nilai token yang dihasilkan saat login
	$token = "MvXPqa5bQs5U09Bbn8uejBE79BjI3NNCwXrtMnjdu52heeZmw9oXgB";

	//Nomor akun yang akan di ambil informasi saldonya
	//Saat ini hanya bisa satu nomor aku saja
	$arrayAccNumber = array('0063001004');

	$response = $bca->getBalanceInfo($token, $arrayAccNumber);
	
	// LIHAT HASIL OUTPUT
	echo json_encode($response);
```

```php
	// Nilai token yang dihasilkan saat login
	$token = "MvXPqa5bQs5U09Bbn8uejBE79BjI3NNCwXrtMnjdu52heeZmw9oXgB";

	$amount = '50000.00';

	// Nilai akun bank anda
	$nomorakun = '0201245680';

	// Nilai akun bank yang akan ditransfer
	$nomordestinasi = '0201245681';

	// Nomor PO, silahkan sesuaikan
	$nomorPO = '12345/PO/2017';

	// Nomor Transaksi anda, Silahkan generate sesuai kebutuhan anda
	$nomorTransaksiID = '00000001;

	$response = $bca->fundTransfers($token, 
						$amount,
						$nomorakun,
						$nomordestinasi,
						$nomorPO,
						'Testing Saja Ko',
						'Online Saja Ko',
						$nomorTransaksiID);
```
