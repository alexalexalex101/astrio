<?php
session_start();
if (!isset($_SESSION['user'])) header('location: nasalogin.php');
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>DSLM Inventory</title>
    <link rel="stylesheet" href="https://use.typekit.net/pen4uct.css">
    <link rel="stylesheet" href="default.css">
    <style>
        /* GLOBAL */
        body {
            margin: 0;
            font-family: "League Spartan", sans-serif;
            background: radial-gradient(circle at 50% 20%, #1a1f2b 0%, #0d1118 60%, #05070c 100%);
            color: #ffffff;
            overflow: hidden;
        }

        /* NASA LOGO */
        .nasa-logo {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 120px;
            z-index: 2000;
            filter: drop-shadow(0 0 12px rgba(255, 255, 255, 0.18));
        }

        /* LOGOUT BUTTON */
        .logout-btn {
            position: absolute;
            top: 25px;
            right: 25px;
            padding: 8px 14px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
        }

        /* MAIN WRAPPER */
        .inventory-wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 94vw;
            height: 84vh;
            background: rgba(10, 14, 22, 0.9);
            border-radius: 20px;
            display: flex;
            overflow: hidden;
        }

        #itemFormArea {
            background: #1a1f2b;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            max-width: 400px;
        }

        #itemFormArea h3 {
            margin-top: 0;
            margin-bottom: 12px;
            color: #fff;
            font-size: 18px;
        }

        #itemFormArea label {
            display: block;
            margin-top: 10px;
            margin-bottom: 4px;
            color: #cfd6e4;
            font-size: 14px;
        }

        #itemFormArea input,
        #itemFormArea select {
            width: 100%;
            padding: 7px;
            border-radius: 4px;
            border: 1px solid #444;
            background: #2a3040;
            color: #fff;
            margin-bottom: 6px;
        }

        #itemFormArea button {
            margin-top: 12px;
            width: 100%;
        }


        /* Back button (special small red card) */
        .back-card {
            width: calc(420px * 0.65);
            /* 65% of normal card width */
            background: #b91c1c;
            /* red background */
            border: 1px solid #991b1b;
            color: white;
            padding: 14px 16px;
            /* slightly less padding */
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(185, 28, 28, 0.4);
            transition: all 0.2s ease;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 8px;
            /* small spacing below */
        }

        .back-card:hover {
            background: #c53030;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(185, 28, 28, 0.6);
        }

        .back-card::before {
            content: "←";
            font-size: 18px;
        }

        .expiry-expired {
            color: #ff4d4d !important;
            font-weight: 700;
        }

        .expiry-critical {
            color: #ff6b6b !important;
            font-weight: 600;
        }

        .expiry-warning {
            color: #ffcc66 !important;
            font-weight: 600;
        }

        .expiry-fresh {
            color: #66ff99;
            /* fresh green */
            font-weight: 600;
        }

        /* Row is always red if it needs waste */
        .row-waste {
            background-color: rgba(255, 77, 77, 0.18) !important;
            position: relative;
        }

        /* Popup container (never affects table layout) */
        .waste-popup {
            position: fixed;
            /* <-- THIS is the key */
            background: #ff4d4d;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            z-index: 999999;
            pointer-events: none;
            opacity: 0;
            transform: translate(-50%, -100%);
            transition: opacity 0.15s ease-out;
        }


        .remaining-empty {
            color: #ff4d4d !important;
            /* bright red */
            font-weight: 700;
        }

        .remaining-low {
            color: #ffcc66 !important;
            /* yellow */
            font-weight: 600;
        }

        .remaining-okay {
            color: #66ff99 !important;
            /* green */
            font-weight: 600;
        }

        /* Row highlight when item should go to waste */
        .row-waste:hover {
            background-color: rgba(255, 77, 77, 0.25) !important;
            /* soft red glow */
            cursor: pointer;
        }

        /* Tooltip styling (browser default is fine, but this improves clarity) */
        .row-waste[title] {
            position: relative;
        }

        /* ============================
   NEW MISSION CONTROL SIDEBAR
   ============================ */

        /* ============================
   IMPROVED SIDEBAR (STABLE + ROOMY)
   ============================ */

        .sidebar {
            width: 350px;
            /* more space but still slim */
            background: rgba(8, 12, 20, 0.92);
            padding: 18px 14px;
            border-right: 1px solid rgba(120, 150, 255, 0.25);
            overflow-y: auto;
            overflow-x: hidden;
            /* prevents sideways drift */
        }

        .sidebar-input {
            margin-top: 50px;
            width: 100%;
            padding: 8px 10px;
            font-size: 13px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(120, 150, 255, 0.25);
            border-radius: 6px;
            color: #e6ecff;
            margin-bottom: 12px;
            transition: border 0.2s ease, background 0.2s ease;

        }

        .sidebar-input:focus {
            outline: none;
            border: 1px solid rgba(150, 180, 255, 0.55);
            background: rgba(255, 255, 255, 0.10);
        }

        .sidebar-header-btn {
            margin-top: 50px;
            width: 100%;
            text-align: left;
            margin-bottom: 12px;
            padding: 8px 10px;
            font-size: 13px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(120, 150, 255, 0.25);
            border-radius: 6px;
            color: #e6ecff;
            transition: border 0.2s ease, background 0.2s ease;
            cursor: pointer;
        }

        .sidebar-header-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(150, 180, 255, 0.6);
        }


        /* Scrollable tree container */
        /* Scrollable tree container */
        .tree-scroll {
            max-height: calc(82vh - 160px);
            overflow-y: auto;
            overflow-x: auto;
            /* horizontal scroll enabled */
            padding-bottom: 6px;
            padding-right: 6px;
            white-space: nowrap;
            /* prevents wrapping */
            margin-top: 62px;
        }

        /* Prevent sideways clipping */
        #tree {
            display: inline-block;
            /* allows horizontal scroll */
            white-space: nowrap;
            padding-bottom: 20px;
        }

        /* BEAUTIFUL SCROLLBAR FOR LEFT SIDEBAR */
        .tree-scroll::-webkit-scrollbar {
            height: 8px;
            /* horizontal bar height */
            width: 8px;
            /* vertical bar width */
        }

        .tree-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .tree-scroll::-webkit-scrollbar-thumb {
            background: rgba(120, 150, 255, 0.45);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .tree-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(150, 180, 255, 0.75);
        }

        /* Prevent nested ULs from clipping */
        #tree ul {
            white-space: nowrap;
            overflow-x: visible;
        }



        .tree-node {
            display: grid;
            grid-template-columns: 14px auto;
            align-items: center;
            gap: 6px;
            padding: 5px 6px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            color: #d7e0ff;
            transition: background 0.15s ease;
            overflow: hidden;
            /* prevents sideways drift */
        }

        /* Hover */
        .tree-node:hover {
            background: rgba(120, 150, 255, 0.18);
        }

        /* Selected */
        #tree li.selected>.tree-node {
            background: rgba(120, 150, 255, 0.35);
            color: #fff;
            font-weight: 600;
        }

        /* Arrow */
        .tree-arrow {
            font-size: 10px;
            opacity: 0.75;
            cursor: pointer;
            user-select: none;
        }

        .tree-arrow:hover {
            opacity: 1;
        }

        /* Collapsed state */
        .tree-collapsed>ul {
            display: none;
        }

        /* Nested UL */
        #tree ul {
            margin-left: 12px;
            padding-left: 10px;
            border-left: 1px solid rgba(120, 150, 255, 0.25);
            overflow-x: hidden;
        }


        /* MAIN PANEL */
        .main-panel {
            flex: 1;
            padding: 26px 30px 26px 26px;
            overflow-y: auto;
        }

        #nodeHeader {
            margin-bottom: 14px;
        }

        #nodeTitle {
            font-size: 20px;
            font-weight: 700;
        }

        #nodePath {
            font-size: 12px;
            color: #9aa4d8;
            margin-top: 4px;
        }

        /* TOP CONTROLS */
        .top-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
            font-family: "League Spartan", sans-serif;
        }

        .search-input {
            flex: 1;
            padding: 8px 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            color: #fff;
            font-size: 13px;
            font-family: "League Spartan", sans-serif;
        }

        .control-btn {
            padding: 8px 12px;
            background: rgba(76, 32, 235, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-size: 13px;
            font-family: "League Spartan", sans-serif;
        }

        /* CTB CARDS */
        .ctb-cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 26px;
            margin-top: 10px;
        }

        .ctb-card {
            width: 420px;
            background: rgba(20, 26, 40, 0.97);
            border-radius: 14px;
            padding: 18px 18px 16px;
            border: 1px solid rgba(120, 150, 255, 0.35);
            box-shadow: 0 0 18px rgba(60, 80, 160, 0.25);
            transition: 0.25s ease;
        }

        .ctb-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 26px rgba(120, 150, 255, 0.45);
        }

        .ctb-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .ctb-breadcrumb {
            font-size: 11px;
            color: #9aa4d8;
            opacity: 0.85;
            margin-bottom: 8px;
        }

        .ctb-meta {
            font-size: 12px;
            color: #cfd8ff;
            display: flex;
            gap: 14px;
            margin-bottom: 10px;
        }

        /* REMAINING BAR */
        .remaining-bar {
            width: 100%;
            height: 12px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 999px;
            overflow: hidden;
            position: relative;
        }

        .remaining-fill {
            height: 100%;
            background: linear-gradient(90deg, #1ddf6b, #7bffb0);
            width: 0%;
            transition: width 0.6s ease;
        }

        .remaining-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
            font-size: 12px;
        }

        .remaining-label {
            color: #9aa4d8;
        }

        .remaining-percent {
            font-weight: 700;
            color: #7bffb0;
        }

        .remaining-fill.green {
            background-color: #4caf50;
        }

        .remaining-fill.yellow {
            background-color: #ffeb3b;
        }

        .remaining-fill.red {
            background-color: #f44336;
        }


        /* ITEMS TABLE */
        #nodeItemsWrapper {
            margin-top: 22px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            background: rgba(8, 12, 20, 0.95);
            border-radius: 10px;
            overflow: hidden;
        }

        .items-table thead {
            background: rgba(255, 255, 255, 0.06);
        }

        .items-table th {
            text-align: left;
            padding: 8px 8px;
            color: #cfe0ff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            font-weight: 600;
        }

        .items-table td {
            padding: 7px 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .items-table tr:nth-child(every) {}

        .items-table tr:nth-child(odd) {
            background: rgba(255, 255, 255, 0.01);
        }

        .items-table tr:hover {
            background: rgba(120, 150, 255, 0.12);
        }

        /* Highlighted row in items table */
        #itemsTable tr.highlighted {
            background: rgba(79, 70, 229, 0.35) !important;
            /* indigo-ish */
            border-left: 4px solid #4f46e5;
            font-weight: 500;
            transition: background 0.4s ease;
        }

        #itemsTable tr.highlighted td {
            color: #e0e7ff;
        }

        .no-items {
            font-size: 13px;
            color: #9aa4d8;
            margin-top: 6px;
        }

        /* INCOMING MODAL */
        .incoming-modal-wrapper {
            position: fixed;
            top: 80px;
            right: 40px;
            display: none;
            z-index: 3000;
        }

        .incoming-footer {
            display: flex;
            justify-content: center;
        }

        .incoming-footer button {
            margin: 10px;
        }

        .incoming-modal {
            width: 430px;
            max-height: 80vh;
            background: rgba(10, 14, 22, 0.97);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 0 40px rgba(120, 150, 255, 0.4);
            padding: 24px;
        }

        .incoming-header {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }

        .incoming-close-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
            padding: 4px 10px;
            transition: color 0.2s ease;
        }

        .incoming-close-btn:hover {
            color: #ff6b6b;
        }

        .incoming-body {
            padding: 10px 12px;
            max-height: 60vh;
            overflow-y: auto;
        }

        #incomingCloseBtn {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        /* Collapsible arrows */
        .tree-node {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .tree-arrow {
            font-size: 10px;
            opacity: 0.7;
            cursor: pointer;
            width: 12px;
            text-align: center;
        }

        .tree-arrow:hover {
            opacity: 1;
        }

        .tree-collapsed>ul {
            display: none;
        }

        .searchTable {
            margin-bottom: 2rem;
        }

        .searchTable .searchCaption {
            margin-bottom: 1rem;
            font-weight: bold;
        }

        /* Pretty scrollbar for MAIN PANEL — matches tree-scroll style */
        .main-panel {
            /* already has overflow-y: auto; — we keep that */
            scrollbar-width: thin;
            /* Firefox support */
            scrollbar-color: rgba(120, 150, 255, 0.45) rgba(255, 255, 255, 0.05);
        }

        /* Webkit browsers (Chrome, Edge, Safari, Opera) */
        .main-panel::-webkit-scrollbar {
            width: 8px;
            /* same thin width as sidebar */
        }

        .main-panel::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            /* very dark subtle track */
            border-radius: 10px;
        }

        .main-panel::-webkit-scrollbar-thumb {
            background: rgba(120, 150, 255, 0.45);
            /* same blue-ish thumb color */
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            /* subtle edge glow */
        }

        .main-panel::-webkit-scrollbar-thumb:hover {
            background: rgba(150, 180, 255, 0.75);
            /* brighter on hover — same as sidebar */
        }
    </style>

