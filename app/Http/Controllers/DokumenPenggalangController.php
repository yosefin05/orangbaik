<?php

namespace App\Http\Controllers;

use App\Models\Penggalang_Dana_Dokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DokumenPenggalangController extends Controller
{
    /**
     * Dokumen verifikasi hanya tersedia untuk admin melalui private storage.
     * URL eksternal dan public-disk legacy sengaja ditolak: keduanya dapat
     * melewati kontrol akses aplikasi.
     */
    public function show(Request $request, $id)
    {
        $dokumen = Penggalang_Dana_Dokumen::findOrFail($id);

        if (!$dokumen->canBeAccessedBy($request->user())) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: dokumen verifikasi hanya dapat diakses administrator.');
        }

        $filePath = (string) $dokumen->file_dokumen;
        $disk = (string) ($dokumen->storage_disk ?: 'private');

        // Fail closed: jangan redirect ke URL luar atau fallback ke disk publik.
        if ($filePath === '' || filter_var($filePath, FILTER_VALIDATE_URL) || $disk !== 'private') {
            abort(Response::HTTP_NOT_FOUND, 'Dokumen belum tersedia pada penyimpanan privat.');
        }

        $privateDisk = Storage::disk('private');
        if (!$privateDisk->exists($filePath)) {
            abort(Response::HTTP_NOT_FOUND, 'File dokumen tidak ditemukan pada penyimpanan privat.');
        }

        $fullPath = $privateDisk->path($filePath);
        $realPath = realpath($fullPath);
        $basePath = realpath($privateDisk->path(''));

        if (!$realPath || !$basePath || ($realPath !== $basePath && !str_starts_with($realPath, $basePath . DIRECTORY_SEPARATOR))) {
            abort(Response::HTTP_FORBIDDEN, 'Path dokumen tidak valid.');
        }

        $response = response()->file($fullPath, [
            'Content-Type' => $privateDisk->mimeType($filePath) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="dokumen-verifikasi"',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'no-referrer',
            'Pragma' => 'no-cache',
        ]);

        $response->setPrivate();
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0, must-revalidate');

        return $response;
    }
}
