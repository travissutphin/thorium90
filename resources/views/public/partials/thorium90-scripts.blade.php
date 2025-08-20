<script>
// Global variables
let mobileMenuOpen = false;
let darkMode = false;

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    initializeDarkMode();
    initializeMobileMenu();
    initializeAnimations();
});

// Dark mode functionality
function toggleDarkMode() {
    const html = document.documentElement;
    const sunIcons = document.querySelectorAll('#sun-icon, #sun-icon-mobile');
    const moonIcons = document.querySelectorAll('#moon-icon, #moon-icon-mobile');
    const darkModeText = document.getElementById('dark-mode-text');
    
    darkMode = !darkMode;
    
    if (darkMode) {
        html.classList.add('dark');
        sunIcons.forEach(icon => icon.classList.remove('hidden'));
        moonIcons.forEach(icon => icon.classList.add('hidden'));
        if (darkModeText) darkModeText.textContent = 'Light Mode';
        localStorage.setItem('thorium90-theme', 'dark');
    } else {
        html.classList.remove('dark');
        sunIcons.forEach(icon => icon.classList.add('hidden'));
        moonIcons.forEach(icon => icon.classList.remove('hidden'));
        if (darkModeText) darkModeText.textContent = 'Dark Mode';
        localStorage.setItem('thorium90-theme', 'light');
    }
}

function initializeDarkMode() {
    const savedTheme = localStorage.getItem('thorium90-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        darkMode = true;
        document.documentElement.classList.add('dark');
        
        const sunIcons = document.querySelectorAll('#sun-icon, #sun-icon-mobile');
        const moonIcons = document.querySelectorAll('#moon-icon, #moon-icon-mobile');
        const darkModeText = document.getElementById('dark-mode-text');
        
        sunIcons.forEach(icon => icon.classList.remove('hidden'));
        moonIcons.forEach(icon => icon.classList.add('hidden'));
        if (darkModeText) darkModeText.textContent = 'Light Mode';
    }
}

// Mobile menu functionality
function setMobileMenuOpen(open) {
    mobileMenuOpen = open;
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIcon = document.getElementById('menu-icon');
    const closeIcon = document.getElementById('close-icon');
    
    if (mobileMenu && menuIcon && closeIcon) {
        if (mobileMenuOpen) {
            mobileMenu.classList.add('open');
            menuIcon.classList.add('hidden');
            closeIcon.classList.remove('hidden');
            // Ensure background is applied
            mobileMenu.style.background = 'rgba(0, 0, 0, 0.9)';
            mobileMenu.style.backgroundColor = 'rgba(0, 0, 0, 0.9)';
        } else {
            mobileMenu.classList.remove('open');
            menuIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
        }
    }
}

function initializeMobileMenu() {
    // Ensure mobile menu has proper background on initialization
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenu) {
        mobileMenu.style.background = 'rgba(0, 0, 0, 0.9)';
        mobileMenu.style.backgroundColor = 'rgba(0, 0, 0, 0.9)';
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileButton = event.target.closest('.p-2');
        
        if (mobileMenuOpen && mobileMenu && !mobileMenu.contains(event.target) && !mobileButton) {
            setMobileMenuOpen(false);
        }
    });
    
    // Close mobile menu when pressing escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && mobileMenuOpen) {
            setMobileMenuOpen(false);
        }
    });
}

// Animation utilities
function initializeAnimations() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all elements with fade-in animation
    document.querySelectorAll('.animate-fade-in').forEach(el => {
        observer.observe(el);
    });
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            const headerOffset = 80;
            const elementPosition = target.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
            
            // Close mobile menu if open
            if (mobileMenuOpen) {
                setMobileMenuOpen(false);
            }
        }
    });
});

// Add scroll effects to header
window.addEventListener('scroll', function() {
    const header = document.querySelector('header');
    if (header) {
        if (window.scrollY > 100) {
            header.classList.add('backdrop-blur-lg');
        } else {
            header.classList.remove('backdrop-blur-lg');
        }
    }
});

// Performance optimization: Debounce scroll events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>