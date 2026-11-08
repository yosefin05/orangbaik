<?php

namespace App\Http\Controllers;

use App\Models\Penggalang_Dana_Dokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DokumenPenggalangController extends Controller
{
    /**
     * Endpoint terproteksi untuk melihat/mengunduh dokumen penggalang dana.
     * Otorisasi ketat:
     * - Admin: Boleh akses semua dokumen.
     * - Pemilik Penggalang Dana: Hanya boleh akses dokumen miliknya.
     * - Publik/Guest: HANYA dokumen organisasi yang explicitly publik. KTP/Individu DITOLAK (403).
     */
    public function show(Request $request, $id)
    {
        $dokumen = Penggalang_Dana_Dokumen::with('penggalangDana')->findOrFail($id);
        $user = Auth::user();

        // Verifikasi hak akses
        if (!$dokumen->canBeAccessedBy($user)) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Dokumen ini bersifat privat dan hanya dapat diakses oleh pemilik atau administrator.');
        }

        $filePath = $dokumen->file_dokumen;

        // 1. Jika URL eksternal (misal: Google Drive, Cloud Storage, atau HTTPS URL)
        if (filter_var($filePath, FILTER_VALIDATE_URL)) {
            // Validasi skema hanya http/https (mencegah javascript: atau file: URI scheme)
            $scheme = parse_url($filePath, PHP_URL_SCHEME);
            if (!in_array(strtolower($scheme), ['http', 'https'], true)) {
                abort(Response::HTTP_BAD_REQUEST, 'Skema URL dokumen tidak valid.');
            }

            // Redirect aman tanpa meneruskan referrer
            return redirect()->away($filePath, 302, [
                'Referrer-Policy' => 'no-referrer',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        // 2. Jika file lokal di storage (private / public disk)
        $disk = $dokumen->storage_disk ?? 'private';
        if (!Storage::disk($disk)->exists($filePath)) {
            // Coba fallback disk public jika migrasi lama menyimpan di public
            if (Storage::disk('public')->exists($filePath)) {
                $disk = 'public';
            } else {
                abort(Response::HTTP_NOT_FOUND, 'File dokumen tidak ditemukan pada penyimpanan.');
            }
        }

        // Cegah path traversal
        $fullPath = Storage::disk($disk)->path($filePath);
        $realPath = realpath($fullPath);
        $basePath = realpath(Storage::disk($disk)->path(''));

        if (!$realPath || !str_starts_with($realPath, $basePath)) {
            abort(Response::HTTP_FORBIDDEN, 'Deteksi percobaan path traversal file tidak sah.');
        }

        $mimeType = Storage::disk($disk)->mimeType($filePath) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
        ]);
    }
}
