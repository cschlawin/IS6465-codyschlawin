<?php

// calling a function
function formatName($first, $middle, $last) 
{ 
    $first = ucfirst(strtolower($first)); 
    $middle = ucfirst(strtolower($middle)); 
    $last = ucfirst(strtolower($last)); 
    
    return $first . " " . $middle . " " . $last; 
} 

$displayName = formatName("mARIA", "lee", "CHEN"); 
echo $displayName; // Maria Lee Chen
echo '<br>';

// returning an array
function cleanNameParts($first, $middle, $last) 
{ 
    return [ 
        ucfirst(strtolower($first)), 
        ucfirst(strtolower($middle)), 
        ucfirst(strtolower($last)) ]; 
    } 

    $nameParts = cleanNameParts("mARIA", "lee", "CHEN"); 
echo $nameParts[0]; // Maria
echo '<br>';


// Build Full Name 1
$first = "Maria"; $middle = "Lee"; $last = "Chen"; 
function buildFullName() 
{ 
    global $first, $middle, $last; 
    return "$first $middle $last"; 
} 
echo buildFullName();
echo '<br>';

function buildFullName2($first, $middle, $last) 
{ 
    return "$first $middle $last"; 
} 
echo buildFullName2($first, $middle, $last);
echo '<br>';


//static variables
function nextTicketNumber() 
{ 
    static $number = 0; 
    $number++; 
    return $number; 
} 

echo nextTicketNumber(); // 1 
echo nextTicketNumber(); // 2
echo '<br>';


//class
class User
{
    public $name;
    public $email;

    public function display()
    {
        return 'User: ' . $this->name;
    }
}

$user = new User('Maria Chen', 'maria@example.com');
$user->name = 'Maria Chen';
$user->email = 'maria@example.com';

echo $user->display();
echo '<br>';


class User2
{
    public $name;
    private $passwordHash;

    public function __construct($name, $password)
    {
        $this->name = $name;
        $this->passwordHash = 
            password_hash($password, PASSWORD_DEFAULT);
    }
    public function display()
    {
        return 'User: ' . $this->name;
    }
}

$user = new User2('Maria Chen', 'temporary-password');
print_r($user);
echo '<br>';

?>