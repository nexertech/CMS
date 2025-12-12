<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpController extends Controller
{
    /**
     * Display the help & support page.
     */
    public function index(): View
    {
        return view('admin.help.index');
    }

    /**
     * Display FAQ section.
     */
    public function faq(): View
    {
        return view('admin.help.faq');
    }

    /**
     * Display documentation.
     */
    public function documentation(): View
    {
        return view('admin.help.documentation');
    }

    /**
     * Display contact support.
     */
    public function contact(): View
    {
        return view('admin.help.contact');
    }

    /**
     * Submit support ticket.
     */
    public function submitTicket(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|string',
            'priority' => 'required|string',
            'message' => 'required|string|min:10',
        ]);

        // Here you would typically save the ticket to database
        // For now, we'll just return success
        
        return redirect()->route('admin.help.contact')
            ->with('success', 'Support ticket submitted successfully! We will get back to you soon.');
    }
}
