<?php 
session_start();
if(!isset($_SESSION['user'])) header('location: nasalogin.php');
$user = $_SESSION['user']; 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DSLM Inventory</title>
    <link rel="stylesheet" href="default.css">
    <link rel="stylesheet" href="https://use.typekit.net/pen4uct.css">
    <style>
        /* COLLAPSIBLE TREE */
            #tree {
                list-style: none;
                padding-left: 10px;
                margin: 0;
            }

            #tree li {
                margin: 3px 0;
            }

            .tree-node {
                cursor: pointer;
                padding: 3px 0;
                font-size: 13px;
                color: #fff;
            }

            .tree-children.collapsed {
                display: none;
            }
            .location-cell {
                max-width: 260px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                font-size: 12px;
            }

            .percent-bar {
                width: 70px;
                height: 8px;
                background: #333;
                border-radius: 4px;
                display: inline-block;
                vertical-align: middle;
            }
            .percent-fill {
                height: 100%;
                background: #4caf50;
                border-radius: 4px;
            }
            .delete-btn {
                background: #c62828;
                color: #fff;
                border: none;
                padding: 4px 8px;
                border-radius: 3px;
                font-size: 11px;
                cursor: pointer;
            }
            .delete-btn:hover {
                background: #b71c1c;
            }
            .tree-node {
                padding: 6px 10px;
                cursor: pointer;
                display: block;
                width: 100%;
            }

            .tree-node:hover {
                background: #e8e8e8;
            }
            .children {
                display: none;
            }

            #incomingModal {
                position: fixed;
                top: 80px;
                right: 40px;
                display: none;
                z-index: 3000;
                pointer-events: none; /* allows clicks to pass through */
            }

            .incoming-modal {
                position: relative;
                width: 420px;
                max-height: 80vh;
                background: rgba(10,12,30,0.97);
                border-radius: 16px;
                box-shadow: 0 0 40px rgba(75,83,185,0.5);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                pointer-events: auto;
            }

            .incoming-header,
            .incoming-footer {
                padding: 12px 16px;
                display: flex;
                align-items: center;
                border-bottom: 1px solid rgba(255,255,255,0.06);
            }
            .incoming-footer {
                border-top: 1px solid rgba(255,255,255,0.06);
                border-bottom: none;
            }
            .incoming-header span {
                font-family: "League Spartan", sans-serif;
                font-size: 1.2rem;
                color: #fff;
            }
            .incoming-close {
                margin-left: auto;
                background: none;
                border: none;
                color: #aaa;
                font-size: 1.2rem;
                cursor: pointer;
            }
            .incoming-body {
                padding: 10px 16px 16px;
                overflow: auto;
            }
            .incoming-footer button {
                background: rgba(255,255,255,0.04);
                border: 1px solid rgba(255,255,255,0.08);
                color: #fff;
                border-radius: 6px;
                padding: 8px 12px;
                cursor: pointer;
            }
            .incoming-footer button:hover {
                background: rgba(255,255,255,0.08);
            }
            /* Drag handle */
            .incoming-drag-handle {
                width: 100%;
                padding: 10px 14px;
                background: rgba(255,255,255,0.05);
                cursor: move;
                font-family: "League Spartan", sans-serif;
                color: #fff;
                font-size: 1rem;
                border-bottom: 1px solid rgba(255,255,255,0.08);
            }

            /* Resize handle */
            .incoming-resize-handle {
                width: 14px;
                height: 14px;
                background: rgba(255,255,255,0.15);
                position: absolute;
                right: 0;
                bottom: 0;
                cursor: se-resize;
                border-radius: 3px;
            }

            .remaining-input {
                width: 70px;
                background: rgba(255,255,255,0.04);
                border: 1px solid rgba(255,255,255,0.12);
                color:#fff;
                border-radius:4px;
                padding:4px 6px;
                font-size:12px;
            }
            .incoming-delete-btn {
                background:#c62828;
                color:#fff;
                border:none;
                padding:4px 8px;
                border-radius:3px;
                font-size:11px;
                cursor:pointer;
            }
            .incoming-delete-btn:hover {
                background:#b71c1c;
            }

        .inventory-wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90vw;
            max-width: 1400px;
            height: 80vh;
            background: rgba(10,12,30,0.97);
            border-radius: 20px;
            display: flex;
            overflow: hidden;
            box-shadow: 0 0 40px rgba(75,83,185,0.4);
            z-index: 2000;
        }

