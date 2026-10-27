<?php
/**
 * Proxy local para el feed RSS de YouTube (evita problemas de CORS)
 * Subdominio: app.concejodechia.gov.co
 */
header('Content-Type: application/xml; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=300'); // Cache de 5 minutos

function fetchUrl($url, $timeout = 8) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($content !== false && $httpCode >= 200 && $httpCode < 400) return $content;
    }
    
    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => $timeout,
                'user_agent' => 'Mozilla/5.0 (compatible; ConcejoChiaPWA/1.0)'
            ]
        ]);
        $content = @file_get_contents($url, false, $context);
        if ($content !== false) return $content;
    }
    
    return false;
}

$channelId = isset($_GET['channel']) ? $_GET['channel'] : 'UC3K3cSdZd8ETB_3-CG8TavQ';
$url = 'https://www.youtube.com/feeds/videos.xml?channel_id=' . urlencode($channelId);

$content = fetchUrl($url);

if ($content === false) {
    http_response_code(502);
    echo '<?xml version="1.0" encoding="UTF-8"?><error>No se pudo obtener el feed de YouTube</error>';
    exit;
}

echo $content;
