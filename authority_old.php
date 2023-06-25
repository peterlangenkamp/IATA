<?php

require '../vendor/autoload.php';

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
$data = (object) array('id' => "1", 'name' => "TNO", 'description' => "Netherlands Organization for Applied Scientific Research");

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


// Issue or Verify Response
if(isset($_GET['token'])) {
    $responsetoken = $config->parser()->parse((string) htmlspecialchars($_GET['token'])); // Parses from a string
    
    $status = $responsetoken->claims()->get('status');
    $sub = $responsetoken->claims()->get('sub');
    
    if ($status == "success" && $sub == "credential-verify-response") {
        $data = $responsetoken->claims()->get('data');
    }   
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authority</title>
    <style>
        .container{
            display: flex;
        }
        .fixed {
            width: 500px;
        }
        .flex-item {
            flex-grow: 1;
        }
        .logo {
            color: darkorange;
            font-family: arial;
            font-size: 32px;
        }
        form {
            border: solid 1px grey;
            display: table;
            padding: 1em 0.5em 1em 0.5em;
        }
        p {
            display: table-row;
        }
        label {
            display: table-cell;
            padding-right: 1em;
        }
        input {
            display: table-cell;
            margin-bottom: 0.3em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="fixed">
            <h1><a href='<?php echo $config_data["HOSTNAME"]."/authority.php";?>' style="color:black;text-decoration:none;">Organization ID</a></h1>
        </div>
        <div class="flex-item">
            <span class="logo">my<b>Authority</b></span>
        </div>
    </div>

    <form action="">
        <p>
            <label for="id">ID</label>
            <input id="id" type="id" value="1" disabled="disabled"><br/>
        </p>
        <p>
            <label for="name">Name</label>
            <input id="name" type="name" value="TNO" disabled="disabled"><br/>
        </p>
        <p>
            <label for="description">Description</label>
            <input id="description" type="description" value="Netherlands Organization for Applied Scientific Research" disabled="disabled"><br/>
        </p>
        <br>
        <a href="<?php echo $config_data["SSI_SERVICE_ENDPOINT"];?>/issue/<?php echo $issuetoken;?>">
        <button type="button">Store in wallet</button>
        </a>
    </form>

    <br>

    <?php if(isset($status) && $status == "success"):?>
        <em>The credential has been stored in your wallet!</em>
    <?php endif; ?>
</body>
</html>