.uppercase {
    text-transform: uppercase;
}

        .inv-sidebar {
            width: 280px;
            background: rgba(5,7,20,0.8);
            padding: 20px;
            overflow-y: auto;
            border-right: 1px solid rgba(255,255,255,0.05);
        }

        .hierarchytextformat {
            color: #ffffff;
            font-family: "League Spartan", sans-serif;
        }
        
        .inv-main {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
        }

        #tree { list-style:none; padding-left:6px; font-family: "League Spartan", sans-serif; font-size:1rem; }
        #tree li { padding:6px 8px; cursor:pointer; border-radius:4px; }
        #tree li:hover { background: rgba(255,255,255,0.03); }
        #tree li.selected { background: rgba(75,83,185,0.35); color:#fff; }
        .node-children { margin-left:16px; }
        
        .items-table { width:100%; border-collapse:collapse; margin-top:12px;color: #ffffff; font-family: "League Spartan", sans-serif; }
        .items-table th, .items-table td { padding:10px; border-bottom:1px solid rgba(255,255,255,0.04); text-align:left; }
        .items-table th { color:#cfe0ff; font-weight:700; }

        .search-input, input, textarea, select, button {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            color:#fff;
            border-radius:6px;
            padding:8px;
        }
        button { cursor:pointer; }

        /* Make sure the planet background stays behind */
        .circle { z-index:1; }
    </style>
</head>
<body>

    <!-- NASA logo + back + logout -->
    <a href="dashboard.php"><img src="images/NASA-Logo.png" alt="Nasa Logo" class="nasalogo"></a>
    <a href="dashboard.php" id="backbutton">Back</a>
    <a href="database/logout.php" id="logoutBtn">Log out</a>

    <!-- The big planet -->
        </div>
    </div>

    <!-- NEW CENTERED INVENTORY PANEL -->
    <div class="inventory-wrapper">
        <aside class="inv-sidebar">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong class="hierarchytextformat">Hierarchy</strong>

                <button id="refreshTree" title="Reload tree" style="background:none; border:none; color:#888; font-size:1.4rem;">↻</button>
            </div>
            <div style="color:rgba(255,255,255,0.6); margin-bottom:12px;">Click on a node...</div>
            <input id="treeSearch" class="search-input" placeholder="Filter tree..." style="width:100%; margin-bottom:10px;"/>
            <ul id="tree" class="hierarchytextformat" aria-label="Hierarchy tree"></ul>

            <hr style="border-color:rgba(255,255,255,0.06); margin:20px 0;">

            <button id="createNodeBtn" style="width:100%; padding:10px; background:#0e3b8f; border:none; border-radius:8px; color:white;">
                + Create Node
            </button>

            <div id="createNodeArea" style="display:none; margin-top:12px;">
                <input id="nodeName" placeholder="Node name" style="width:100%; padding:8px; margin-bottom:8px;">
                <button id="saveNode" style="width:100%; padding:10px; background:#2b79f6; border:none; border-radius:8px;">Save Node</button>
                <button id="cancelcreateNodeBtn" style="width:100%; padding:10px; background:#2b79f6; border:none; border-radius:8px;margin-top: 8px;">Cancel</button>

            </div>
        </aside>

        <main class="inv-main">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div>
                    <strong id="nodeTitle" class="hierarchytextformat" style="font-size:1.5rem;">Select a node</strong>
                    <div id="nodePath" style="color:rgba(255,255,255,0.6); margin-top:4px;"></div>
                </div>
                <div style="display:flex; gap:10px;">
                    <input id="globalSearch" class="search-input" placeholder="Search all items..." />
                    <button id="newItemBtn" style="padding:8px 16px; background:#4b53b9; border:none; border-radius:8px;">+ New Item</button>
                    <button id="incomingItembtn" style="padding:8px 16px; background:#4b53b9; border:none; border-radius:8px;">Add Incoming</button>

                </div>
            </div>

            <!-- Add Item Form -->
            <div id="itemFormArea" style="display:none; background:rgba(255,255,255,0.04); padding:20px; border-radius:12px; margin-top:20px;">
                <h3 class="hierarchytextformat">Add New Item</h3>
                <form id="itemForm">
                    <input type="hidden" id="form_hierarchy_id">
                    <div style="display:flex; gap:12px; margin-bottom:12px;margin-top:12px;">
                        <input id="item_name" placeholder="Item name" style="flex:1;">
                        <select id="item_type">
                            <option value="food" style="color:black;">Food</option>
                            <option value="equipment" style="color:black;">Equipment</option>
                            <option value="tool" style="color:black;">Tool</option>
                            <option value="waste" style="color:black;">Waste</option>
                        </select>
                    </div>
                    <textarea id="item_location" placeholder="Location" style="width:100%; height:80px;"></textarea>
                    <div style="display:flex; gap:12px; margin-top:12px;">
                        <input id="item_expiry" type="date">
                        <input id="item_calories" type="number" placeholder="Calories">
                        <input id="item_rfid" placeholder="RFID / Barcode">
                    </div>
                    <div style="margin-top:16px; display:flex; gap:12px;">
                        <button type="submit" style="flex:1; padding:10px; background:#2b79f6; border:none; border-radius:8px;">Save Item</button>
                        <button type="button" id="cancelForm" style="flex:1; padding:10px; background:#444; border:none; border-radius:8px;">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Add Incoming Item Form -->
            <div id="incomingItemFormArea" style="display:none; background:rgba(255,255,255,0.04); padding:20px; border-radius:12px; margin-top:20px;">
                <h3 class="hierarchytextformat">Add Incoming Item</h3>
                <div style="margin-bottom:12px;margin-top:12px;">
                    <input id="incomingSearch" placeholder="Search incoming items..." style="width:100%; padding:8px;">
                </div>
                <div style="max-height:200px; overflow-y:auto;">
                    <ul id="incomingItemsList" style="list-style:none; padding-left:0;"></ul>
                </div>
                <div style="margin-top:12px; display:flex; gap:12px;">
                    <button id="addIncomingBtn" style="flex:1; padding:10px; background:#2b79f6; border:none; border-radius:8px;">Add to Node</button>
                    <button type="button" id="cancelIncomingForm" style="flex:1; padding:10px; background:#444; border:none; border-radius:8px;">Cancel</button>
                </div>
            </div>


<!-- Node View (child nodes + items) -->
<div id="nodeView">
    <div id="noItems"></div>
    <table class="items-table" id="itemsTable" style="display:none;">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Expiry</th>
                <th>Calories</th>
                <th>Location</th>
                <th>RFID</th>
                <th>Remaining</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody id="itemsBody"></tbody>
    </table>
</div>

<!-- Search Results View -->
<div id="searchView" style="display:none;">
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
                <th>Actions</th>
            </tr>
        </thead>

        <tbody id="searchResults"></tbody>
    </table>
</div>


        </main>
    </div>
<!-- Incoming Items Modal -->
<div id="incomingModal" style="display:none;">
  <div class="incoming-modal">
    <div class="incoming-drag-handle">Incoming Items</div>
    <div class="incoming-resize-handle"></div>

    <div class="incoming-header">
      <span>Incoming Items</span>
      <button class="incoming-close" id="incomingCloseBtn">✕</button>
    </div>
    <div class="incoming-body">
      <table class="items-table">
        <thead>
          <tr>
            <th>✓</th>
            <th>Name</th>
            <th>Type</th>
            <th>Expiry</th>
            <th>Calories</th>
            <th>Location</th>
            <th>RFID</th>
            <th>Remaining %</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="incomingBody"></tbody>
      </table>
      <div id="incomingEmpty" style="color:rgba(255,255,255,0.6); margin-top:10px;"></div>
    </div>
    <div class="incoming-footer">
      <button id="incomingRefreshBtn">Refresh</button>
      <div style="flex:1;"></div>
      <button id="incomingMoveBtn" style="background:#4b53b9;">Move Selected to Inventory</button>
      <button id="incomingCancelBtn">Close</button>
    </div>
  </div>
</div>


<script>

// endpoints placed in database/ folder
const GET_TREE = 'database/get_hierarchy.php';
const GET_ITEMS = 'database/get_items_by_node.php';
const CREATE_ITEM = 'database/create_item.php';
const CREATE_NODE = 'database/create_node.php';
const SEARCH_ITEMS = 'database/search_items.php';

let currentNode = null;
let treeData = [];

document.addEventListener('DOMContentLoaded', () => {
    loadTree();
    document.getElementById('incomingItembtn').onclick = showIncomingItemForm;
    document.getElementById('cancelIncomingForm').onclick = hideIncomingItemForm;
    document.getElementById('addIncomingBtn').onclick = addSelectedIncomingItem;
    document.getElementById('incomingSearch').addEventListener('input', debounce(searchIncomingItems, 300));
    document.getElementById('refreshTree').onclick = loadTree;
    document.getElementById('newItemBtn').onclick = showItemForm;
    document.getElementById('cancelForm').onclick = hideItemForm;
    document.getElementById('itemForm').addEventListener('submit', submitItemForm);
    document.getElementById('createNodeBtn').onclick = () => {
        document.getElementById('createNodeArea').style.display = 'block';
    };
    document.getElementById('cancelcreateNodeBtn').onclick = () => {
        document.getElementById('createNodeArea').style.display = 'none';
    };    
    document.getElementById('saveNode').onclick = createNode;

    document.getElementById('globalSearch').addEventListener('input', debounce(e => {
        const q = e.target.value.trim();
        if (q.length === 0) { if (currentNode) loadNode(currentNode); return; }
        searchItems(q);
    }, 300));

    document.getElementById('treeSearch').addEventListener('input', debounce(e => {
        renderTree(filterTree(e.target.value.trim()));
    }, 200));
});
let scannewitem = false;
let scannerSequence = true;
let scanningIncoming = false;   // NEW
let scanBuffer = "";
let lastKeyTime = Date.now();

document.addEventListener("keydown", function(e) {
    let currentTime = Date.now();
    let timeDiff = currentTime - lastKeyTime;

    // Reset buffer if too slow (human typing)
    if (timeDiff > 50) {
        scanBuffer = "";
        scannerSequence = true; // new sequence
    } else {
        // check if THIS character was typed too slowly
        if (timeDiff > 20) {
            scannerSequence = false; // not a true scanner sequence
        }
    }

    if (e.key.length === 1) {
        scanBuffer += e.key;
    }

    if (e.key === "Enter") {
        const isScanner = (scannerSequence && scanBuffer.length > 3);

        if (isScanner) {
            if (scanningIncoming) {
                autoAddIncomingByRFID(scanBuffer);
            } else if (scannewitem) {
                document.getElementById("item_rfid").value = scanBuffer;
            } else {
                document.getElementById("globalSearch").value = scanBuffer;
                searchItems(scanBuffer);
            }
        }

        // reset
        scanBuffer = "";
        scannerSequence = true;
    }

    lastKeyTime = currentTime;
});
const GET_INCOMING = 'database/get_incoming_items.php';
const ADD_INCOMING = "database/add_incoming_item_to_node.php";

const incomingModal = document.getElementById('incomingModal');
const incomingBody = document.getElementById('incomingBody');
const incomingEmpty = document.getElementById('incomingEmpty');

document.getElementById('incomingItembtn').addEventListener('click', openIncomingModal);
document.getElementById('incomingCloseBtn').addEventListener('click', closeIncomingModal);
document.getElementById('incomingCancelBtn').addEventListener('click', closeIncomingModal);
document.getElementById('incomingRefreshBtn').addEventListener('click', loadIncomingItems);
document.getElementById('incomingMoveBtn').addEventListener('click', moveSelectedIncomingItems);

// DRAGGING
(function() {
    const modal = document.querySelector('.incoming-modal');
    const handle = document.querySelector('.incoming-drag-handle');

    let offsetX = 0, offsetY = 0, dragging = false;

    handle.addEventListener('mousedown', e => {
        dragging = true;
        offsetX = e.clientX - modal.offsetLeft;
        offsetY = e.clientY - modal.offsetTop;
        document.body.style.userSelect = 'none';
    });

    document.addEventListener('mousemove', e => {
        if (!dragging) return;
        modal.style.left = (e.clientX - offsetX) + 'px';
        modal.style.top = (e.clientY - offsetY) + 'px';
    });

    document.addEventListener('mouseup', () => {
        dragging = false;
        document.body.style.userSelect = 'auto';
    });
})();
// RESIZING
(function() {
    const modal = document.querySelector('.incoming-modal');
    const grip = document.querySelector('.incoming-resize-handle');

    let resizing = false, startX, startY, startW, startH;

    grip.addEventListener('mousedown', e => {
        resizing = true;
        startX = e.clientX;
        startY = e.clientY;
        startW = modal.offsetWidth;
        startH = modal.offsetHeight;
        document.body.style.userSelect = 'none';
        e.preventDefault();
    });

    document.addEventListener('mousemove', e => {
        if (!resizing) return;
        modal.style.width = (startW + (e.clientX - startX)) + 'px';
        modal.style.height = (startH + (e.clientY - startY)) + 'px';
    });

    document.addEventListener('mouseup', () => {
        resizing = false;
        document.body.style.userSelect = 'auto';
    });
})();
function toggleNode(el) {
    const kids = el.nextElementSibling;
    if (!kids) return;

    kids.style.display = kids.style.display === "none" ? "block" : "none";
}
function selectNode(id) {
    document.querySelectorAll('.children').forEach(c => c.style.display = 'none');

    const el = document.querySelector(`[data-id="${id}"]`);
    if (el && el.nextElementSibling) {
        el.nextElementSibling.style.display = 'block';
    }

    window.currentNodeId = id;
}

function openIncomingModal() {
    incomingModal.style.display = 'block';
    loadIncomingItems();
}
function closeIncomingModal() {
    incomingModal.style.display = 'none';
}

function loadIncomingItems() {
    incomingBody.innerHTML = '';
    incomingEmpty.textContent = 'Loading...';
    fetch(GET_INCOMING)
        .then(r => r.json())
        .then(items => {
            incomingBody.innerHTML = '';
            if (!items || !items.length) {
                incomingEmpty.textContent = 'No incoming items.';
                return;
            }
            incomingEmpty.textContent = '';
            items.forEach(it => {
                const tr = document.createElement('tr');

                const shortLoc = (it.location || '').length > 30
                    ? it.location.slice(0, 27) + '...'
                    : (it.location || '');

                tr.innerHTML = `
                    <td><input type="checkbox" class="incoming-check" data-id="${it.id}"></td>
                    <td>${escapeHtml(it.name)}</td>
                    <td>${escapeHtml(it.type || '')}</td>
                    <td>${escapeHtml(it.expiry_date || '')}</td>
                    <td>${escapeHtml(it.calories || '')}</td>
                    <td class="location-cell" title="${escapeHtml(it.location || '')}">${escapeHtml(shortLoc)}</td>
                    <td>${escapeHtml(it.rfid || '')}</td>
                    <td>
                        <input type="number" min="0" max="100" value="100"
                               class="remaining-input" data-id="${it.id}">
                    </td>
                    <td>
                        <button class="incoming-delete-btn" data-id="${it.id}">Remove</button>
                    </td>
                `;
                incomingBody.appendChild(tr);
            });

            incomingBody.querySelectorAll('.incoming-delete-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    deleteIncomingItem(id);
                });
            });
        })
        .catch(() => {
            incomingEmpty.textContent = 'Error loading incoming items.';
        });
}

