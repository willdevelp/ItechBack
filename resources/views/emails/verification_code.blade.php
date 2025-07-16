<!-- resources/views/emails/verification_code.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Code de vérification</title>
</head>
<body>
    <h1>Votre code de vérification</h1>
    <p>Voici votre code de vérification pour votre inscription :</p>
    
    <div style="font-size: 24px; font-weight: bold; letter-spacing: 2px; margin: 20px 0;">
        {{ $code }}
    </div>
    
    <p>Ce code expirera dans 15 minutes.</p>
    
    <p>Si vous n'avez pas demandé cette inscription, veuillez ignorer cet email.</p>
</body>
</html>