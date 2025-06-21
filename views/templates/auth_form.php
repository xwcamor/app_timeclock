<div class="container form-container">
    <?php include __DIR__ . '/alerts.php'; ?>
    
    <h2 class="text-center mb-4"><?= $form_title ?></h2>
    
    <form method="POST" action="<?= $form_action ?>">
        <div class="mb-3">
            <label for="dni" class="form-label">DNI</label>
            <input type="text" class="form-control" id="dni" name="dni" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary w-100"><?= $submit_text ?></button>
        
        <?php if (isset($show_register_link)): ?>
            <div class="mt-3 text-center">
                <a href="register.php">Registrar nuevo administrador</a>
            </div>
        <?php endif; ?>
    </form>
</div>