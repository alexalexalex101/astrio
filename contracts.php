<?php
  session_start();
  include("database/connection.php");
  include_once("database/action_logger.php");

  /* ------------------------------
    CONTRACT VALUE FORMATTER
  ------------------------------ */
  function formatContractValue($value)
  {
    if ($value >= 1000000000) {
      return '$' . round($value / 1000000000, 2) . 'B';
    } elseif ($value >= 1000000) {
      return '$' . round($value / 1000000, 2) . 'M';
    } elseif ($value >= 1000) {
      return '$' . round($value / 1000, 2) . 'K';
    }
    return '$' . $value;
  }

  /* ------------------------------
    HANDLE ADD CONTRACT
  ------------------------------ */
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contract'])) {
    try {
      $stmt = $conn->prepare("INSERT INTO contracts (supplier_id, contract_name, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)");
      $stmt->execute([
        $_POST['supplier_id'],
        $_POST['contract_name'],
        $_POST['start_date'],
        $_POST['end_date'],
        $_POST['status']
      ]);
      log_action($conn, 'contracts', 'success', [
        'operation' => 'add_contract',
        'contract_name' => $_POST['contract_name'],
        'supplier_id' => (int)$_POST['supplier_id']
      ], 'contracts.php');
    } catch (Throwable $e) {
      log_action($conn, 'contracts', 'error', [
        'operation' => 'add_contract',
        'error' => $e->getMessage()
      ], 'contracts.php');
      throw $e;
    }
  }

  /* ------------------------------
    HANDLE DELETE
  ------------------------------ */
  if (isset($_GET['delete'])) {
    try {
      $stmt = $conn->prepare("DELETE FROM contracts WHERE contract_id = ?");
      $stmt->execute([$_GET['delete']]);
      log_action($conn, 'contracts', 'success', [
        'operation' => 'delete_contract',
        'contract_id' => (int)$_GET['delete']
      ], 'contracts.php');
    } catch (Throwable $e) {
      log_action($conn, 'contracts', 'error', [
        'operation' => 'delete_contract',
        'contract_id' => (int)$_GET['delete'],
        'error' => $e->getMessage()
      ], 'contracts.php');
      throw $e;
    }
  }

  /* ------------------------------
    FETCH DATA
  ------------------------------ */
  $suppliersStmt = $conn->query("SELECT supplier_id, name FROM suppliers");
  $suppliers = $suppliersStmt->fetchAll(PDO::FETCH_ASSOC);

  $stmt = $conn->query("
    SELECT c.contract_id, c.contract_name, c.start_date, c.end_date, c.status,
          c.contract_value, s.name AS supplier_name
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
    /* ------------------------------
   PAGE BACKGROUND
------------------------------ */


    /* ------------------------------
   MAIN PLANET (LESS BRIGHT)
------------------------------ */

    /* ------------------------------
   TABLE CONTAINER
------------------------------ */
    #contractsContainer {
      width: 100%;
      max-height: 380px;
      overflow-y: auto;
      margin: 1rem auto;
      border-radius: 12px;
      background: rgba(12, 18, 44, 0.85);
      box-shadow: 0 0 12px rgba(75, 83, 185, 0.5);
      font-family: "League Spartan", sans-serif;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      color: #e0e6ff;
    }

    th,
    td {
      padding: 12px;
      height: 50px;
      text-align: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    th {
      background: linear-gradient(90deg, #0b2f73, #151d3a);
      text-transform: uppercase;
      letter-spacing: 1px;
      position: sticky;
      top: 0;
    }

    tbody tr:nth-child(even) {
      background-color: rgba(255, 255, 255, 0.04);
    }

    tbody tr:hover {
      background-color: rgba(75, 83, 185, 0.25);
      transition: 0.3s;
    }


    /* ------------------------------
   FORM
------------------------------ */
    .form-container {
      width: 60%;
      margin: 1rem auto;
      background: rgba(12, 18, 44, 0.9);
      padding: 1rem;
      border-radius: 10px;
      box-shadow: 0 0 12px rgba(75, 83, 185, 0.5);
      display: none;
    }

    .form-container input,
    .form-container select {
      width: 100%;
      padding: 6px;
      margin: 6px 0;
      border-radius: 5px;
      border: none;
    }

    .form-container button {
      background: #0b2f73;
      color: white;
      padding: 8px 14px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .form-container button:hover {
      background: #182a5e;
    }
  </style>
</head>

<body>

  <a href="dashboard.php">
    <img src="images/NASA-Logo.png" alt="NASA Logo" class="nasalogo">
  </a>

  <a href="database/logout.php" id="logoutBtn">Log out</a>
  <a href="dashboard.php" id="backbutton">Back</a>

  <div class="main-planet">
    <div class="planet-content">

      <h2 class="page-title">Contract Management</h2>

      <!-- ADD FORM -->
      <div class="form-container" id="formcontainer">
        <form method="POST">
          <label>Contract Name</label>
          <input type="text" name="contract_name" required>

          <label>Supplier</label>
          <select name="supplier_id" required>
            <?php foreach ($suppliers as $s): ?>
              <option value="<?= $s['supplier_id']; ?>">
                <?= htmlspecialchars($s['name']); ?>
              </option>
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

          <button type="submit" name="add_contract" style="font-family: 'League Spartan';font-weight:600;margin-top:1rem">Add Contract</button>
        </form>
      </div>

      <!-- TABLE -->
      <div id="contractsContainer">
        <table id="contractsTable">
          <thead>
            <tr>
              <th>Contract</th>
              <th>Supplier</th>
              <th>Start</th>
              <th>End</th>
              <th>Status</th>
              <th>Value</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            <?php if (empty($contracts)): ?>
              <tr>
                <td colspan="7" style="color:#ccc;">No contracts found</td>
              </tr>
            <?php else: ?>
              <?php foreach ($contracts as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['contract_name']); ?></td>
                  <td><?= htmlspecialchars($row['supplier_name']); ?></td>
                  <td><?= htmlspecialchars($row['start_date']); ?></td>
                  <td><?= htmlspecialchars($row['end_date']); ?></td>
                  <td style="color:<?= $row['status'] == 'Active' ? '#00ff9c' : ($row['status'] == 'Pending' ? '#ffd000' : '#ff5c5c'); ?>">
                    <?= htmlspecialchars($row['status']); ?>
                  </td>
                  <td><?= formatContractValue($row['contract_value']); ?></td>
                  <td>
                    <a href="contracts.php?delete=<?= $row['contract_id']; ?>" style="color:#ff5c5c;">Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <button id="incomingItembtn" class="loginbuttons" style="margin-top:1.5rem;">Add Contract</button>
      <button id="cancelAddContractBtn" class="loginbuttons" style="display:none;">Cancel</button>

    </div>
  </div>

  <script>
    const addBtn = document.getElementById('incomingItembtn');
    const cancelBtn = document.getElementById('cancelAddContractBtn');
    const formContainer = document.getElementById('formcontainer');
    const contractsContainer = document.getElementById('contractsContainer');

    addBtn.addEventListener('click', () => {
      formContainer.style.display = 'block';
      contractsContainer.style.display = 'none';
      addBtn.style.display = 'none';
      cancelBtn.style.display = 'inline-block';
    });

    cancelBtn.addEventListener('click', () => {
      formContainer.style.display = 'none';
      contractsContainer.style.display = 'block';
      addBtn.style.display = 'inline-block';
      cancelBtn.style.display = 'none';
    });
  </script>

</body>

</html>
