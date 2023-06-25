<?php

require '../vendor/autoload.php';

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

$config = file_get_contents(dirname(__FILE__) . '/config.json');
$config_data = json_decode($config, true);

$callbackUrl = $config_data["HOSTNAME"]."/networknode.php?token=";

// Prepare
$credentialType = "OrgID";
$orgID = $config_data["SSI_SERVICE_MY_ORG_ID"];
$secretKey = InMemory::plainText($config_data["SSI_SERVICE_SHARED_SECRET"]);

// Verify Request
$jti = bin2hex(random_bytes(12));
$now = new DateTimeImmutable();
$signer = new Sha256();
$config = Configuration::forSymmetricSigner($signer, $secretKey);
$jwt = $config->builder()
    ->identifiedBy($jti)
    ->issuedBy($orgID)
    ->permittedFor('ssi-service-provider')
    ->issuedAt($now)
    ->relatedTo('credential-verify-request')
    ->withClaim('type', $credentialType)
//    ->withClaim('predicates', (object) null)
    ->withClaim('callbackUrl', $callbackUrl)
    ->getToken($config->signer(), $config->signingKey());
$verifytoken = $jwt->toString();


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
    <title>NetworkNode</title>
    <link rel="icon" href="favicon_blue.png" type="image/x-icon">
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
            color: steelblue;
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
            <h1><a href='<?php echo $config_data["HOSTNAME"]."/networknode.php";?>' style="color:black;text-decoration:none;">Connect and Authenticate</a></h1>
        </div>
        <div class="flex-item">
            <span class="logo">my<b>NetworkNode</b></span>
        </div>
    </div>

    <?php if(!isset($status) || ($status !== "success")):?>
        <!-- <em>Please provide your OrgID credential.</em> -->
        <em>Welcome! Please set up a connection and provide your OrgID credential to authenticate.</em>
        <br>
        <br>
        <a href="<?php echo $config_data["SSI_SERVICE_ENDPOINT"];?>/verify/<?php echo $verifytoken;?>">
            <button>Continue</button>
        </a>
    <?php endif; ?>

    <?php if(isset($status) && $status == "success"):?>
        <em>Thank you for sharing.</em><br/>
        <em>We have received the following information.</em>
        <br>
        <br>
        <form action="">
            <p>
                <label for="id">ID</label>
                <input id="id" name="id" type="text" value="<?php echo $data['id'];?>" disabled="disabled"><br/>
            </p>
            <p>
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="<?php echo $data['name'];?>" disabled="disabled"><br/>
            </p>
            <p>
                <label for="description">Description</label>
                <input id="description" name="description" type="text" value="<?php echo $data['description'];?>" disabled="disabled"><br/>
            </p>
        </form>
    <?php endif; ?>
</body>
</html>
