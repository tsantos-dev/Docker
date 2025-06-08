<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Docker Environment</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Ambiente PHP com Apache e Docker Funcionando!</h1>
        <p>Data e Hora Atual: <?php echo date('Y-m-d H:i:s'); ?></p>
        <h2>Informações do PHP:</h2>
        <?php phpinfo(); ?>
    </div>
</body>
</html>