function getSelectedIncoming() {
    const checks = Array.from(document.querySelectorAll('.incoming-check:checked'));
    return checks.map(ch => {
        const id = ch.getAttribute('data-id');
        const remInput = document.querySelector(`.remaining-input[data-id="${id}"]`);
        let remaining = parseInt(remInput && remInput.value, 10);
        if (isNaN(remaining) || remaining < 0) remaining = 0;
        if (remaining > 100) remaining = 100;
        return { id, remaining };
    });
}

function moveSelectedIncomingItems() {
    const selected = getSelectedIncoming();
    if (!selected.length) {
        alert('Select at least one incoming item.');
        return;
    }
    if (!window.currentNodeId) {
        alert('Select a node in the hierarchy first.');
        return;
    }

    const ids = selected.map(s => s.id);
    const remainingMap = {};
    selected.forEach(s => remainingMap[s.id] = s.remaining);

    const formData = new FormData();
    formData.append('hierarchy_id', window.currentNodeId);
    formData.append('ids', ids.join(','));
    formData.append('remaining', JSON.stringify(remainingMap));

    fetch(ADD_INCOMING, { method: 'POST', body: formData })
        .then(r => r.text())
        .then(txt => {
            if (txt.trim() === 'OK') {
                loadIncomingItems();
                if (typeof loadItemsForNode === 'function') {
                    loadItemsForNode(window.currentNodeId);
                }
            } else {
                alert('Error: ' + txt);
            }
        })
        .catch(err => alert('Error: ' + err));
}

