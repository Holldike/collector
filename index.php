<?php

require 'vendor/autoload.php';
require 'config.php';

$db = new Db(DB_USER, DB_PASS, DB_HOST, DB);
$linkParser = new LinkParser();
$collector = new Collector($linkParser, $db);

while(true) {
    $newWord = generateNewWord();
    $searchQueries = generateSearchQueries($newWord);

    foreach ($searchQueries as $query) {
        if ($page = file_get_contents($query)) {
            $collector->setPage($page);
            $collector->process();
        }
    }
}

function generateNewWord() {
    $glas = ["a", "e", "i", "y", "o", "u"];
    $soglas = ["b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "q", "r", "s", "t", "v", "x", "w", "z"];

    $wordlen = 5;

    $newWord = '';

    for ($i = 0; $i < $wordlen / 2; $i++) {
        $ng = rand(0, count($glas) - 1);
        $nsg = rand(0, count($soglas) - 1);
        $newWord .= $glas[$ng] . $soglas[$nsg];
    }

    return $newWord;
}

function generateSearchQueries(string $newWord) {
    return
        $inquiries = [
            'https://search.yahoo.com/search?p=' . $newWord . '&fr=yfp-t&fp=1&toggle=1&cop=mss&ei=UTF-8',
            'https://www.bing.com/search?q=' . $newWord . '&qs=n&form=QBLH&sp=-1&pq=1&sc=8-1&sk=&cvid=748CC6D483DE4E51BEA8836D9CDF5C70',
            'http://www.baidu.com/s?ie=utf-8&f=8&rsv_bp=1&rsv_idx=1&ch=&tn=baidu&bar=&wd=' . $newWord .'&rn=&oq=&rsv_pq=ec41a0a9003d6984&rsv_t=fb51v4F3MNd4aRxgbSGtw%2B4fLUR9%2BJcWPv1frN%2BPdLEMmGtcya%2FHNxVv28c&rqlang=cn',
            'https://duckduckgo.com/?q=' . $newWord . '&t=h_&ia=web',
            'https://search.naver.com/search.naver?sm=top_hty&fbm=1&ie=utf8&query=' . $newWord,
            'https://www.sogou.com/web?query=' . $newWord . '&_asf=www.sogou.com&_ast=1570648272&w=01019900&p=40040100&ie=utf8&from=index-nologin&s_from=index&sut=1352&sst0=1570648271964&lkt=9%2C1570648270612%2C1570648271298&sugsuv=1570648269524531&sugtime=1570648271964',
            'https://www.qwant.com/?q=' . $newWord . '&t=web',
            'https://uk.ask.com/web?q=' . $newWord . '&qsrc=0&o=0&l=dir&qo=homepageSearchBox'
        ];
}