<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    public function testValidateRequired()
    {
        require_once __DIR__ . '/../src/Validation.php';
        $data = [];
        $rules = ['username' => ['required']];
        $errors = \Ondine\Validation::validate($data, $rules);
        $this->assertArrayHasKey('username', $errors);
    }

    public function testValidateMin()
    {
        require_once __DIR__ . '/../src/Validation.php';
        $data = ['password' => '123'];
        $rules = ['password' => ['min:6']];
        $errors = \Ondine\Validation::validate($data, $rules);
        $this->assertArrayHasKey('password', $errors);
    }
}
