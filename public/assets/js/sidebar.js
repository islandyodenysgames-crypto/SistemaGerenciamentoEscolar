const sidebar = document.getElementById('sidebar');

const toggle = document.getElementById('sidebarToggle');

if (sidebar && toggle) {

    toggle.addEventListener('click', () => {

        sidebar.classList.toggle('collapsed');

    });

}