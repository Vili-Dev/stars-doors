/**
 * Stars Doors - JavaScript principal
 * Fonctionnalités JavaScript communes à toute l'application
 */

(function() {
    'use strict';

    // Configuration globale
    const Config = {
        AJAX_TIMEOUT: 10000,
        DEBOUNCE_DELAY: 300,
        ANIMATION_DURATION: 300
    };

    // Utilitaires
    const Utils = {
        /**
         * Fonction de debounce pour limiter les appels fréquents
         */
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        /**
         * Formatage des nombres avec séparateurs
         */
        formatNumber: function(number, decimals = 2) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        },

        /**
         * Formatage des prix
         */
        formatPrice: function(price) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(price);
        },

        /**
         * Validation côté client
         */
        validateEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        validatePhone: function(phone) {
            const re = /^(?:(?:\+|00)33|0)[1-9](?:[0-9]{8})$/;
            return re.test(phone.replace(/\s/g, ''));
        },

        /**
         * Affichage de notifications toast
         */
        showToast: function(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container') || this.createToastContainer();
            const toast = this.createToast(message, type);
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Suppression automatique après fermeture
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        },

        createToastContainer: function() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1055';
            document.body.appendChild(container);
            return container;
        },

        createToast: function(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                            data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            return toast;
        },

        /**
         * Requêtes AJAX sécurisées avec CSRF
         */
        ajax: function(options) {
            const defaults = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': window.csrfToken || ''
                },
                timeout: Config.AJAX_TIMEOUT
            };

            const config = Object.assign({}, defaults, options);
            
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), config.timeout);

            return fetch(config.url, {
                method: config.method,
                headers: config.headers,
                body: config.data ? JSON.stringify(config.data) : null,
                signal: controller.signal
            })
            .then(response => {
                clearTimeout(timeoutId);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .catch(error => {
                clearTimeout(timeoutId);
                if (error.name === 'AbortError') {
                    throw new Error('Timeout: La requête a pris trop de temps');
                }
                throw error;
            });
        }
    };

    // Fonctionnalités de recherche
    const Search = {
        init: function() {
            this.initSearchForm();
            this.initFilters();
            this.initDateValidation();
        },

        initSearchForm: function() {
            const searchForm = document.querySelector('#search-form, form[action*="search"]');
            if (!searchForm) return;

            // Validation des dates
            const dateDebut = searchForm.querySelector('input[name="date_debut"]');
            const dateFin = searchForm.querySelector('input[name="date_fin"]');

            if (dateDebut && dateFin) {
                dateDebut.addEventListener('change', () => this.validateDates(dateDebut, dateFin));
                dateFin.addEventListener('change', () => this.validateDates(dateDebut, dateFin));
            }
        },

        validateDates: function(dateDebut, dateFin) {
            const debut = new Date(dateDebut.value);
            const fin = new Date(dateFin.value);
            const aujourd = new Date();
            aujourd.setHours(0, 0, 0, 0);

            // Réinitialiser les erreurs
            dateDebut.classList.remove('is-invalid');
            dateFin.classList.remove('is-invalid');

            if (dateDebut.value && debut < aujourd) {
                dateDebut.classList.add('is-invalid');
                dateDebut.setCustomValidity('La date de début ne peut pas être dans le passé');
            } else {
                dateDebut.setCustomValidity('');
            }

            if (dateDebut.value && dateFin.value && fin <= debut) {
                dateFin.classList.add('is-invalid');
                dateFin.setCustomValidity('La date de fin doit être postérieure à la date de début');
            } else {
                dateFin.setCustomValidity('');
            }

            // Mise à jour de la date de fin minimale
            if (dateDebut.value) {
                const minDateFin = new Date(debut);
                minDateFin.setDate(minDateFin.getDate() + 1);
                dateFin.min = minDateFin.toISOString().split('T')[0];
            }
        },

        initFilters: function() {
            const filterInputs = document.querySelectorAll('.search-filter');
            filterInputs.forEach(input => {
                input.addEventListener('change', Utils.debounce(() => {
                    this.updateSearchResults();
                }, Config.DEBOUNCE_DELAY));
            });
        },

        updateSearchResults: function() {
            // Cette fonction sera développée pour les recherches en temps réel
            console.log('Mise à jour des résultats de recherche...');
        },

        initDateValidation: function() {
            // Définir la date minimum à aujourd'hui pour tous les champs de date
            const dateInputs = document.querySelectorAll('input[type="date"]');
            const today = new Date().toISOString().split('T')[0];
            
            dateInputs.forEach(input => {
                if (!input.min && input.name !== 'date_naissance') {
                    input.min = today;
                }
            });
        }
    };

    // Fonctionnalités pour les images
    const Images = {
        init: function() {
            this.initLazyLoading();
            this.initImagePreview();
            this.initImageCarousel();
        },

        initLazyLoading: function() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            observer.unobserve(img);
                        }
                    });
                });

                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
        },

        initImagePreview: function() {
            const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', (e) => {
                    this.previewImage(e.target);
                });
            });
        },

        previewImage: function(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = input.parentNode.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'image-preview img-thumbnail mt-2';
                        preview.style.maxWidth = '200px';
                        input.parentNode.appendChild(preview);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        },

        initImageCarousel: function() {
            // Amélioration des carrousels d'images
            const carousels = document.querySelectorAll('.carousel');
            carousels.forEach(carousel => {
                // Ajouter la navigation au clavier
                carousel.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowLeft') {
                        bootstrap.Carousel.getInstance(carousel).prev();
                    } else if (e.key === 'ArrowRight') {
                        bootstrap.Carousel.getInstance(carousel).next();
                    }
                });
            });
        }
    };

    // Fonctionnalités pour les formulaires
    const Forms = {
        init: function() {
            this.initValidation();
            this.initSubmitButtons();
            this.initPasswordToggle();
            this.initAutocomplete();
        },

        initValidation: function() {
            const forms = document.querySelectorAll('.needs-validation');
            forms.forEach(form => {
                form.addEventListener('submit', (e) => {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Focus sur le premier champ invalide
                        const firstInvalid = form.querySelector(':invalid');
                        if (firstInvalid) {
                            firstInvalid.focus();
                        }
                    }
                    form.classList.add('was-validated');
                });

                // Validation en temps réel
                const inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.addEventListener('blur', () => {
                        if (input.checkValidity()) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        } else {
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                        }
                    });
                });
            });
        },

        initSubmitButtons: function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', () => {
                    const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        const originalText = submitBtn.textContent;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi...';
                        
                        // Réactiver après 10 secondes pour éviter le blocage permanent
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalText;
                        }, 10000);
                    }
                });
            });
        },

        initPasswordToggle: function() {
            const passwordInputs = document.querySelectorAll('input[type="password"]');
            passwordInputs.forEach(input => {
                const toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'btn btn-outline-secondary';
                toggle.innerHTML = '<i class="fas fa-eye"></i>';
                toggle.setAttribute('aria-label', 'Afficher/masquer le mot de passe');
                
                // Wrapper pour le positioning
                const wrapper = document.createElement('div');
                wrapper.className = 'input-group';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
                wrapper.appendChild(toggle);
                
                toggle.addEventListener('click', () => {
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    toggle.innerHTML = isPassword ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
                });
            });
        },

        initAutocomplete: function() {
            // Fonctionnalité d'autocomplétion pour les villes
            const cityInputs = document.querySelectorAll('input[name="ville"], input[name="city"]');
            cityInputs.forEach(input => {
                // Cette fonctionnalité sera développée avec une API de géolocalisation
                input.addEventListener('input', Utils.debounce((e) => {
                    // Autocomplétion des villes françaises
                    console.log('Recherche de villes pour:', e.target.value);
                }, Config.DEBOUNCE_DELAY));
            });
        }
    };

    // Fonctionnalités pour les favoris
    const Favorites = {
        init: function() {
            this.initToggleButtons();
        },

        initToggleButtons: function() {
            const favoriteButtons = document.querySelectorAll('.favorite-toggle');
            favoriteButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggleFavorite(button);
                });
            });
        },

        toggleFavorite: function(button) {
            const listingId = button.dataset.listingId;
            if (!listingId) return;

            button.disabled = true;
            const icon = button.querySelector('i');
            const originalClass = icon.className;

            Utils.ajax({
                url: '/api/favorites/toggle',
                data: { listing_id: listingId }
            })
            .then(response => {
                if (response.success) {
                    icon.className = response.is_favorite ? 'fas fa-heart' : 'far fa-heart';
                    Utils.showToast(response.message, 'success');
                } else {
                    Utils.showToast(response.message || 'Erreur lors de la mise à jour', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur favoris:', error);
                Utils.showToast('Erreur de connexion', 'danger');
            })
            .finally(() => {
                button.disabled = false;
            });
        }
    };

    // Fonctionnalités de géolocalisation
    const Location = {
        init: function() {
            this.initLocationButtons();
        },

        initLocationButtons: function() {
            const locationButtons = document.querySelectorAll('.location-btn');
            locationButtons.forEach(button => {
                button.addEventListener('click', () => {
                    this.getCurrentLocation(button);
                });
            });
        },

        getCurrentLocation: function(button) {
            if (!navigator.geolocation) {
                Utils.showToast('La géolocalisation n\'est pas supportée', 'warning');
                return;
            }

            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Localisation...';

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.handleLocationSuccess(position, button);
                },
                (error) => {
                    this.handleLocationError(error);
                    button.disabled = false;
                    button.textContent = originalText;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 300000
                }
            );
        },

        handleLocationSuccess: function(position, button) {
            const { latitude, longitude } = position.coords;
            
            // Ici on pourrait faire un appel à une API de géocodage inverse
            // pour obtenir la ville à partir des coordonnées
            console.log('Position:', latitude, longitude);
            
            button.disabled = false;
            button.textContent = 'Position trouvée';
            Utils.showToast('Position détectée avec succès', 'success');
        },

        handleLocationError: function(error) {
            let message = 'Erreur de géolocalisation';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    message = 'Géolocalisation refusée par l\'utilisateur';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = 'Position non disponible';
                    break;
                case error.TIMEOUT:
                    message = 'Timeout de géolocalisation';
                    break;
            }
            Utils.showToast(message, 'warning');
        }
    };

    // Initialisation au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation de tous les modules
        Search.init();
        Images.init();
        Forms.init();
        Favorites.init();
        Location.init();

        // Mise à jour du copyright automatique
        const copyrightYear = document.querySelector('.copyright-year');
        if (copyrightYear) {
            copyrightYear.textContent = new Date().getFullYear();
        }

        // Initialisation des tooltips Bootstrap
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialisation des popovers Bootstrap
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        console.log('Stars Doors JavaScript initialisé');
    });

    // Exposition des utilitaires globalement si nécessaire
    window.StarsDoors = {
        Utils: Utils,
        Search: Search,
        Images: Images,
        Forms: Forms,
        Favorites: Favorites,
        Location: Location
    };

})();