<?php
// dashboard.php - Modern Gradient Design
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

$monthly = fetch_all($mysqli, "SELECT ym, net_sales FROM v_monthly_sales");
$category = fetch_all($mysqli, "SELECT category, net_sales FROM v_sales_by_category");
$region = fetch_all($mysqli, "SELECT region, net_sales FROM v_sales_by_region");
$topProducts = fetch_all($mysqli, "SELECT product_name, qty_sold, net_sales FROM v_top_products");
$payment = fetch_all($mysqli, "SELECT payment_method, net_sales FROM v_payment_share");
$hourly = fetch_all($mysqli, "SELECT hour_of_day, net_sales FROM v_hourly_sales");
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
  <title>Retail Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body { 
      background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
      background-size: 400% 400%;
      animation: gradientShift 15s ease infinite;
      min-height: 100vh;
      color: #1a1a2e;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    
    /* Glassmorphism Nav */
    .top-nav {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(30px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 20px;
      margin: 20px;
      padding: 16px 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 20px;
      z-index: 1000;
    }
    
    .top-nav h1 {
      font-size: 1.5rem;
      font-weight: 800;
      background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .logo-icon {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .logout-btn {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 10px 24px;
      border-radius: 12px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .logout-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }
    
    .container-fluid { 
      max-width: 1400px; 
      margin: 0 auto;
      padding: 0 20px 40px 20px;
    }
    
    /* Glassmorphism Cards */
    .card { 
      background: rgba(255, 255, 255, 0.25);
      backdrop-filter: blur(30px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-radius: 24px;
      padding: 28px;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      position: relative;
      overflow: hidden;
    }
    
    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #4facfe);
      background-size: 200% 100%;
      animation: shimmer 3s linear infinite;
      opacity: 0;
      transition: opacity 0.3s;
    }
    
    .card:hover::before {
      opacity: 1;
    }
    
    @keyframes shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }
    
    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 48px rgba(0, 0, 0, 0.15);
      border-color: rgba(255, 255, 255, 0.6);
    }
    
    .card h5 { 
      color: rgba(255, 255, 255, 0.95);
      font-weight: 700;
      font-size: 1.05rem;
      margin-bottom: 20px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    /* KPI Cards with Gradient Icons */
    .kpi-card {
      position: relative;
    }
    
    .kpi-icon {
      position: absolute;
      top: 24px;
      right: 24px;
      width: 56px;
      height: 56px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      opacity: 0.4;
    }
    
    .kpi-icon.sales {
      background: linear-gradient(135deg, #667eea, #764ba2);
    }
    
    .kpi-icon.qty {
      background: linear-gradient(135deg, #f093fb, #f5576c);
    }
    
    .kpi-icon.buyers {
      background: linear-gradient(135deg, #4facfe, #00f2fe);
    }
    
    .kpi-label {
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.85rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 8px;
    }
    
    .kpi { 
      font-size: 2.4rem; 
      font-weight: 800; 
      background: linear-gradient(135deg, #fff 0%, rgba(255, 255, 255, 0.8) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      line-height: 1.2;
      text-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .kpi-subtext {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.88rem;
      margin-top: 8px;
      font-weight: 500;
    }
    
    .grid { 
      display: grid; 
      gap: 24px; 
      grid-template-columns: repeat(12, 1fr); 
    }
    
    .col-12 { grid-column: span 12; }
    .col-6 { grid-column: span 6; }
    .col-4 { grid-column: span 4; }
    .col-8 { grid-column: span 8; }
    
    @media (max-width: 991px) {
      .col-6, .col-4, .col-8 { grid-column: span 12; }
      .top-nav { margin: 12px; padding: 12px 20px; }
      .top-nav h1 { font-size: 1.2rem; }
      .kpi { font-size: 2rem; }
    }
    
    canvas { max-height: 360px; }
    
    /* Fade in animation */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .card {
      animation: fadeInUp 0.6s ease-out forwards;
    }
    
    .card:nth-child(1) { animation-delay: 0.1s; }
    .card:nth-child(2) { animation-delay: 0.2s; }
    .card:nth-child(3) { animation-delay: 0.3s; }
    .card:nth-child(4) { animation-delay: 0.4s; }
    .card:nth-child(5) { animation-delay: 0.5s; }
    .card:nth-child(6) { animation-delay: 0.6s; }
    .card:nth-child(7) { animation-delay: 0.7s; }
  </style>
</head>
<body>
  <!-- Glassmorphism Navigation -->
  <nav class="top-nav">
    <h1>
      <div class="logo-icon">📊</div>
      <span>Retail Dashboard</span>
    </h1>
    <button class="logout-btn" onclick="if(confirm('ต้องการออกจากระบบหรือไม่?')) window.location.href='logout.php'">
      ออกจากระบบ
    </button>
  </nav>

  <div class="container-fluid">
    <!-- KPI Cards with Icons -->
    <div class="grid mb-4" style="margin-top: 24px;">
      <div class="card kpi-card col-4">
        <div class="kpi-icon sales">💰</div>
        <div class="kpi-label">ยอดขาย 30 วัน</div>
        <div class="kpi">฿<?= nf($kpi['sales_30d']) ?></div>
        <div class="kpi-subtext">ยอดขายรวมทั้งหมด</div>
      </div>
      <div class="card kpi-card col-4">
        <div class="kpi-icon qty">📦</div>
        <div class="kpi-label">จำนวนชิ้นขาย 30 วัน</div>
        <div class="kpi"><?= number_format((int)$kpi['qty_30d']) ?></div>
        <div class="kpi-subtext">สินค้าที่ขายไปแล้ว</div>
      </div>
      <div class="card kpi-card col-4">
        <div class="kpi-icon buyers">👥</div>
        <div class="kpi-label">จำนวนผู้ซื้อ 30 วัน</div>
        <div class="kpi"><?= number_format((int)$kpi['buyers_30d']) ?></div>
        <div class="kpi-subtext">ลูกค้าที่ไม่ซ้ำกัน</div>
      </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid">
      <div class="card col-8">
        <h5>📈 ยอดขายรายเดือน</h5>
        <canvas id="chartMonthly"></canvas>
      </div>

      <div class="card col-4">
        <h5>🎯 สัดส่วนตามหมวด</h5>
        <canvas id="chartCategory"></canvas>
      </div>

      <div class="card col-6">
        <h5>🏆 Top 10 สินค้าขายดี</h5>
        <canvas id="chartTopProducts"></canvas>
      </div>

      <div class="card col-6">
        <h5>🗺️ ยอดขายตามภูมิภาค</h5>
        <canvas id="chartRegion"></canvas>
      </div>

      <div class="card col-6">
        <h5>💳 วิธีการชำระเงิน</h5>
        <canvas id="chartPayment"></canvas>
      </div>

      <div class="card col-6">
        <h5>⏰ ยอดขายรายชั่วโมง</h5>
        <canvas id="chartHourly"></canvas>
      </div>

      <div class="card col-12">
        <h5>👤 ลูกค้าใหม่ vs ลูกค้าเดิม</h5>
        <canvas id="chartNewReturning"></canvas>
      </div>
    </div>
  </div>

<script>
const monthly = <?= json_encode($monthly, JSON_UNESCAPED_UNICODE) ?>;
const category = <?= json_encode($category, JSON_UNESCAPED_UNICODE) ?>;
const region = <?= json_encode($region, JSON_UNESCAPED_UNICODE) ?>;
const topProducts = <?= json_encode($topProducts, JSON_UNESCAPED_UNICODE) ?>;
const payment = <?= json_encode($payment, JSON_UNESCAPED_UNICODE) ?>;
const hourly = <?= json_encode($hourly, JSON_UNESCAPED_UNICODE) ?>;
const newReturning = <?= json_encode($newReturning, JSON_UNESCAPED_UNICODE) ?>;

const toXY = (arr, x, y) => ({ labels: arr.map(o => o[x]), values: arr.map(o => parseFloat(o[y])) });

// Gradient colors
const gradients = {
  purple: ['#667eea', '#764ba2'],
  pink: ['#f093fb', '#f5576c'],
  blue: ['#4facfe', '#00f2fe'],
  orange: ['#fa709a', '#fee140'],
  green: ['#30cfd0', '#330867'],
  multi: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe', '#fa709a', '#fee140']
};

Chart.defaults.color = 'rgba(255, 255, 255, 0.9)';
Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
Chart.defaults.font.family = 'Inter';

// Monthly Sales
(() => {
  const {labels, values} = toXY(monthly, 'ym', 'net_sales');
  const ctx = document.getElementById('chartMonthly').getContext('2d');
  const gradient = ctx.createLinearGradient(0, 0, 0, 400);
  gradient.addColorStop(0, 'rgba(102, 126, 234, 0.4)');
  gradient.addColorStop(1, 'rgba(102, 126, 234, 0.05)');
  
  new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: [{ 
      label: 'ยอดขาย (฿)', 
      data: values, 
      borderColor: '#667eea',
      backgroundColor: gradient,
      tension: 0.4, 
      fill: true,
      borderWidth: 3,
      pointBackgroundColor: '#fff',
      pointBorderColor: '#667eea',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6
    }] },
    options: { 
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: 'rgba(255, 255, 255, 0.1)' } },
        y: { grid: { color: 'rgba(255, 255, 255, 0.1)' } }
      }
    }
  });
})();

// Category
(() => {
  const {labels, values} = toXY(category, 'category', 'net_sales');
  new Chart(document.getElementById('chartCategory'), {
    type: 'doughnut',
    data: { labels, datasets: [{ 
      data: values, 
      backgroundColor: gradients.multi,
      borderWidth: 0
    }] },
    options: { 
      plugins: { 
        legend: { 
          position: 'bottom',
          labels: { 
            color: 'rgba(255, 255, 255, 0.9)',
            padding: 15,
            font: { size: 11, weight: 600 }
          }
        }
      },
      cutout: '65%'
    }
  });
})();

// Top Products
(() => {
  const labels = topProducts.map(o => o.product_name);
  const qty = topProducts.map(o => parseInt(o.qty_sold));
  const ctx = document.getElementById('chartTopProducts').getContext('2d');
  const gradient = ctx.createLinearGradient(0, 0, 500, 0);
  gradient.addColorStop(0, '#f093fb');
  gradient.addColorStop(1, '#f5576c');
  
  new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: [{ 
      label: 'ชิ้นที่ขาย', 
      data: qty, 
      backgroundColor: gradient,
      borderRadius: 8
    }] },
    options: {
      indexAxis: 'y',
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: 'rgba(255, 255, 255, 0.1)' } },
        y: { grid: { display: false } }
      }
    }
  });
})();

