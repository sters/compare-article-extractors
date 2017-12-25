<?php
require_once 'vendor/autoload.php';

$extractors = [
    'sters/extract-content' => function ($url, $html) {
        $result = new \ExtractContent\ExtractContent($html);

        return trim($result->analyse()[0]);
    },

    'zackslash/php-web-article-extractor' => function ($url, $html) {
        $result = \WebArticleExtractor\Extract::extractFromHTML($html);

        return trim($result->text);
    },

    'scotteh/php-goose'     => function ($url, $html) {
        $goose = new \Goose\Client([
            'language' => 'ja',
        ]);
        $result = $goose->extractContent($url, $html);

        return trim($result->getCleanedArticleText());
    },
    'j0k3r/php-readability' => function ($url, $html) {
        $result = new \Readability\Readability($html, $url);
        $result->init();

        if (is_null($result->getContent())) {
            return null;
        }

        return $result->getContent()->textContent;
    },
];

$urls = [
    'https://gomiba.co.in/blog/archives/1374',
    'https://qiita.com/inouetakuya/items/58e5fc12a8015882cfad',
    'https://medium.com/japan/500-startups-event-feb-9-2017-515463fce905',
    'https://headlines.yahoo.co.jp/hl?a=20171223-00050118-yom-soci',
    'https://ja.wikipedia.org/wiki/World_Wide_Web',
];

foreach ($urls as $index => $url) {
    $html = file_get_contents($url);
    foreach ($extractors as $extractorName => $extractor) {
        // 本文抽出
        $resultBody = $extractor($url, $html);

        // 差分の均一化：句点で改行
        $resultBody = str_replace('。', "。\n", $resultBody);

        // 差分の均一化：複数の空白
        $resultBody = preg_replace('/(\r?\n)+/', "\n", $resultBody);
        $resultBody = preg_replace('/([\t ]+(\r?\n)+)+/', "\n", $resultBody);
        $resultBody = preg_replace('/[\t ]+/', " ", $resultBody);

        // 結果の保存
        $dirPath = __DIR__ . '/results/url' . $index;
        $filePath = $dirPath . '/' . str_replace('/','__', $extractorName) . '.txt';
        @mkdir(__DIR__ . '/results/url' . $index);
        file_put_contents($filePath, $resultBody);
    }
}
