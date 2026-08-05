/**
 * PizzaFlow — Add/Edit User form helpers
 * Live validation, password toggles, friendlier UX
 */
(function () {
    'use strict';

    const form = document.getElementById('addUserForm') || document.getElementById('editUserForm');
    if (!form) return;

    const isEdit = form.id === 'editUserForm';
    const fields = {
        name: form.querySelector('#name'),
        phone: form.querySelector('#phone'),
        email: form.querySelector('#email'),
        role: form.querySelector('#role'),
        password: form.querySelector('#password'),
        password_confirmation: form.querySelector('#password_confirmation'),
    };

    const phonePattern = /^(\+94|0)?[1-9]\d{8}$/;
    const namePattern = /^[\p{L}\s'\-.]+$/u;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function setError(fieldName, message) {
        const input = fields[fieldName];
        const box = form.querySelector(`[data-error-for="${fieldName}"]`);
        if (!input) return;

        if (message) {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            if (box) box.textContent = message;
        } else {
            input.classList.remove('is-invalid');
            if (input.value.trim() !== '' || (fieldName.startsWith('password') && isEdit && !input.value)) {
                if (!(fieldName.startsWith('password') && isEdit && !fields.password.value)) {
                    input.classList.add('is-valid');
                }
            }
            if (box && !box.dataset.serverError) box.textContent = '';
        }
    }

    function validateName() {
        const value = fields.name.value.trim();
        if (!value) return setError('name', 'Full name is required.'), false;
        if (value.length < 2) return setError('name', 'Name must be at least 2 characters.'), false;
        if (!namePattern.test(value)) return setError('name', 'Use letters only (spaces and hyphens allowed).'), false;
        return setError('name', ''), true;
    }

    function validatePhone() {
        const value = fields.phone.value.replace(/\s+/g, '');
        fields.phone.value = value;
        if (!value) return setError('phone', 'Phone number is required.'), false;
        if (!phonePattern.test(value)) {
            return setError('phone', 'Use 0771234567 or +94771234567.'), false;
        }
        return setError('phone', ''), true;
    }

    function validateEmail() {
        const value = fields.email.value.trim().toLowerCase();
        fields.email.value = value;
        if (!value) return setError('email', 'Email address is required.'), false;
        if (!emailPattern.test(value)) return setError('email', 'Enter a valid email address.'), false;
        return setError('email', ''), true;
    }

    function validateRole() {
        if (!fields.role.value) return setError('role', 'Please select a role.'), false;
        return setError('role', ''), true;
    }

    function validatePassword() {
        const value = fields.password.value;
        if (isEdit && !value) {
            setError('password', '');
            fields.password.classList.remove('is-valid', 'is-invalid');
            return true;
        }
        if (!value) return setError('password', 'Password is required.'), false;
        if (value.length < 8) return setError('password', 'Password must be at least 8 characters.'), false;
        if (!/[A-Za-z]/.test(value) || !/\d/.test(value)) {
            return setError('password', 'Password must include letters and numbers.'), false;
        }
        return setError('password', ''), true;
    }

    function validateConfirm() {
        const password = fields.password.value;
        const confirm = fields.password_confirmation.value;

        if (isEdit && !password && !confirm) {
            setError('password_confirmation', '');
            fields.password_confirmation.classList.remove('is-valid', 'is-invalid');
            return true;
        }
        if (!confirm) return setError('password_confirmation', 'Please confirm the password.'), false;
        if (confirm !== password) return setError('password_confirmation', 'Passwords do not match.'), false;
        return setError('password_confirmation', ''), true;
    }

    function validateAll() {
        return [
            validateName(),
            validatePhone(),
            validateEmail(),
            validateRole(),
            validatePassword(),
            validateConfirm(),
        ].every(Boolean);
    }

    fields.name?.addEventListener('blur', validateName);
    fields.phone?.addEventListener('blur', validatePhone);
    fields.email?.addEventListener('blur', validateEmail);
    fields.role?.addEventListener('change', validateRole);
    fields.password?.addEventListener('input', () => {
        validatePassword();
        if (fields.password_confirmation.value) validateConfirm();
    });
    fields.password_confirmation?.addEventListener('input', validateConfirm);

    // Title-case name on blur for friendlier data
    fields.name?.addEventListener('blur', () => {
        fields.name.value = fields.name.value
            .trim()
            .replace(/\s+/g, ' ')
            .replace(/\b\w/g, (c) => c.toUpperCase());
        validateName();
    });

    form.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-toggle-password');
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (!input || !icon) return;

            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });

    form.addEventListener('submit', (event) => {
        if (!validateAll()) {
            event.preventDefault();
            const firstInvalid = form.querySelector('.is-invalid');
            firstInvalid?.focus();
        }
    });
})();
