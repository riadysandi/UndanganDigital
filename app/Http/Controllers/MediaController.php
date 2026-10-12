<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Upload foto / file media (cover, mempelai, galeri, atau musik) untuk undangan digital.
     */
    public function upload(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpeg,jpg,png,webp,gif,mp3,ogg,wav,m4a,mpga', 'max:15360'], // Max 15MB
            'type' => ['nullable', 'string', 'in:cover,groom,bride,gallery,music,qris'],
        ]);

        $file = $request->file('file');
        $type = $request->input('type', 'gallery');

        // Beri nama unik dan simpan ke disk public
        $filename = $type . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $directory = "invitations/{$invitation->id}";
        $path = $file->storeAs($directory, $filename, 'public');

        $url = asset("storage/{$path}");

        return response()->json([
            'success' => true,
            'url' => $url,
            'path' => $path,
            'filename' => $filename,
            'type' => $type,
        ]);
    }
}
