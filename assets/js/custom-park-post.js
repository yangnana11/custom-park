document.addEventListener('DOMContentLoaded', function() {
    const titleLabel = document.querySelector('#title-prompt-text');
    if (titleLabel) {
        titleLabel.setAttribute('aria-label', 'Add Name');
        titleLabel.parentElement.querySelector('label').textContent = 'Add Name';
    }
});