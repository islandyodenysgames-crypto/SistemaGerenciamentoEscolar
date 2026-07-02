const sidebar = document.getElementById('sidebar');

const sidebarButtons = [

    document.getElementById('sidebarToggle'),

    document.getElementById('sidebarToggleTop')

];

sidebarButtons.forEach(button => {

    if (!button) return;

    button.addEventListener('click', () => {

        sidebar.classList.toggle('collapsed');

    });

});