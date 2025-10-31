<?php
// dashboard.php (Minimal • Warm Tone)
// Simple Sales Dashboard (Chart.js + Bootstrap) using mysqli (no PDO)

// ----- DB Connection -----
$DB_HOST = 'localhost';
$DB_USER = 's67160167';
$DB_PASS = 'cv91AaKA';
$DB_NAME = 's67160167';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
  http_response_code(500);
  die('Database connection failed: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

function fetch_all($mysqli, $sql) {
  $res = $mysqli->query($sql);
  if (!$res) { return []; }
  $rows = [];
  while ($row = $res->fetch_assoc()) { $rows[] = $row; }
  $res->free();
  return $rows;
}

// ----- Data -----
$monthly      = fetch_all($mysqli, "SELECT ym, net_sales FROM v_monthly_sales");
$category     = fetch_all($mysqli, "SELECT category, net_sales FROM v_sales_by_category");
$region       = fetch_all($mysqli, "SELECT region, net_sales FROM v_sales_by_region");
$topProducts  = fetch_all($mysqli, "SELECT product_name, qty_sold, net_sales FROM v_top_products");
$payment      = fetch_all($mysqli, "SELECT payment_method, net_sales FROM v_payment_share");
$hourly       = fetch_all($mysqli, "SELECT hour_of_day, net_sales FROM v_hourly_sales");
$newReturning = fetch_all($mysqli, "SELECT date_key, new_customer_sales, returning_sales FROM v_new_vs_returning ORDER BY date_key");
$kpis = fetch_all($mysqli, "
  SELECT
    (SELECT SUM(net_amount) FROM fact_sales WHERE date_key >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)) AS sales_30d,
    (SELECT SUM(quantity)   FROM fact_sales WHERE date_key >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)) AS qty_30d,
    (SELECT COUNT(DISTINCT customer_id) FROM fact_sales WHERE date_key >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)) AS buyers_30d
");
$kpi = $kpis ? $kpis[0] : ['sales_30d'=>0,'qty_30d'=>0,'buyers_30d'=>0];

