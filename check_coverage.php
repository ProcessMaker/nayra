#!/usr/bin/env php
<?php
$coverage = new DomDocument();
$coverage->loadHTMLFile('coverage/index.html', LIBXML_NOERROR);
$xpath = new DOMXpath($coverage);

$percentage = $xpath->query('/html/body/div/div/table/tbody/tr[1]/td[3]/div')->item(0);
echo "Code coverage: {$percentage->nodeValue}\n";
if($percentage->nodeValue !== '100.00%') {
    exit(1);
}
