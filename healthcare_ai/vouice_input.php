<?php
require 'vendor/autoload.php';
use kornrunner\Ethereum;

$privateKey = "your_ethereum_private_key";
$transactionData = json_encode(["patient_id" => 123, "diagnosis" => "Malaria"]);

$eth = new Ethereum("https://mainnet.infura.io/v3/YOUR_INFURA_PROJECT_ID");
$txHash = $eth->sendTransaction($privateKey, "0xRecipientAddress", $transactionData);

echo "Blockchain Transaction ID: " . $txHash;
?>
