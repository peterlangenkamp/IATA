<?php

require '../vendor/autoload.php';

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

$config = file_get_contents(dirname(__FILE__) . '/config.json');
$config_data = json_decode($config, true);

$callbackUrl = $config_data["HOSTNAME"]."/networknode2.php?token=";

// Prepare
$credentialType = "OrgProfile";
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

// Set own data
$keys = ["cargo", "tr_modality", "load", "unload", "eta", "incident", "position", "dep_tech"];

$owndata['cargo'] = "goods,equipment,containers";
$owndata['tr_modality'] = "rail,air";
$owndata['load'] = "yes";
$owndata['unload'] = "yes";
$owndata['eta'] = "-";
$owndata['incident'] = "no";
$owndata['position'] = "-";
$owndata['dep_tech'] = "OpenAPI";

if(isset($status) && $status == "success"){
    foreach($keys as $key){
        $datamatch[$key] = ($owndata[$key] == $data[$key] || !empty(array_intersect(explode(",", $owndata[$key]), explode(",", $data[$key])))) ? true : false;
        $intersect[$key] = implode(",", array_intersect(explode(",", $owndata[$key]), explode(",", $data[$key])));
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
        tbody td {
            text-align: center;
        }
        th, td {
            padding: 4px 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="fixed">
            <h1><a href='<?php echo $config_data["HOSTNAME"]."/networknode2.php";?>' style="color:black;text-decoration:none;">Organizational Profile</a></h1>
        </div>
        <div class="flex-item">
            <span class="logo">my<b>NetworkNode</b></span>
        </div>
    </div>

    <?php if(!isset($status) || ($status !== "success")):?>
        <!-- <em>Please provide your OrgProfile credential.</em> -->
        <em>Please connect and provide your OrgProfile credential to match capabilities.</em>
        <br>
        <br>
        <a href="<?php echo $config_data["SSI_SERVICE_ENDPOINT"];?>/verify/<?php echo $verifytoken;?>">
            <button>Share credential</button>
        </a>
        <br>
        <br>
    <?php endif; ?>

    <?php if(isset($status) && $status == "success"):?>
        <em>Thank you for sharing.</em><br/>
        <?php foreach ($keys as $key):
            if ($owndata[$key] != $data[$key] && !array_intersect(explode(",", $owndata[$key]), explode(",", $data[$key]))):?>
                <em style="color:red;">Unfortunately one or more capabilities and/or events do not match!</em>
            <?php 
                    $flag = true;
                    break;
                endif;
            ?>
        <?php endforeach;?>
        <?php if(!isset($flag)):?>
            <em style="color:green;">The capabilties and events are a match, you are good to go!</em>
        <?php endif;?>
        <br>
        <br>
    <?php endif; ?>

    <table>
            <thead>
                <tr>
                    <th>Capability</th>
                    <th><span class="logo" style="color:steelblue;font-size:1rem;">my<b>NetworkNode</b></span></th>
                    <th><span class="logo" style="color:darkgreen;font-size:1rem;">my<b>NewNode</b></span></th>
                    <?php if(isset($status)):?>
                        <th>Common Profile</td>
                    <?php endif;?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Cargo</td>
                    <td><?php echo $owndata['cargo']; ?></td>
                    <td><?php echo isset($data['cargo']) ? $data['cargo']: ""; ?></td>
                    <?php if(isset($datamatch) && !$datamatch['cargo']):?>
                        <td style="color:red">&#x2717;<td>
                    <?php elseif(isset($status) && $datamatch['cargo']):?>
                        <!-- <td style="color:green">&#x2713;</td> -->
                        <td style="color:green"><?php echo $intersect['cargo'];?></td>
                    <?php else:?>
                        <td></td>
                    <?php endif;?>
                </tr>
                <tr>
                    <td>Transport Modality</td>
                    <td><?php echo $owndata['tr_modality']; ?></td>
                    <td><?php echo isset($data['tr_modality']) ? $data['tr_modality']: ""; ?></td>
                    <?php if(isset($status) && !$datamatch['tr_modality']):?>
                        <td style="color:red">&#x2717;<td>
                    <?php elseif(isset($status) && $datamatch['tr_modality']):?>
                        <!-- <td style="color:green">&#x2713;</td> -->
                        <td style="color:green"><?php echo $intersect['tr_modality'];?></td>
                    <?php else:?>
                        <td></td>
                    <?php endif;?>
                </tr>
                <tr>
                    <td>Load</td>
                    <td><?php echo $owndata['load']; ?></td>
                    <td><?php echo isset($data['load']) ? $data['load']: ""; ?></td>
                    <?php if(isset($status) && !$datamatch['load']):?>
                        <td style="color:red">&#x2717;<td>
                    <?php elseif(isset($status) && $datamatch['load']):?>
                        <!-- <td style="color:green">&#x2713;</td> -->
                        <td style="color:green"><?php echo $intersect['load'];?></td>
                    <?php else:?>
                        <td></td>
                    <?php endif;?>
                </tr>
                <tr>
                    <td>Unload</td>
                    <td><?php echo $owndata['unload']; ?></td>
                    <td><?php echo isset($data['unload']) ? $data['unload']: ""; ?></td>
                    <?php if(isset($status) && !$datamatch['unload']):?>
                        <td style="color:red">&#x2717;<td>
                    <?php elseif(isset($status) && $datamatch['unload']):?>
                        <!-- <td style="color:green">&#x2713;</td> -->
                        <td style="color:green"><?php echo $intersect['unload'];?></td>
                    <?php else:?>
                        <td></td>
                    <?php endif;?>
                </tr>
                <tr>
                    <td>ETA</td>
                    <td><?php echo $owndata['eta']; ?></td>
                    <td><?php echo isset($data['eta']) ? $data['eta']: ""; ?></td>
                    <?php if(isset($status) && !$datamatch['eta']):?>
                        <td style="color:red">&#x2717;<td>
                    <?php elseif(isset($status) && $datamatch['eta']):?>
                        <!-- <td style="color:green">&#x2713;</td> -->
                        <td style="color:green"><?php echo $intersect['eta'];?></td>
                    <?php else:?>
                        <td></td>
                    <?php endif;?>
                </tr>
                <tr>
                    <td>Incident</td>
                    <td><?php echo $owndata['incident']; ?></td>
                    <td><?php echo isset($data['incident']) ? $data['incident']: ""; ?></td>
                    <?php if(isset($status) && !$datamatch['incident']):?>
                        <td style="color:red">&#x2717;<td>
                    <?php elseif(isset($status) && $datamatch['incident']):?>
                        <!-- <td style="color:green">&#x2713;</td> -->
                        <td style="color:green"><?php echo $intersect['incident'];?></td>
                    <?php else:?>
                        <td></td>
                    <?php endif;?>
                </tr>
                <tr>
                    <td>Position</td>
                    <td><?php echo $owndata['position']; ?></td>
                    <td><?php echo isset($data['position']) ? $data['position']: ""; ?></td>
                    <?php if(isset($status) && !$datamatch['position']):?>
                        <td style="color:red">&#x2717;<td>
                    <?php elseif(isset($status) && $datamatch['position']):?>
                        <!-- <td style="color:green">&#x2713;</td> -->
                        <td style="color:green"><?php echo $intersect['position'];?></td>
                    <?php else:?>
                        <td></td>
                    <?php endif;?>
                </tr>
                <tr>
                    <td>Deployment Technology</td>
                    <td><?php echo $owndata['dep_tech']; ?></td>
                    <td><?php echo isset($data['dep_tech']) ? $data['dep_tech']: ""; ?></td>
                    <?php if(isset($status) && !$datamatch['dep_tech']):?>
                        <td style="color:red">&#x2717;<td>
                    <?php elseif(isset($status) && $datamatch['dep_tech']):?>
                        <!-- <td style="color:green">&#x2713;</td> -->
                        <td style="color:green"><?php echo $intersect['dep_tech'];?></td>
                    <?php else:?>
                        <td></td>
                    <?php endif;?>
                </tr>
            </tbody>
        </table>
</body>
</html>
