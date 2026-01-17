// Verificamos si la URL ya tiene ?lang=
const params = new URLSearchParams(window.location.search);

// Si NO tiene lang, ejecutamos detección
if (!params.has('lang')) {
    // 1. Verificar si hay preferencia guardada
    const pref = localStorage.getItem('userLang');
    if (pref) {
        window.location.search = `?lang=${pref}`;
    } else {
        // 2. Si no, checar IP
        const paisesHispanos = ['AR', 'BO', 'CL', 'CO', 'CR', 'CU', 'DO', 'EC', 'SV', 'GT', 'HN', 'MX', 'NI', 'PA', 'PY', 'PE', 'PR', 'UY', 'VE'];

        fetch('https://ipapi.co/json/')
            .then(res => res.json())
            .then(data => {
                if (paisesHispanos.includes(data.country_code)) {
                    window.location.search = '?lang=es';
                } else {
                    window.location.search = '?lang=en';
                }
            })
            .catch(err => {
                // Fallback navegador
                const nav = navigator.language || navigator.userLanguage;
                if (nav.startsWith('es')) {
                    window.location.search = '?lang=es';
                } else {
                    window.location.search = '?lang=en';
                }
            });
    }
} else {
    // Si YA tiene lang, guardamos la preferencia para el futuro
    localStorage.setItem('userLang', params.get('lang'));
}

// Función para cambiar idioma manualmente
function cambiarIdioma(nuevoLang) {
    localStorage.setItem('userLang', nuevoLang);
    window.location.search = `?lang=${nuevoLang}`;
}

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. PRELOADER ---
    setTimeout(() => {
        const preloader = document.getElementById('preloader');
        if (preloader) preloader.classList.add('hide-loader');
    }, 800);

    // --- 2. SISTEMA DE EXPLOSIÓN ---
    const explosionInputs = document.querySelectorAll('.trigger-explosion');
    const colors = ['#9d4edd', '#ff0055', '#ffffff'];
    explosionInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            input.classList.add('vibrating'); setTimeout(() => input.classList.remove('vibrating'), 200); spawnParticles(input);
        });
    });

    function spawnParticles(targetElement) {
        const rect = targetElement.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2 + window.scrollY;
        for (let i = 0; i < 12; i++) { createParticle(centerX, centerY); }
    }

    function createParticle(x, y) {
        const particle = document.createElement('div');
        particle.classList.add('particle'); document.body.appendChild(particle);
        const color = colors[Math.floor(Math.random() * colors.length)];
        particle.style.backgroundColor = color; particle.style.color = color;
        particle.style.left = `${x}px`; particle.style.top = `${y}px`;
        const angle = Math.random() * Math.PI * 2; const velocity = 60 + Math.random() * 90;
        const tx = Math.cos(angle) * velocity; const ty = Math.sin(angle) * velocity;
        particle.style.setProperty('--tx', `${tx}px`); particle.style.setProperty('--ty', `${ty}px`);
        particle.addEventListener('animationend', () => { particle.remove(); });
    }
});
