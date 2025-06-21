<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Control de Asistencia' ?></title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Lightbox CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.1.0/css/buttons.bootstrap5.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
    body {
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
        display: flex;
        height: 100vh;
        overflow-x: hidden; /* Añadido para prevenir scroll horizontal en body */
    }

    /* Contenedor principal con flexbox */
    .d-flex {
        display: flex;
        flex-direction: row;
        width: 100%;
        height: 100%;
    }

    /* Sidebar con ancho fijo y altura 100% */
    #sidebar {
        width: 250px;
        background-color: #343a40;
        color: white;
        position: fixed;
        height: 100vh;
        top: 0;
        left: 0;
        z-index: 1000; /* Asegura que el sidebar esté por encima */
    }

    /* Contenido principal (tabla) */
    .flex-grow-1 {
        flex-grow: 1;
        padding-top: 20px;
        width: calc(100% - 250px); /* Añadido para calcular ancho correcto */
        overflow-x: auto; /* Permite scroll horizontal solo en el contenido */
    }

    /* Resto de tus estilos se mantienen exactamente igual */
    .table-container {
        margin-top: 0;
    }

    .table-responsive {
        margin-top: 0;
    }

    .card-custom {
        max-width: 500px;
        margin: 50px auto;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    .form-control {
        border-radius: 5px;
    }

    .btn {
        border-radius: 5px;
    }

    @media (max-width: 767px) {
        body {
            flex-direction: column;
            height: auto;
            min-height: 100vh;
            padding: 0;
        }
        
        .d-flex {
            flex-direction: column;
            width: 100%;
        }
        
        #sidebar {
            position: relative;
            width: 100% !important;
            height: auto;
            padding: 15px 0;
        }
        
        .flex-grow-1 {
            margin-left: 0px;
            padding: 15px;
            width: 100%;
            overflow-x: auto;
        }
        
        .table-responsive {
            width: 100%;
            margin: 0;
            padding: 0;
        }
        
        .table {
            width: 100% !important;
            max-width: 100% !important;
        }
    }
</style>

    <!-- jQuery y DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>
    <div class="d-flex">
        <?php if (isset($show_sidebar) && $show_sidebar): ?>
            <?php include __DIR__ . '/sidebar.php'; ?>
        <?php endif; ?>
        
        <div class="flex-grow-1">