function deleteIncomingItem(id) {
    if (!confirm('Remove this incoming item?')) return;
    const formData = new FormData();
    formData.append('delete_id', id);

    fetch(ADD_INCOMING, { method: 'POST', body: formData })
        .then(r => r.text())
        .then(txt => {
            if (txt.trim() === 'OK') {
                loadIncomingItems();
            } else {
                alert('Error: ' + txt);
            }
        })
        .catch(err => alert('Error: ' + err));
}

function shortenLocation(full) {
    if (!full) return '';
    const parts = full.split(' / ');
    return parts.length > 2 ? parts.slice(-2).join(' / ') : full;
}

function autoAddIncomingByRFID(rfid) {
    fetch('database/get_incoming_items.php?q=' + encodeURIComponent(rfid))
    .then(r => r.json())
    .then(items => {

        // No matches
        if (!items || items.length === 0) {
            showTemporaryMessage("❌ No match for: " + rfid, "red");
            clearIncomingScan();
            return;
        }

        // Too many matches
        if (items.length > 1) {
            showTemporaryMessage("⚠️ Multiple matches. Manual select required.", "orange");
            clearIncomingScan();
            return;
        }

        // Exactly one match → auto-add it
        const item = items[0];
        const fd = new FormData();
        fd.append('ids', item.id);
        fd.append('hierarchy_id', currentNode);

        fetch('database/add_incoming_item_to_node.php', { method: 'POST', body: fd })
        .then(r => r.text())
        .then(txt => {
            if (txt.trim() === 'OK') {

                // SUCCESS feedback
                showTemporaryMessage("✔️ Added: " + item.name, "green");

                // Reset UI for next scan
                clearIncomingScan();

                // Refresh lists
                loadNode(currentNode);
                searchIncomingItems();
            } else {
                showTemporaryMessage("❌ Error: " + txt, "red");
                clearIncomingScan();
            }
        });
    });
}

