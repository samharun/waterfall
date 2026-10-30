(() => {
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    // Keep the server-rendered copy when animation or Bengali segmentation is unavailable.
    if (motion.matches || typeof Intl.Segmenter !== 'function') return;
    const segmenter = new Intl.Segmenter(document.documentElement.lang || 'bn', { granularity: 'grapheme' });
    const hosts = [...document.querySelectorAll('.hero-copy h1, .hero-description')];
    const prepared = hosts.map(host => {
        const source = document.createElement('span');
        source.className = 'typing-source';
        source.append(...host.childNodes);
        const layer = document.createElement('span');
        layer.className = 'typing-layer';
        layer.setAttribute('aria-hidden', 'true');
        layer.append(...[...source.childNodes].map(node => node.cloneNode(true)));
        const walker = document.createTreeWalker(layer, NodeFilter.SHOW_TEXT);
        const parts = [];
        while (walker.nextNode()) {
            const node = walker.currentNode;
            parts.push({ node, letters: [...segmenter.segment(node.textContent)].map(item => item.segment) });
            node.textContent = '';
        }
        host.classList.add('typing-host');
        host.append(source, layer);
        return { host, source, layer, parts };
    });
    let stopped = false;
    const finish = () => {
        stopped = true;
        for (const { host, source, layer } of prepared) {
            host.replaceChildren(...source.childNodes);
            host.classList.remove('typing-host');
            layer.remove();
        }
        motion.removeEventListener('change', onMotionChange);
    };
    const onMotionChange = () => { if (motion.matches && !stopped) finish(); };
    motion.addEventListener('change', onMotionChange);
    const pause = ms => new Promise(resolve => setTimeout(resolve, ms));
    (async () => {
        await pause(250);
        while (!stopped) {
        const cycleStarted = performance.now();
        for (const { parts } of prepared) {
            for (const { node } of parts) node.textContent = '';
        }
        for (let index = 0; index < prepared.length && !stopped; index++) {
            const { parts } = prepared[index];
            const caret = document.createElement('span');
            caret.className = 'typing-caret';
            for (const { node, letters } of parts) {
                if (stopped) break;
                node.after(caret);
                for (const letter of letters) {
                    if (stopped) break;
                    node.textContent += letter;
                    await pause(index === 0 ? 85 : 28);
                }
            }
            caret.remove();
            await pause(180);
        }
        // Keep the complete text visible until ten seconds from this cycle's start.
        if (!stopped) await pause(Math.max(0, 10000 - (performance.now() - cycleStarted)));
        }
    })().catch(() => { if (!stopped) finish(); });
})();
