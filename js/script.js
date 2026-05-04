/**
 * JavaScript for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Form validation and interactive features
 * Following W3Schools (2021) JavaScript best practices
 * Interactive elements inspired by Vinted (2023) and Depop (2023)
 */

// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all features
    initFormValidation();
    initCartInteractions();
    initSearchFeatures();
    initImagePreviews();
    initMobileMenu();
    initScrollEffects();
});

/**
 * Form Validation
 * Client-side validation following HTML5 standards
 */
function initFormValidation() {
    // Registration form validation
    const registerForm = document.querySelector('form[action="register.php"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirm');
            const email = document.getElementById('email');
            
            // Password strength validation
            if (password.value.length < 8) {
                showError(password, 'Password must be at least 8 characters long');
                e.preventDefault();
                return;
            }
            
            // Password confirmation
            if (password.value !== passwordConfirm.value) {
                showError(passwordConfirm, 'Passwords do not match');
                e.preventDefault();
                return;
            }
            
            // Email format validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                showError(email, 'Please enter a valid email address');
                e.preventDefault();
                return;
            }
            
            clearAllErrors();
        });
    }
    
    // Login form validation
    const loginForm = document.querySelector('form[action="login.php"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const usernameOrEmail = document.getElementById('username_or_email');
            const password = document.getElementById('password');
            
            if (!usernameOrEmail.value.trim()) {
                showError(usernameOrEmail, 'Username or email is required');
                e.preventDefault();
                return;
            }
            
            if (!password.value) {
                showError(password, 'Password is required');
                e.preventDefault();
                return;
            }
            
            clearAllErrors();
        });
    }
    
    // Checkout form validation
    const checkoutForm = document.querySelector('.checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const deliveryAddress = document.getElementById('delivery_address');
            const termsCheckbox = document.querySelector('input[name="terms"]');
            
            if (!deliveryAddress.value.trim() || deliveryAddress.value.trim().length < 10) {
                showError(deliveryAddress, 'Please provide a complete delivery address');
                e.preventDefault();
                return;
            }
            
            if (!termsCheckbox.checked) {
                alert('Please agree to the terms and conditions');
                e.preventDefault();
                return;
            }
            
            clearAllErrors();
        });
    }
    
    // Real-time validation feedback
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
}

/**
 * Validate individual field
 */
function validateField(field) {
    clearError(field);
    
    // Required field validation
    if (field.hasAttribute('required') && !field.value.trim()) {
        showError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (field.type === 'email' && field.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(field.value)) {
            showError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Password validation
    if (field.id === 'password' && field.value) {
        if (field.value.length < 8) {
            showError(field, 'Password must be at least 8 characters long');
            return false;
        }
    }
    
    // Password confirmation
    if (field.id === 'password_confirm') {
        const password = document.getElementById('password');
        if (password.value !== field.value) {
            showError(field, 'Passwords do not match');
            return false;
        }
    }
    
    return true;
}

/**
 * Show error message for field
 */
function showError(field, message) {
    clearError(field);
    field.classList.add('error');
    
    const errorDiv = document.createElement('span');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

/**
 * Clear error for field
 */
function clearError(field) {
    field.classList.remove('error');
    const errorMsg = field.parentNode.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.remove();
    }
}

/**
 * Clear all error messages
 */
function clearAllErrors() {
    const errorFields = document.querySelectorAll('.error');
    const errorMessages = document.querySelectorAll('.error-message');
    
    errorFields.forEach(field => field.classList.remove('error'));
    errorMessages.forEach(msg => msg.remove());
}

/**
 * Cart Interactions
 * AJAX cart updates and quantity management
 */
function initCartInteractions() {
    // Quantity input validation
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const value = parseInt(this.value);
            if (isNaN(value) || value < 1) {
                this.value = 1;
            } else if (value > 99) {
                this.value = 99;
            }
        });
        
        // Prevent negative values
        input.addEventListener('keydown', function(e) {
            if (e.key === '-' || e.key === 'e') {
                e.preventDefault();
            }
        });
    });
    
    // Add to cart feedback
    const addToCartButtons = document.querySelectorAll('a[href*="add_to_cart.php"]');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Show loading state
            const originalText = this.textContent;
            this.textContent = 'Adding...';
            this.classList.add('loading');
            
            // Reset after a short delay (in real app, this would be AJAX)
            setTimeout(() => {
                this.textContent = originalText;
                this.classList.remove('loading');
            }, 1000);
        });
    });
}

