<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: nasalogin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
    <title>Inventory Hierarchy - Mermaid</title>
    <link rel="stylesheet" href="https://use.typekit.net/pen4uct.css">
    <link rel="stylesheet" href="default.css">
    <!-- Local libraries -->
    <script src="js/mermaid.min.js"></script>
    <script src="js/hammer.min.js"></script>

    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: "League Spartan", sans-serif;
            background: #0a0e16;
            color: #e0e8ff;
            touch-action: none;
            /* ← prevents browser pinch/scroll interference */
        }

        #app {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        #header {
            padding: 16px 24px;
            background: rgba(10, 14, 22, 0.85);
            border-bottom: 1px solid rgba(120, 150, 255, 0.25);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        #mermaid-container {
            flex: 1;
            padding: 10px;
            overflow: hidden;
            /* ← important for mobile */
            background: radial-gradient(circle at 50% 30%, #1a2030 0%, #0b111c 100%);
        }

        #mermaid-viewport {
            width: 100%;
            height: 100%;
            overflow: hidden;
            position: relative;
            cursor: grab;
            touch-action: none;
            /* ← allows custom gestures */
        }

        #mermaid-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            transform-origin: 0 0;
            will-change: transform;
        }

        #details-panel {
            position: absolute;
            bottom: 16px;
            left: 16px;
            width: 90%;
            max-width: 420px;
            max-height: 55vh;
            overflow-y: auto;
            background: rgba(10, 14, 22, 0.92);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid rgba(120, 150, 255, 0.35);
            backdrop-filter: blur(8px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6);
            z-index: 20;
            touch-action: pan-y pinch-zoom;
            /* ← allows scrolling + pinch inside panel */
        }

        /* Individual item cards - full width, column-friendly */
        .item-card {
            background: rgba(20, 30, 50, 0.7);
            border: 1px solid rgba(90, 140, 255, 0.3);
            border-radius: 8px;
            padding: 14px 16px;
            /* slightly more padding */
            width: 100%;
            /* full width of container */
            box-sizing: border-box;
            /* prevents padding overflow */
            transition: all 0.2s ease;
            margin-bottom: 1rem;
        }

        /* Hover & highlighted states */
        .item-card:hover {
            background: rgba(40, 60, 100, 0.8);
            cursor: pointer;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(90, 140, 255, 0.4);
        }

        .item-card.highlighted {
            background: rgba(60, 100, 180, 0.7) !important;
            border-color: #60a5fa;
            box-shadow: 0 0 12px rgba(96, 165, 250, 0.5);
            transform: translateY(-2px);
        }

        /* Optional: make text more readable in column layout */
        .item-card strong {
            display: block;
            font-size: 15px;
            margin-bottom: 6px;
        }

        .item-card small {
            display: block;
            line-height: 1.4;
            margin: 4px 0;
        }

        /* Button inside card - make it full-width or centered */
        .go-to-item-btn {
            font-family: "League Spartan", sans-serif;
            margin-top: 12px;
            width: 100%;
            /* full width button looks better in column */
            padding: 8px;
            background: #4f46e5;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .go-to-item-btn:hover {
            background: #6366f1;
        }

        .back-btn {
            padding: 10px 18px;
            background: #c53030;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        .back-btn:hover {
            background: #e53e3e;
        }


        /* Scrollbar styling */
        #details-panel::-webkit-scrollbar {
            width: 8px;
        }

        #details-panel::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.04);
        }

        #details-panel::-webkit-scrollbar-thumb {
            background: rgba(120, 150, 255, 0.5);
            border-radius: 4px;
        }

        .flowchart .node.selected rect {
            fill: #2a4a8a !important;
            /* darker blue fill */
            stroke: #60a5fa !important;
            /* bright blue border */
            stroke-width: 3.5px !important;
            filter: drop-shadow(0 0 10px #60a5fa88);
            transition: all 0.18s ease;
        }

        .flowchart .node rect {
            transition: all 0.18s ease;
            /* smooth change */
        }

        /* Optional: subtle hover effect too */
        .flowchart .node:hover rect {
            filter: brightness(1.15);
        }

        h3 {
            margin-bottom: 1rem;
        }

        #node-search-container {
            position: relative;
            width: 240px;
            max-width: 45vw;
        }

        #node-search {
            width: 100%;
            padding: 8px 12px;
            font-size: 14px;
            background: rgba(30, 40, 70, 0.8);
            border: 1px solid rgba(120, 150, 255, 0.4);
            border-radius: 6px;
            color: #e0e8ff;
            font-family: "League Spartan", sans-serif;
        }

        #node-search:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.25);
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 320px;
            overflow-y: auto;
            background: rgba(10, 14, 22, 0.95);
            border: 1px solid rgba(120, 150, 255, 0.4);
            border-radius: 6px;
            margin-top: 4px;
            z-index: 25;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
            display: none;
            backdrop-filter: blur(6px);
        }

        .search-results.visible {
            display: block;
        }

        .search-result-item {
            padding: 10px 14px;
            cursor: pointer;
            transition: all 0.15s;
            border-bottom: 1px solid rgba(120, 150, 255, 0.15);
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover,
        .search-result-item:focus {
            background: rgba(60, 100, 180, 0.5);
        }

        .search-result-item .name {
            font-weight: 500;
            display: block;
        }

        .search-result-item .path {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 3px;
        }

        .search-result-item .stats {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        .search-results::-webkit-scrollbar {
            width: 8px;
        }

        .search-results::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .search-results::-webkit-scrollbar-thumb {
            background: rgba(75, 83, 185, 0.6);
            border-radius: 10px;
        }

        .search-results::-webkit-scrollbar-thumb:hover {
            background: rgba(75, 83, 185, 0.8);
        }

        .nasalogo {
            top: 5rem;
        }
    </style>


</head>

<body>

    <div id="app">

        <div id="header">
            <!-- Left side -->
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <div id="current-node">Select a location</div>
                <div id="path"></div>
            </div>

            <!-- Right side – search takes priority on the far right -->
            <div style="display: flex; align-items: center; gap: 16px;">
                <div id="node-search-container">
                    <input type="text" id="node-search" placeholder="Search locations..." autocomplete="off">
                    <div id="search-results" class="search-results"></div>
                </div>

                <button id="back-btn" class="back-btn" style="display:none;">↑ Back</button>
                <a href="dashboard.php"><img src="images/NASA-Logo.png" alt="Nasa Logo" class="nasalogo"></a>
            </div>
        </div>

        <!-- Replace the entire <div id="mermaid-container"> ... </div> block with this -->

        <div id="mermaid-container">
            <div id="mermaid-viewport" style="
        width: 100%;
        height: 100%;
        overflow: hidden;
        position: relative;
        cursor: grab;
    ">
                <div id="mermaid-wrapper" style="
            position: absolute;
            top: 0;
            left: 0;
            transform-origin: 0 0;
            will-change: transform;
        ">
                    <div id="mermaid-output"></div>
                </div>
            </div>
        </div>

        <div id="details-panel">
            <h3>Items</h3>
            <div id="items-list">Select a node to see items</div>
        </div>
    </div>

    <script>
        // =============================
        // MERMAID CONFIG
        // =============================
        mermaid.initialize({
            startOnLoad: false,
            theme: 'dark',
            flowchart: {
                curve: 'basis',
                useMaxWidth: false,
                htmlLabels: true,
                nodeSpacing: 60,
                rankSpacing: 100,
            },
        });

        // =============================
        // PAN + ZOOM ENGINE
        // =============================
        const viewport = document.getElementById('mermaid-viewport');
        const wrapper = document.getElementById('mermaid-wrapper');

        let scale = 1;
        let panX = 0;
        let panY = 60;

        let lastScale = 1;

        // Smooth transform update
        function updateTransform() {
            wrapper.style.transform = `translate(${panX}px, ${panY}px) scale(${scale})`;
        }

        // =============================
        // DESKTOP CONTROLS
        // =============================
        let isPanning = false;
        let startX, startY;

        viewport.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return;

            startX = e.clientX - panX;
            startY = e.clientY - panY;
            isPanning = true;
            viewport.style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isPanning) return;

            panX = e.clientX - startX;
            panY = e.clientY - startY;
            updateTransform();
        });

        document.addEventListener('mouseup', () => {
            isPanning = false;
            viewport.style.cursor = 'grab';
        });

        // =============================
        // DESKTOP ZOOM
        // =============================
        viewport.addEventListener('wheel', (e) => {
            e.preventDefault();

            const zoomSpeed = 0.04;
            const oldScale = scale;

            scale += e.deltaY > 0 ? -zoomSpeed : zoomSpeed;
            scale = Math.max(0.3, Math.min(5, scale));

            const rect = viewport.getBoundingClientRect();
            const mx = e.clientX - rect.left;
            const my = e.clientY - rect.top;

            const factor = scale / oldScale;

            panX = mx - (mx - panX) * factor;
            panY = my - (my - panY) * factor;

            /* CRITICAL FIX */
            targetPanX = panX;
            targetPanY = panY;

            updateTransform();
        }, {
            passive: false
        });

        // =============================
        // HAMMER MOBILE
        // =============================
        const hammer = new Hammer(viewport, {
            touchAction: 'none'
        });

        hammer.get('pan').set({
            direction: Hammer.DIRECTION_ALL,
            threshold: 2,
        });

        hammer.get('pinch').set({
            enable: true
        });

        // =============================
        // PERFECT SMOOTH PAN ENGINE
        // =============================

        let targetPanX = panX;
        let targetPanY = panY;

        let velocityX = 0;
        let velocityY = 0;

        let lastPanX = 0;
        let lastPanY = 0;


        // 60FPS smoothing engine
        function smoothEngine() {

            panX += (targetPanX - panX) * 0.22;
            panY += (targetPanY - panY) * 0.22;

            // No inertia
            velocityX = 0;
            velocityY = 0;

            updateTransform();

            requestAnimationFrame(
                smoothEngine
            );

        }

        smoothEngine();


        // Start pan
        hammer.on('panstart', () => {

            lastPanX = 0;
            lastPanY = 0;

            velocityX = 0;
            velocityY = 0;

        });


        // During pan
        hammer.on('pan', ev => {

            const dx =
                ev.deltaX - lastPanX;

            const dy =
                ev.deltaY - lastPanY;

            lastPanX = ev.deltaX;
            lastPanY = ev.deltaY;

            targetPanX += dx;
            targetPanY += dy;

            velocityX = dx * 0.15;
            velocityY = dy * 0.15;

        });


        // End pan
        hammer.on('panend', () => {

            velocityX = 0;
            velocityY = 0;

        });

        // =============================
        // PERFECT PINCH ZOOM
        // =============================

        let startScale = 1;
        let startPanX = 0;
        let startPanY = 0;
        let pinchCenterX = 0;
        let pinchCenterY = 0;


        // Pinch start
        hammer.on('pinchstart', (ev) => {

            const rect =
                viewport.getBoundingClientRect();

            startScale = scale;

            startPanX = panX;
            startPanY = panY;

            pinchCenterX =
                ev.center.x - rect.left;

            pinchCenterY =
                ev.center.y - rect.top;

        });


        // Pinching
        hammer.on('pinch', (ev) => {

            let newScale =
                startScale * ev.scale;

            newScale =
                Math.max(0.3,
                    Math.min(5, newScale));

            const scaleFactor =
                newScale / startScale;

            scale = newScale;

            panX =
                pinchCenterX -
                (pinchCenterX - startPanX) * scaleFactor;

            panY =
                pinchCenterY -
                (pinchCenterY - startPanY) * scaleFactor;

            /* CRITICAL FIX */
            targetPanX = panX;
            targetPanY = panY;

            updateTransform();

        });

        hammer.on('doubletap', () => {

            // Remove highlight from nodes
            document
                .querySelectorAll('.node')
                .forEach(n =>
                    n.classList.remove('selected')
                );

            // Clear selected node
            currentNodeId = null;

            currentNodeEl.textContent =
                "Select a location";

            pathEl.textContent = "";

            itemsList.innerHTML =
                "Select a node to see items";

        });

        // BLOCK BROWSER ZOOM
        viewport.addEventListener('touchmove', (e) => {
            if (e.touches.length >= 2) e.preventDefault();
        }, {
            passive: false
        });

        // =============================
        // MOBILE START POSITION
        // =============================
        if (window.innerWidth < 768) {
            scale = 0.45;
            panY = 20;
            updateTransform();
        }
        let treeData = null;
        let currentNodeId = null;

        // ─── NODE SEARCH ────────────────────────────────────────────────

        const searchInput = document.getElementById('node-search');
        const searchResults = document.getElementById('search-results');

        let allNodesFlat = [];

        // Build flat list of nodes once hierarchy is loaded
        function buildFlatNodeList(nodes, path = []) {
            const list = [];
            for (const node of nodes) {
                const fullPath = [...path, node.name];
                list.push({
                    id: String(node.id),
                    name: node.name,
                    path: fullPath.slice(0, -1).join(" › "),
                    itemCount: node.item_count || 0,
                    childrenCount: node.children?.length || 0
                });
                if (node.children?.length) {
                    list.push(...buildFlatNodeList(node.children, fullPath));
                }
            }
            return list;
        }

        // Called from loadAndRender after treeData is set
        function prepareSearch() {
            allNodesFlat = buildFlatNodeList(treeData);
        }

        function renderSearchResults(matches) {
            if (!matches.length) {
                searchResults.innerHTML = '<div class="search-result-item" style="color:#94a3b8;padding:12px;text-align:center;">No matches</div>';
                searchResults.classList.add('visible');
                return;
            }

            let html = '';
            matches.forEach(match => {
                html += `
            <div class="search-result-item" data-node-id="${match.id}">
                <span class="name">${match.name}</span>
                ${match.path ? `<span class="path">${match.path}</span>` : ''}
                <div class="stats">${match.childrenCount} children • ${match.itemCount} items</div>
            </div>
        `;
            });
            searchResults.innerHTML = html;
            searchResults.classList.add('visible');

            // Click handler for results
            searchResults.querySelectorAll('.search-result-item').forEach(item => {
                item.addEventListener('click', () => {
                    const nodeId = item.dataset.nodeId;
                    selectNode(nodeId);

                    // Also try to find & highlight the node in diagram
                    const nodeEl = document.querySelector(`.node[id^="flowchart-n${nodeId}"]`) ||
                        document.querySelector(`.node[id$="n${nodeId}"]`);
                    if (nodeEl) {
                        document.querySelectorAll('.node').forEach(n => n.classList.remove('selected'));
                        nodeEl.classList.add('selected');
                        centerOnNode(nodeEl);
                    }

                    // Hide results & clear input
                    searchInput.value = '';
                    searchResults.classList.remove('visible');
                    searchResults.innerHTML = '';
                });
            });
        }

        // Live search
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim().toLowerCase();
            if (term.length < 1) {
                searchResults.classList.remove('visible');
                return;
            }

            const matches = allNodesFlat.filter(node =>
                node.name.toLowerCase().includes(term)
            ).slice(0, 15); // limit shown results

            renderSearchResults(matches);
        });

        // Hide results when clicking outside
        document.addEventListener('click', e => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('visible');
            }
        });

        // Optional: focus search with keyboard shortcut (e.g. / or Ctrl+K)
        document.addEventListener('keydown', e => {
            if (e.key === '/' && !e.ctrlKey && !e.metaKey && document.activeElement !== searchInput) {
                e.preventDefault();
                searchInput.focus();
            }
        });

        const mermaidDiv = document.getElementById('mermaid-output');
        const currentNodeEl = document.getElementById('current-node');
        const pathEl = document.getElementById('path');
        const itemsList = document.getElementById('items-list');

        async function loadAndRender() {
            try {
                const res = await fetch("database/get_hierarchy.php");
                if (!res.ok) throw new Error(`Fetch failed: ${res.status}`);

                const rawText = await res.text();
                let parsedData;
                try {
                    parsedData = JSON.parse(rawText);
                } catch (e) {
                    console.error("JSON parse error:", e, rawText.substring(0, 300));
                    mermaidDiv.innerHTML = '<p style="color:#ff6b6b">Invalid server response</p>';
                    return;
                }

                treeData = parsedData;

                // ─── CRITICAL: Attach .parent references ───────────────────────
                function attachParents(nodes, parent = null) {
                    for (const node of nodes) {
                        node.parent = parent; // ← this line is usually missing!
                        if (node.children && Array.isArray(node.children)) {
                            attachParents(node.children, node);
                        }
                    }
                }

                if (Array.isArray(treeData)) {
                    attachParents(treeData, null); // start with root nodes having parent = null
                }

                prepareSearch();

                if (treeData?.length > 0) {
                    renderMermaid(treeData);
                    selectNode(treeData[0].id); // auto-select root
                } else {
                    mermaidDiv.innerHTML = '<p>No hierarchy data</p>';
                }
            } catch (err) {
                console.error("Load failed:", err);
                mermaidDiv.innerHTML = `<p style="color:#ff6b6b">Error: ${err.message}</p>`;
            }
        }

        function extractAllIds(nodes, collected = new Set()) {
            for (const node of nodes) {
                if (node.id != null) collected.add(String(node.id));
                if (node.children && Array.isArray(node.children)) {
                    extractAllIds(node.children, collected);
                }
            }
            return Array.from(collected).sort();
        }

        function buildMermaidGraph(nodes, parentId = null) {
            let graph = "graph TD\n";

            function addNode(node, parent) {
                const safeId = 'n' + String(node.id).replace(/[^a-zA-Z0-9]/g, '_');
                const safeName = node.name.replace(/"/g, '\\"');

                graph += `    ${safeId}["${safeName}<br/>(${node.children?.length || 0} children • ${node.item_count || 0} items)"]\n`;

                if (parent) {
                    graph += `    ${parent} --> ${safeId}\n`;
                }

                if (node.children?.length) {
                    node.children.forEach(child => addNode(child, safeId));
                }
            }

            nodes.forEach(node => addNode(node, parentId));
            return graph;
        }

        function renderMermaid(nodes) {
            const graphDefinition = buildMermaidGraph(nodes);
            console.log("Mermaid graph definition:", graphDefinition);

            mermaid.render('graphDiv', graphDefinition).then(({
                svg
            }) => {
                mermaidDiv.innerHTML = svg;
                mermaidDiv.querySelectorAll('a').forEach(a => a.removeAttribute('href'));

                // Make nodes clickable + highlight
                document.querySelectorAll('.node').forEach(nodeEl => {
                    let rawId = nodeEl.id.replace('flowchart-', '');
                    let cleaned = rawId.replace(/^n/, '');
                    const id = cleaned.split(/[^0-9]/)[0];

                    nodeEl.style.cursor = 'pointer';

                    nodeEl.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();

                        document.querySelectorAll('.node').forEach(n => n.classList.remove('selected'));
                        nodeEl.classList.add('selected');

                        centerOnNode(nodeEl);
                        selectNode(id);
                    });
                });

                // Center on root node after render
                requestAnimationFrame(() => {
                    const svgEl = mermaidDiv.querySelector('svg');
                    if (!svgEl) return;

                    const rootNodeEl = mermaidDiv.querySelector('.node') ||
                        mermaidDiv.querySelector('[id^="flowchart-"]');

                    if (rootNodeEl) {
                        const rootRect = rootNodeEl.getBoundingClientRect();
                        const viewportRect = viewport.getBoundingClientRect();

                        const targetX = viewportRect.width / 2 - (rootRect.left - viewportRect.left) - (rootRect.width / 2);
                        const targetY = 100 - (rootRect.top - viewportRect.top);

                        panX = targetX;
                        panY = targetY;

                        targetPanX = targetX;
                        targetPanY = targetY;

                        scale = 1;

                        updateTransform();
                    }
                });
            }).catch(err => {
                console.error("Mermaid render error:", err);
                mermaidDiv.innerHTML = "<p style='color:#ff6b6b'>Diagram render failed</p>";
            });
        }

        function centerOnNode(nodeEl, duration = 700) {
            if (!nodeEl) return;

            const nodeRect = nodeEl.getBoundingClientRect();
            const viewportRect = viewport.getBoundingClientRect();

            const nodeCenterX = nodeRect.left + nodeRect.width / 2;
            const viewportCenterX = viewportRect.left + viewportRect.width / 2;

            const nodeCenterY = nodeRect.top + nodeRect.height / 2;
            const viewportCenterY = viewportRect.top + viewportRect.height / 2;

            const newTargetPanX =
                panX + (viewportCenterX - nodeCenterX);

            const newTargetPanY =
                panY + (viewportCenterY - nodeCenterY);

            /* Update engine target */
            targetPanX = newTargetPanX;
            targetPanY = newTargetPanY;

            const startX = panX;
            const startY = panY;
            let startTime = null;

            function easeOutQuad(t) {
                return t * (2 - t);
            }

            function animate(time) {
                if (!startTime) startTime = time;
                const elapsed = time - startTime;
                let progress = elapsed / duration;
                progress = Math.min(1, Math.max(0, progress));
                const eased = easeOutQuad(progress);

                panX = startX + (newTargetPanX - startX) * eased;
                panY = startY + (newTargetPanY - startY) * eased;

                /* CRITICAL FIX */
                window.targetPanX = panX;
                window.targetPanY = panY;

                updateTransform();

                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            }

            requestAnimationFrame(animate);
        }

        function buildPath(node) {
            if (!node) return "—";

            const parts = [];
            let current = node;
            while (current) {
                parts.unshift(current.name);
                current = current.parent;
            }
            return parts.join(" › ");
        }

        function findNode(nodes, id) {
            for (const node of nodes) {
                if (String(node.id) === String(id)) {
                    return node;
                }
                if (node.children?.length) {
                    const found = findNode(node.children, id);
                    if (found) return found;
                }
            }
            return null;
        }

        async function selectNode(nodeId) {
            console.log("selectNode START — nodeId:", nodeId);

            if (!treeData) {
                itemsList.innerHTML = "<span style='color:#ff6b6b'>Hierarchy data not loaded</span>";
                return;
            }

            let node = null;

            function search(nodes) {
                for (const n of nodes) {
                    if (String(n.id) === String(nodeId)) {
                        node = n;
                        return true;
                    }
                    if (n.children?.length) {
                        if (search(n.children)) return true;
                    }
                }
                return false;
            }
            search(treeData);

            if (!node) {
                currentNodeEl.textContent = "Unknown location";
                pathEl.textContent = "";
                itemsList.innerHTML = "<span style='color:#ff6b6b'>Location not found</span>";
                return;
            }

            currentNodeId = nodeId;
            currentNodeEl.textContent = node.name;
            pathEl.textContent = buildPath(node);


            itemsList.innerHTML = "Loading...";

            try {
                const res = await fetch(`database/get_items_by_node.php?id=${nodeId}`);
                if (!res.ok) throw new Error(`Server returned ${res.status}`);

                const items = await res.json();

                if (!items?.length) {
                    itemsList.innerHTML = "No items in this location.";
                    return;
                }

                let html = "";
                items.forEach(item => {
                    html += `
                <div class="item-card" data-item-id="${item.id}">
                    <strong>${item.name}</strong>
                    <small>Type: ${item.type} • Expiry: ${item.expiry_date || '-'}</small>
                    <small>Remaining: ${item.remaining_percent ?? 100}% • RFID: ${item.rfid || '-'}</small>
                    <button class="go-to-item-btn" style="
                        margin-top: 10px;
                        padding: 6px 12px;
                        background: #4f46e5;
                        border: none;
                        border-radius: 6px;
                        color: white;
                        font-size: 13px;
                        cursor: pointer;
                    ">Go to item →</button>
                </div>
            `;
                });
                itemsList.innerHTML = html;

                // Item card interactions
                document.querySelectorAll('.item-card').forEach(card => {

                    const btn = card.querySelector('.go-to-item-btn');
                    if (btn) {
                        btn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            const itemId = card.dataset.itemId;
                            if (itemId) {
                                window.location.href = `inventory.php?highlight=item-${itemId}`;
                            }
                        });
                    }
                });
            } catch (err) {
                console.error("Items fetch failed:", err);
                itemsList.innerHTML = `<span style='color:#ff6b6b'>Error loading items: ${err.message}</span>`;
            }
        }

        // Back button handler (outside renderMermaid)


        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadAndRender();
        });
    </script>

</body>

</html>