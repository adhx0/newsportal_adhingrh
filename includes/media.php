<?php

/** Mengubah URL YouTube menjadi URL embed (privacy-enhanced), atau null jika tidak valid. */
function getYouTubeEmbedUrl($url): ?string
{
  $parts = parse_url(trim((string) ($url ?? '')));

  if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
    return null;
  }

  $host = strtolower($parts['host'] ?? '');
  $hostBoleh = [
    'youtube.com',
    'www.youtube.com',
    'm.youtube.com',
    'youtube-nocookie.com',
    'www.youtube-nocookie.com',
    'youtu.be',
  ];

  if (!in_array($host, $hostBoleh, true)) {
    return null;
  }

  $path = $parts['path'] ?? '';

  if ($host === 'youtu.be') {
    $videoId = trim($path, '/');
  } else {
    parse_str($parts['query'] ?? '', $query);
    $videoId = is_string($query['v'] ?? null) ? $query['v'] : '';

    if ($videoId === '' && preg_match('~/(?:embed|shorts|live)/([\w-]{11})~', $path, $m)) {
      $videoId = $m[1];
    }
  }

  return preg_match('/^[\w-]{11}$/', $videoId)
    ? 'https://www.youtube-nocookie.com/embed/' . $videoId
    : null;
}

/** URL http(s) yang berakhiran .mp4? */
function isMp4MediaUrl($url): bool
{
  $parts = parse_url(trim((string) ($url ?? '')));

  return $parts
    && !empty($parts['host'])
    && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
    && preg_match('/\.mp4$/i', $parts['path'] ?? '') === 1;
}

/** HTML pemutar video (YouTube / MP4). String kosong jika URL tidak didukung. */
function renderVideo($url, string $judul = 'Video artikel', string $kelas = 'my-4'): string
{
  $kelas = e($kelas);

  if (($youtube = getYouTubeEmbedUrl($url)) !== null) {
    return '<div class="article-media-wrapper ' . $kelas . '"><div class="ratio ratio-16x9">'
      . '<iframe src="' . e($youtube) . '" title="' . e($judul) . '" loading="lazy" '
      . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
      . 'referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>'
      . '</div></div>';
  }

  if (isMp4MediaUrl($url)) {
    return '<div class="article-media-wrapper ' . $kelas . '">'
      . '<video class="w-100" controls preload="metadata" playsinline>'
      . '<source src="' . e(trim((string) $url)) . '" type="video/mp4">'
      . 'Browser kamu tidak mendukung pemutar video.</video></div>';
  }

  return '';
}

/** Mengganti tag <oembed> dari CKEditor menjadi pemutar video. */
function renderMediaEmbed(string $isi): string
{
  $hasil = preg_replace_callback(
    '~<oembed\b[^>]*\burl=(["\'])(.*?)\1[^>]*>\s*</oembed>~is',
    function ($m) {
      return renderVideo(html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    },
    $isi
  );

  return $hasil ?? $isi;
}
