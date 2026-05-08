<?php

namespace App\Services;

use GuzzleHttp\Client;

class MetadataFetcherService
{
    public function fetch(string $url): array
    {
        try {
            $client = new Client([
                'timeout'         => 15,
                'connect_timeout' => 8,
                'allow_redirects' => ['max' => 5, 'strict' => false, 'track_redirects' => false],
                'verify'          => false,
                'headers'         => [
                    'User-Agent'                => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Language'           => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Accept-Encoding'           => 'gzip, deflate, br',
                    'Cache-Control'             => 'no-cache',
                    'Pragma'                    => 'no-cache',
                    'Sec-Fetch-Dest'            => 'document',
                    'Sec-Fetch-Mode'            => 'navigate',
                    'Sec-Fetch-Site'            => 'none',
                    'Sec-Fetch-User'            => '?1',
                    'Upgrade-Insecure-Requests' => '1',
                    'DNT'                       => '1',
                ],
            ]);

            $response = $client->get($url);
            $html     = $response->getBody()->getContents();

            $titre = null;
            $image = null;

            // ── 1. Next.js __NEXT_DATA__ (LeBonCoin, BienIci…) ─────────────
            if (preg_match('/<script[^>]+id=["\']__NEXT_DATA__["\'][^>]*>(.+?)<\/script>/s', $html, $m)) {
                $json = json_decode($m[1], true);
                if ($json) {
                    $titre = $titre ?? $this->dig($json, ['props','pageProps','ad','subject'])
                                    ?? $this->dig($json, ['props','pageProps','adView','title'])
                                    ?? $this->dig($json, ['props','pageProps','listing','title']);

                    $imgs  = $this->dig($json, ['props','pageProps','ad','images','urls_large'])
                          ?? $this->dig($json, ['props','pageProps','ad','images','urls'])
                          ?? $this->dig($json, ['props','pageProps','adView','images']);
                    if (is_array($imgs) && count($imgs) > 0) {
                        $image = $image ?? (is_string($imgs[0]) ? $imgs[0] : ($imgs[0]['url'] ?? null));
                    }
                }
            }

            // ── 2. JSON-LD ───────────────────────────────────────────────────
            if ((!$titre || !$image) && preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.+?)<\/script>/si', $html, $ms)) {
                foreach ($ms[1] as $raw) {
                    $ld = json_decode($raw, true);
                    if (!$ld) continue;
                    $titre = $titre ?? $ld['name'] ?? $ld['headline'] ?? null;
                    $image = $image ?? (is_array($ld['image'] ?? null) ? ($ld['image']['url'] ?? $ld['image'][0] ?? null) : ($ld['image'] ?? null));
                    if ($titre && $image) break;
                }
            }

            // ── 3. Open Graph ────────────────────────────────────────────────
            if (!$titre) {
                foreach ([
                    '/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i',
                    '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:title["\'][^>]*>/i',
                ] as $pattern) {
                    if (preg_match($pattern, $html, $m)) { $titre = $m[1]; break; }
                }
            }
            if (!$image) {
                foreach ([
                    '/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i',
                    '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\'][^>]*>/i',
                ] as $pattern) {
                    if (preg_match($pattern, $html, $m)) { $image = $m[1]; break; }
                }
            }

            // ── 4. Fallback <title> ──────────────────────────────────────────
            if (!$titre && preg_match('/<title>([^<]+)<\/title>/i', $html, $m)) {
                $titre = $m[1];
            }

            $found = $titre || $image;

            return [
                'titre'         => $titre ? trim(html_entity_decode($titre, ENT_QUOTES | ENT_HTML5)) : null,
                'thumbnail_url' => $image,
                'success'       => $found,
                'error'         => $found ? null : 'Aucune métadonnée trouvée (site protégé — remplis manuellement)',
            ];

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return ['titre' => null, 'thumbnail_url' => null, 'success' => false, 'error' => 'Impossible de joindre le site'];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $code = $e->getResponse()->getStatusCode();
            if (in_array($code, [403, 429, 401])) {
                // Fallback via microlink.io (gère le JS rendering)
                return $this->fetchViaMicrolink($url);
            }
            return ['titre' => null, 'thumbnail_url' => null, 'success' => false, 'error' => "Erreur HTTP $code"];
        } catch (\Throwable $e) {
            return ['titre' => null, 'thumbnail_url' => null, 'success' => false, 'error' => 'Erreur : ' . $e->getMessage()];
        }
    }

    private function fetchViaMicrolink(string $url): array
    {
        try {
            $client = new Client(['timeout' => 15, 'verify' => false]);
            $resp   = $client->get('https://api.microlink.io/', [
                'query' => ['url' => $url, 'meta' => 'false'],
                'headers' => ['Accept' => 'application/json'],
            ]);
            $data = json_decode($resp->getBody()->getContents(), true);

            if (($data['status'] ?? '') === 'success') {
                $d     = $data['data'] ?? [];
                $titre = $d['title'] ?? $d['description'] ?? null;
                $image = $d['image']['url'] ?? $d['logo']['url'] ?? null;
                return [
                    'titre'         => $titre ? trim(html_entity_decode($titre, ENT_QUOTES | ENT_HTML5)) : null,
                    'thumbnail_url' => $image,
                    'success'       => (bool) ($titre || $image),
                    'error'         => ($titre || $image) ? null : 'Aucune métadonnée trouvée',
                ];
            }
        } catch (\Throwable) {}

        return ['titre' => null, 'thumbnail_url' => null, 'success' => false,
                'error' => 'LeBonCoin bloque le scraping — colle l\'URL, remplis le titre à la main et clique "Voir l\'annonce" depuis la fiche'];
    }

    private function dig(?array $arr, array $keys): mixed
    {
        $cur = $arr;
        foreach ($keys as $key) {
            if (!is_array($cur) || !array_key_exists($key, $cur)) return null;
            $cur = $cur[$key];
        }
        return $cur;
    }
}
