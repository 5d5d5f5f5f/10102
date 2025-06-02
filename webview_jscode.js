(function(){
    // ✅ 自动写入直播源接口地址
    (function(){
        const autoSourceUrl = "https://web125.pl10000.top";
        const configKey = "tvurl"; // 根据酷9识别的key来改，常见为tvurl或iptv_url
        if (!localStorage.getItem(configKey)) {
            localStorage.setItem(configKey, autoSourceUrl);
            console.log("直播源接口地址已写入：" + autoSourceUrl);
        }
    })();

    const startTime = Date.now();

    // 增强版Shadow DOM查询（保留您原有的函数结构）
    function getVideoParentShadowRoots() {
        const walker = document.createTreeWalker(document, NodeFilter.SHOW_ELEMENT);
        let node;
        while ((node = walker.nextNode())) {
            if (node.shadowRoot) {
                const deepFind = (root) => {
                    const innerWalker = root.createTreeWalker(root, NodeFilter.SHOW_ELEMENT);
                    let innerNode;
                    while ((innerNode = innerWalker.nextNode())) {
                        if (innerNode.shadowRoot) {
                            const video = innerNode.shadowRoot.querySelector('video');
                            if (video) return video;
                            const result = deepFind(innerNode.shadowRoot);
                            if (result) return result;
                        }
                    }
                    return null;
                };
                const video = deepFind(node.shadowRoot);
                if (video) return video;
            }
        }
        return null;
    }

    function removeControls() {
        const selectors = [
            '#control_bar', '.controls', 
            '.vjs-control-bar', 'xg-controls',
            '.xgplayer-ads', '.fixed-layer',
            'div[style*="z-index: 9999"]'
        ];
        selectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(e => {
                e.style.display = 'none';
                e.parentNode?.removeChild(e);
            });
        });
    }

    function setupVideo(video) {
        const container = document.createElement('div');
        container.style.cssText = `
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            z-index: 2147483647 !important;
            background: black !important;
            overflow: hidden !important;
            transform: translateZ(0);
        `;

        video.style.cssText = `
            width: 100% !important;
            height: 100% !important;
            object-fit: fill !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
        `;

        document.body.appendChild(container);
        container.appendChild(video);

        const tryPlay = () => {
            if (video.paused) {
                video.play().catch(() => {
                    video.muted = false;
                    video.play();
                });
            }
        };

        const enterFullscreen = () => {
            const fullscreenElem = container.requestFullscreen ? container : video;
            const requestFS = fullscreenElem.requestFullscreen || 
                            fullscreenElem.webkitRequestFullscreen || 
                            fullscreenElem.mozRequestFullScreen;

            if(requestFS) {
                requestFS.call(fullscreenElem).catch(() => {
                    container.style.width = `${window.innerWidth}px`;
                    container.style.height = `${window.innerHeight}px`;
                });
            }
            video.volume = 1;
        };

        setTimeout(() => {
            tryPlay();
            enterFullscreen();
        }, 300);
    }

    function checkVideo() {
        if (Date.now() - startTime > 15000) {
            clearInterval(interval);
            return;
        }

        let video = document.querySelector('video') || getVideoParentShadowRoots();

        if (video && video.readyState > 0) {
            clearInterval(interval);
            removeControls();
            setupVideo(video);

            if (video.muted || video.volume === 0) {
                video.muted = false;
                video.volume = 1.0;
            }
        }
    }

    const interval = setInterval(checkVideo, 100);

    const viewportMeta = document.createElement('meta');
    viewportMeta.name = "viewport";
    viewportMeta.content = "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no";
    document.head.appendChild(viewportMeta);
})();
