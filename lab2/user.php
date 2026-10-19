<?php

function cleanText($value)
{
    return trim($value);
}

class User
{
    public const STATUS_ACTIVE = 'active';
    public $name;
    public $email;
    public $status;
    
    public function __construct($name, $email)
    {
        $this->name = cleanText($name);
        $this->email = strtolower(cleanText($email));
        $this->status = self::STATUS_ACTIVE;
    }
}


class Tutor extends User
{
    public $subject;

    public function __construct($name, $email, $subject)
    {
        parent::__construct($name, $email);
        $this->subject = cleanText($subject);
    }
    
    public function profileSummary()
    {
        return $this->name . ' (' . $this->email . ')' .
            ' -Tutor for ' . $this->subject;
    }
}

$tutor = new Tutor(' Jordan Lee ', 'JLEE@EXAMPLE.COM ', 'PHP');
echo $tutor->profileSummary();




$gpa = 3.8;
if ($gpa >= 3.9) {
    echo "Full scholarship";
} elseif ($gpa >= 3.7) {
    echo "Partial scholarship";
} else {
    echo "No scholarship";
}













?>
