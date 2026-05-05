<?php

namespace App\Services;

use GuzzleHttp\Client;

class MetadataFetcherService
{
    public function fetch(string $url): array
    {
        try {
            $client = new Client([
                'timeout' => 10,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ],
                'allow_redirects' => true,
                'verify'          => false,
            ]);

            $html = $client->get($url)->getBody()->getContents();

            $titre = null;
            if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
                $titre = $m[1];
            } elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:title["\'][^>]*>/i', $html, $m)) {
                $titre = $m[1];
            } elseif (preg_match('/<title>([^<]+)<\/title>/i', $html, $m)) {
                $titre = $m[1];
            }

            $image = null;
            if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
                $image = $m[1];
            } elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\'][^>]*>/i', $html, $m)) {
                $image = $m[1];
            }

            return [
                'titre'         => $titre ? trim(html_entity_decode($titre)) : null,
                'thumbnail_url' => $image,
                'success'       => true,
            ];
        } catch (\Throwable $e) {
            return ['titre' => null, 'thumbnail_url' => null, 'success' => false, 'error' => $e->getMessage()];
        }
    }
}