function nf($n) { return number_format((float)$n, 2); }
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Retail DW Dashboard</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <!-- Prompt (Thai) + Inter (Latin) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Prompt:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap + Chart.js -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <style>
    :root{
      /* Warm minimal palette */
      --bg:        #FAF6F1;   /* cream */
      --bg-2:      #F4EDE5;   /* lighter sand */
      --card:      #FFFAF5;   /* warm white */
      --text:      #3A2F2A;   /* deep brown */
      --muted:     #7A6A61;   /* taupe */
      --border:    rgba(58,47,42,0.08);
      --grid:      rgba(58,47,42,0.10);
      --accent:    #E07A5F;   /* terracotta */
      --accent-2:  #D97706;   /* amber */
      --accent-3:  #C08457;   /* clay */
      --accent-4:  #B45309;   /* caramel */
      --accent-5:  #A16207;   /* warm gold */
      --radius:    16px;
      --shadow:    0 10px 30px rgba(58,47,42,0.10);
    }
    *{ box-sizing:border-box; }
    html,body{ height:100%; }
    body{
      background: radial-gradient(1200px 800px at 15% -10%, var(--bg-2), transparent),
                  radial-gradient(1200px 800px at 95% 0%, #FFEEDA, transparent),
                  var(--bg);
      color: var(--text);
      font-family: "Inter","Prompt",system-ui,-apple-system,Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans Thai", sans-serif;
      letter-spacing: .1px;
    }

    .app-header{
      background: linear-gradient(180deg, #FFF5E9, #FFF9F3);
      border: 1px solid var(--border);
      border-radius: calc(var(--radius) + 4px);
      padding: 20px 20px;
      box-shadow: var(--shadow);
    }
    .brand{
      display:flex; align-items:center; gap:.75rem;
      font-weight:700; font-size:1.25rem;
    }
    .brand-dot{
      width:12px; height:12px; border-radius:50%;
      background: linear-gradient(135deg,var(--accent),var(--accent-2));
      box-shadow:0 0 0 4px rgba(224,122,95,0.15);
    }
    .sub{ color: var(--muted); font-size:.95rem; }

    .grid { display:grid; gap: 1rem; grid-template-columns: repeat(12, 1fr); }
    .col-12 { grid-column: span 12; }
    .col-6  { grid-column: span 6; }
    .col-4  { grid-column: span 4; }
    .col-8  { grid-column: span 8; }
    @media (max-width: 991px) { .col-6,.col-4,.col-8{ grid-column: span 12; } }

    .card{
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
    }
    .card h5{ color: var(--text); font-weight:700; }
    .kpi{ font-size:1.6rem; font-weight:700; }
    .kpi-desc{ color: var(--muted); font-size:.95rem; }

    .kpi-chip{
      display:inline-flex; align-items:center; gap:.5rem;
      background: #FFF2E0; color: var(--accent-2);
      padding: .25rem .6rem; border-radius: 999px;
      font-size:.85rem; font-weight:600;
      border: 1px solid rgba(217,119,6,0.2);
    }

    .section-title{
      display:flex; align-items:center; justify-content:space-between; gap:.5rem;
      margin-bottom:.25rem;
    }

    canvas{ max-height: 360px; }

    /* Soft focus outline on focusable elements */
    a, button { outline-color: var(--accent); outline-offset: 3px; }
     .btn-logout {
  background: var(--accent);
  color: #fff;
  padding: 0.45rem 1.1rem;
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.95rem;
  text-decoration: none;
  box-shadow: 0 3px 8px rgba(224,122,95,0.25);
  transition: all 0.2s ease;
}
.btn-logout:hover {
  background: var(--accent-2);
  box-shadow: 0 5px 12px rgba(217,119,6,0.3);
  text-decoration: none;
}
.btn-logout:active {
  transform: scale(0.97);
}
  </style>
</head>
<body class="p-3 p-md-4">
  <div class="container-fluid" style="max-width:1400px;">
    <div class="app-header mb-3 d-flex align-items-center justify-content-between">
      <div class="brand">
        <span class="brand-dot"></span>
        <span>ยอดขาย (Retail DW) — Dashboard</span>
      </div>
      <div class="app-header mb-3 d-flex align-items-center justify-content-between flex-wrap">
  <div class="brand">
    <span class="brand-dot"></span>
    <span>ยอดขาย (Retail DW) — Dashboard</span>
  </div>

  <div class="d-flex align-items-center gap-2">
    <span class="sub me-2">แหล่งข้อมูล: MySQL (mysqli)</span>
    <a href="logout.php" class="btn-logout">ออกจากระบบ</a>
  </div>
</div>

      <span class="sub">แหล่งข้อมูล: MySQL (mysqli)</span>

    </div>

    <!-- KPI -->
    <div class="grid mb-3">
      <div class="card p-3 col-4">
        <div class="section-title">
          <h5 class="mb-0">ยอดขาย 30 วัน</h5>
          <span class="kpi-chip">THB</span>
        </div>
        <div class="kpi">฿<?= nf($kpi['sales_30d']) ?></div>
        <div class="kpi-desc">ยอดขายรวมช่วง 30 วันที่ผ่านมา</div>
      </div>
      <div class="card p-3 col-4">
        <div class="section-title">
          <h5 class="mb-0">จำนวนชิ้นขาย 30 วัน</h5>
        </div>
        <div class="kpi"><?= number_format((int)$kpi['qty_30d']) ?> ชิ้น</div>
        <div class="kpi-desc">จำนวนรายการสินค้าที่ถูกขาย</div>
      </div>
      <div class="card p-3 col-4">
        <div class="section-title">
          <h5 class="mb-0">จำนวนผู้ซื้อ 30 วัน</h5>
        </div>
        <div class="kpi"><?= number_format((int)$kpi['buyers_30d']) ?> คน</div>
        <div class="kpi-desc">จำนวนผู้ซื้อที่ไม่ซ้ำกัน</div>
      </div>
    </div>

    <!-- Charts grid -->
    <div class="grid">
      <div class="card p-3 col-8">
        <h5 class="mb-2">ยอดขายรายเดือน (2 ปี)</h5>
        <canvas id="chartMonthly"></canvas>
      </div>

      <div class="card p-3 col-4">
        <h5 class="mb-2">สัดส่วนยอดขายตามหมวด</h5>
        <canvas id="chartCategory"></canvas>
      </div>

      <div class="card p-3 col-6">
        <h5 class="mb-2">Top 10 สินค้าขายดี</h5>
        <canvas id="chartTopProducts"></canvas>
      </div>

      <div class="card p-3 col-6">
        <h5 class="mb-2">ยอดขายตามภูมิภาค</h5>
        <canvas id="chartRegion"></canvas>
      </div>

      <div class="card p-3 col-6">
        <h5 class="mb-2">วิธีการชำระเงิน</h5>
        <canvas id="chartPayment"></canvas>
      </div>

      <div class="card p-3 col-6">
        <h5 class="mb-2">ยอดขายรายชั่วโมง</h5>
        <canvas id="chartHourly"></canvas>
      </div>

      <div class="card p-3 col-12">
        <h5 class="mb-2">ลูกค้าใหม่ vs ลูกค้าเดิม (รายวัน)</h5>
        <canvas id="chartNewReturning"></canvas>
      </div>
    </div>
  </div>

<script>
/* ---------- Data from PHP ---------- */
const monthly      = <?= json_encode($monthly, JSON_UNESCAPED_UNICODE) ?>;
const category     = <?= json_encode($category, JSON_UNESCAPED_UNICODE) ?>;
const region       = <?= json_encode($region, JSON_UNESCAPED_UNICODE) ?>;
const topProducts  = <?= json_encode($topProducts, JSON_UNESCAPED_UNICODE) ?>;
const payment      = <?= json_encode($payment, JSON_UNESCAPED_UNICODE) ?>;
const hourly       = <?= json_encode($hourly, JSON_UNESCAPED_UNICODE) ?>;
const newReturning = <?= json_encode($newReturning, JSON_UNESCAPED_UNICODE) ?>;

/* ---------- Helpers ---------- */
const toXY = (arr, x, y) => ({ labels: arr.map(o => o[x]), values: arr.map(o => parseFloat(o[y] ?? 0)) });

const fmtTHB = v => {
  if (v === null || v === undefined || isNaN(v)) return '฿0';
  return '฿' + Number(v).toLocaleString('th-TH', { maximumFractionDigits: 2 });
};

// Warm palette
const warm = {
  line:     '#E07A5F',
  line2:    '#D97706',
  bar:      '#C08457',
  bar2:     '#B45309',
  bar3:     '#A16207',
  doughnut: ['#E07A5F','#D97706','#C08457','#B45309','#A16207','#F1A27A','#F2C77B'],
  pie:      ['#D97706','#E07A5F','#C08457','#B45309','#A16207','#F2C77B','#ECC68D'],
  grid:     getComputedStyle(document.documentElement).getPropertyValue('--grid').trim(),
  text:     getComputedStyle(document.documentElement).getPropertyValue('--text').trim(),
  muted:    getComputedStyle(document.documentElement).getPropertyValue('--muted').trim()
};

// Global defaults
Chart.defaults.font.family = '"Inter","Prompt",system-ui,-apple-system,Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans Thai", sans-serif';
Chart.defaults.color = warm.text;

// Common axis style
const axis = {
  x: { ticks: { color: warm.muted }, grid: { color: warm.grid } },
  y: { ticks: { color: warm.muted, callback: (v)=>Number(v).toLocaleString('th-TH') }, grid: { color: warm.grid } }
};

// Tooltip formatter
const tooltip = {
  callbacks: {
    label: ctx => {
      const dsLabel = ctx.dataset.label ? ctx.dataset.label + ': ' : '';
      const val = ctx.parsed.y ?? ctx.parsed;
      return dsLabel + fmtTHB(val);
    }
  }
};

// Empty-state guard
const safe = (arr)=> Array.isArray(arr) && arr.length>0;

/* ---------- Monthly (Line / Area) ---------- */
(() => {
  const {labels, values} = toXY(monthly, 'ym', 'net_sales');
  new Chart(document.getElementById('chartMonthly'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'ยอดขาย (฿)',
        data: values,
        borderColor: warm.line,
        backgroundColor: (ctx) => {
          const {chart} = ctx;
          const {ctx: c} = chart;
          const g = c.createLinearGradient(0, 0, 0, chart.height);
          g.addColorStop(0, 'rgba(224,122,95,0.30)');
          g.addColorStop(1, 'rgba(224,122,95,0.02)');
          return g;
        },
        tension: .25,
        fill: true,
        pointRadius: 2,
        pointHoverRadius: 4
      }]
    },
    options: {
      maintainAspectRatio: false,
      plugins: { legend: { labels: { color: warm.text } }, tooltip },
      scales: axis
    }
  });
})();

