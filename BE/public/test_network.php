<?php

echo "<h1>Testing Network Connectivity from Production Server</h1>";

$host = "ai.mindhub.io.vn";
echo "Resolving host $host...<br/>";
$ip = gethostbyname($host);
echo "IP Address resolved: $ip<br/>";

echo "Checking port 443 connectivity to $host...<br/>";
$fp = @fsockopen($host, 443, $errno, $errstr, 5);
if (!$fp) {
    echo "Connection failed: $errstr ($errno)<br/>";
} else {
    echo "Connection successful!<br/>";
    fclose($fp);
}
