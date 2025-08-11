<?php

namespace Scrapify\ApiTools;

use Symfony\Component\DomCrawler\Crawler;
use League\HTMLToMarkdown\HtmlConverter;
use Scrapify\ImageTools\HtmlToImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class ApiScrapeService
{
    
    private $apiKey = 'bedc32e975d616481eb456a5c450a615'; 
    public function scrape(string $url, string $outputType, array $specificOptions = []): array
    {
        try {
            $html = file_get_contents($url);
            if (!$html || empty(trim($html))) {
                throw new \Exception("Failed to retrieve HTML from URL");
            }

            $crawler = new Crawler($html);

            if ($outputType === 'markdown') {
                $converter = new HtmlConverter();
                return ['type' => 'markdown', 'data' => $converter->convert($html)];
            }

            if ($outputType === 'screenshot') {
                $targetUrl = $url;

                $outputDir = public_path('images');
                if (!File::exists($outputDir)) {
                    File::makeDirectory($outputDir, 0755, true);
                }

                $timestamp = time();
                $fileName = "html_to_image_{$timestamp}.png";
                $imagePath = $outputDir . DIRECTORY_SEPARATOR . $fileName;

                $apiUrl = "https://api.screenshotlayer.com/api/capture"
                    . "?access_key={$this->apiKey}"
                    . "&url=" . urlencode($targetUrl)
                    . "&fullpage=1";

                try {
                    $response = Http::timeout(30)->get($apiUrl);

                    if ($response->failed()) {
                        throw new \Exception("HTTP request failed: " . $response->body());
                    }

                    // Check if JSON is returned
                    $contentType = $response->header('Content-Type');
                    if (str_contains($contentType, 'application/json')) {
                        $json = $response->json();

                        // If ScreenshotLayer returns a direct image URL in JSON
                        if (!empty($json['image_url'])) {
                            $imageBinary = Http::timeout(30)->get($json['image_url'])->body();
                            File::put($imagePath, $imageBinary);
                            return ['type' => 'screenshot', 'data' => url('images/' . $fileName)];
                        }

                        throw new \Exception("ScreenshotLayer API error: " . json_encode($json));
                    }

                    // Otherwise, treat as raw PNG
                    File::put($imagePath, $response->body());
                    return ['type' => 'screenshot', 'data' => url('images/' . $fileName)];

                } catch (\Throwable $e) {
                    return ['type' => 'error', 'data' => "Screenshot failed: " . $e->getMessage()];
                }
            }

            if ($outputType === 'specific') {
                if (empty($specificOptions)) {
                    return ['type' => 'html', 'data' => 'Please select at least one field'];
                }

                $result = [];

                foreach ($specificOptions as $option) {
                    switch (strtolower($option)) {
                        case 'email':
                            $result['emails'] = $crawler->filter('a[href^="mailto:"]')
                                ->each(fn($node) => str_replace('mailto:', '', $node->attr('href')));
                            break;
                        case 'link':
                            $result['links'] = $crawler->filter('a[href]')
                                ->each(fn($node) => $node->attr('href'));
                            break;
                        case 'phone':
                            $result['phones'] = $crawler->filter('a[href^="tel:"]')
                                ->each(fn($node) => str_replace('tel:', '', $node->attr('href')));
                            break;
                        case 'image':
                            $result['images'] = $crawler->filter('img')
                                ->each(fn($node) => $node->attr('src'));
                            break;
                        case 'metadata':
                            $meta = $crawler->filter('meta')->each(function ($node) {
                                return [
                                    'name' => $node->attr('name') ?? $node->attr('property') ?? '',
                                    'content' => $node->attr('content') ?? ''
                                ];
                            });
                            $result['metadata'] = array_values(array_filter($meta, fn($m) => !empty($m['name']) && !empty($m['content'])));
                            break;
                        case 'heading':
                            $headings = [];
                            for ($i = 1; $i <= 6; $i++) {
                                $tag = 'h' . $i;
                                $texts = $crawler->filter($tag)->each(fn($node) => trim($node->text()));
                                if (!empty($texts)) {
                                    $headings[$tag] = array_values(array_unique($texts));
                                }
                            }
                            $result['headings'] = $headings;
                            break;
                    }
                }

                return ['type' => 'specific', 'data' => $result];
            }

            return ['type' => 'html', 'data' => $html];

        } catch (\Exception $e) {
            return ['type' => 'error', 'data' => $e->getMessage()];
        }
    }
}
