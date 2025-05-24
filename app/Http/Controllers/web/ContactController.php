<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index() {
        $compacts = [
        ];
        return view('source.web.contacts.index',$compacts);
    }
}
