<?php
/**
 * Endpoint para obtener videos y estado EN VIVO en tiempo real del canal oficial de YouTube del Concejo Municipal de Chía
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');

/**
 * Función para realizar peticiones HTTP con cURL y fallback a file_get_contents
 */
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

// ID Oficial de Canal y Handle del Concejo Municipal de Chía
$channelId = 'UC3K3cSdZd8ETB_3-CG8TavQ';
$handle = '@ConcejoChia';

// 1. Detectar si el canal está transmitiendo EN VIVO en este momento
$isLive = false;
$liveVideoId = null;

$liveHtml = fetchUrl("https://www.youtube.com/{$handle}/live", 6);
if ($liveHtml !== false) {
    // Verificar si la página contiene marcas de transmisión en directo activa
    $containsLiveBadge = (
        strpos($liveHtml, '"isLive":true') !== false ||
        strpos($liveHtml, '"isLiveContent":true') !== false ||
        strpos($liveHtml, 'BADGE_STYLE_TYPE_LIVE_NOW') !== false ||
        (strpos($liveHtml, 'transmitiendo en vivo') !== false && strpos($liveHtml, 'OFFLINE') === false)
    );

    if ($containsLiveBadge) {
        $isLive = true;
        // Intentar extraer el ID del video en vivo
        if (preg_match('/<link rel="canonical" href="https:\/\/www\.youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})">/', $liveHtml, $m)) {
            $liveVideoId = $m[1];
        } elseif (preg_match('/"videoId":"([a-zA-Z0-9_-]{11})"/', $liveHtml, $m)) {
            $liveVideoId = $m[1];
        }
    }
}

// 2. Obtener lista de videos recientes del feed RSS oficial
$rssUrl = "https://www.youtube.com/feeds/videos.xml?channel_id={$channelId}";
$xml = fetchUrl($rssUrl, 8);

$videos = [];
$channelTitle = 'Concejo Municipal de Chía';

if ($xml !== false) {
    $feed = @simplexml_load_string($xml);
    if ($feed) {
        if (isset($feed->title)) $channelTitle = (string)$feed->title;
        $ns = $feed->getNamespaces(true);

        foreach ($feed->entry as $entry) {
            $videoId = '';
            if (isset($entry->children($ns['yt'] ?? '')->videoId)) {
                $videoId = (string)$entry->children($ns['yt'])->videoId;
            } else {
                $idText = (string)$entry->id;
                if (strpos($idText, ':') !== false) {
                    $parts = explode(':', $idText);
                    $videoId = end($parts);
                }
            }

            if ($videoId) {
                $videos[] = [
                    'id' => $videoId,
                    'title' => (string)$entry->title,
                    'published' => (string)$entry->published,
                    'updated' => (string)$entry->updated,
                    'thumbnail' => "https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg",
                    'url' => "https://www.youtube.com/watch?v={$videoId}"
                ];
            }
        }
    }
}

// Ordenar inteligentemente los videos para priorizar las sesiones del año en curso (ej: 2026) sobre cargas tardías de años pasados (ej: 2025)
usort($videos, function($a, $b) {
    preg_match('/20\d{2}/', $a['title'], $mA);
    preg_match('/20\d{2}/', $b['title'], $mB);
    $yearA = isset($mA[0]) ? (int)$mA[0] : (int)date('Y', strtotime($a['published']));
    $yearB = isset($mB[0]) ? (int)$mB[0] : (int)date('Y', strtotime($b['published']));
    
    if ($yearA !== $yearB) {
        return $yearB <=> $yearA; // Año más reciente primero
    }
    
    return strtotime($b['published']) <=> strtotime($a['published']);
});

// Respuesta estructurada JSON
echo json_encode([
    'error' => false,
    'is_live' => $isLive,
    'live_video_id' => $liveVideoId,
    'channel_id' => $channelId,
    'channel' => $channelTitle,
    'videos' => $videos
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
