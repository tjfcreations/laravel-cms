<div>
    <iframe id="logViewer" src="{{ route('log-viewer.index') }}" style="width:100%; height:100%; border:none;"></iframe>

    <style>
        .fi-main,
        .fi-main>div {
            padding: 0 !important;
            height: 100% !important;
        }
    </style>

    <script>
        const iframe = document.getElementById('logViewer');

        iframe.onload = () => {
            const doc = iframe.contentDocument || iframe.contentWindow.document;

            const style = doc.createElement('style');
            style.textContent = `
                nav h1, nav h1 + div > a { display: none !important; }
                nav > div > div { margin-top: 0 !important; }
                a[href*="buymeacoffee.com"] { display: none !important; }
            `;

            doc.head.prepend(style);
        };
    </script>

</div>
