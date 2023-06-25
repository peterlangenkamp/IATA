<?php

session_start();

require '../vendor/autoload.php';

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
            <h1><a href='<?php echo $config_data["HOSTNAME"]."/authority2.php";?>' style="color:black;text-decoration:none;">Organizational Profile</a></h1>
        </div>
        <div class="flex-item">
            <span class="logo">my<b>Authority</b></span>
        </div>
    </div>
    
    This is the Organizational Profile of <span class="logo" style="color:darkgreen;font-size:1rem;">my<b>NewNode</b></span><br/>
    <br>

    <form action="./authority2_submit.php" method="post">
        <p>
            <label for="cargo">Cargo</label>
            <input id="cargo" name="cargo" type="text" value="<?php echo isset($_SESSION['cargo']) ? $_SESSION['cargo'] : 'goods'; ?>"><br/>
        </p>
        <p>
            <label for="tr_modality">Transport Modality</label>
            <input id="tr_modality" name="tr_modality" type="text" value="<?php echo isset($_SESSION['tr_modality']) ? $_SESSION['tr_modality'] : 'air,road'; ?>"><br/>
        </p>
        <p>
            <label for="load">Load</label>
            <input id="load" name="load" type="text" value="<?php echo isset($_SESSION['load']) ? $_SESSION['load'] : 'yes'; ?>"><br/>
        </p>
        <p>
            <label for="unload">Unload</label>
            <input id="unload" name="unload" type="text" value="<?php echo isset($_SESSION['unload']) ? $_SESSION['unload'] : 'yes'; ?>"><br/>
        </p>
        <p>
            <label for="eta">ETA</label>
            <input id="eta" name="eta" type="text" value="<?php echo isset($_SESSION['eta']) ? $_SESSION['eta'] : '-'; ?>"><br/>
        </p>
        <p>
            <label for="incident">Incident</label>
            <input id="incident" name="incident" type="text" value="<?php echo isset($_SESSION['incident']) ? $_SESSION['incident'] : 'no'; ?>"><br/>
        </p>
        <p>
            <label for="position">Position</label>
            <input id="position" name="position" type="text" value="<?php echo isset($_SESSION['position']) ? $_SESSION['position'] : '-'; ?>"><br/>
        </p>
        <p>
            <label for="dep_tech">Deployment Technology</label>
            <input id="dep_tech" name="dep_tech" type="text" value="<?php echo isset($_SESSION['dep_tech']) ? $_SESSION['dep_tech'] : 'OpenAPI,RDF'; ?>"><br/>
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