/* ---------- Category (Doughnut) ---------- */
(() => {
  const {labels, values} = toXY(category, 'category', 'net_sales');
  new Chart(document.getElementById('chartCategory'), {
    type: 'doughnut',
    data: { labels, datasets: [{ data: values, backgroundColor: warm.doughnut, borderWidth: 0 }] },
    options: {
      cutout: '60%',
      plugins: {
        legend: { position: 'bottom', labels: { color: warm.muted } },
        tooltip: { callbacks: { label: (ctx)=>`${ctx.label}: ${fmtTHB(ctx.parsed)}` } }
      }
    }
  });
})();

/* ---------- Top Products (Horizontal Bar) ---------- */
(() => {
  const labels = topProducts.map(o => o.product_name);
  const qty = topProducts.map(o => parseInt(o.qty_sold ?? 0));
  new Chart(document.getElementById('chartTopProducts'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'ชิ้นที่ขาย',
        data: qty,
        borderRadius: 10,
        backgroundColor: warm.bar
      }]
    },
    options: {
      indexAxis: 'y',
      maintainAspectRatio:false,
      plugins: { legend: { labels: { color: warm.text } },
        tooltip: { callbacks: { label: (ctx)=> `${ctx.dataset.label}: ${Number(ctx.parsed.x).toLocaleString('th-TH')} ชิ้น` } }
      },
      scales: {
        x: { ticks: { color: warm.muted }, grid: { color: warm.grid } },
        y: { ticks: { color: warm.muted }, grid: { color: 'transparent' } }
      }
    }
  });
})();