// Region
(() => {
  const {labels, values} = toXY(region, 'region', 'net_sales');
  new Chart(document.getElementById('chartRegion'), {
    type: 'bar',
    data: { labels, datasets: [{ 
      label: 'ยอดขาย (฿)', 
      data: values, 
      backgroundColor: gradients.multi,
      borderRadius: 8
    }] },
    options: { 
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: { grid: { color: 'rgba(255, 255, 255, 0.1)' } }
      }
    }
  });
})();

// Payment
(() => {
  const {labels, values} = toXY(payment, 'payment_method', 'net_sales');
  new Chart(document.getElementById('chartPayment'), {
    type: 'pie',
    data: { labels, datasets: [{ 
      data: values, 
      backgroundColor: gradients.multi,
      borderWidth: 0
    }] },
    options: { 
      plugins: { 
        legend: { 
          position: 'bottom',
          labels: { 
            color: 'rgba(255, 255, 255, 0.9)',
            padding: 15,
            font: { size: 11, weight: 600 }
          }
        }
      }
    }
  });
})();

// Hourly
(() => {
  const {labels, values} = toXY(hourly, 'hour_of_day', 'net_sales');
  const ctx = document.getElementById('chartHourly').getContext('2d');
  const gradient = ctx.createLinearGradient(0, 0, 0, 400);
  gradient.addColorStop(0, 'rgba(79, 172, 254, 0.8)');
  gradient.addColorStop(1, 'rgba(0, 242, 254, 0.8)');
  
  new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: [{ 
      label: 'ยอดขาย (฿)', 
      data: values, 
      backgroundColor: gradient,
      borderRadius: 8
    }] },
    options: { 
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: { grid: { color: 'rgba(255, 255, 255, 0.1)' } }
      }
    }
  });
})();

