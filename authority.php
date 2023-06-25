<?php

session_start();

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
$signer = new Sha256();
$config = Configuration::forSymmetricSigner($signer, $secretKey);

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
    <link rel="icon" href="favicon_orange.png" type="image/x-icon">
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

    This is the Organization ID of <span class="logo" style="color:darkgreen;font-size:1rem;">my<b>NewNode</b></span><br/>
    <br>

    <form action="./authority_submit.php" method="post">
        <p>
            <label for="id">ID</label>
            <input id="id" name="id" type="text" value="<?php echo isset($_SESSION['id']) ? $_SESSION['id'] : 1; ?>"><br/>
        </p>
        <p>
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="<?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'TNO'; ?>"><br/>
        </p>
        <p>
            <label for="description">Description</label>
            <input id="description" name="description" type="text" value="<?php echo isset($_SESSION['description']) ? $_SESSION['description'] : 'Netherlands Organization for Applied Scientific Research'; ?>"><br/>
        </p>
        <br>
        <input type="submit" value="Store in wallet">
    </form>

    <br>

    <?php if(isset($status) && $status == "success"):?>
        <em>The credential has been stored in your wallet!</em>
    <?php endif; ?>
</body>
</html>