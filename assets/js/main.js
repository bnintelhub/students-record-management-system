// assets/js/main.js
// Dynamic interactions, live filtering, and file upload previews

document.addEventListener('DOMContentLoaded', () => {
    // 1. Live Table Search
    const searchInput = document.getElementById('studentSearchInput');
    const tableBody = document.getElementById('studentTableBody');

    if (searchInput && tableBody) {
        searchInput.addEventListener('keyup', () => {
            const query = searchInput.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // 2. Photo Upload Preview
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');

    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    photoPreview.src = e.target.result;
                    photoPreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // 3. Confirm Delete helper
    window.confirmDelete = function(event, message = "Are you sure you want to delete this record?") {
        if (!confirm(message)) {
            event.preventDefault();
            return false;
        }
        return true;
    };
});
