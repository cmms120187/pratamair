<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pesan Baru dari Form Kontak</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 10px;">
            Pesan Baru dari Form Kontak TPM CMMS
        </h2>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #333; margin-top: 0;">Detail Pengirim:</h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 150px;">Nama Lengkap:</td>
                    <td style="padding: 8px 0;">{{ $name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Email:</td>
                    <td style="padding: 8px 0;"><a href="mailto:{{ $email }}" style="color: #667eea;">{{ $email }}</a></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Nomor Telepon:</td>
                    <td style="padding: 8px 0;"><a href="tel:{{ $phone }}" style="color: #667eea;">{{ $phone }}</a></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Nama Perusahaan:</td>
                    <td style="padding: 8px 0;">{{ $company }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Paket yang Dipilih:</td>
                    <td style="padding: 8px 0;">
                        <span style="background: #667eea; color: white; padding: 4px 12px; border-radius: 4px; text-transform: capitalize;">
                            {{ $package }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        
        <div style="background: #fff; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0;">
            <h3 style="color: #333; margin-top: 0;">Pesan:</h3>
            <p style="white-space: pre-wrap; margin: 0;">{{ $message }}</p>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 12px;">
            <p>Email ini dikirim otomatis dari form kontak TPM CMMS.</p>
            <p>Waktu: {{ now()->format('d F Y, H:i:s') }}</p>
        </div>
    </div>
</body>
</html>

