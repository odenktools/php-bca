<?php

namespace Bca;

use Carbon\Carbon;

class BcaHelper
{
    /**
     * Generate ISO8601 Time.
     *
     * @param string $timeZone Time yang akan dipergunakan
     *
     * @return string
     */
    public static function generateIsoTime($timeZone)
    {
        $date = Carbon::now($timeZone);
        date_default_timezone_set($timeZone);
        $fmt = $date->format('Y-m-d\TH:i:s');
        $ISO8601 = sprintf("$fmt.%s%s", substr(microtime(), 2, 3), date('P'));

        return $ISO8601;
    }

    /**
     * Generate Signature.
     *
     * @param string $url Url yang akan disign.
     * @param string $auth_token string nilai token dari login.
     * @param string $secret_key string secretkey yang telah diberikan oleh BCA.
     * @param string $isoTime string Waktu ISO8601.
     * @param array|mixed $bodyToHash array Body yang akan dikirimkan ke Server BCA.
     *
     * @return string
     */
    public static function generateSign($url, $auth_token, $secret_key, $isoTime, $bodyToHash = [])
    {
        $hash = hash("sha256", "");
        if (is_array($bodyToHash)) {
            ksort($bodyToHash);
            $encoderData = json_encode($bodyToHash, JSON_UNESCAPED_SLASHES);
            $hash = hash("sha256", $encoderData);
        }
        $stringToSign = $url . ":" . $auth_token . ":" . $hash . ":" . $isoTime;
        $auth_signature = hash_hmac('sha256', $stringToSign, $secret_key, false);

        return $auth_signature;
    }

    /**
     * Merge from existing array.
     *
     * @param array $existing_options
     * @param array $new_options
     * @return array
     */
    public static function mergeCurlOptions(&$existing_options, $new_options)
    {
        $existing_options = $new_options + $existing_options;
        return $existing_options;
    }

    /**
     * Validasi jika clientsecret telah di-definsikan.
     *
     * @param array $sourceAccountId
     *
     * @throws BcaHttpException Error jika array tidak memenuhi syarat
     * @return bool
     */
    public static function validateArray($sourceAccountId = [])
    {
        if (!is_array($sourceAccountId)) {
            throw new BcaHttpException('Data harus array.');
        }
        if (empty($sourceAccountId)) {
            throw new BcaHttpException('AccountNumber tidak boleh kosong.');
        } else {
            $max = sizeof($sourceAccountId);
            if ($max > 20) {
                throw new BcaHttpException('Maksimal Account Number ' . 20);
            }
        }

        return true;
    }

    /**
     * Implode an array with the key and value pair giving
     * a glue, a separator between pairs and the array
     * to implode.
     *
     * @param string $glue The glue between key and value
     * @param string $separator Separator between pairs
     * @param array $array The array to implode
     *
     * @throws BcaHttpException error
     * @return string The imploded array
     */
    public static function arrayImplode($glue, $separator, $array = [])
    {
        if (!is_array($array)) {
            throw new BcaHttpException('Data harus array.');
        }
        if (empty($array)) {
            throw new BcaHttpException('parameter array tidak boleh kosong.');
        }
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $val = implode(',', $val);
            }
            $string[] = "{$key}{$glue}{$val}";
        }

        return implode($separator, $string);
    }
}