/**
 * Search Features
 * Enhanced search functionality
 */
function initSearchFeatures() {
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        // Auto-clear placeholder on focus
        searchInput.addEventListener('focus', function() {
            if (this.value === this.placeholder) {
                this.value = '';
            }
        });
        
        // Restore placeholder on blur if empty
        searchInput.addEventListener('blur', function() {
            if (this.value === '') {
                this.value = '';
            }
        });
        
        // Search on Enter key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    }
    
    // Filter dropdowns
    const filterSelects = document.querySelectorAll('.filter-group select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            if (form) {
                // Auto-submit filters
                form.submit();
            }
        });
    });
}

/**
 * Image Previews
 * Product image handling and previews
 */
function initImagePreviews() {
    // Handle missing images
    const productImages = document.querySelectorAll('.product-image img');
    productImages.forEach(img => {
        img.addEventListener('error', function() {
            // Replace with placeholder
            const placeholder = document.createElement('div');
            placeholder.className = 'placeholder-image';
            placeholder.textContent = '👔';
            
            this.parentNode.replaceChild(placeholder, this);
        });
        
        // Add loading state
        img.addEventListener('load', function() {
            this.style.opacity = '1';
        });
        
        // Set initial loading state
        img.style.opacity = '0';
        img.style.transition = 'opacity 0.3s ease';
    });
}

/**
 * Mobile Menu
 * Responsive navigation for mobile devices
 */
function initMobileMenu() {
    // Check if mobile menu is needed
    if (window.innerWidth <= 768) {
        const navLinks = document.querySelector('.nav-links');
        if (navLinks && !navLinks.classList.contains('mobile-processed')) {
            // Create mobile menu button
            const menuButton = document.createElement('button');
            menuButton.className = 'mobile-menu-btn';
            menuButton.innerHTML = '☰';
            menuButton.setAttribute('aria-label', 'Toggle navigation menu');
            
            // Insert before nav links
            navLinks.parentNode.insertBefore(menuButton, navLinks);
            
            // Toggle menu
            menuButton.addEventListener('click', function() {
                navLinks.classList.toggle('mobile-open');
                this.innerHTML = navLinks.classList.contains('mobile-open') ? '✕' : '☰';
            });
            
            // Mark as processed
            navLinks.classList.add('mobile-processed');
            
            // Add mobile styles
            const style = document.createElement('style');
            style.textContent = `
                .mobile-menu-btn {
                    display: block;
                    background: none;
                    border: none;
                    font-size: 1.5rem;
                    cursor: pointer;
                    color: var(--text-primary);
                }
                
                .nav-links.mobile-open {
                    display: flex !important;
                    flex-direction: column;
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    background: var(--bg-secondary);
                    box-shadow: 0 2px 4px var(--shadow-light);
                    padding: 1rem;
                    z-index: 1000;
                }
                
                @media (min-width: 769px) {
                    .mobile-menu-btn {
                        display: none !important;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
}

/**
 * Scroll Effects
 * Smooth scrolling and parallax effects
 */
function initScrollEffects() {
    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Sticky navigation effect
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        let lastScroll = 0;
        
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                navbar.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.1)';
            }
            
            lastScroll = currentScroll;
        });
    }
}

/**
 * Utility Functions
 */
function showLoading(element) {
    element.classList.add('loading');
    element.disabled = true;
}

function hideLoading(element) {
    element.classList.remove('loading');
    element.disabled = false;
}

function showMessage(message, type = 'info') {
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert alert-${type}`;
    messageDiv.textContent = message;
    
    // Insert at top of main content
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertBefore(messageDiv, mainContent.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
        
        // Scroll to top to see message
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function confirmAction(message) {
    return confirm(message);
}

// Export functions for global use
window.PastimesUtils = {
    showLoading,
    hideLoading,
    showMessage,
    confirmAction,
    validateField,
    showError,
    clearError
};

// Handle window resize for mobile menu
window.addEventListener('resize', function() {
    initMobileMenu();
});