// New vs Returning
(() => {
  const labels = newReturning.map(o => o.date_key);
  const newC = newReturning.map(o => parseFloat(o.new_customer_sales));
  const retC = newReturning.map(o => parseFloat(o.returning_sales));
  
  const ctx = document.getElementById('chartNewReturning').getContext('2d');
  const gradient1 = ctx.createLinearGradient(0, 0, 0, 400);
  gradient1.addColorStop(0, 'rgba(240, 147, 251, 0.4)');
  gradient1.addColorStop(1, 'rgba(240, 147, 251, 0.05)');
  
  const gradient2 = ctx.createLinearGradient(0, 0, 0, 400);
  gradient2.addColorStop(0, 'rgba(79, 172, 254, 0.4)');
  gradient2.addColorStop(1, 'rgba(79, 172, 254, 0.05)');
  
  new Chart(ctx, {
    type: 'line',
    data: { labels,
      datasets: [
        { 
          label: 'ลูกค้าใหม่ (฿)', 
          data: newC, 
          borderColor: '#f093fb', 
          backgroundColor: gradient1, 
          tension: 0.4, 
          fill: true, 
          borderWidth: 3,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#f093fb',
          pointBorderWidth: 2,
          pointRadius: 3
        },
        { 
          label: 'ลูกค้าเดิม (฿)', 
          data: retC, 
          borderColor: '#4facfe', 
          backgroundColor: gradient2, 
          tension: 0.4, 
          fill: true, 
          borderWidth: 3,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#4facfe',
          pointBorderWidth: 2,
          pointRadius: 3
        }
      ]
    },
    options: { 
      plugins: { 
        legend: { 
          position: 'top',
          labels: { 
            color: 'rgba(255, 255, 255, 0.9)',
            padding: 15,
            font: { size: 12, weight: 600 }
          }
        }
      },
      scales: {
        x: { 
          grid: { color: 'rgba(255, 255, 255, 0.1)' }, 
          ticks: { maxTicksLimit: 12 } 
        },
        y: { grid: { color: 'rgba(255, 255, 255, 0.1)' } }
      }
    }
  });
})();
</script>
</body>
</html>