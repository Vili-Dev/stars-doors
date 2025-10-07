</div> <!-- Fermeture du conteneur principal ouvert dans nav.php -->

<!-- Footer -->
<footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <!-- À propos -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5><i class="fas fa-star text-primary"></i> Stars Doors</h5>
                <p class="text-muted">
                    Votre plateforme de confiance pour la location de logements de courte durée. 
                    Découvrez des hébergements uniques et vivez des expériences inoubliables.
                </p>
                <div class="social-links">
                    <a href="#" class="text-light me-3" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-light me-3" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-light me-3" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-light" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
            
            <!-- Liens rapides -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6>Découvrir</h6>
                <ul class="list-unstyled">
                    <li><a href="<?php echo SITE_URL; ?>" class="text-muted text-decoration-none">Accueil</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/search.php" class="text-muted text-decoration-none">Rechercher</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Destinations populaires</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Offres spéciales</a></li>
                </ul>
            </div>
            
            <!-- Support -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6>Support</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-muted text-decoration-none">Centre d'aide</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Nous contacter</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">FAQ</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Signaler un problème</a></li>
                </ul>
            </div>
            
            <!-- Légal -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6>Légal</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-muted text-decoration-none">Conditions d'utilisation</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Politique de confidentialité</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Mentions légales</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Cookies</a></li>
                </ul>
            </div>
            
            <!-- Propriétaires -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6>Propriétaires</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-muted text-decoration-none">Mettre en location</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Guide du propriétaire</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Outils de gestion</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Assurance</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Barre de séparation -->
        <hr class="my-4">
        
        <!-- Copyright et informations -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0 text-muted">
                    &copy; <?php echo date('Y'); ?> Stars Doors. Tous droits réservés.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 text-muted">
                    <i class="fas fa-code"></i> 
                    Version <?php echo APP_VERSION ?? '1.0.0'; ?> | 
                    <i class="fas fa-shield-alt"></i> 
                    Site sécurisé SSL
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bouton retour en haut -->
<button id="scrollTopBtn" class="btn btn-primary position-fixed" 
        style="bottom: 20px; right: 20px; display: none; z-index: 1000;" 
        title="Retour en haut" aria-label="Retour en haut de la page">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Scripts JavaScript -->
<!-- Bootstrap Bundle avec Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JavaScript -->
<script src="assets/js/main.js"></script>

<!-- Scripts supplémentaires selon la page -->
<?php if (isset($additional_scripts)): ?>
    <?php foreach ($additional_scripts as $script): ?>
    <script src="<?php echo htmlspecialchars($script); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- JavaScript inline pour les fonctionnalités de base -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bouton retour en haut
    const scrollTopBtn = document.getElementById('scrollTopBtn');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollTopBtn.style.display = 'block';
        } else {
            scrollTopBtn.style.display = 'none';
        }
    });
    
    scrollTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Auto-fermeture des alertes après 5 secondes
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Validation côté client pour les formulaires
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Confirmation avant suppression
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                event.preventDefault();
            }
        });
    });
});

// Protection CSRF automatique pour les formulaires AJAX
if (typeof window.csrfToken === 'undefined') {
    window.csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
}
</script>

<!-- Analytics (à configurer selon les besoins) -->
<?php if (defined('GOOGLE_ANALYTICS_ID') && GOOGLE_ANALYTICS_ID): ?>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo GOOGLE_ANALYTICS_ID; ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?php echo GOOGLE_ANALYTICS_ID; ?>');
</script>
<?php endif; ?>

</body>
</html>