document.addEventListener("DOMContentLoaded", function() {
    const seeMoreButtons = document.querySelectorAll('.see-more');

    seeMoreButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const shortDesc = this.previousElementSibling.previousElementSibling;
            const fullDesc = shortDesc.nextElementSibling;

            shortDesc.style.display = (shortDesc.style.display === 'none') ? 'inline' : 'none';
            fullDesc.style.display = (fullDesc.style.display === 'none') ? 'inline' : 'none';

            this.textContent = (fullDesc.style.display === 'inline') ? 'See Less' : 'See More';
        });
    });
});