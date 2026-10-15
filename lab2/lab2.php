lab2.php

<?php 

//require_once 'lab2B.php';

// expression
$x = -5;
$y = 3 * (abs(2 * $x) +4);
echo $y;
echo '<br>';

//boolean
var_dump(2 > 1);    // true
var_dump(2 == 3);   // false
var_dump("5" == 5);   // true
var_dump("5" === 5);  // false
echo '<br>';

//unary operators
$c = 5;
$d = -$c; // unary minus
$e = +$c;
$f = ++$c; // unary plus
echo $d;
echo '<br>';
echo $e;
echo '<br>';
echo $f;
echo '<br>';

//precedence
$a = 2 + 3 * 4;     // 14, multiplication has higher precedence than addition
$b = (2 + 3) * 4;   // 20, parentheses change the order or operations
echo $a;
echo '<br>';
echo $b;
echo '<br>';

//associativity
$level = $score = $score = $time = 0;
echo $level;
echo '<br>';
echo $score;
echo '<br>';
echo $time;
echo '<br>';

//equality and assignment
$a = 5;
$b = 5;
var_dump($a == $b);     // true
var_dump($a === $b);    // true
$c = 10;
$d = 20;
var_dump($c == $d);     // false
var_dump($c === $d);    // false


//logical operators
$a = 1;
$b = 0;
var_dump($a && $b);     // false
var_dump($a || $b);     // true
var_dump(!$a);          // false
var_dump(!$b);          // true

//if
$gpa = 3.59;

if ($gpa >= 3.7) {
    echo "Student gets scholarship";    
}elseif ($gpa >= 3.5) {
    echo "Student gets partial scholarship";
}else {
    echo "Student does not get scholarship";
}
    echo '<br>';

//switch
$status = "pending";
switch ($status) {
    case "approved":
    echo "Access granted";
    break;
    case "pending":
    echo "Review in progress";
    break;
    default:
    echo "Access denied";
    break;
}
echo '<br>';

//while
$fuel = 10;
while ($fuel > 1) {
    echo "There is enough fuel<br>";
    $fuel--;
}

echo '<br>';

//for
for ($count = 1; $count <= 12; $count++) {
echo "Count: $count times 12 is " .
($count * 12) . "<br>";
}
echo '<br>';



?>