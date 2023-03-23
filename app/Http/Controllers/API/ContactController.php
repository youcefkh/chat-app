<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('XssSanitization', ['only' => ['store', 'update']]);
    }

    public function index()
    {
        $user = User::find(Auth::user()->id);
        return $user->contacts;
    }

    public function destroy(Contact $contact)
    {
        return $contact->delete();
    }
}
