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
}

/* LOGOUT BUTTON */
.logout-btn {
    position: absolute;
    top: 25px;
    right: 25px;
    padding: 8px 14px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
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

/* ============================
   NEW MISSION CONTROL SIDEBAR
   ============================ */

/* ============================
   IMPROVED SIDEBAR (STABLE + ROOMY)
   ============================ */

.sidebar {
    width: 260px; /* more space but still slim */
    background: rgba(8, 12, 20, 0.92);
    padding: 18px 14px;
    border-right: 1px solid rgba(120,150,255,0.25);
    overflow-y: auto;
    overflow-x: hidden; /* prevents sideways drift */
}
.sidebar-input {
    margin-top:50px;
    width: 100%;
    padding: 8px 10px;
    font-size: 13px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(120,150,255,0.25);
    border-radius: 6px;
    color: #e6ecff;
    margin-bottom: 12px;
    transition: border 0.2s ease, background 0.2s ease;
}

.sidebar-input:focus {
    outline: none;
    border: 1px solid rgba(150,180,255,0.55);
    background: rgba(255,255,255,0.10);
}
.sidebar-header-btn {
    margin-top:50px;
    width: 100%;
    text-align: left;
    margin-bottom: 12px;
    padding: 8px 10px;
    font-size: 13px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(120,150,255,0.25);
    border-radius: 6px;
    color: #e6ecff;
    transition: border 0.2s ease, background 0.2s ease;
    cursor: pointer;
}
.sidebar-header-btn:hover {
    background: rgba(255,255,255,0.12);
    border-color: rgba(150,180,255,0.6);
}


/* Scrollable tree container */
/* Scrollable tree container */
.tree-scroll {
    max-height: calc(82vh - 160px);
    overflow-y: auto;
    overflow-x: auto; /* horizontal scroll enabled */
    padding-bottom: 6px;
    padding-right: 6px;
    white-space: nowrap; /* prevents wrapping */
}

/* Prevent sideways clipping */
#tree {
    display: inline-block; /* allows horizontal scroll */
    white-space: nowrap;
    padding-bottom:20px;
}
/* BEAUTIFUL SCROLLBAR FOR LEFT SIDEBAR */
.tree-scroll::-webkit-scrollbar {
    height: 8px;   /* horizontal bar height */
    width: 8px;    /* vertical bar width */
}

.tree-scroll::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
}

.tree-scroll::-webkit-scrollbar-thumb {
    background: rgba(120,150,255,0.45);
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.15);
}

.tree-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(150,180,255,0.75);
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
    overflow: hidden; /* prevents sideways drift */
}

/* Hover */
.tree-node:hover {
    background: rgba(120,150,255,0.18);
}

/* Selected */
#tree li.selected > .tree-node {
    background: rgba(120,150,255,0.35);
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
.tree-collapsed > ul {
    display: none;
}

/* Nested UL */
#tree ul {
     margin-left: 12px; 
     padding-left: 10px; 
     border-left: 1px solid rgba(120,150,255,0.25); 
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
}

.search-input {
    flex: 1;
    padding: 8px 10px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 6px;
    color: #fff;
    font-size: 13px;
}

.control-btn {
    padding: 8px 12px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 6px;
    color: #fff;
    cursor: pointer;
    font-size: 13px;
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
    border: 1px solid rgba(120,150,255,0.35);
    box-shadow: 0 0 18px rgba(60,80,160,0.25);
    transition: 0.25s ease;
}

.ctb-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 26px rgba(120,150,255,0.45);
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
    background: rgba(255,255,255,0.08);
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
    background: rgba(255,255,255,0.06);
}

.items-table th {
    text-align: left;
    padding: 8px 8px;
    color: #cfe0ff;
    border-bottom: 1px solid rgba(255,255,255,0.12);
    font-weight: 600;
}

