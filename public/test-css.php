<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Test</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Наш CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-primary">
            <i class="fas fa-check-circle"></i> CSS Test
        </h1>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Primary Card</h5>
                        <p class="card-text">This should be blue with white text.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Success Card</h5>
                        <p class="card-text">This should be green with white text.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Warning Card</h5>
                        <p class="card-text">This should be yellow with white text.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <button class="btn btn-primary">
                <i class="fas fa-save"></i> Primary Button
            </button>
            <button class="btn btn-success">
                <i class="fas fa-check"></i> Success Button
            </button>
            <button class="btn btn-danger">
                <i class="fas fa-times"></i> Danger Button
            </button>
        </div>
        
        <div class="mt-4">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> This is an info alert.
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Наш JS -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
