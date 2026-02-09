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
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Inventory Hierarchy - Mermaid</title>
    <link rel="stylesheet" href="https://use.typekit.net/pen4uct.css">
    
    <!-- Mermaid -->
    <script src="https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.min.js"></script>
    
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: "League Spartan", sans-serif;
            background: #0a0e16;
            color: #e0e8ff;
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

        #current-node {
            font-size: 22px;
            font-weight: 700;
        }

        #path {
            font-size: 15px;
            color: #a0b0ff;
            opacity: 0.9;
        }

        #mermaid-container {
            flex: 1;
            padding: 20px;
            overflow: auto;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            background: radial-gradient(circle at 50% 30%, #1a2030 0%, #0b111c 100%);
        }

/* Add this to your <style> block */
#mermaid-output svg {
    min-width: 1400px;          /* or whatever feels good */
    max-width: none !important;
}

.flowchart .node rect,
.flowchart .node foreignObject {
    min-width: 220px !important;
}



.flowchart .label {
    font-size: 15px !important;
}


        #details-panel {
            position: absolute;
            bottom: 20px;
            left: 20px;
            width: 380px;
            max-height: 60vh;
            overflow-y: auto;
            background: rgba(10, 14, 22, 0.92);
            border-radius: 12px;
            padding: 16px 20px;
            border: 1px solid rgba(120, 150, 255, 0.35);
            backdrop-filter: blur(8px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
            z-index: 20;
        }

        #details-panel h3 {
            margin: 0 0 12px 0;
            font-size: 18px;
        }

        .item-card {
            background: rgba(20, 30, 50, 0.7);
            border: 1px solid rgba(90, 140, 255, 0.3);
            border-radius: 8px;
            padding: 12px 16px;
            margin: 10px 0;
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
            background: rgba(255,255,255,0.04);
        }
        #details-panel::-webkit-scrollbar-thumb {
            background: rgba(120,150,255,0.5);
            border-radius: 4px;
        }
    </style>
            <link rel="stylesheet" href="default.css">

</head>
<body>

<div id="app">

    <div id= "header">
        <div>
            <div id="current-node">Select a location</div>
            <div id="path"></div>
        </div>
        <button id="back-btn" class="back-btn" style="display:none;">↑ Back</button>
                    <a href="dashboard.php"><img src="images/NASA-Logo.png" alt="Nasa Logo" class="nasalogo" style="top:5rem;"></a>

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
// Mermaid configuration
mermaid.initialize({
    startOnLoad: false,
    theme: 'dark',
    flowchart: {
        curve: 'basis',
        useMaxWidth: false,        // ← most important
        htmlLabels: true,
        nodeSpacing: 60,
        rankSpacing: 100
    }
});

// Global state
// --------------------------------------------------
// Improved Pan + Zoom
// --------------------------------------------------
const viewport = document.getElementById('mermaid-viewport');
const wrapper = document.getElementById('mermaid-wrapper');

let scale = 1;
let panX = 0;
let panY = 80;
let isPanning = false;
let startX, startY;

function updateTransform() {
    wrapper.style.transform = `translate(${panX}px, ${panY}px) scale(${scale})`;
}

