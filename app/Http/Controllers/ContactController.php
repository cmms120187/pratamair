<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'company' => 'required|string|max:255',
            'package' => 'required|string|in:starter,professional,enterprise',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Silakan periksa kembali data yang Anda masukkan.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Kirim email ke admin
            Mail::send('emails.contact', [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company' => $request->company,
                'package' => $request->package,
                'message' => $request->message,
            ], function ($mail) use ($request) {
                $mail->to(config('mail.admin_email'))
                     ->subject('Pesan Baru dari Form Kontak - ' . $request->company);
            });

            // Kirim email konfirmasi ke pengirim
            Mail::send('emails.contact-confirmation', [
                'name' => $request->name,
            ], function ($mail) use ($request) {
                $mail->to($request->email)
                     ->subject('Terima Kasih - Pesan Anda Telah Diterima');
            });

            return response()->json([
                'success' => true,
                'message' => 'Terima kasih! Pesan Anda telah dikirim. Tim kami akan menghubungi Anda segera.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Contact form error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Maaf, terjadi kesalahan saat mengirim pesan. Silakan coba lagi atau hubungi kami langsung.'
            ], 500);
        }
    }
}

