<?php

session_start();

require '../vendor/autoload.php';

$config = file_get_contents(dirname(__FILE__) . '/config.json');
$config_data = json_decode($config, true);

$callbackUrl = $config_data["HOSTNAME"]."/newnode.php?token=";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NewNode</title>
    <link rel="icon" href="favicon_green.png" type="image/x-icon">
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
            color: darkgreen;
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
            <h1><a href='<?php echo $config_data["HOSTNAME"]."/newnode.php";?>' style="color:black;text-decoration:none;">Acquire VCs</a></h1>
        </div>
        <div class="flex-item">
            <span class="logo">my<b>NewNode</b></span>
        </div>
    </div>

    Visit <span class="logo" style="color:darkorange;font-size:1rem;">my<b>Authority</b></span> to obtain the required credentials
    <ul>
        <li>
            <a href="./authority.php">Organization ID</a>
        </li>
        <li>
            <a href="./authority2.php">Organizational Profile</a>
        </li>
    </ul>

    <br>

    <h1>Connections</h1>
    Establish a connection with <span class="logo" style="color:steelblue;font-size:1rem;">my<b>NetworkNode</b></span>?<br/>
    <br>
    <a href="./networknode.php">
        <button>Continue</button>
    </a>
    <br>

    <h2>Share Profile</h2>
    Share Organizational Profile with <span class="logo" style="color:steelblue;font-size:1rem;">my<b>NetworkNode</b></span>?<br/>
    <br>
    <a href="./networknode2.php">
        <button>Continue</button>
    </a>
    <br>

    <!-- <?php if(isset($status) && $status == "success"):?>
        <em>The credential has been stored in your wallet!</em>
    <?php endif; ?> -->
</body>
</html>