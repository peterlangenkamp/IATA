<?php

session_start();

require '../vendor/autoload.php';

$_SESSION["id"] = $_POST["id"];
$_SESSION["name"] = $_POST["name"];
$_SESSION["description"] = $_POST["description"];

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

$config = file_get_contents(dirname(__FILE__) . '/config.json');
$config_data = json_decode($config, true);

$callbackUrl = $config_data["HOSTNAME"]."/authority.php?token=";

// Prepare
$credentialType = "OrgID";
$orgID = $config_data["SSI_SERVICE_MY_ORG_ID"];
$secretKey = InMemory::plainText($config_data["SSI_SERVICE_SHARED_SECRET"]);

// Issue Request
$data = (object) array('id' => $_POST["id"], 'name' => $_POST["name"], 'description' => $_POST["description"]);
// print_r($data);

$jti = bin2hex(random_bytes(12));
$now = new DateTimeImmutable();
$signer = new Sha256();
$config = Configuration::forSymmetricSigner($signer, $secretKey);
$jwt = $config->builder()
    ->identifiedBy($jti)
    ->issuedBy($orgID)
    ->permittedFor('ssi-service-provider')
    ->issuedAt($now)
    ->relatedTo('credential-issue-request')
    ->withClaim('type', $credentialType)
    ->withClaim('data', $data)
    ->withClaim('callbackUrl', $callbackUrl)
    ->getToken($config->signer(), $config->signingKey());
$issuetoken = $jwt->toString();

$url = $config_data["SSI_SERVICE_ENDPOINT"]."/issue/".$issuetoken;

header("Location: ".$url);

?>