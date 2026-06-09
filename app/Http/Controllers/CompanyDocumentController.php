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

        $request->validate([
            'title' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'mimes:pdf', 'max:20480'], // 20 MB each
        ], [
            'files.required' => 'Choose at least one PDF.',
            'files.*.mimes' => 'Every file must be a PDF.',
            'files.*.max' => 'Each PDF must be 20 MB or smaller.',
        ]);

        $files = $request->file('files');
        $single = count($files) === 1;

        foreach ($files as $file) {
            CompanyDocument::create([
                // Use the given title only when a single file is uploaded; otherwise the file name.
                'title' => ($single && filled($request->title))
                    ? $request->title
                    : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'description' => $request->description ?: null,
                'file_path' => $file->store('company', 'local'),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'uploaded_by' => $request->user()->id,
                'is_active' => true,
            ]);
        }

        $count = count($files);

        return redirect()->route('about.index')
            ->with('flash', $count . ' ' . str('document')->plural($count) . ' uploaded.');
    }
}