</head>

<body>

    <!-- NASA LOGO (same size + position as dashboard) -->
    <a href="dashboard.php">
        <img src="images/NASA-Logo.png" class="nasa-logo">
    </a>

    <!-- LOGOUT BUTTON -->
    <a href="database/logout.php" class="logout-btn">Log out</a>

    <!-- MAIN WRAPPER -->
    <div class="inventory-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="tree-scroll">
                <ul id="tree"></ul>
            </div>


        </aside>

        <!-- MAIN PANEL -->
        <main class="main-panel">

            <!-- NODE HEADER -->
            <div id="nodeHeader" class="node-header">
                <div>
                    <div id="nodeTitle" class="node-title">Select a node</div>
                    <div id="nodePath" class="node-path"></div>
                </div>
            </div>

            <!-- TOP CONTROLS -->
            <div class="top-controls">
                <input id="globalSearch" class="search-input" placeholder="Search items...">
                <button id="newItemBtn" class="control-btn">+ New Item</button>
                <button id="incomingItembtn" class="control-btn">Incoming</button>
            </div>

            <!-- ITEM FORM -->
            <div id="itemFormArea" class="item-form-area" style="display:none;">
                <form id="itemForm">
                    <input type="hidden" id="form_hierarchy_id" name="hierarchy_id">

                    <label>Name</label>
                    <input id="item_name" name="name" required>

                    <label>Type</label>
                    <select id="item_type" name="type">
                        <option value="food">Food</option>
                        <option value="equipment">Equipment</option>
                        <option value="tool">Tool</option>
                        <option value="medical">Medical</option>
                        <option value="waste">Waste</option>
                    </select>

                    <label>Expiry Date</label>
                    <input id="item_expiry" name="expiry_date" type="date">

                    <label>Calories</label>
                    <input id="item_calories" name="calories" type="number">

                    <label>RFID</label>
                    <input id="item_rfid" name="rfid">

                    <label>Remaining (%)</label>
                    <input id="item_remaining" name="remaining_percent" type="number" min="0" max="100" value="100">

                    <div class="form-buttons">
                        <button type="submit" class="control-btn">Save Item</button>
                        <button type="button" id="cancelForm" class="control-btn">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- CTB CARDS -->
            <div id="ctbCards" class="ctb-cards"></div>

            <!-- ITEMS TABLE -->
            <div id="nodeItemsWrapper">
                <button id="takeSelectedBtn" class="control-btn" style="display:none; margin-bottom:10px;">Take Selected</button>
                <div id="noItems" class="no-items"></div>
                <div id="searchView" class="searchTable" style="display:none;"></div>
                <table id="itemsTable" class="items-table" style="display:none;">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Expiry</th>
                            <th>Calories</th>
                            <th>Location</th>
                            <th>RFID</th>
                            <th>Remaining</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                </table>
            </div>


        </main>

    </div>

    <!-- INCOMING MODAL -->
    <div id="incomingModal" class="incoming-modal-wrapper">
        <div class="incoming-modal">
            <div class="incoming-resize-handle"></div>

            <div class="incoming-header">
                <div class="incoming-drag-handle">INCOMING ITEMS</div>
                <button id="incomingCloseBtn" class="incoming-close-btn">&times;</button>
            </div>


            <div class="incoming-body">
                <table class="items-table">
                    <tbody id="incomingBody"></tbody>
                </table>
                <div id="incomingEmpty" class="incoming-empty"></div>
            </div>

            <div class="incoming-footer">
                <button id="incomingRefreshBtn" class="control-btn">Refresh</button>
                <button id="incomingMoveBtn" class="control-btn">Move Selected</button>
            </div>
        </div>
    </div>

    <!-- JS WILL BE ADDED IN BLOCK 3 -->
    <script>
        // ===============================
        // INVENTORY.JS — Mission Control
        // ===============================

        let currentNode = null;
        let fullTree = null;
        let highlightItemId = null;

        document.addEventListener("DOMContentLoaded", () => {
            // Clear any leftover highlight classes on every page load / refresh
            document.querySelectorAll('#itemsBody tr.highlighted').forEach(row => {
                row.classList.remove('highlighted');
            });

            // Parse highlight param
            const params = new URLSearchParams(window.location.search);
            const highlight = params.get('highlight');
            if (highlight && highlight.startsWith('item-')) {
                highlightItemId = highlight.replace('item-', '');
                console.log("Highlight requested for item ID:", highlightItemId);
            } else {
                highlightItemId = null; // explicit reset on normal load
            }

            loadTree();
            setupSearch();
            setupForm();
            setupIncoming();

            const takeBtn = document.getElementById('takeSelectedBtn');
            if (takeBtn) takeBtn.onclick = takeSelected;

            // The interval check can stay, but it's now redundant with the cleanup above
            if (highlightItemId) {
                const checkTreeInterval = setInterval(() => {
                    if (fullTree) {
                        clearInterval(checkTreeInterval);
                        autoJumpToItemNode(highlightItemId);
                    }
                }, 300);
            }
        });

        // New function: jump to the node's parent and highlight item
        async function autoJumpToItemNode(itemId) {
            try {
                const res = await fetch(`database/get_item_node.php?id=${itemId}`);
                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                const data = await res.json();

                if (data.error) {
                    console.warn("Could not find node for item:", data.error);
                    return;
                }

                const nodeId = String(data.hierarchy_id);
                console.log("Found node for item:", nodeId);

                // Find the node object in the tree
                const targetNode = findNodeById(fullTree, nodeId);

                if (targetNode) {
                    console.log("Auto-selecting node:", targetNode.name);
                    selectNode(targetNode);
                    // → this calls loadItems() → renderItems() → highlight happens there
                } else {
                    console.warn("Node ID from DB not found in tree:", nodeId);
                }
            } catch (err) {
                console.error("Failed to auto-jump to item node:", err);
            }
        }

        // Helper: find node by ID in the tree structure
        function findNodeById(nodes, targetId) {
            for (const node of nodes) {
                if (String(node.id) === targetId) {
                    return node;
                }
                if (node.children && node.children.length) {
                    const found = findNodeById(node.children, targetId);
                    if (found) return found;
                }
            }
            return null;
        }

        // ---------- TREE ----------
        function loadTree() {
            fetch("database/get_hierarchy.php")
                .then(r => r.json())
                .then(d => {
                    fullTree = d;
                    renderTree(d);
                });
        }

        function renderTree(tree) {
            const ul = document.getElementById("tree");
            ul.innerHTML = "";

            tree.forEach(n => ul.appendChild(renderNode(n, null)));

            // ────────────────────────────────────────
            // Handle highlight/jump **after** full render
            // ────────────────────────────────────────
            if (highlightItemId) {
                autoJumpToItemNode(highlightItemId);
                // Do NOT clear highlightItemId here anymore
            } else if (tree.length > 0) {
                // Normal startup behavior
                const firstNode = tree[0];
                selectNode(firstNode);

                const firstLi = document.querySelector(`#tree li[data-id="${firstNode.id}"]`);
                if (firstLi) {
                    firstLi.classList.add("selected");
                    if (firstNode.children?.length > 0) {
                        firstLi.classList.remove("tree-collapsed");
                        const arrow = firstLi.querySelector(".tree-arrow");
                        if (arrow) arrow.textContent = "▼";
                    }
                    firstLi.scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });
                }
            }
        }

        function renderNode(node, parent) {
            node.parent = parent || null;

            const li = document.createElement("li");
            li.classList.add("tree-collapsed");
            li.dataset.id = node.id; // ← ADD THIS LINE

            const row = document.createElement("div");
            row.className = "tree-node";

            const arrow = document.createElement("span");
            arrow.className = "tree-arrow";
            arrow.textContent = node.children && node.children.length ? "▶" : "";

            const label = document.createElement("span");
            label.textContent = node.name;

            row.appendChild(arrow);
            row.appendChild(label);
            li.appendChild(row);

            // Select node
            row.onclick = e => {
                e.stopPropagation();
                selectNode(node);

                document.querySelectorAll("#tree li").forEach(x => x.classList.remove("selected"));
                li.classList.add("selected");
            };

            // Expand/collapse
            arrow.onclick = e => {
                e.stopPropagation();
                li.classList.toggle("tree-collapsed");
                arrow.textContent = li.classList.contains("tree-collapsed") ? "▶" : "▼";
            };

            // Children
            if (node.children && node.children.length) {
                const ul = document.createElement("ul");
                node.children.forEach(c => ul.appendChild(renderNode(c, node)));
                li.appendChild(ul);
            }

            return li;
        }

        function expandPathToNode(targetNode) {
            if (!targetNode) return;

            // Build path from root → target (inclusive)
            const pathFromRoot = [];
            let curr = targetNode;
            while (curr) {
                pathFromRoot.unshift(curr);
                curr = curr.parent;
            }

            // Expand step-by-step from root downward
            pathFromRoot.forEach((node, index) => {
                const li = document.querySelector(`#tree li[data-id="${node.id}"]`);
                if (!li) {
                    console.warn(`Cannot expand — missing li for node ${node.id}`);
                    return;
                }

                // Expand every node except possibly the last one (optional — you can expand target too)
                if (index < pathFromRoot.length - 1 || true) { // ← change 'true' to 'false' if you don't want target expanded
                    if (li.classList.contains("tree-collapsed")) {
                        li.classList.remove("tree-collapsed");
                        const arrow = li.querySelector(".tree-arrow");
                        if (arrow) arrow.textContent = "▼";
                    }
                }
            });

            // Give DOM a moment to render children / compute layout
            setTimeout(() => {
                const targetLi = document.querySelector(`#tree li[data-id="${targetNode.id}"]`);
                if (targetLi) {
                    targetLi.scrollIntoView({
                        behavior: "smooth",
                        block: "center", // better visibility
                        inline: "nearest"
                    });
                }
            }, 80); // ← 50–150 ms is usually enough
        }

        function hideCTBCards() {
            const container = document.getElementById("ctbCards");
            if (!container) return;
            container.style.display = "none";
            container.innerHTML = "";
        }

        function selectNode(node) {
            // Auto-clear search bar + hide search results
            document.getElementById("globalSearch").value = "";
            document.getElementById("searchView").style.display = "none";
            document.getElementById("searchView").innerHTML = "";

            currentNode = node;

            document.getElementById("nodeTitle").textContent = node.name;
            document.getElementById("nodePath").textContent = buildPath(node);

            // ─── ADD THESE LINES ───────────────────────────────
            expandPathToNode(node);

            document.querySelectorAll("#tree li").forEach(x => x.classList.remove("selected"));
            const myLi = document.querySelector(`#tree li[data-id="${node.id}"]`);
            if (myLi) myLi.classList.add("selected");
            // ─────────────────────────────────────────────────────

            if (node.children && node.children.length) {
                showCTBCards(node.children);
                clearItems();
            } else {
                hideCTBCards();
                loadItems(node.id);
            }
        }

        function buildPath(node) {
            const p = [];
            let n = node;
            while (n) {
                p.unshift(n.name);
                n = n.parent;
            }
            return p.join(" › ");
        }

        // ---------- CTB CARDS ----------
        function showCTBCards(children) {
            const container = document.getElementById("ctbCards");
            container.innerHTML = "";
            container.style.display = "flex";

            if (currentNode && currentNode.parent) {
                const backCard = document.createElement("div");
                backCard.className = "back-card";
                backCard.textContent = "Back to " + currentNode.parent.name;
                backCard.onclick = () => selectNode(currentNode.parent);
                container.appendChild(backCard);
            }

            children.forEach(child => {
                console.log("CHILD:", child);

                const remaining = child.remaining;
                const used = 100 - remaining; // NEW
                const items = child.item_count ?? 0;
                const kids = child.children ? child.children.length : 0;

                const card = document.createElement("div");
                card.className = "ctb-card";

                let barHTML = "";
                if (child.remaining !== undefined) {

                    // Determine bar color based on USED percent
                    let color = "green";
                    if (used >= 60 && used < 85) color = "yellow";
                    if (used >= 85) color = "red";

                    barHTML = `
                <div class="remaining-bar">
                    <div class="remaining-fill ${color}" id="fill-${child.id}"></div>
                </div>

                <div class="remaining-row">
                    <span class="remaining-label">Used</span>
                    <span class="remaining-percent" id="percent-${child.id}">${used}%</span>
                </div>
            `;
                }

                card.innerHTML = `
            <div class="ctb-title">${child.name}</div>
            <div class="ctb-breadcrumb">${buildPath(child)}</div>

            <div class="ctb-meta">
                <span>Children: ${kids}</span>
                <span>Items: ${items}</span>
            </div>

            ${barHTML}
        `;

                card.onclick = () => selectNode(child);
                container.appendChild(card);

                if (child.remaining !== undefined) {
                    animateRemaining(child.id, used); // animate USED percent
                }
            });
        }

        function animateRemaining(id, percent) {
            const fill = document.getElementById(`fill-${id}`);
            const label = document.getElementById(`percent-${id}`);
            if (!fill || !label) return;

            requestAnimationFrame(() => {
                fill.style.width = percent + "%";
                label.textContent = percent + "%";
            });
        }

        // ---------- ITEMS ----------
        function loadItems(id) {
            fetch("database/get_items_by_node.php?id=" + id)
                .then(r => r.json())
                .then(items => renderItems(items));
        }

        function shortenLocation(path) {
            if (!path) return "";
            const parts = path.split("/");
            if (parts.length <= 2) return path;
            return "…/" + parts.slice(-2).join("/");
        }
        // Create one global popup element
        let wastePopup = document.createElement("div");
        wastePopup.className = "waste-popup";
        document.body.appendChild(wastePopup);

        function renderItems(items) {
            const table = document.getElementById("itemsTable");
            const body = document.getElementById("itemsBody");
            const noItems = document.getElementById("noItems");
            const takeBtn = document.getElementById('takeSelectedBtn');

            body.innerHTML = "";

            if (!items || !items.length) {
                noItems.textContent = "No items in this node.";
                table.style.display = "none";
                if (takeBtn) takeBtn.style.display = 'none';
                return;
            }

            noItems.textContent = "";
            table.style.display = "table";
            if (takeBtn) takeBtn.style.display = 'inline-block';

            items.forEach(item => {
                const tr = document.createElement("tr");
                tr.dataset.itemId = item.id;

                /* ============================
                   EXPIRY WARNING
                ============================ */
                let expiryDisplay = item.expiry_date || "-";
                let expiryClass = "";

                if (item.expiry_date) {
                    const cleanDate = item.expiry_date.replace(/\//g, "-").split(" ")[0];
                    const [year, month, day] = cleanDate.split("-");

                    if (year && month && day) {
                        const exp = new Date(year, month - 1, day);
                        const today = new Date();
                        const diffDays = Math.ceil((exp - today) / (1000 * 60 * 60 * 24));

                        if (!isNaN(diffDays)) {
                            if (diffDays < 0) expiryClass = "expiry-expired";
                            else if (diffDays <= 3) expiryClass = "expiry-critical";
                            else if (diffDays <= 7) expiryClass = "expiry-warning";
                            else expiryClass = "expiry-fresh";
                        }
                    }
                }

                /* ============================
                   REMAINING WARNING
                ============================ */
                let remainingDisplay = (item.remaining_percent ?? 100) + "%";
                let remainingClass = "";

                if (item.remaining_percent !== null && item.remaining_percent !== undefined) {
                    const rem = Number(item.remaining_percent);

                    if (rem === 0) remainingClass = "remaining-empty";
                    else if (rem <= 10) remainingClass = "remaining-low";
                    else remainingClass = "remaining-okay";
                }

                /* ============================
                   WASTE INDICATOR
                ============================ */
                let wasteIcon = "";
                let wasteTitle = "";

                const needsWaste =
                    expiryClass === "expiry-expired" ||
                    remainingClass === "remaining-empty";

                if (needsWaste) {
                    wasteIcon = "⚠️ ";
                    if (expiryClass === "expiry-expired")
                        wasteTitle = "Expired — should be moved to Waste Bay";
                    else if (remainingClass === "remaining-empty")
                        wasteTitle = "0% remaining — should be moved to Waste Bay";
                }

                /* ⭐ NEW — row always red + popup on hover */
                if (needsWaste) {
                    tr.classList.add("row-waste");

                    tr.addEventListener("mouseenter", (e) => {
                        wastePopup.textContent = wasteTitle;
                        const rect = tr.getBoundingClientRect();
                        wastePopup.style.left = (rect.left + rect.width / 2) + "px";
                        wastePopup.style.top = (rect.top - 8) + "px";
                        wastePopup.style.opacity = "1";
                    });

                    tr.addEventListener("mouseleave", () => {
                        wastePopup.style.opacity = "0";
                    });
                }


                /* ============================
                   BUILD TABLE ROW
                ============================ */
                tr.innerHTML = `
            <td><input type="checkbox" class="item-select" data-id="${item.id}"></td>
            <td>${item.name}</td>
            <td>${item.type}</td>
            <td class="${expiryClass}">${wasteIcon}${expiryDisplay}</td>
            <td>${item.calories || ""}</td>
            <td>${shortenLocation(item.location || "")}</td>
            <td>${item.rfid || ""}</td>
            <td class="${remainingClass}">${remainingDisplay}</td>
        `;

                body.appendChild(tr);
            });

            /* ============================
               HIGHLIGHT LOGIC (unchanged)
            ============================ */
            if (highlightItemId) {
                setTimeout(() => {
                    const row = body.querySelector(`tr[data-item-id="${highlightItemId}"]`);
                    if (row) {
                        document.querySelectorAll('#itemsBody tr.highlighted').forEach(r => {
                            r.classList.remove('highlighted');
                        });

                        row.classList.add('highlighted');
                        row.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                        if (window.history && window.history.replaceState) {
                            const cleanUrl = window.location.pathname + (window.location.hash || '');
                            window.history.replaceState({}, document.title, cleanUrl);
                        }

                        highlightItemId = null;
                    } else {
                        highlightItemId = null;
                    }
                }, 400);
            }
        }


        function clearItems() {
            document.getElementById("itemsBody").innerHTML = "";
            document.getElementById("itemsTable").style.display = "none";
            document.getElementById("noItems").textContent = "";
            const takeBtn = document.getElementById('takeSelectedBtn');
            if (takeBtn) takeBtn.style.display = 'none';
        }

        function takeSelected() {
            const checks = Array.from(document.querySelectorAll('.item-select:checked'));
            if (!checks.length) {
                alert('No items selected');
                return;
            }

            const ids = checks.map(c => c.dataset.id);
            const input = prompt('Enter percent to TAKE from each selected item (1-100):', '1');
            if (input === null) return;
            const amount = parseInt(input, 10);
            if (isNaN(amount) || amount <= 0) {
                alert('Invalid amount');
                return;
            }

            fetch('database/take_items.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        ids: ids.join(','),
                        amount
                    })
                })
                .then(r => r.text())
                .then(text => {
                    if (text.trim() === 'OK') {
                        if (currentNode && currentNode.id) loadItems(currentNode.id);
                    } else {
                        alert('Error: ' + text);
                    }
                })
                .catch(err => alert('Request failed: ' + err));
        }

        // ---------- SEARCH ----------
        function setupSearch() {
            const input = document.getElementById("globalSearch");
            const searchView = document.getElementById("searchView");

            input.addEventListener("input", e => {
                const q = e.target.value.trim();

                // Hide search view if too short
                if (q.length < 2) {
                    searchView.style.display = "none";
                    searchView.innerHTML = "";
                    return;
                }

                fetch("database/search_items.php?q=" + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(items => {

                        // No results → hide
                        if (!items || !items.length) {
                            searchView.style.display = "none";
                            searchView.innerHTML = "";
                            return;
                        }

                        // Build table
                        let html = `
                    <p class="searchCaption">Search Results</p>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Expiry</th>
                                <th>Calories</th>
                                <th>Location</th>
                                <th>RFID</th>
                                <th>Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                        items.forEach(item => {
                            html += `
                        <tr data-item-id="${item.id}">
                            <td>${item.name}</td>
                            <td>${item.type}</td>
                            <td>${item.expiry_date || "-"}</td>
                            <td>${item.calories || ""}</td>
                            <td>${shortenLocation(item.location || "")}</td>
                            <td>${item.rfid || ""}</td>
                            <td>${(item.remaining_percent ?? 100)}%</td>
                        </tr>
                    `;
                        });

                        html += `</tbody></table>`;

                        searchView.innerHTML = html;
                        searchView.style.display = "block";

                        // ⭐ MAKE SEARCH ROWS CLICKABLE
                        const rows = searchView.querySelectorAll("tbody tr");
                        rows.forEach(row => {
                            row.style.cursor = "pointer";

                            row.addEventListener("click", (e) => {
                                // Ignore checkbox or button clicks
                                if (e.target.tagName === "INPUT" || e.target.tagName === "BUTTON") return;

                                const itemId = row.dataset.itemId;
                                if (itemId) {
                                    window.location.href = `inventory.php?highlight=item-${itemId}`;
                                }
                            });
                        });
                    });
            });
        }

        // ---------- FORM ----------
        function setupForm() {
            const btn = document.getElementById("newItemBtn");
            const cancel = document.getElementById("cancelForm");
            const form = document.getElementById("itemForm");

            if (btn) {
                btn.onclick = () => {

                    // ⭐ BLOCK ADDING ITEMS IF NODE IS FULL
                    if (currentNode && currentNode.remaining !== undefined && currentNode.remaining <= 0) {
                        alert("This node is 100% full. You cannot add more items here.");
                        return;
                    }

                    // Normal behavior
                    document.getElementById("itemFormArea").style.display = "block";
                    document.getElementById("form_hierarchy_id").value = currentNode?.id || "";
                };
            }

            if (cancel) {
                cancel.onclick = () => {
                    document.getElementById("itemFormArea").style.display = "none";
                };
            }

            if (form) {
                form.onsubmit = e => {
                    e.preventDefault();
                    const data = new FormData(form);

                    fetch("database/create_item.php", {
                            method: "POST",
                            body: data
                        })
                        .then(r => r.text())
                        .then(() => {
                            document.getElementById("itemFormArea").style.display = "none";
                            if (currentNode && (!currentNode.children || !currentNode.children.length)) {
                                loadItems(currentNode.id);
                            }
                        });
                };
            }
        }

        // ---------- INCOMING ----------
        function setupIncoming() {
            const modal = document.getElementById("incomingModal");
            const openBtn = document.getElementById("incomingItembtn");
            const closeBtn = document.getElementById("incomingCloseBtn");
            const refreshBtn = document.getElementById("incomingRefreshBtn");
            const moveBtn = document.getElementById("incomingMoveBtn");
            const cancelBtn = document.getElementById("incomingCancelBtn");

            // OPEN MODAL
            if (openBtn) {
                openBtn.onclick = () => {
                    modal.style.display = "block";
                    loadIncoming();
                };
            }

            // CLOSE MODAL (X BUTTON)
            if (closeBtn) {
                closeBtn.onclick = () => {
                    modal.style.display = "none";
                };
            }

            // REFRESH
            if (refreshBtn) {
                refreshBtn.onclick = () => loadIncoming();
            }

            // MOVE SELECTED
            if (moveBtn) {
                moveBtn.onclick = () => moveSelectedIncoming();
            }

            // CANCEL BUTTON (if present)
            if (cancelBtn) {
                cancelBtn.onclick = () => {
                    modal.style.display = "none";
                };
            }
        }

        function loadIncoming() {
            fetch("database/get_incoming_items.php")
                .then(r => r.json())
                .then(items => {
                    const body = document.getElementById("incomingBody");
                    const empty = document.getElementById("incomingEmpty");

                    body.innerHTML = "";

                    if (!items || !items.length) {
                        empty.textContent = "No incoming items.";
                        return;
                    }

                    empty.textContent = "";

                    items.forEach(item => {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                    <td><input type="checkbox" class="incoming-select" data-id="${item.id}"></td>
                    <td>${item.name}</td>
                    <td>${item.type}</td>
                    <td>${item.rfid || ''}</td>
                    <td><input type="number" min="0" max="100" value="100" class="incoming-remaining" data-id="${item.id}" style="width:70px;"></td>
                `;
                        body.appendChild(tr);
                    });
                });
        }

        function moveSelectedIncoming() {
            if (!currentNode || !currentNode.id) {
                alert('Select a node to move items into first');
                return;
            }

            const checks = Array.from(document.querySelectorAll('.incoming-select:checked'));
            if (!checks.length) {
                alert('No incoming items selected');
                return;
            }

            const ids = checks.map(c => c.dataset.id);
            const remainingInputs = document.querySelectorAll('.incoming-remaining');
            const remainingMap = {};
            remainingInputs.forEach(inp => {
                const id = inp.dataset.id;
                const val = parseInt(inp.value, 10);
                if (!isNaN(val)) remainingMap[id] = Math.max(0, Math.min(100, val));
            });

            fetch('database/add_incoming_item_to_node.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        ids: ids.join(','),
                        hierarchy_id: currentNode.id,
                        remaining: JSON.stringify(remainingMap)
                    })
                })
                .then(r => r.text())
                .then(text => {
                    if (text.trim() === 'OK') {
                        // refresh items in current node and incoming list
                        loadItems(currentNode.id);
                        loadIncoming();
                        document.getElementById('incomingModal').style.display = 'none';
                    } else {
                        alert('Error: ' + text);
                    }
                })
                .catch(err => alert('Request failed: ' + err));
        }

        // ─── Highlight row from URL param on load ──────────────────────────────
        function highlightRowFromUrl() {
            const params = new URLSearchParams(window.location.search);
            const highlight = params.get('highlight');

            if (!highlight || !highlight.startsWith('item-')) return;

            const itemId = highlight.replace('item-', '');
            const row = document.querySelector(`#itemsBody tr:has([data-id="${itemId}"])`);

            if (row) {
                // Add highlight class
                row.classList.add('highlighted');

                // Smooth scroll to the row (centered in view if possible)
                setTimeout(() => {
                    row.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center', // tries to center vertically
                        inline: 'nearest'
                    });

                    // Optional: flash / pulse effect
                    row.style.transition = 'background 1.2s';
                    setTimeout(() => {
                        row.classList.remove('highlighted');
                        void row.offsetWidth; // force reflow
                        row.classList.add('highlighted');
                    }, 300);
                }, 600); // small delay so table is rendered
            }
        }
    </script>



</body>

</html>