function clearIncomingScan() {
    document.getElementById('incomingSearch').value = "";
    scanBuffer = "";
}

function showTemporaryMessage(msg, color="white") {
    let box = document.createElement("div");
    box.textContent = msg;
    box.style.position = "fixed";
    box.style.top = "20px";
    box.style.right = "20px";
    box.style.padding = "12px 18px";
    box.style.background = "rgba(0,0,0,0.75)";
    box.style.border = "1px solid rgba(255,255,255,0.2)";
    box.style.color = color;
    box.style.fontFamily = "League Spartan, sans-serif";
    box.style.fontSize = "1rem";
    box.style.borderRadius = "8px";
    box.style.zIndex = 9999;
    box.style.opacity = "1";
    box.style.transition = "opacity 0.5s ease";

    document.body.appendChild(box);

    setTimeout(() => { box.style.opacity = "0"; }, 1200);
    setTimeout(() => { box.remove(); }, 1800);
}


function showIncomingItemForm() {
    if (!window.currentNodeId) return alert('Select a node first.');

    scanningIncoming = true;   // ENABLE SCAN MODE
    scannewitem = false;       // ensure only one mode is active

    document.getElementById('incomingItemFormArea').style.display = 'block';
    document.getElementById('incomingItemsList').innerHTML = '';
    document.getElementById('incomingSearch').value = '';

    document.getElementById('incomingSearch').focus();

    searchIncomingItems();
}


