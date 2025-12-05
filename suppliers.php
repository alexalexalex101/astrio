<?php
session_start();
include("database/connection.php");

// Fetch suppliers with PDO
$stmt = $conn->query("SELECT * FROM suppliers");
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="default.css">
  <style>
  /* Table container */
  table {
    width: 85%;
    margin: 2rem auto;
    border-collapse: collapse;
    background: rgba(12, 18, 44, 0.9); /* deep space blue */
    box-shadow: 0 0 25px rgba(75, 83, 185, 0.7), inset 0 0 10px rgba(255,255,255,0.1);
    border-radius: 12px;
    overflow: hidden;
    color: #e0e6ff;
    font-family: "League Spartan", sans-serif;
  }

  /* Header row */
  th {
    padding: 14px;
    background: linear-gradient(90deg, #0e3b8f, #1c2143);
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 1rem;
    border-bottom: 2px solid #4b53b9;
  }

  /* Table cells */
  td {
    padding: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    font-size: 0.95rem;
  }

  /* Zebra striping */
  tr:nth-child(even) {
    background-color: rgba(255,255,255,0.05);
  }

  /* Hover effect */
  tbody tr:hover {
    background-color: rgba(75, 83, 185, 0.3);
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  /* Dragging highlight */
  tr.dragging {
    opacity: 0.7;
    background: rgba(198, 115, 255, 0.3);
    box-shadow: 0 0 15px rgba(198,115,255,0.7);
  }

  /* Rounded corners for first/last cells */
  th:first-child, td:first-child {
    padding-left: 20px;
  }
  th:last-child, td:last-child {
    padding-right: 20px;
  }
</style>
</head>
<body>
  <a href="dashboard.php"><img src="images/NASA-Logo.png" alt="Nasa Logo" class="nasalogo"></a>
  <a href="database/logout.php" id="logoutBtn">Log out</a>

  <div class="circle">
    <div class="planetcenter">
      <h2 class="LoginTitle">Supplier Database</h2>
      
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
              <td><?php echo htmlspecialchars($row['contract_id']); ?></td>
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