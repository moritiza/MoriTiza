<?php 

namespace Core\Libraries;

class SessionHandler extends \SessionHandler
{
    private $key = null;
    private $savePath = SESSIONPATH;

    public function __construct()
    {
        if (! extension_loaded('openssl')) {
            throw new \RuntimeException(sprintf(
                "You need the OpenSSL extension to use %s",
                __CLASS__
            ));
        }
        
        if (! extension_loaded('mbstring')) {
            throw new \RuntimeException(sprintf(
                "You need the Multibytes extension to use %s",
                __CLASS__
            ));
        }
    }

    public function open($savePath = '', $sessionName)
    {
        $this->key = $this->getKey('KEY_' . $sessionName);
        return parent::open($this->savePath, $sessionName);
    }

    public function refresh()
    {
        return session_regenerate_id(true);
    }

    public function read($id)
    {
        $data = parent::read($id);
        return empty($data) ? '' : $this->decrypt($data, $this->key);
    }

    public function write($id, $data)
    {
        return parent::write($id, $this->encrypt($data, $this->key));
    }

    protected function getKey($name)
    {
        if (empty($_COOKIE[$name])) {
            $key         = random_bytes(64); // 32 for encryption and 32 for authentication
            $cookieParam = session_get_cookie_params();
            $encKey      = base64_encode($key);
            setcookie(
                $name,
                $encKey,
                // if session cookie lifetime > 0 then add to current time
                // otherwise leave it as zero, honoring zero's special meaning
                // expire at browser close.
                ($cookieParam['lifetime'] > 0) ? time() + $cookieParam['lifetime'] : 0,
                $cookieParam['path'],
                $cookieParam['domain'],
                $cookieParam['secure'],
                $cookieParam['httponly']
            );
            $_COOKIE[$name] = $encKey;
        } else {
            $key = base64_decode($_COOKIE[$name]);
        }

        return $key;
    }

    protected function encrypt($data, $key)
    {
        $iv = random_bytes(16); // AES block size in CBC mode
        // Encryption
        $ciphertext = openssl_encrypt(
            $data,
            'AES-256-CBC',
            mb_substr($key, 0, 32, '8bit'),
            OPENSSL_RAW_DATA,
            $iv
        );
        // Authentication
        $hmac = hash_hmac(
            'SHA256',
            $iv . $ciphertext,
            mb_substr($key, 32, null, '8bit'),
            true
        );
        return $hmac . $iv . $ciphertext;
    }

    protected function decrypt($data, $key)
    {
        $hmac       = mb_substr($data, 0, 32, '8bit');
        $iv         = mb_substr($data, 32, 16, '8bit');
        $ciphertext = mb_substr($data, 48, null, '8bit');
        // Authentication
        $hmacNew = hash_hmac(
            'SHA256',
            $iv . $ciphertext,
            mb_substr($key, 32, null, '8bit'),
            true
        );
        if (! hash_equals($hmac, $hmacNew)) {
            throw new \RuntimeException('Authentication failed');
        }
        // Decrypt
        return openssl_decrypt(
            $ciphertext,
            'AES-256-CBC',
            mb_substr($key, 0, 32, '8bit'),
            OPENSSL_RAW_DATA,
            $iv
        );
    }
}