function hideIncomingItemForm() {
    scanningIncoming = false;   // disable scan mode
    document.getElementById('incomingItemFormArea').style.display = 'none';
    document.getElementById('incomingItemsList').innerHTML = '';
}


function searchIncomingItems() {
    const q = document.getElementById('incomingSearch').value.trim();
    fetch('database/get_incoming_items.php?q=' + encodeURIComponent(q))
    .then(r => r.json())
    .then(items => {
        const ul = document.getElementById('incomingItemsList');
        ul.innerHTML = '';
        if (!items || items.length === 0) {
            ul.innerHTML = '<li style="color:#888;">No items found</li>';
            return;
        }
        items.forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(it.name)}</td>
                <td class="uppercase">${escapeHtml(it.type || '')}</td>
                <td>${it.expiry_date || ''}</td>
                <td>${it.calories || ''}</td>

                <!-- Shortened location -->
                <td class="location-cell" title="${escapeHtml(it.location || '')}">
                    ${escapeHtml(shortenLocation(it.location))}
                </td>

                <td>${escapeHtml(it.rfid || '')}</td>

                <!-- Remaining % bar -->
                <td>
                    <div class="percent-bar">
                        <div class="percent-fill" style="width:${it.remaining_percent || 0}%"></div>
                    </div>
                    <span style="font-size:11px;margin-left:4px;">${it.remaining_percent || 0}%</span>
                </td>

                <!-- Remove button -->
                <td>
                    <button class="delete-btn" onclick="deleteItem(${it.id})">Remove</button>
                </td>
            `;
            body.appendChild(tr);
        });
    });
}

function addSelectedIncomingItem() {
    // get all selected <li>
    const selectedLis = Array.from(document.querySelectorAll('#incomingItemsList li.selected'));
    if (selectedLis.length === 0) return alert('Select at least one item');

    // collect IDs
    const ids = selectedLis.map(li => li.dataset.id);

    const fd = new FormData();
    fd.append('ids', ids.join(','));
    fd.append('hierarchy_id', currentNode);

    fetch('database/add_incoming_item_to_node.php', { method:'POST', body:fd })
    .then(r => r.text())
    .then(txt => {
        if (txt.trim() === 'OK') {
            alert('Items added to node');

            // remove only the items that were added
            selectedLis.forEach(li => li.remove());

            loadNode(currentNode);
        } else {
            alert('Error: ' + txt);
        }
    })
    .catch(err => alert('Error: ' + err));
}



function loadTree() {
    return fetch(GET_TREE)
        .then(r => r.json())
        .then(json => {
            treeData = json;
            renderTree(treeData);
        })
        .catch(err => {
            console.error('Failed to load tree:', err);
            alert('Could not load hierarchy tree');
        });
}

function filterTree(q) {
    if (!q) return treeData;
    const lower = q.toLowerCase();
    return treeData.filter(n => (n.name||'').toLowerCase().includes(lower));
}

function renderTree(data) {
    const rootNodes = data.filter(x => x.parent_id == null || x.parent_id === '0' || x.parent_id === 0);
    const ul = document.getElementById('tree');
    ul.innerHTML = '';
    rootNodes.forEach(node => {
        ul.appendChild(createNodeLI(node, data));
    });
}
function deleteItem(id) {
    if (!confirm("Remove this item?")) return;

    fetch('delete_item.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + encodeURIComponent(id)
    })
    .then(() => loadItemsForNode(currentNodeId)) // reload table
    .catch(err => console.error(err));
}

function createNodeLI(node, data) {
    const li = document.createElement('li');
    li.classList.add('tree-node');
    li.dataset.id = node.id;
    li.textContent = node.name;

    // clicking a tree node selects + loads + remembers
    li.onclick = (e) => {
        e.stopPropagation();
        selectTreeNode(node.id);
    };

    // find children
    const children = data.filter(x => String(x.parent_id) === String(node.id));
    if (children.length > 0) {
        const ul = document.createElement('ul');
        children.forEach(child => {
            ul.appendChild(createNodeLI(child, data));
        });
        li.appendChild(ul);
    }

    return li;
}


function selectTreeNode(id) {
    window.currentNodeId = id;

    // highlight
    document.querySelectorAll('#tree li').forEach(li => li.classList.remove('selected'));
    const li = document.querySelector('#tree li[data-id="'+id+'"]');
    if (li) li.classList.add('selected');

    // update title
    const node = treeData.find(n => String(n.id) === String(id));
    document.getElementById('nodeTitle').textContent = node ? node.name : 'Select a node';

    // breadcrumbs
    let path = node ? node.name : '';
    let p = node;
    while (p && p.parent_id) {
        p = treeData.find(n => String(n.id) === String(p.parent_id));
        if (p) path = p.name + ' / ' + path;
    }
    document.getElementById('nodePath').textContent = path;

    // form hidden field
    document.getElementById('form_hierarchy_id').value = id;

    // remember last selected
    localStorage.setItem('lastNode', id);

    // load node
    loadNode(id);
}



function loadNode(id) {
    fetch(GET_ITEMS + '?id=' + encodeURIComponent(id))
    .then(r => r.json())
    .then(items => {
        const body = document.getElementById('itemsBody');
        const noItemsDiv = document.getElementById('noItems');
        const table = document.getElementById('itemsTable');

        // Reset
        body.innerHTML = '';
        table.style.display = 'none';
        noItemsDiv.innerHTML = '';
        noItemsDiv.style.display = 'block';

        // Case 1: Has actual items → show normal table
        if (items && items.length > 0) {
            table.style.display = 'table';
            items.forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(it.name)}</td>
                <td class="uppercase">${escapeHtml(it.type || '')}</td>
                <td>${it.expiry_date || ''}</td>
                <td>${it.calories || ''}</td>

                <!-- Shortened location -->
                <td class="location-cell" title="${escapeHtml(it.location || '')}">
                    ${escapeHtml(shortenLocation(it.location))}
                </td>

                <td>${escapeHtml(it.rfid || '')}</td>

                <!-- Remaining % bar -->
                <td>
                    <div class="percent-bar">
                        <div class="percent-fill" style="width:${it.remaining_percent || 0}%"></div>
                    </div>
                    <span style="font-size:11px;margin-left:4px;">${it.remaining_percent || 0}%</span>
                </td>

                <!-- Remove button -->
                <td>
                    <button class="delete-btn" onclick="deleteItem(${it.id})">Remove</button>
                </td>
            `;
            body.appendChild(tr);
        });
            noItemsDiv.style.display = 'none';
            return;
        }

        // Case 2: No items → show child nodes as clickable cards (FLEXBOX VERSION)
        const children = treeData.filter(n => String(n.parent_id) === String(id));

        if (children.length > 0) {
            noItemsDiv.innerHTML = `
                <div style="text-align:center; padding:30px 20px 20px; color:rgba(255,255,255,0.7);">
                    <div style="font-size:1.2rem; margin-bottom:8px;">No items in this node</div>
                    <div style="font-size:0.95rem; opacity:0.8;">Select a location below to continue</div>
                </div>
            `;

            const flexContainer = document.createElement('div');
            flexContainer.style.cssText = `
                display: flex;
                flex-wrap: wrap;
                gap: 18px;
                padding: 0 20px 30px;
                justify-content: center;
                align-items: stretch;
            `;

            children.forEach(child => {
                const card = document.createElement('div');
                card.style.cssText = `
                    flex: 1 1 260px;
                    max-width: 320px;
                    background: rgba(255,255,255,0.06);
                    border: 1px solid rgba(255,255,255,0.1);
                    border-radius: 12px;
                    padding: 24px 20px;
                    text-align: center;
                    cursor: pointer;
                    transition: all 0.25s ease;
                    font-family: "League Spartan", sans-serif;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                `;

                card.onmouseover = () => {
                    card.style.background = 'rgba(75,83,185,0.3)';
                    card.style.transform = 'translateY(-4px)';
                    card.style.boxShadow = '0 12px 30px rgba(75,83,185,0.4)';
                };
                card.onmouseout = () => {
                    card.style.background = 'rgba(255,255,255,0.06)';
                    card.style.transform = 'translateY(0)';
                    card.style.boxShadow = '0 4px 15px rgba(0,0,0,0.2)';
                };

                card.onclick = (e) => {
                    e.stopPropagation();
                    selectTreeNode(child.id); // this now selects + loads + remembers
                };




                card.innerHTML = `
                    <div style="font-weight:700; font-size:1.25rem; margin-bottom:8px; color:#ffffff;">
                        ${escapeHtml(child.name)}
                    </div>
                    <div style="font-size:0.9rem; color:rgba(255,255,255,0.6);">
                        Click to enter →
                    </div>
                `;

                flexContainer.appendChild(card);
            });

            noItemsDiv.appendChild(flexContainer);

        } else {
            // Truly empty
            noItemsDiv.innerHTML = `
                <div style="text-align:center; padding:60px 20px; color:rgba(255,255,255,0.5); font-size:1.1rem;">
                    This location is completely empty
                </div>
            `;
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('noItems').textContent = 'Error loading node';
    });
} 