/* ---------- Region (Bar) ---------- */
(() => {
  const {labels, values} = toXY(region, 'region', 'net_sales');
  new Chart(document.getElementById('chartRegion'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'ยอดขาย (฿)',
        data: values,
        backgroundColor: warm.bar2,
        borderRadius: 10
      }]
    },
    options: {
      maintainAspectRatio:false,
      plugins: { legend: { labels: { color: warm.text } }, tooltip },
      scales: axis
    }
  });
})();

/* ---------- Payment (Pie) ---------- */
(() => {
  const {labels, values} = toXY(payment, 'payment_method', 'net_sales');
  new Chart(document.getElementById('chartPayment'), {
    type: 'pie',
    data: { labels, datasets: [{ data: values, backgroundColor: warm.pie, borderWidth: 0 }] },
    options: {
      plugins: {
        legend: { position: 'bottom', labels: { color: warm.muted } },
        tooltip: { callbacks: { label: (ctx)=> `${ctx.label}: ${fmtTHB(ctx.parsed)}` } }
      }
    }
  });
})();

/* ---------- Hourly (Bar) ---------- */
(() => {
  const {labels, values} = toXY(hourly, 'hour_of_day', 'net_sales');
  new Chart(document.getElementById('chartHourly'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'ยอดขาย (฿)',
        data: values,
        backgroundColor: warm.bar3,
        borderRadius: 8
      }]
    },
    options: {
      plugins: { legend: { labels: { color: warm.text } }, tooltip },
      scales: axis
    }
  });
})();

/* ---------- New vs Returning (Dual Line) ---------- */
(() => {
  const labels = newReturning.map(o => o.date_key);
  const newC = newReturning.map(o => parseFloat(o.new_customer_sales ?? 0));
  const retC = newReturning.map(o => parseFloat(o.returning_sales ?? 0));
  new Chart(document.getElementById('chartNewReturning'), {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label: 'ลูกค้าใหม่ (฿)', data: newC, borderColor: warm.line,     tension: .25, fill: false, pointRadius: 0, pointHoverRadius: 3 },
        { label: 'ลูกค้าเดิม (฿)', data: retC, borderColor: warm.line2,    tension: .25, fill: false, pointRadius: 0, pointHoverRadius: 3 }
      ]
    },
    options: {
      plugins: { legend: { labels: { color: warm.text } }, tooltip },
      scales: {
        x: { ticks: { color: warm.muted, maxTicksLimit: 12 }, grid: { color: warm.grid } },
        y: { ticks: { color: warm.muted, callback: (v)=>Number(v).toLocaleString('th-TH') }, grid: { color: warm.grid } }
      }
    }
  });
})();
</script>

</body>
</html>
