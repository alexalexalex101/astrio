<?php
session_start();
include("database/connection.php");

// Fetch suppliers with their latest contract
$stmt = $conn->query("
    SELECT s.*, c.contract_name
    FROM suppliers s
    LEFT JOIN contracts c 
      ON c.supplier_id = s.supplier_id
      AND c.contract_id = (
          SELECT c2.contract_id 
          FROM contracts c2 
          WHERE c2.supplier_id = s.supplier_id 
          ORDER BY c2.start_date DESC 
          LIMIT 1
      )
");
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="default.css">
  <style>
    /* Scrollable container */
    #supplierContainer {
      width: 65%;
      max-height: 400px;
      overflow-y: auto;
      margin: 2rem auto;
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 12px;
      box-shadow: 0 0 25px rgba(75, 83, 185, 0.7), inset 0 0 10px rgba(255,255,255,0.1);
      background: rgba(12, 18, 44, 0.9);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      color: #e0e6ff;
      font-family: "League Spartan", sans-serif;
    }

    th, td {
      padding: 12px;
      height: 50px;           /* uniform row height */
      text-align: center;
      vertical-align: middle;  /* center text vertically */
      border-bottom: 1px solid rgba(255,255,255,0.1);
      box-sizing: border-box;
    }

    th {
      background: linear-gradient(90deg, #0e3b8f, #1c2143);
      color: #fff;
      text-transform: uppercase;
      letter-spacing: 1px;
      border-bottom: 2px solid #4b53b9;
    }

    tbody tr:nth-child(even) {
      background-color: rgba(255,255,255,0.05);
    }

    tbody tr:hover {
      background-color: rgba(75, 83, 185, 0.3);
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    tr.dragging {
      opacity: 0.7;
      background: rgba(198, 115, 255, 0.3);
      box-shadow: 0 0 15px rgba(198,115,255,0.7);
    }

    /* Scrollbar styling */
    #supplierContainer::-webkit-scrollbar {
      width: 8px;
    }
    #supplierContainer::-webkit-scrollbar-track {
      background: rgba(255,255,255,0.05);
      border-radius: 10px;
    }
    #supplierContainer::-webkit-scrollbar-thumb {
      background: rgba(75,83,185,0.6);
      border-radius: 10px;
    }
    #supplierContainer::-webkit-scrollbar-thumb:hover {
      background: rgba(75,83,185,0.8);
    }
  </style>
</head>
<body>
  <a href="dashboard.php"><img src="images/NASA-Logo.png" alt="Nasa Logo" class="nasalogo"></a>
  <a href="database/logout.php" id="logoutBtn">Log out</a>
  <a href="dashboard.php" id="backbutton">Back</a>

  <div class="circle">
    <div class="planetcenter">
      <h2 class="LoginTitle">Supplier Database</h2>

      <!-- Scrollable supplier table -->
      <div id="supplierContainer">
        <table id="supplierTable">
          <thead>
            <tr>
              <th>Name</th>
              <th>Contract</th>
              <th>Item Supplied</th>
              <th>Risk Level</th>
              <th>Email</th>
              <th>Tracking Method</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($suppliers as $row): ?>
            <tr draggable="true">
              <td><?php echo htmlspecialchars($row['name']); ?></td>
              <td><?php echo htmlspecialchars($row['contract_name']); ?></td>
              <td><?php echo htmlspecialchars($row['item_supplied']); ?></td>
              <td><?php echo htmlspecialchars($row['risk_level']); ?></td>
              <td><?php echo htmlspecialchars($row['contact_email']); ?></td>
              <td><?php echo htmlspecialchars($row['tracking_method']); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    const table = document.getElementById("supplierTable");
    let draggedRow = null;

    table.querySelectorAll("tbody tr").forEach(row => {
      row.addEventListener("dragstart", e => {
        draggedRow = row;
        row.classList.add("dragging");
      });

      row.addEventListener("dragend", e => {
        draggedRow = null;
        row.classList.remove("dragging");
      });

      row.addEventListener("dragover", e => {
        e.preventDefault();
        const tbody = table.querySelector("tbody");
        const afterElement = getDragAfterElement(tbody, e.clientY);
        if (afterElement == null) {
          tbody.appendChild(draggedRow);
        } else {
          tbody.insertBefore(draggedRow, afterElement);
        }
      });
    });

    function getDragAfterElement(tbody, y) {
      const rows = [...tbody.querySelectorAll("tr:not(.dragging)")];
      return rows.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
          return { offset: offset, element: child };
        } else {
          return closest;
        }
      }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
  </script>
</body>
</html>
