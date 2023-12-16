<?php
//Set start time of running the script
$startTime = microtime(true);
/*Get SQL DB parameters from config file to the $SQL variable with actual data
$SQL = array(
    'host' => '',
    'user' => '',
    'pass' => '',
    'dbName' => ''
);
*/
require_once 'config.php';

$DB = new mysqli($SQL['host'], $SQL['user'], $SQL['pass'], $SQL['dbName']);

//Create SQL Query
$query = 'SELECT SQL_NO_CACHE * FROM categories';

//Get query result to assoc array
$queryResult = $DB->query($query);
$queryArray = $queryResult->fetch_all(MYSQLI_ASSOC);

//Make result array usable for next steps
$result = array();
foreach ($queryArray as $row) {
    $result[$row['categories_id']] = $row['parent_id'];
}

/*
 * Recursive function to add all children to the parent
 */
function getChildrenForParent($inputArray, $parentID = 0): array
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
//Call recursive function
$result = getChildrenForParent($result);
echo '<pre>';
print_r($result);
echo '</pre>';
//echo json_encode($result, JSON_PRETTY_PRINT);
//show script work time
echo '<hr>' . (microtime(true) - $startTime);