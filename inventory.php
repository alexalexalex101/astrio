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
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <strong class="hierarchytextformat">Hierarchy</strong>
                <button id="refreshTree" title="Reload tree" style="background:none; border:none; color:#888; font-size:1.4rem;">↻</button>
            </div>
            <input id="treeSearch" class="search-input" placeholder="Filter tree..." style="width:100%; margin-bottom:10px;"/>
            <ul id="tree" class="hierarchytextformat" aria-label="Hierarchy tree"></ul>

            <hr style="border-color:rgba(255,255,255,0.06); margin:20px 0;">

            <button id="createNodeBtn" style="width:100%; padding:10px; background:#0e3b8f; border:none; border-radius:8px; color:white;">
                + Create Node
            </button>

            <div id="createNodeArea" style="display:none; margin-top:12px;">
                <input id="nodeName" placeholder="Node name" style="width:100%; padding:8px; margin-bottom:8px;">
                <select id="nodeType" style="width:100%; padding:8px; margin-bottom:8px;">
                    <option value="corporation">Corporation</option>
                    <option value="large_team">Large Team</option>
                    <option value="small_team">Small Team</option>
                </select>
                <button id="saveNode" style="width:100%; padding:10px; background:#2b79f6; border:none; border-radius:8px;">Save Node</button>
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
                </div>
            </div>

            <div id="itemsSection">
                <table class="items-table" id="itemsTable" style="display:none;">
                    <thead><tr><th>Name</th><th>Type</th><th>Expiry</th><th>Calories</th><th>Notes</th></tr></thead>
                    <tbody id="itemsBody"></tbody>
                </table>
                <div id="noItems" style="color:rgba(255,255,255,0.6); text-align:center; padding:40px;">
                    Select a node to view its items
                </div>
            </div>

            <!-- Add Item Form -->
            <div id="itemFormArea" style="display:none; background:rgba(255,255,255,0.04); padding:20px; border-radius:12px; margin-top:20px;">
                <h3 class="hierarchytextformat">Add New Item</h3>
                <form id="itemForm">
                    <input type="hidden" id="form_hierarchy_id">
                    <div style="display:flex; gap:12px; margin-bottom:12px;">
                        <input id="item_name" placeholder="Item name" style="flex:1;">
                        <select id="item_type">
                            <option value="food">Food</option>
                            <option value="equipment">Equipment</option>
                            <option value="tool">Tool</option>
                            <option value="waste">Waste</option>
                        </select>
                    </div>
                    <textarea id="item_notes" placeholder="Notes / description" style="width:100%; height:80px;"></textarea>
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
    document.getElementById('refreshTree').onclick = loadTree;
    document.getElementById('newItemBtn').onclick = showItemForm;
    document.getElementById('cancelForm').onclick = hideItemForm;
    document.getElementById('itemForm').addEventListener('submit', submitItemForm);
    document.getElementById('createNodeBtn').onclick = () => {
        document.getElementById('createNodeArea').style.display = 'block';
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
        body.innerHTML = '';
        if (!items || items.length === 0) {
            document.getElementById('itemsTable').style.display = 'none';
            document.getElementById('noItems').textContent = 'No items for this node.';
            return;
        }
        document.getElementById('noItems').textContent = '';
        document.getElementById('itemsTable').style.display = 'table';
        items.forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${escapeHtml(it.name)}</td>
                            <td>${escapeHtml(it.type || '')}</td>
                            <td>${it.expiry_date || ''}</td>
                            <td>${it.calories || ''}</td>
                            <td>${escapeHtml(it.notes || '')}</td>`;
            body.appendChild(tr);
        });
    });
}

function showItemForm() {
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
    document.getElementById('itemFormArea').style.display = 'none';
    document.getElementById('itemForm').reset();
}

function submitItemForm(e) {
    e.preventDefault();
    const fd = new FormData();
    fd.append('name', document.getElementById('item_name').value);
    fd.append('hierarchy_id', document.getElementById('form_hierarchy_id').value);
    fd.append('notes', document.getElementById('item_notes').value);
    fd.append('expiry', document.getElementById('item_expiry').value);
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
    const type = document.getElementById('nodeType').value;
    if (!name) return alert('Enter a node name');
    // use parent = currentNode if set
    const fd = new FormData();
    fd.append('name', name);
    fd.append('type', type);
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
                            <td>${escapeHtml(it.type || '')}</td>
                            <td>${it.expiry_date || ''}</td>
                            <td>${it.calories || ''}</td>
                            <td>${escapeHtml(it.notes || '')}</td>`;
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