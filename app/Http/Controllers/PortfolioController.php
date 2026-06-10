<?php

namespace App\Http\Controllers;

use App\Models\PortfolioItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PortfolioController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $this->validateItem($request, null);
        $type = $data['type'];

        $item = new PortfolioItem();
        $item->type = $type;
        $item->title = $data['title'];
        $item->description = $data['description'] ?? null;
        $item->url = $data['url'] ?? null;
        $item->credentials = $type === 'website' ? $this->cleanCredentials($request) : null;
        $item->uploaded_by = $request->user()->id;
        $item->is_active = true;

        if ($request->hasFile('image')) {
            $item->image_path = $request->file('image')->store('portfolio', 'public');
        }

        $item->save();

        return redirect()->route('portfolio.index', ['tab' => $type])->with('flash', PortfolioItem::label($type) . ' item added.');
    }

    public function update(Request $request, PortfolioItem $item): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $this->validateItem($request, $item);

        $item->title = $data['title'];
        $item->description = $data['description'] ?? null;
        $item->url = $data['url'] ?? null;
        if ($item->type === 'website') {
            $item->credentials = $this->cleanCredentials($request);
        }

        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $item->image_path = $request->file('image')->store('portfolio', 'public');
        }

        $item->save();

        return redirect()->route('portfolio.index', ['tab' => $item->type])->with('flash', 'Item updated.');
    }

    public function destroy(Request $request, PortfolioItem $item): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $type = $item->type;

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }
        // Remove gallery image files too (DB rows cascade automatically).
        foreach ($item->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }
        $item->delete();

        return redirect()->route('portfolio.index', ['tab' => $type])->with('flash', 'Item removed.');
    }

    /** Add one or more images to an item (used by Automations). */
    public function addImages(Request $request, PortfolioItem $item): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'max:8192'], // 8 MB each
        ], [
            'images.required' => 'Choose at least one image.',
            'images.*.image' => 'Every file must be an image.',
            'images.*.max' => 'Each image must be 8 MB or smaller.',
        ]);

        $start = (int) $item->images()->max('sort_order');
        foreach ($request->file('images') as $i => $file) {
            $item->images()->create([
                'image_path' => $file->store('portfolio', 'public'),
                'sort_order' => $start + $i + 1,
            ]);
        }

        return redirect()->route('portfolio.index', ['tab' => $item->type, 'open' => $item->id])
            ->with('flash', 'Images added.');
    }

    /** Remove a single image from an item. */
    public function destroyImage(Request $request, \App\Models\PortfolioImage $image): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $item = $image->item;
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return redirect()->route('portfolio.index', ['tab' => $item->type, 'open' => $item->id])
            ->with('flash', 'Image removed.');
    }

    private function validateItem(Request $request, ?PortfolioItem $item): array
    {
        $type = $item->type ?? $request->input('type');
        $hasExistingImage = $item && $item->image_path;
        $urlProvided = filled($request->input('url'));

        // Graphics need an image OR an Instagram URL. Automations hold multiple
        // images added afterwards, so they need none at creation.
        $imageRequired = match ($type) {
            'graphic' => ! $hasExistingImage && ! $urlProvided,
            default => false,
        };

        $messages = [
            'image.required' => $type === 'graphic'
                ? 'Add an image or an Instagram link.'
                : 'An image is required.',
        ];

        return $request->validate([
            'type' => [$item ? 'nullable' : 'required', Rule::in(array_keys(PortfolioItem::TYPES))],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            // Required for websites, videos & articles; optional Instagram link for graphics.
            'url' => [in_array($type, ['website', 'video', 'article']) ? 'required' : 'nullable', 'url', 'max:500'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'max:8192'], // 8 MB
        ], $messages);
    }

    /** Keep only credential rows that actually have something filled in. */
    private function cleanCredentials(Request $request): array
    {
        return collect($request->input('credentials', []))
            ->map(fn ($row) => [
                'label' => trim($row['label'] ?? ''),
                'username' => trim($row['username'] ?? ''),
                'password' => trim($row['password'] ?? ''),
                'url' => trim($row['url'] ?? ''),
            ])
            ->filter(fn ($row) => $row['label'] !== '' || $row['username'] !== '' || $row['password'] !== '')
            ->values()
            ->all();
    }
}
