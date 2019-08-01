#!/usr/bin/env php
<?php
$coverage = new DomDocument();
$coverage->loadHTMLFile('coverage/index.html', LIBXML_NOERROR);
$xpath = new DOMXpath($coverage);

$percentage = $xpath->query('/html/body/div/table/tbody/tr[1]/td[3]/div')->item(0);
if($percentage->nodeValue !== '100.00%') {
    echo "Code coverage: {$percentage->nodeValue}\n";
    exit(1);
}
