document.addEventListener('DOMContentLoaded', function () {
    console.log('Script loaded'); // Debugging: Check if the script is running

    // Smooth scrolling for in-page navigation
    const sidebarLinks = document.querySelectorAll('.sb-sidebar-navigation a[data-scroll-target]');
    sidebarLinks.forEach((link) => {
        link.addEventListener('click', function (e) {
            const targetId = this.getAttribute('data-scroll-target');

            if (targetId && targetId.startsWith('#')) {
                e.preventDefault();
                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    console.log('Scrolling to in-page target:', targetId); // Debugging
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });

    // Auto-scroll to results section on page load
    const mainContentSection = document.querySelector('.sb-main-content');
    if (mainContentSection) {
        console.log('Main content section found. Scrolling into view...'); // Debugging
        mainContentSection.scrollIntoView({ behavior: 'smooth' });
    } else {
        console.log('Main content section not found on this page.'); // Debugging
    }
});