<?php

session_start();

require '../vendor/autoload.php';

$_SESSION["cargo"] = $_POST["cargo"];
$_SESSION["tr_modality"] = $_POST["tr_modality"];
$_SESSION["load"] = $_POST["load"];
$_SESSION["unload"] = $_POST["load"];
$_SESSION["load"] = $_POST["load"];
$_SESSION["eta"] = $_POST["eta"];
$_SESSION["incident"] = $_POST["incident"];
$_SESSION["position"] = $_POST["position"];
$_SESSION["dep_tech"] = $_POST["dep_tech"];

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

$config = file_get_contents(dirname(__FILE__) . '/config.json');
$config_data = json_decode($config, true);

$callbackUrl = $config_data["HOSTNAME"]."/authority2.php?token=";

// Prepare
$credentialType = "OrgProfile";
$orgID = $config_data["SSI_SERVICE_MY_ORG_ID"];
$secretKey = InMemory::plainText($config_data["SSI_SERVICE_SHARED_SECRET"]);

// Issue Request
$data = (object) array('cargo' => $_POST["cargo"], 'tr_modality' => $_POST["tr_modality"], 'load' => $_POST["load"], 'unload' => $_POST["unload"], 'eta' => $_POST["eta"], 'incident' => $_POST["incident"], 'position' => $_POST["position"], 'dep_tech' => $_POST["dep_tech"]);

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