viewport.addEventListener('mousedown', (e) => {
    if (e.button !== 0) return;
    isPanning = true;
    startX = e.clientX - panX;
    startY = e.clientY - panY;
    viewport.style.cursor = 'grabbing';
    e.preventDefault();
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

document.addEventListener('mouseleave', () => {
    isPanning = false;
    viewport.style.cursor = 'grab';
});

// Improved wheel zoom – zooms toward mouse cursor
viewport.addEventListener('wheel', (e) => {
    e.preventDefault();

    const zoomSpeed = 0.15;
    const delta = e.deltaY > 0 ? -zoomSpeed : zoomSpeed;

    const oldScale = scale;
    scale = Math.max(0.3, Math.min(5, scale + delta)); // limit zoom range

    // Zoom toward mouse position
    const rect = viewport.getBoundingClientRect();
    const mouseX = e.clientX - rect.left;
    const mouseY = e.clientY - rect.top;

    // Calculate how much to adjust pan to keep mouse point stable
    const factor = scale / oldScale;
    panX = mouseX - (mouseX - panX) * factor;
    panY = mouseY - (mouseY - panY) * factor;

    updateTransform();
});

// Optional: double-click to reset view
viewport.addEventListener('dblclick', () => {
    scale = 1;
    panX = 0;
    panY = 0;
    updateTransform();
});

// Optional: make cursor grab when hovering viewport
viewport.style.cursor = 'grab';

let treeData = null;
let currentNodeId = null;

// DOM elements
const mermaidDiv = document.getElementById('mermaid-output');
const currentNodeEl = document.getElementById('current-node');
const pathEl = document.getElementById('path');
const itemsList = document.getElementById('items-list');
const backBtn = document.getElementById('back-btn');

// --------------------------------------------------
// Load hierarchy & render
// --------------------------------------------------
async function loadAndRender() {
    try {
        const res = await fetch("database/get_hierarchy.php");
        treeData = await res.json();
        
        if (treeData?.length > 0) {
            // Start with root
            renderMermaid(treeData);
            selectNode(treeData[0].id);
        }
    } catch (err) {
        console.error("Failed to load hierarchy", err);
        mermaidDiv.innerHTML = "<p style='color:#ff6b6b'>Failed to load hierarchy</p>";
    }
}

function buildMermaidGraph(nodes, parentId = null) {
    let graph = "graph TD\n";
    
    function addNode(node, parent) {
        const safeId = node.id.replace(/[^a-zA-Z0-9]/g, '_');
        const safeName = node.name.replace(/"/g, '\\"');
        
        graph += `    ${safeId}["${safeName}<br/>(${node.children?.length || 0} children • ${node.item_count || 0} items)"]\n`;
        
        if (parent) {
            const safeParent = parent.replace(/[^a-zA-Z0-9]/g, '_');
            graph += `    ${safeParent} --> ${safeId}\n`;
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
    mermaid.render('graphDiv', graphDefinition).then(({ svg }) => {
        mermaidDiv.innerHTML = svg;
        mermaidDiv.querySelectorAll('a').forEach(a => {
            a.removeAttribute('href'); // stops browser scroll
        });
        
        // Make nodes clickable
        document.querySelectorAll('.node').forEach(nodeEl => {
            const id = nodeEl.id.replace('flowchart-', '');
            nodeEl.style.cursor = 'pointer';

            nodeEl.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                centerOnNode(nodeEl);
                selectNode(id);
            });
        });


        // === Center on the root node after render ===
        requestAnimationFrame(() => {
            const svgEl = mermaidDiv.querySelector('svg');
            if (!svgEl) return;

            // Find the first (root) node — Mermaid usually names it flowchart-0 or similar
            const rootNodeEl = mermaidDiv.querySelector('.node') || 
                             mermaidDiv.querySelector('[id^="flowchart-"]');

            if (rootNodeEl) {
                const rootRect = rootNodeEl.getBoundingClientRect();
                const viewportRect = viewport.getBoundingClientRect();

                // Calculate pan so the root is roughly in the center
                const targetX = viewportRect.width / 2 - (rootRect.left - viewportRect.left) - (rootRect.width / 2);
                const targetY = 100 - (rootRect.top - viewportRect.top); // 100px from top is nice starting point

                // Apply
                panX = targetX;
                panY = targetY;
                scale = 1; // or 0.9 / 1.1 — adjust if you want to start slightly zoomed

                updateTransform();
            }
        });
    }).catch(err => {
        console.error("Mermaid render error:", err);
        mermaidDiv.innerHTML = "<p style='color:#ff6b6b'>Diagram render failed</p>";
    });
}

// Event delegation - only one listener
itemsList.addEventListener('click', (e) => {
    const card = e.target.closest('.item-card');
    if (!card) return;

    const itemName = card.querySelector('strong')?.textContent;
    if (!itemName) return;

    // Update header
    currentNodeEl.textContent = itemName;
    
    // Optional: show full path + item
    pathEl.textContent = `${buildPath(findNode(treeData, currentNodeId))} → ${itemName}`;

    // Visual feedback
    const allCards = itemsList.querySelectorAll('.item-card');
    allCards.forEach(c => c.style.background = 'rgba(20, 30, 50, 0.7)');
    card.style.background = 'rgba(60, 100, 180, 0.55)';

    // Optional: you can store the selected item id if you want later actions
    // card.dataset.itemId or something similar
});

function centerOnNode(nodeEl) {
    const nodeRect = nodeEl.getBoundingClientRect();
    const viewportRect = viewport.getBoundingClientRect();

    // Node center (screen coords)
    const nodeCenterX = nodeRect.left + nodeRect.width / 2;
    const nodeCenterY = nodeRect.top + nodeRect.height / 2;

    // Viewport center
    const viewportCenterX = viewportRect.left + viewportRect.width / 2;
    const viewportCenterY = viewportRect.top + viewportRect.height / 2;

    // Adjust pan so node moves to center
    panX += (viewportCenterX - nodeCenterX);
    panY += (viewportCenterY - nodeCenterY);

    updateTransform();
}


function buildPath(node) {
    if (!node) return "";
    const parts = [];
    let current = node;
    while (current) {
        parts.unshift(current.name);
        current = current.parent;
    }
    return parts.join(" → ");
}

async function selectNode(nodeId) {
    if (!treeData) return;

    // Find node in tree
    function findNode(nodes, id) {
        for (const node of nodes) {
            if (node.id == id) return node;
            if (node.children) {
                const found = findNode(node.children, id);
                if (found) return found;
            }
        }
        return null;
    }

    const node = findNode(treeData, nodeId);
    if (!node) return;

    currentNodeId = nodeId;
    currentNodeEl.textContent = node.name;
    pathEl.textContent = buildPath(node);

    // Update back button
    backBtn.style.display = node.parent ? 'block' : 'none';
    if (node.parent) {
        backBtn.textContent = `↑ Back to ${node.parent.name}`;
    }

    // Load items
    itemsList.innerHTML = "Loading...";
    try {
        const res = await fetch(`database/get_items_by_node.php?id=${nodeId}`);
        const items = await res.json();

        if (!items?.length) {
            itemsList.innerHTML = "No items in this location.";
            return;
        }

        let html = "";
        items.forEach(item => {
            html += `
                <div class="item-card">
                    <strong>${item.name}</strong><br>
                    <small>Type: ${item.type} • Expiry: ${item.expiry || '-'}</small><br>
                    <small>Remaining: ${item.remaining ?? 100}% • RFID: ${item.rfid || '-'}</small>
                </div>
            `;
        });
        itemsList.innerHTML = html;
    } catch (err) {
        itemsList.innerHTML = "<span style='color:#ff6b6b'>Error loading items</span>";
    }
}

// Back button handler
backBtn.addEventListener('click', () => {
    if (!currentNodeId) return;

    function findParent(nodes, targetId) {
        for (const node of nodes) {
            if (node.id == targetId) return node.parent;
            if (node.children) {
                const found = findParent(node.children, targetId);
                if (found !== undefined) return found;
            }
        }
        return null;
    }

    const parent = findParent(treeData, currentNodeId);
    if (parent) {
        selectNode(parent.id);
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadAndRender();
});
</script>

</body>
</html>