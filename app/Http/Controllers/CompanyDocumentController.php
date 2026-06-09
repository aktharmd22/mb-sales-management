<?php

namespace App\Http\Controllers;

use App\Models\CompanyDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanyDocumentController extends Controller
{
    /** Stream a company PDF inline (any authenticated user). */
    public function view(CompanyDocument $document, string $mode = 'inline'): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        $disposition = $mode === 'download' ? 'attachment' : 'inline';

        return Storage::disk('local')->response($document->file_path, $document->file_name, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $document->file_name . '"',
        ]);
    }

    /**
     * Store an uploaded company PDF (admin only).
     * Plain multipart form POST — reliable on any server, no AJAX upload handshake.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'], // 20 MB
        ]);

        $file = $request->file('file');
        $path = $file->store('company', 'local');

        CompanyDocument::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
            'is_active' => true,
        ]);

        return redirect()->route('about.index')->with('flash', 'Document uploaded.');
    }
}
