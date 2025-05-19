<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Embed_Editor_View {
    public function render() {
        ?>
        <div class="wrap">
            <h2>Embed Editor</h2>
            
            <div class="mgwpp-iframe-wrapper">
                <iframe 
                    src="https://v0-update-and-deploy.vercel.app/"
                    width="100%"
                    height="100%"
                    frameborder="0"
                ></iframe>
            </div>
            
            <p>
                <a 
                    href="https://v0-update-and-deploy.vercel.app/" 
                    target="_blank" 
                    rel="noopener noreferrer"
                >
                    Open Editor in New Tab ↗
                </a>
            </p>
        </div>
        
        <style>
            .mgwpp-iframe-wrapper {
                height: 700px;
                max-width: 1200px;
                margin: 20px 0;
                border: 1px solid #ccd0d4;
                background: #fff;
            }
        </style>
        <?php 
    }
}