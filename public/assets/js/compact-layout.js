(() => {
    const sidebar = document.querySelector('.layout-sidebar');
    const button = document.querySelector('.compact-button');
    const toggle = document.querySelector('.toggle-button');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');

    if (sidebar) {
        const sidebarWidth = sidebar.style.maxWidth;
        const { compactWidth } = sidebar.dataset;

        button.addEventListener('click', () => {
            sidebar.querySelector('.sidebar-content').style = 'width: 100%;';
            const content = sidebar.querySelector('.sidebar-content');
            content.style = `width: ${sidebarWidth}; transition: all 0ms;`;

            if (sidebar.classList.contains('compact')) {
                sidebar.classList.remove('compact');
                sidebar.style = `max-width: ${sidebarWidth};`;
            } else {
                sidebar.classList.add('compact');
                sidebar.style = `max-width: ${compactWidth}px;`;
            }
        }) 

        let collapseHeight = [];
        sidebar.addEventListener('mouseenter', () => {
            if (sidebar.classList.contains('compact')) {
                let collapse = 0;
                document.querySelectorAll('.collapse-content').forEach(content => {
                    content.style.maxHeight = collapseHeight[collapse];
                    content.style.transition = 'all 0ms';
                    collapse++;
                })
                const content = sidebar.querySelector('.sidebar-content');
                content.style = `width: ${sidebarWidth};`;
            }
        })
        sidebar.addEventListener('mouseleave', () => {
            if (sidebar.classList.contains('compact')) {
                let collapse = 0;
                document.querySelectorAll('.collapse-content').forEach(content => {
                    collapseHeight[collapse] = content.style.maxHeight
                    content.style.maxHeight = '0px';
                    content.style.transition = 'all 0ms';
                    collapse++;
                })
                const content = sidebar.querySelector('.sidebar-content');
                content.style = `width: ${compactWidth}px;`;
            }
        })

        // hide sidebar on mobile screen
        window.onresize = displayWindowSize;
        window.onload = displayWindowSize;
        function displayWindowSize() {
            let screenSize = window.innerWidth;
            if (screenSize < 992) {
                sidebar.style = `max-width: 0px; overflow: hidden;`;
            }else {
                if (sidebar.classList.contains('pos-sidebar')) {
                    sidebar.style = `max-width: 0px; overflow: hidden;`;
                }else{
                    sidebar.style = `max-width: ${sidebarWidth}; overflow: auto;`;
                }
            }
        }

        // control sidebar hide and show by toggle button
        toggle.addEventListener('click', () => {
            const width = sidebar.style.maxWidth;
            if (width == '0px') {
                if (sidebar.classList.contains('compact')) {
                    sidebar.style = `max-width: ${compactWidth}px;`;
                } else {
                    sidebar.style = `max-width: ${sidebarWidth}; overflow: auto;`;
                }
            }else{
                sidebar.style = `max-width: 0px; overflow: hidden;`;
            }
        })
    }

    // mobile sidebar handle
    if (mobileSidebar) {
        const content = mobileSidebar.querySelector('.layout-sidebar-mobile');
        const compactButton = mobileSidebar.querySelector('.close-sidebar');

        mobileSidebar.addEventListener('click', function(event) {
            if (!content.contains(event.target)) {
                content.style.maxWidth = '0px';
            }
        });
        mobileSidebarToggle.addEventListener('click', () => {
            content.style.maxWidth = '260px';
        })
        compactButton.addEventListener('click', () => {
            content.style.maxWidth = '0px';
            mobileSidebarToggle.click();
        })
    }
})();
