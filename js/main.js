// ========================================
// Jason Black Campaign Website - Main JavaScript
// ========================================

// ====== HEADER SCROLL EFFECT ======
document.addEventListener("DOMContentLoaded", function () {
    const header = document.querySelector("header");

    function checkScroll() {
        if (window.scrollY > 0) {
            header.classList.add("scrolled");
        } else {
            header.classList.remove("scrolled");
        }
    }

    // Run on load
    checkScroll();

    // Run on scroll
    window.addEventListener("scroll", checkScroll);
});

// ====== NAVIGATION & MOBILE MENU ======
document.addEventListener("DOMContentLoaded", function () {
    const headerOffset = 150; // offset for both desktop & mobile
    const navLinks = document.querySelectorAll(".nav-menu a, .mobile-menu a");
    const sections = document.querySelectorAll("section[id]");
    const menuBtn = document.querySelector(".mobile-menu-btn");
    const menuWrap = document.querySelector(".mobile-menu-wrap");
    const menuClose = document.querySelector(".mobile-menu-close");

    // ====== Mobile Menu Toggle ======
    menuBtn.addEventListener("click", () => {
        menuWrap.classList.add("active");
    });

    menuClose.addEventListener("click", () => {
        menuWrap.classList.remove("active");
    });

    document.addEventListener("click", (e) => {
        if (menuWrap.classList.contains("active") && !menuWrap.contains(e.target) && !menuBtn.contains(e.target)) {
            menuWrap.classList.remove("active");
        }
    });

    // ====== Smooth Scroll on Click ======
    navLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            const targetId = this.getAttribute("href").slice(1);
            const target = document.getElementById(targetId);

            if (target) {
                const top = target.getBoundingClientRect().top + window.scrollY - headerOffset;

                // close mobile menu before scrolling
                if (menuWrap.classList.contains("active")) {
                    menuWrap.classList.remove("active");
                }

                window.scrollTo({
                    top: top,
                    behavior: "smooth"
                });
            }
        });
    });

    // ====== Scroll Spy ======
    function updateActiveMenu() {
        let currentSectionId = "";

        sections.forEach(section => {
            const rect = section.getBoundingClientRect();
            const sectionTop = rect.top;
            const sectionHeight = rect.height;

            // check if section is at least halfway in view OR near top
            if (sectionTop <= window.innerHeight / 2 && sectionTop + sectionHeight >= window.innerHeight / 2) {
                currentSectionId = section.id;
            }
        });

        if (currentSectionId) {
            // remove active class from all
            document.querySelectorAll(".nav-menu-item, .mobile-menu-item").forEach(li => {
                li.classList.remove("active");
            });

            // add active to current
            document.querySelectorAll(`a[href="#${currentSectionId}"]`).forEach(link => {
                if (link.closest("li")) {
                    link.closest("li").classList.add("active");
                }
            });
        }
    }

    window.addEventListener("scroll", updateActiveMenu);
    updateActiveMenu(); // run on load
});

// ====== FORM VALIDATION & SUBMISSION ======

