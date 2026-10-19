<?php

$gpa = 3.8;
if ($gpa >= 3.9) {
    echo "Full scholarship";
} elseif ($gpa >= 3.7) {
    echo "Partial scholarship";
} else {
    echo "No scholarship";
}
echo "<br>";

function formatCourse($courseCode, $courseTitle) 
{
    return $courseCode . ": " . $courseTitle;
}

echo formatCourse('IS 6465', 'Web Application Development');
echo "<br>";


function nextTicketNumber() 
{
    static $number = 0;
    $number++;
    return $number;
}

echo nextTicketNumber(); // 1
echo nextTicketNumber(); // 2
echo "<br>";


class Student 
{
    public $name;

    public function __construct($name) 
{
    $this->name = $name;
}

    public function greet() 
{
        return "Hello, " . $this->name;
    }
}

$student = new Student("Maria");
echo $student->greet();
?>