// Helper: quick check if a node has items (optional — makes cards smarter)
function childHasItems(nodeId) {
    // You can improve this later with a small backend call if needed
    // For now, just assume yes if it has children of its own
    return treeData.some(n => String(n.parent_id) === String(nodeId));
}

function showItemForm() {
    scannewitem = true

    if (!currentNodeId) {
        alert('Select a node first.');
        return;
    }

    document.getElementById('itemFormArea').style.display = 'block';

    document.getElementById('form_hierarchy_id').value = currentNode;

    document.getElementById('itemForm').reset();

    document.getElementById('item_name').focus();
}

function hideItemForm() {
    scannewitem = false
    document.getElementById('itemFormArea').style.display = 'none';
    document.getElementById('itemForm').reset();
}

function submitItemForm(e) {
    e.preventDefault();
    const fd = new FormData();
    fd.append('name', document.getElementById('item_name').value);
    fd.append('hierarchy_id', document.getElementById('form_hierarchy_id').value);
    fd.append('location', document.getElementById('item_location').value);
    fd.append('expiry_date', document.getElementById('item_expiry').value);
    fd.append('calories', document.getElementById('item_calories').value);
    fd.append('rfid', document.getElementById('item_rfid').value);
    fd.append('type', document.getElementById('item_type').value);

    fetch('database/create_incoming_item.php', { method:'POST', body: fd })
    .then(r => r.text())
    .then(txt => {
        if (txt.trim() === 'OK') {
            alert('Saved');
            hideItemForm();
            loadNode(document.getElementById('form_hierarchy_id').value);
        } else {
            alert('Error: ' + txt);
        }
    }).catch(err => alert('Error: ' + err));
}

