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

    public function store(Request $request)
    {
        return Contact::create([
            'user_id' => Auth::user()->id,
            'contact_id' => $request->id
        ]);
    }

    public function show($id)
    {
        return;
    }

    public function destroy(Contact $contact)
    {
        return $contact->delete();
    }

    /**
     * search users by name to add to contacts
     */
    public function search(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $contacts = $user->contacts;
        $existingContactsIds = $contacts->pluck('id');
        $suggestions = User::where('name', 'LIKE', "%$request->search%")->whereNotIn('id', $existingContactsIds)->get();
        
        return $suggestions;
    }
}