.items-table td {
    padding: 7px 8px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.items-table tr:nth-child(every) {}

.items-table tr:nth-child(odd) {
    background: rgba(255,255,255,0.01);
}

.items-table tr:hover {
    background: rgba(120,150,255,0.12);
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

.incoming-modal {
    width: 430px;
    max-height: 80vh;
    background: rgba(10,14,22,0.97);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 0 40px rgba(120,150,255,0.4);
}

.incoming-header {
    padding: 10px 12px;
    border-bottom: 1px solid rgba(255,255,255,0.12);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
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

.tree-collapsed > ul {
    display: none;
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
        <input id="treeSearch" class="sidebar-input" placeholder="Filter tree...">
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
                <input type="hidden" id="form_hierarchy_id">

                <label>Name</label>
                <input id="item_name" required>

                <label>Type</label>
                <select id="item_type">
                    <option value="food">Food</option>
                    <option value="equipment">Equipment</option>
                    <option value="tool">Tool</option>
                    <option value="medical">Medical</option>
                    <option value="waste">Waste</option>
                </select>

                <label>Location</label>
                <textarea id="item_location" rows="2"></textarea>

                <label>Expiry Date</label>
                <input id="item_expiry" type="date">

                <label>Calories</label>
                <input id="item_calories" type="number">

                <label>RFID</label>
                <input id="item_rfid">

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
            <div id="noItems" class="no-items"></div>
            <table id="itemsTable" class="items-table" style="display:none;">
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
                <tbody id="itemsBody"></tbody>
            </table>
        </div>

        <div id="searchView" style="display:none;"></div>

    </main>

</div>

<!-- INCOMING MODAL -->
<div id="incomingModal" class="incoming-modal-wrapper">
    <div class="incoming-modal">
        <div class="incoming-drag-handle">INCOMING ITEMS</div>
        <div class="incoming-resize-handle"></div>

        <div class="incoming-header">
            <span>Incoming</span>
            <button id="incomingCloseBtn" class="incoming-close">✕</button>
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
            <button id="incomingCancelBtn" class="control-btn">Close</button>
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

document.addEventListener("DOMContentLoaded", () => {
    loadTree();
    setupSearch();
    setupForm();
    setupIncoming();
});

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
}

function renderNode(node, parent) {
    node.parent = parent || null;

    const li = document.createElement("li");
    li.classList.add("tree-collapsed");

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


function selectNode(node) {
    currentNode = node;

    document.getElementById("nodeTitle").textContent = node.name;
    document.getElementById("nodePath").textContent = buildPath(node);

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

    children.forEach(child => {
        const remaining = child.remaining ?? 100;
        const items = child.item_count ?? 0;
        const kids = child.children ? child.children.length : 0;

        const card = document.createElement("div");
        card.className = "ctb-card";

        card.innerHTML = `
            <div class="ctb-title">${child.name}</div>
            <div class="ctb-breadcrumb">${buildPath(child)}</div>

            <div class="ctb-meta">
                <span>Children: ${kids}</span>
                <span>Items: ${items}</span>
            </div>

            <div class="remaining-bar">
                <div class="remaining-fill" id="fill-${child.id}"></div>
            </div>

            <div class="remaining-row">
                <span class="remaining-label">Remaining</span>
                <span class="remaining-percent" id="percent-${child.id}">${remaining}%</span>
            </div>
        `;

        card.onclick = () => selectNode(child);
        container.appendChild(card);

        animateRemaining(child.id, remaining);
    });
}

function hideCTBCards() {
    const container = document.getElementById("ctbCards");
    container.style.display = "none";
    container.innerHTML = "";
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

function renderItems(items) {
    const table = document.getElementById("itemsTable");
    const body = document.getElementById("itemsBody");
    const noItems = document.getElementById("noItems");

    body.innerHTML = "";

    if (!items || !items.length) {
        noItems.textContent = "No items in this node.";
        table.style.display = "none";
        return;
    }

    noItems.textContent = "";
    table.style.display = "table";

    items.forEach(item => {
        const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${item.name}</td>
            <td>${item.type}</td>
            <td>${item.expiry || ""}</td>
            <td>${item.calories || ""}</td>
            <td>${shortenLocation(item.location || "")}</td>
            <td>${item.rfid || ""}</td>
            <td>${(item.remaining ?? 100)}%</td>
        `;

        body.appendChild(tr);
    });
}

function clearItems() {
    document.getElementById("itemsBody").innerHTML = "";
    document.getElementById("itemsTable").style.display = "none";
    document.getElementById("noItems").textContent = "";
}

// ---------- SEARCH ----------
function setupSearch() {
    const input = document.getElementById("globalSearch");
    const searchView = document.getElementById("searchView");

    input.addEventListener("input", e => {
        const q = e.target.value.trim();

        if (q.length < 2) {
            searchView.style.display = "none";
            searchView.innerHTML = "";
            return;
        }

        fetch("database/search_items.php?q=" + encodeURIComponent(q))
            .then(r => r.json())
            .then(items => {
                if (!items || !items.length) {
                    searchView.style.display = "none";
                    searchView.innerHTML = "";
                    return;
                }

                let html = `
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
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.type}</td>
                            <td>${item.expiry || ""}</td>
                            <td>${item.calories || ""}</td>
                            <td>${shortenLocation(item.location || "")}</td>
                            <td>${item.rfid || ""}</td>
                            <td>${(item.remaining ?? 100)}%</td>
                        </tr>
                    `;
                });

                html += `</tbody></table>`;
                searchView.innerHTML = html;
                searchView.style.display = "block";
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

    if (openBtn) {
        openBtn.onclick = () => {
            modal.style.display = "block";
            loadIncoming();
        };
    }

    if (closeBtn) {
        closeBtn.onclick = () => {
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
                    <td>${item.name}</td>
                    <td>${item.type}</td>
                    <td>${item.rfid}</td>
                `;
                body.appendChild(tr);
            });
        });
}
</script>



</body>
</html>
