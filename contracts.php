<?php
session_start();
include("database/connection.php");

// Handle Add Contract form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contract'])) {
    $stmt = $conn->prepare("INSERT INTO contracts (supplier_id, contract_name, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['supplier_id'],
        $_POST['contract_name'],
        $_POST['start_date'],
        $_POST['end_date'],
        $_POST['status']
    ]);
}

// Handle Delete Contract
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM contracts WHERE contract_id = ?");
    $stmt->execute([$_GET['delete']]);
}

// Fetch suppliers for dropdown
$suppliersStmt = $conn->query("SELECT supplier_id, name FROM suppliers");
$suppliers = $suppliersStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch contracts with supplier names
$stmt = $conn->query("
  SELECT c.contract_id, c.contract_name, c.start_date, c.end_date, c.status, s.name AS supplier_name
  FROM contracts c
  JOIN suppliers s ON c.supplier_id = s.supplier_id
  ORDER BY c.start_date ASC
");
$contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="default.css">
  <style>
    
  table {
    width: 40%;
    margin: 0.8rem auto;
    border-collapse: collapse;
    background: rgba(12, 18, 44, 0.9);
    box-shadow: 0 0 15px rgba(75, 83, 185, 0.6), inset 0 0 6px rgba(255,255,255,0.08);
    border-radius: 10px;
    overflow: hidden;
    color: #e0e6ff;
    font-family: "League Spartan", sans-serif;
    font-size: 0.78rem;  
  }

  th {
    padding: 8px;
    background: linear-gradient(90deg, #0e3b8f, #1c2143);
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-size: 0.75rem;
    border-bottom: 2px solid #4b53b9;
  }

  td {
    padding: 7px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    font-size: 0.77rem;
  }

  tr:nth-child(even) {
    background-color: rgba(255,255,255,0.03);
  }

  tbody tr:hover {
    background-color: rgba(75, 83, 185, 0.22);
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  tr.dragging {
    opacity: 0.75;
    background: rgba(198, 115, 255, 0.22);
    box-shadow: 0 0 10px rgba(198,115,255,0.6);
  }

 
  .form-container {
    width: 42%;
    margin: 1.2rem auto;
    background: rgba(12,18,44,0.9);
    padding: 0.7rem 0.9rem;
    border-radius: 10px;
    box-shadow: 0 0 14px rgba(75,83,185,0.55);
    color: #e0e6ff;
    font-size: 0.78rem;
  }

  .form-container input,
  .form-container select {
    width: 100%;
    padding: 5px;
    margin: 5px 0 8px 0;
    border-radius: 5px;
    border: none;
    font-size: 0.78rem;
  }

  .form-container button {
    background: #0e3b8f;
    color: #fff;
    padding: 7px 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.78rem;
  }

  .form-container button:hover {
    background: #1c2143;
  }
</style>



</head>
<body>
  <a href="dashboard.php"><img src="images/NASA-Logo.png" alt="Nasa Logo" class="nasalogo"></a>
  <a href="database/logout.php" id="logoutBtn">Log out</a>

  <div class="circle">
    <div class="planetcenter">
      <h2 class="LoginTitle">Contract Management</h2>

      <!-- Add Contract Form -->
      <div class="form-container">
        <form method="POST">
          <label>Contract Name</label>
          <input type="text" name="contract_name" required>
          <label>Supplier</label>
          <select name="supplier_id" required>
            <?php foreach ($suppliers as $s): ?>
              <option value="<?php echo $s['supplier_id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
            <?php endforeach; ?>
          </select>
          <label>Start Date</label>
          <input type="date" name="start_date" required>
          <label>End Date</label>
          <input type="date" name="end_date" required>
          <label>Status</label>
          <select name="status" required>
            <option value="Active">Active</option>
            <option value="Pending">Pending</option>
            <option value="Expired">Expired</option>
          </select>
          <button type="submit" name="add_contract">Add Contract</button>
        </form>
      </div>

      <!-- Contracts Table -->
      <table id="contractsTable">
        <thead>
          <tr>
            <th>Contract Name</th>
            <th>Supplier</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($contracts)): ?>
            <tr><td colspan="6" style="text-align:center; color:#ccc;">No contracts found</td></tr>
          <?php else: ?>
            <?php foreach ($contracts as $row): ?>
              <tr draggable="true">
                <td><?php echo htmlspecialchars($row['contract_name']); ?></td>
                <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                <td style="color:<?php echo $row['status']=='Active'?'#0f0':($row['status']=='Pending'?'#ff0':'#f00'); ?>">
                  <?php echo htmlspecialchars($row['status']); ?>
                </td>
                <td><a href="contracts.php?delete=<?php echo $row['contract_id']; ?>" style="color:red;">Delete</a></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
  const table = document.getElementById("contractsTable");
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
