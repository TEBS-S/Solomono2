<?php
$startTime = microtime(true);
require_once 'config.php';

$DB = new mysqli($SQL['host'], $SQL['user'], $SQL['pass'], $SQL['dbName']);

$query = 'SELECT SQL_NO_CACHE * FROM categories';

$queryResult = $DB->query($query);
$queryArray = $queryResult->fetch_all(MYSQLI_ASSOC);

$result = array();
foreach ($queryArray as $row) {
    $result[$row['categories_id']] = $row['parent_id'];
}

function getChildrenForParent($inputArray, $parentID = 0)
{
    $arrayParent = array();
    foreach ($inputArray as $itemCat => $itemParent) {
        if ($parentID == $itemParent) {
            $arrayChild = getChildrenForParent($inputArray, $itemCat);
            if (!empty($arrayChild)) {
                $arrayParent[$itemCat] = $arrayChild;
            } else {
                $arrayParent[$itemCat] = $itemCat;
            }
        }
    }
    return $arrayParent;
}

$result = getChildrenForParent($result);
echo '<pre>';
print_r($result);
echo '</pre>';
//echo json_encode($result, JSON_PRETTY_PRINT);

echo '<hr>' . (microtime(true) - $startTime);