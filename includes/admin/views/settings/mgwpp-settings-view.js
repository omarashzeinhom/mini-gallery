document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.nav-tab');
            const contents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = this.getAttribute('href').substring(1);
                    
                    tabs.forEach(t => t.classList.remove('nav-tab-active'));
                    contents.forEach(c => c.classList.remove('active'));
                    
                    this.classList.add('nav-tab-active');
                    document.getElementById(target).classList.add('active');
                });
            });
        });