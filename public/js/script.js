document.addEventListener('DOMContentLoaded', () => {
const links = document.querySelectorAll('.nav-link');
const slider = document.querySelector('.slider');
const slides = document.querySelectorAll('.slide');
const count = slides.length;


// Largeurs dynamiques
slides.forEach(slide => {
  slide.style.width = `${window.innerWidth}px`;
});
slider.style.width = `${count * window.innerWidth}px`;


// Fonction pour changer de slide
function goTo(index) {
  const target = Math.max(0, Math.min(index, count - 1));
  slider.style.transform = `translateX(-${target * window.innerWidth}px)`;
}

// Navigation via navbar
links.forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    links.forEach(l => l.classList.remove('is-active'));
    link.classList.add('is-active');
    goTo(parseInt(link.getAttribute('data-index'), 10));
  });
});

// Slide actif au chargement
const active = document.querySelector('.nav-link.is-active');
if (active) goTo(parseInt(active.getAttribute('data-index'), 10));


    const contactForm = document.getElementById('contactForm');
    const contactSlide = document.getElementById('contactSlide');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Bloque la soumission classique

            const submitButton = contactForm.querySelector('button[type="submit"]');
            submitButton.textContent = 'Envoi en cours...';
            submitButton.disabled = true;

            grecaptcha.ready(function() {
                grecaptcha.execute('6Lck2qsrAAAAAGvrLAaye4ZIvQFsnMdcSFC7aJfw', { action: 'contact' }).then(function(token) {
                    
                    const formData = new FormData(contactForm);
                    formData.append('recaptcha_response', token); // Ajoute le token reCAPTCHA

                    fetch('/contact', {
                        method: 'POST',
                        body: formData, // On envoie directement le FormData
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            contactSlide.innerHTML = `
                                <div class="loader check"></div>
                                <h1>Mail envoyé avec succès ✅</h1>
                                <p>Merci pour votre message. Je vous répondrai dans les plus brefs délais.</p>
                            `;
                        } else {
                            contactSlide.innerHTML = `
                                <div class="loader error"></div>
                                <h1>Échec de l'envoi ❌</h1>
                                <p>${result.message || "Une erreur est survenue. Veuillez réessayer."}</p>
                                <p>Contactez moi directement at contact@rohan-martin.fr<p>
                            `;
                        }
                    })
                    .catch(error => {
                        contactSlide.innerHTML = `
                            <div class="loader error"></div>
                            <h1>Erreur réseau ❌</h1>
                            <p>Impossible de contacter le serveur. Contactez moi directement at contact@rohan-martin.fr</p>
                        `;
                    })
                    .finally(() => {
                        submitButton.textContent = 'Envoyer';
                        submitButton.disabled = false;
                    });
                });
            });
        });
    }
});
