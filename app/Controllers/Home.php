<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $data['title'] = 'Home Page';
        return view('index', $data); // home.php sa Views
    }

    public function about()
    {
        $data['title'] = 'About Us';
        return view('about', $data); // about.php sa Views
    }

    public function contact()
    {
        $data['title'] = 'Contact Page';
        return view('contact', $data); // contact.php sa Views
    }
}