function createNode(e) {
    const name = document.getElementById('nodeName').value.trim();
    if (!name) return alert('Enter a node name');
    // use parent = currentNode if set
    const fd = new FormData();
    fd.append('name', name);
    fd.append('parent_id', currentNode ? currentNode : 0);

    fetch(CREATE_NODE, { method:'POST', body:fd })
    .then(r => r.text())
    .then(txt => {
        if (txt.trim() === 'OK') {
            document.getElementById('createNodeArea').style.display = 'none';
            document.getElementById('nodeName').value = '';
            loadTree().then(() => { if (currentNode) loadNode(currentNode); });
        } else {
            alert('Error: ' + txt);
        }
    })
    .catch(err => alert('Error: ' + err));
}

function searchItems(q) {

    // 🔥 Add this: disable the node view while searching
    document.getElementById('noItems').style.display = 'none';

    fetch(SEARCH_ITEMS + '?q=' + encodeURIComponent(q))
    .then(r => r.json())
    .then(items => {
        const body = document.getElementById('itemsBody');
        body.innerHTML = '';

        if (!items || items.length === 0) {
            document.getElementById('itemsTable').style.display = 'none';
            document.getElementById('noItems').textContent = 'No search results';
            document.getElementById('noItems').style.display = 'block';
            return;
        }

        document.getElementById('itemsTable').style.display = 'table';
        document.getElementById('noItems').style.display = 'none';

        items.forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(it.name)}</td>
                <td class="uppercase">${escapeHtml(it.type || '')}</td>
                <td>${it.expiry_date || ''}</td>
                <td>${it.calories || ''}</td>

                <!-- Shortened location -->
                <td class="location-cell" title="${escapeHtml(it.location || '')}">
                    ${escapeHtml(shortenLocation(it.location))}
                </td>

                <td>${escapeHtml(it.rfid || '')}</td>

                <!-- Remaining % bar -->
                <td>
                    <div class="percent-bar">
                        <div class="percent-fill" style="width:${it.remaining_percent || 0}%"></div>
                    </div>
                    <span style="font-size:11px;margin-left:4px;">${it.remaining_percent || 0}%</span>
                </td>

                <!-- Remove button -->
                <td>
                    <button class="delete-btn" onclick="deleteItem(${it.id})">Remove</button>
                </td>
            `;
            body.appendChild(tr);
        });

    });
}


// small utilities
function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];}); }
function debounce(fn, wait){ let t; return function(evt){ clearTimeout(t); t = setTimeout(()=>fn(evt), wait); } }

window.addEventListener('DOMContentLoaded', () => {
    const last = localStorage.getItem('lastNode');
    if (last) {
        selectTreeNode(last);
    }
});

</script>

</body>
</html>
