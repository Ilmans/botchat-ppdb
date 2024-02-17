<?php

namespace classes;

class Cache
{
    private $cachePath = 'cache/';

    public function set($key, $data, $expiry = 3600)
    {
        $filePath = $this->cachePath . md5($key);
        $data = serialize([$expiry + time(), $data]);
        return file_put_contents($filePath, $data);
    }

    public function get($key)
    {
        $filePath = $this->cachePath . md5($key);
        if (!file_exists($filePath)) {
            return false;
        }
        $data = unserialize(file_get_contents($filePath));
        if (time() > $data[0]) {
            unlink($filePath);
            return false;
        }
        return $data[1];
    }

    public function delete($key)
    {
        $filePath = $this->cachePath . md5($key);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function cleanExpired()
    {
        $files = glob($this->cachePath . '*');
        if ($files === false) {
            return;
        }
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            if (time() > $data[0]) {
                unlink($file);
            }
        }
    }
}
