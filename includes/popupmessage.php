<?php
// Students with balances
$sql = "SELECT COUNT(DISTINCT studentadmno) 
        FROM feebalances
        WHERE yearlybal > 0
        AND LEFT(feebalancecode, 4) = YEAR(CURDATE())";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$studentsWithBalance = $stmt->fetchColumn();

// Students without balances
$sql = "SELECT COUNT(DISTINCT studentadmno) 
        FROM feebalances
        WHERE yearlybal = 0
        AND LEFT(feebalancecode, 4) = YEAR(CURDATE())";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$studentsWithoutBalance = $stmt->fetchColumn();

// Total collected
$sql = "SELECT SUM(totalpaid) 
        FROM feebalances 
        WHERE LEFT(feebalancecode, 4) = YEAR(CURDATE())";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$totalCollected = $stmt->fetchColumn();

// Outstanding balance amount
$sql = "SELECT SUM(yearlybal) 
        FROM feebalances 
        WHERE yearlybal > 0
        AND LEFT(feebalancecode, 4) = YEAR(CURDATE())";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$outstandingAmount = $stmt->fetchColumn();

// Percentages
$totalStudents = $studentsWithBalance + $studentsWithoutBalance;
$clearedPercent = $totalStudents > 0 ? round(($studentsWithoutBalance / $totalStudents) * 100, 1) : 0;
$owingPercent   = $totalStudents > 0 ? round(($studentsWithBalance / $totalStudents) * 100, 1) : 0;

// Fee collection percentages
$totalFees = $totalCollected + $outstandingAmount;
$paidPercent = $totalFees > 0 ? round(($totalCollected / $totalFees) * 100, 1) : 0;
$outstandingPercent = $totalFees > 0 ? round(($outstandingAmount / $totalFees) * 100, 1) : 0;

// Top grades
$sql = "SELECT gradefullname, SUM(yearlybal) as totalbal
        FROM feebalances
        WHERE LEFT(feebalancecode, 4) = YEAR(CURDATE())
        GROUP BY gradefullname
        ORDER BY totalbal DESC
        LIMIT 3";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$topGrades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Accountant tips
$accountantTips = [
    "💡 Reconcile fee collections weekly to avoid discrepancies.",
    "📊 Track outstanding balances monthly to spot payment gaps early.",
    "🔄 Verify arrears carried forward before generating new invoices.",
    "🧾 Maintain soft and hard copies of all receipts for audit purposes.",
    "💰 Remind parents/guardians of due dates a week before deadline.",
    "📉 Compare termly collections vs projections to monitor performance.",
    "📂 Archive cleared student records yearly to reduce clutter.",
    "✅ Double-check banking slips against system records daily.",
    "📅 Schedule fee reminders automatically in the system.",
    "🔒 Restrict financial module access to authorized staff only."
];
$randomTip = $accountantTips[array_rand($accountantTips)];
?>

<!-- Mascot Popup -->
<div id="mascotPopup" style="
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 360px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    font-family: Arial, sans-serif;
    display: none;
    z-index: 9999;
    animation: slideUp 0.8s ease-out;
