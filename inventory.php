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


            <div id="itemsSection">
                <table class="items-table" id="itemsTable" style="display:none;">
                    <thead><tr><th>Name</th><th>Type</th><th>Expiry</th><th>Calories</th><th>Location</th><th>RFID</th></tr></thead>
                    <tbody id="itemsBody"></tbody>
                </table>
                <div id="noItems" style="color:rgba(255,255,255,0.6); text-align:center; padding:40px;">
                    Select a node to view its items
                </div>
            </div>

        </main>
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
let scannewitem=false
let scanBuffer = "";
let lastKeyTime = Date.now();

document.addEventListener("keydown", function(e) {
    let currentTime = Date.now();
    let timeDiff = currentTime - lastKeyTime;

    // If time between keys is large, it's probably manual typing
    if (timeDiff > 50) {
        scanBuffer = "";
    }

    // Ignore control keys
    if (e.key.length === 1) {
        scanBuffer += e.key;
    }

    if (e.key === "Enter") {
        if (scanBuffer.length > 3) {

            // Put the scanned value INTO your global search box
            if (scannewitem) {
                let gs = document.getElementById("item_rfid");
                gs.value = scanBuffer;
            }
            else {
                let gs = document.getElementById("globalSearch");
                gs.value = scanBuffer;
                searchItems(scanBuffer);
            }
        }

        scanBuffer = "";
    }

    lastKeyTime = currentTime;
});

function showIncomingItemForm() {
    if (!currentNode) return alert('Select a node first.');
    document.getElementById('incomingItemFormArea').style.display = 'block';
    document.getElementById('incomingItemsList').innerHTML = '';
    document.getElementById('incomingSearch').value = '';
    searchIncomingItems();
}

function hideIncomingItemForm() {
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
            const li = document.createElement('li');
            li.textContent = `${it.name} (${it.type})`;
            li.dataset.id = it.id;
            li.style.padding = '6px';
            li.style.cursor = 'pointer';
            li.onclick = () => li.classList.toggle('selected');
            ul.appendChild(li);
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
    fetch(GET_TREE).then(r => r.json()).then(json => {
        treeData = json;
        renderTree(treeData);
    }).catch(err => console.error(err));
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

function createNodeLI(node, data) {
    const li = document.createElement('li');
    li.textContent = node.name;
    li.dataset.id = node.id;
    li.onclick = (e) => { e.stopPropagation(); selectTreeNode(node.id); };

    // children
    const children = data.filter(d => String(d.parent_id) === String(node.id));
    if (children && children.length) {
        const childUl = document.createElement('ul');
        childUl.className = 'node-children';
        children.forEach(c => childUl.appendChild(createNodeLI(c, data)));
        li.appendChild(childUl);
    }
    return li;
}

function selectTreeNode(id) {
    currentNode = id;
    // highlight
    document.querySelectorAll('#tree li').forEach(li => li.classList.remove('selected'));
    const li = document.querySelector('#tree li[data-id="'+id+'"]');
    if (li) li.classList.add('selected');

    // update title
    const node = treeData.find(n => String(n.id) === String(id));
    document.getElementById('nodeTitle').textContent = node ? node.name : 'Select a node';
    // build a simple path (breadcrumbs)
    let path = node ? node.name : '';
    let p = node;
    while (p && p.parent_id) {
        p = treeData.find(n => String(n.id) === String(p.parent_id));
        if (p) path = p.name + ' / ' + path;
    }
    document.getElementById('nodePath').textContent = path;
    document.getElementById('form_hierarchy_id').value = id;

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
                tr.innerHTML = `<td>${escapeHtml(it.name)}</td>
                    <td class="uppercase">${escapeHtml(it.type || '')}</td>
                    <td>${it.expiry_date || ''}</td>
                    <td>${it.calories || ''}</td>
                    <td>${escapeHtml(it.location || '')}</td>
                    <td>${escapeHtml(it.rfid || '')}</td>`;
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
                    selectTreeNode(child.id);
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

    if (!currentNode) {
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

    fetch(CREATE_ITEM, { method:'POST', body: fd })
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
            loadTree();
        } else {
            alert('Error: ' + txt);
        }
    })
    .catch(err => alert('Error: ' + err));
}

function searchItems(q) {
    fetch(SEARCH_ITEMS + '?q=' + encodeURIComponent(q))
    .then(r => r.json())
    .then(items => {
        // display results in the items table
        const body = document.getElementById('itemsBody');
        body.innerHTML = '';
        if (!items || items.length === 0) {
            document.getElementById('itemsTable').style.display = 'none';
            document.getElementById('noItems').textContent = 'No search results';
            return;
        }
        document.getElementById('noItems').textContent = '';
        document.getElementById('itemsTable').style.display = 'table';
        items.forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${escapeHtml(it.name)}</td>
                <td class="uppercase">${escapeHtml(it.type || '')}</td>
                <td>${it.expiry_date || ''}</td>
                <td>${it.calories || ''}</td>
                <td>${escapeHtml(it.location || '')}</td>
                <td>${escapeHtml(it.rfid || '')}</td>`;
            body.appendChild(tr);
        });
    });
}

// small utilities
function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];}); }
function debounce(fn, wait){ let t; return function(evt){ clearTimeout(t); t = setTimeout(()=>fn(evt), wait); } }
</script>

</body>
</html>