// Form submission handler
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Clear previous errors
    clearErrors();
    
    // Get form data
    const name = document.getElementById('name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const email = document.getElementById('email').value.trim();
    
    let isValid = true;
    let errorCount = 0;
    
    // Validate name
    if (name === '') {
        showError('name-error', 'Name is required');
        isValid = false;
        errorCount++;
    } else if (name.length < 2) {
        showError('name-error', 'Name must be at least 2 characters');
        isValid = false;
        errorCount++;
    } else if (name.length > 50) {
        showError('name-error', 'Name must be less than 50 characters');
        isValid = false;
        errorCount++;
    }
    
    // Validate phone
    if (phone === '') {
        showError('phone-error', 'Phone number is required');
        isValid = false;
        errorCount++;
    } else if (!isValidPhone(phone)) {
        showError('phone-error', 'Please enter a valid phone number (10+ digits)');
        isValid = false;
        errorCount++;
    }
    
    // Validate email
    if (email === '') {
        showError('email-error', 'Email is required');
        isValid = false;
        errorCount++;
    } else if (!isValidEmail(email)) {
        showError('email-error', 'Please enter a valid email address');
        isValid = false;
        errorCount++;
    } else if (email.length > 100) {
        showError('email-error', 'Email must be less than 100 characters');
        isValid = false;
        errorCount++;
    }
    
    // If there are validation errors, don't submit
    if (errorCount > 0) {
        console.log('Form validation failed with', errorCount, 'errors');
        // Focus on first error field
        if (name === '') {
            document.getElementById('name').focus();
        } else if (phone === '') {
            document.getElementById('phone').focus();
        } else if (email === '') {
            document.getElementById('email').focus();
        }
        return false;
    }
    
    // If all validation passes, submit the form
    if (isValid) {
        submitForm(name, phone, email);
    }
});

// ====== REAL-TIME VALIDATION ======
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const phoneInput = document.getElementById('phone');
    const emailInput = document.getElementById('email');
    
    // Real-time validation for name
    nameInput.addEventListener('blur', function() {
        const name = this.value.trim();
        if (name !== '') {
            if (name.length < 2) {
                showError('name-error', 'Name must be at least 2 characters');
            } else if (name.length > 50) {
                showError('name-error', 'Name must be less than 50 characters');
            } else {
                clearFieldError('name-error');
            }
        }
    });
    
    // Real-time validation for phone
    phoneInput.addEventListener('blur', function() {
        const phone = this.value.trim();
        if (phone !== '') {
            if (!isValidPhone(phone)) {
                showError('phone-error', 'Please enter a valid phone number (10+ digits)');
            } else {
                clearFieldError('phone-error');
            }
        }
    });
    
    // Real-time validation for email
    emailInput.addEventListener('blur', function() {
        const email = this.value.trim();
        if (email !== '') {
            if (!isValidEmail(email)) {
                showError('email-error', 'Please enter a valid email address');
            } else if (email.length > 100) {
                showError('email-error', 'Email must be less than 100 characters');
            } else {
                clearFieldError('email-error');
            }
        }
    });
});

// ====== UTILITY FUNCTIONS ======

function clearFieldError(fieldId) {
    const errorElement = document.getElementById(fieldId);
    errorElement.textContent = '';
    errorElement.style.display = 'none';
}

function clearErrors() {
    document.getElementById('name-error').textContent = '';
    document.getElementById('phone-error').textContent = '';
    document.getElementById('email-error').textContent = '';
    document.getElementById('success-msg').style.display = 'none';
}

function showError(elementId, message) {
    const errorElement = document.getElementById(elementId);
    errorElement.textContent = message;
    errorElement.style.color = 'red';
    errorElement.style.display = 'block';
    errorElement.style.fontSize = '14px';
    errorElement.style.marginTop = '5px';
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    // Remove all non-digit characters except + at the beginning
    const cleanPhone = phone.replace(/[^\d\+]/g, '');
    
    // Check if it starts with + and has 11-16 digits, or just 10-15 digits
    if (cleanPhone.startsWith('+')) {
        return /^\+[1-9]\d{10,15}$/.test(cleanPhone);
    } else {
        return /^[1-9]\d{9,14}$/.test(cleanPhone);
    }
}

function submitForm(name, phone, email) {
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.textContent;
    
    // Show loading state
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    // Prepare form data
    const formData = new FormData();
    formData.append('name', name);
    formData.append('phone', phone);
    formData.append('email', email);
    
    // Submit to PHP script
    fetch('submit_form.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            document.getElementById('success-msg').style.display = 'block';
            document.getElementById('contactForm').reset();
        } else {
            // Show error message
            showError('email-error', data.message || 'An error occurred. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('email-error', 'An error occurred. Please try again.');
    })
    .finally(() => {
        // Reset button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}
