class PaymentUI {
    static showLoading(button) {
        button.originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
        button.disabled = true;
    }

    static hideLoading(button) {
        button.innerHTML = button.originalText;
        button.disabled = false;
    }

    static showError(message) {
        const toast = new bootstrap.Toast(document.getElementById('errorToast'));
        document.getElementById('errorMessage').textContent = message;
        toast.show();
    }

    static showSuccess(message) {
        const toast = new bootstrap.Toast(document.getElementById('successToast'));
        document.getElementById('successMessage').textContent = message;
        toast.show();
    }
}