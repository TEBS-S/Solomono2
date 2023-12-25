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

// Recursive function to add all children to the parent

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
$results['recusive'] = getChildrenForParent($result);

//show script work time
$time['recurcive'] = (microtime(true) - $startTime);


$startTime = microtime(true);

//Do the same request to the DB to compare two methods in a whole work
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

$results['nonrecusive'] = array();
foreach ($result as $item => $parent) {
    if ($parent == 0) {
        $results['nonrecusive'][$parent][$item] = $item;
    } else {
        //Build parents tree
        $parent_tree = array($parent);
        $tmp_parent = $parent;
        while ($tmp_parent > 0) {
            $tmp_parent = $result[$tmp_parent];
            $parent_tree[] = $tmp_parent;
        }
        //use reversed parents tree to set link to the nearest parent of the item
        $tmp_link = &$results['nonrecusive'];
        foreach (array_reverse($parent_tree) as $tmp_parent) {
            $tmp_link = &$tmp_link[$tmp_parent];
        }
        //if link is not an array - make him an array; v.v. just add the item
        if (is_array($tmp_link)) {
            $tmp_link[$item] = $item;
        } else {
            $tmp_link = array($item => $item);
        }
    }
}
$time['nonrecurcive'] = (microtime(true) - $startTime);
//show script work time and small analytics

echo 'Recursive time is ' . number_format($time['recurcive'], 5) .
    '<br>Non-recusrive time is ' . number_format($time['nonrecurcive'], 5) .
    '<br>which is better in &asymp;' . number_format($time['recurcive'] / $time['nonrecurcive']) . ' times';

echo '<br>results are the same? ';
var_dump($results['recusive'] == $results['nonrecusive'][0]);
//show results
echo '<pre>';
print_r($results['nonrecusive'][0]);
echo '</pre>';