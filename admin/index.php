<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>FoodieGo Admin - Order Management</title>
  <link rel="stylesheet" href="../assets/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
  <header>
    <div class="logo">🍔 FoodieGo Admin</div>
    <nav>
      <a href="../">Customer Site</a>
    </nav>
  </header>
  <main>
    <h2>Order Management</h2>
    <div id="orders" class="cart-box">Loading orders...</div>
  </main>

  <script>
    async function load() {
      try {
        let r = await fetch('../api/orders.php');
        let data = await r.json();
        const container = document.getElementById('orders');
        if (!data.length) {
          container.innerHTML = '<p class="empty">No orders placed yet.</p>';
          return;
        }
        container.innerHTML = data.map(o => {
          const pm = o.payment_method || 'COD';
          const ps = o.payment_status || 'Pending';
          const pmClass = pm === 'UPI' ? 'badge-upi' : (pm === 'Card' ? 'badge-card' : 'badge-cod');
          const psClass = ps === 'Paid' ? 'badge-paid' : 'badge-pending';
          
          return `
            <div class="cart-row" style="align-items: center; padding: 18px 0;">
              <div>
                <b style="font-size: 16px;">Order #${o.id} — ${o.customer_name}</b>
                <span class="badge-pay ${pmClass}">${pm}</span>
                <span class="badge-pay ${psClass}">${ps}</span>
                <div class="muted" style="margin-top: 4px;">📞 ${o.phone} • 📍 ${o.address}</div>
                <div class="muted" style="font-size: 12px; margin-top: 2px;">🕒 ${o.created_at}</div>
              </div>
              <div style="text-align: right; min-width: 140px;">
                <b style="font-size: 18px; color: #e84b22;">₹${Number(o.total).toFixed(2)}</b>
                <div style="margin-top: 6px;">
                  <select onchange="update(${o.id}, this.value)" style="padding: 6px 10px; font-weight: 600;">
                    ${['Pending','Confirmed','Preparing','Out for Delivery','Delivered','Cancelled'].map(s => `<option ${s === o.status ? 'selected' : ''}>${s}</option>`).join('')}
                  </select>
                </div>
              </div>
            </div>
          `;
        }).join('');
      } catch(e) {
        console.error(e);
      }
    }

    async function update(id, status) {
      await fetch('../api/update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status })
      });
      load();
    }

    load();
    setInterval(load, 15000);
  </script>
</body>
</html>
