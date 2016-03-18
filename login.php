<?php
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


$CLIENT_ID = "############"; // -> YOUR REDDIT APP ID
$TYPE = "code";
$RANDOM_STRING = generateRandomString();
$URI = "http://###############.###/~fnmps/LabelDataset/labeling.php";# -> YOUR REDDIT APP REDIRECT URL
$DURATION = "temporary";
$SCOPE_STRING = "identity history";

header("Location:https://www.reddit.com/api/v1/authorize?client_id=". $CLIENT_ID ."&response_type=".$TYPE."&state=" . $RANDOM_STRING."&redirect_uri=" . $URI . "&duration=" . $DURATION . "&scope=" . $SCOPE_STRING);

?>

