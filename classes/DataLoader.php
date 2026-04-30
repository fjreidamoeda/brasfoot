<?php
class DataLoader {
    public static function load($filename) {
        $path = __DIR__ . "/../data/" . $filename . ".json";
        if (!file_exists($path)) return [];
        $json = file_get_contents($path);
        return json_decode($json, true) ?: [];
    }
}