">
    <!-- Draggable Header -->
    <div id="mascotHeader" style="cursor: move; display:flex; align-items:center; justify-content:space-between; background:#1976d2; color:#fff; padding:10px 15px; border-radius:16px 16px 0 0;">
        <div style="display:flex; align-items:center;">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135673.png" 
                 alt="Mascot" width="35" style="margin-right:10px;">
            <h3 style="margin:0; font-size:15px; font-weight:700;">
                📊 Accountant Dashboard
            </h3>
        </div>
        <button onclick="closePopup()" style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer;">&times;</button>
    </div>

    <div style="padding:15px;">
        <!-- Fee Collection Chart -->
        <div style="margin-bottom:15px;">
            <div style="font-weight:600; margin-bottom:8px; color:#1976d2;">💰 Fee Collection Status</div>
            <div style="display:flex; height:20px; background:#f0f0f0; border-radius:10px; overflow:hidden; margin-bottom:5px;">
                <div style="width:<?php echo $paidPercent; ?>%; background:#4caf50; display:flex; align-items:center; justify-content:center; color:white; font-size:11px; font-weight:bold;">
                    <?php if ($paidPercent >= 15) echo $paidPercent . '%'; ?>
                </div>
                <div style="width:<?php echo $outstandingPercent; ?>%; background:#f44336; display:flex; align-items:center; justify-content:center; color:white; font-size:11px; font-weight:bold;">
                    <?php if ($outstandingPercent >= 15) echo $outstandingPercent . '%'; ?>
                </div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:12px;">
                <span style="color:#4caf50;">Paid: KSh <?php echo number_format($totalCollected); ?></span>
                <span style="color:#f44336;">Due: KSh <?php echo number_format($outstandingAmount); ?></span>
            </div>
        </div>
     
        <!-- Balances -->
        <div style="color:#d32f2f; font-weight:600; margin-bottom:5px;">
            ⚠️ <?php echo $studentsWithBalance; ?> students still have an outstanding balance this year.
        </div>
        <div style="color:#388e3c; font-weight:600; margin-bottom:10px;">
            ✅ <?php echo $studentsWithoutBalance; ?> students are fully cleared.
        </div>

        <!-- Student Status Chart -->
        <div style="margin-bottom:15px;">
            <div style="font-weight:600; margin-bottom:8px; color:#1976d2;">👥 Student Payment Status</div>
            <div style="display:flex; height:20px; background:#f0f0f0; border-radius:10px; overflow:hidden; margin-bottom:5px;">
                <div style="width:<?php echo $owingPercent; ?>%; background:#f44336; display:flex; align-items:center; justify-content:center; color:white; font-size:11px; font-weight:bold;">
                    <?php if ($owingPercent >= 15) echo $owingPercent . '%'; ?>
                </div>
                <div style="width:<?php echo $clearedPercent; ?>%; background:#4caf50; display:flex; align-items:center; justify-content:center; color:white; font-size:11px; font-weight:bold;">
                    <?php if ($clearedPercent >= 15) echo $clearedPercent . '%'; ?>
                </div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:12px;">
                <span style="color:#f44336;">Owing: <?php echo $studentsWithBalance; ?> students</span>
                <span style="color:#4caf50;">Cleared: <?php echo $studentsWithoutBalance; ?> students</span>
            </div>
        </div>

        <!-- Top grades -->
        <div style="margin-top:15px; font-size:13px; color:#555;">
            🏫 Grades with highest outstanding balances:<br>
            <?php foreach($topGrades as $g){ ?>
                - <?php echo $g['gradefullname']; ?>: KSh <?php echo number_format($g['totalbal']); ?><br>
            <?php } ?>
        </div>

        <!-- Random Tip -->
        <div style="margin-top:15px; padding:10px; background:#f1f8ff; border-left:4px solid #1976d2; border-radius:8px; font-size:13px; color:#333;">
            <?php echo $randomTip; ?>
        </div>
    </div>
</div>

<!-- Animations + Drag + Close -->
<style>
@keyframes slideUp {
    from { transform: translateY(100px); opacity:0; }
    to { transform: translateY(0); opacity:1; }
}
@keyframes fadeOut {
    to { opacity:0; transform: translateY(50px); }
}
</style>
<script>
window.onload = function() {
    const popup = document.getElementById("mascotPopup");
    popup.style.display = "block";

    setTimeout(() => {
        closePopup();
    }, 60000); // Auto-hide after 1 minute
};

// Close button
function closePopup() {
    const popup = document.getElementById("mascotPopup");
    popup.style.animation = "fadeOut 0.8s forwards";
    setTimeout(() => popup.style.display = "none", 800);
}

// Make popup draggable
dragElement(document.getElementById("mascotPopup"), document.getElementById("mascotHeader"));

function dragElement(elmnt, header) {
    let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    header.onmousedown = dragMouseDown;

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
        elmnt.style.bottom = "auto";
        elmnt.style.right = "auto";
    }

    function closeDragElement() {
        document.onmouseup = null;
        document.onmousemove = null;
    }
}
</script>