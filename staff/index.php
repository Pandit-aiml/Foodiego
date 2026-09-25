<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FoodieGo Staff & Kitchen Portal</title>
  <link rel="stylesheet" href="../assets/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    .staff-container {
      max-width: 1200px;
      margin: 30px auto;
      padding: 0 20px;
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .stat-card {
      background: #ffffff;
      padding: 22px;
      border-radius: 16px;
      border: 1px solid #f0f0f0;
      box-shadow: 0 4px 15px rgba(0,0,0,0.04);
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .stat-icon {
      font-size: 32px;
      width: 54px;
      height: 54px;
      border-radius: 14px;
      background: #fff5ee;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .stat-val {
      font-size: 26px;
      font-weight: 800;
      color: #222;
    }
    .stat-lbl {
      font-size: 13px;
      color: #777;
      font-weight: 600;
    }
    .staff-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 24px;
      border-bottom: 2px solid #eee;
      padding-bottom: 10px;
      flex-wrap: wrap;
    }
    .staff-tab-btn {
      background: #f5f5f5;
      border: 0;
      padding: 10px 18px;
      border-radius: 10px;
      font-weight: 700;
      font-size: 14px;
      color: #555;
      cursor: pointer;
      transition: all 0.2s;
    }
    .staff-tab-btn.active {
      background: #e84b22;
      color: #ffffff;
    }
    .order-card-staff {
      background: #ffffff;
      border-radius: 16px;
      border: 1px solid #eee;
      padding: 20px;
      margin-bottom: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 12px;
    }
    .order-items-list {
      background: #fafafa;
      padding: 12px 16px;
      border-radius: 10px;
      margin: 12px 0;
      font-size: 14px;
    }
    .order-actions {
      display: flex;
      gap: 10px;
      align-items: center;
      justify-content: space-between;
      margin-top: 14px;
      padding-top: 12px;
      border-top: 1px solid #f0f0f0;
    }
    .quick-btn {
      padding: 6px 12px;
      font-size: 13px;
      font-weight: 700;
      border-radius: 8px;
      border: 0;
      cursor: pointer;
      background: #eee;
      color: #333;
    }
    .quick-btn-prep { background: #fff3cd; color: #856404; }
    .quick-btn-deliv { background: #cce5ff; color: #004085; }
    .quick-btn-done { background: #d4edda; color: #155724; }
    
    /* Menu management */
    .menu-manage-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
    }
    .menu-item-card {
      background: #fff;
      border-radius: 14px;
      border: 1px solid #eee;
      padding: 16px;
      display: flex;
      gap: 14px;
    }
    .menu-item-card img {
      width: 70px;
      height: 70px;
      border-radius: 10px;
      object-fit: cover;
    }
    .switch-stock {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      font-weight: 700;
      margin-top: 8px;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">👨‍🍳 FoodieGo Staff & Kitchen</div>
    <nav>
      <a href="../" target="_blank">View Customer Site ↗</a>
      <div id="staffAuthNav"></div>
    </nav>
  </header>

  <div class="staff-container">
    <!-- Live Stats Overview -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div>
          <div class="stat-val" id="statRevenue">₹0.00</div>
          <div class="stat-lbl">Total Revenue</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">🔥</div>
        <div>
          <div class="stat-val" id="statActive">0</div>
          <div class="stat-lbl">Active Kitchen Orders</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div>
          <div class="stat-val" id="statOrders">0</div>
          <div class="stat-lbl">Total Orders Placed</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div>
          <div class="stat-val" id="statCustomers">0</div>
          <div class="stat-lbl">Registered Customers</div>
        </div>
      </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="staff-tabs">
      <button class="staff-tab-btn active" id="tabOrders">📦 Orders Management</button>
      <button class="staff-tab-btn" id="tabMenu">🍲 Menu & Stock Manager</button>
    </div>

    <!-- Section 1: Orders Management -->
    <div id="secOrders">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="margin: 0;">Live Orders Queue</h3>
        <select id="statusFilter" style="width: auto; padding: 8px 14px; font-weight: 700;">
          <option value="ALL">All Order Statuses</option>
          <option value="ACTIVE" selected>🔥 Active Orders Only</option>
          <option value="Pending">Pending</option>
          <option value="Preparing">Preparing</option>
          <option value="Out for Delivery">Out for Delivery</option>
          <option value="Delivered">Delivered</option>
          <option value="Cancelled">Cancelled</option>
        </select>
      </div>

      <div id="ordersList">Loading live orders...</div>
    </div>

    <!-- Section 2: Menu & Stock Manager -->
    <div id="secMenu" class="hidden">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0;">Restaurant Menu Items</h3>
        <button class="btn" id="openAddDishModal">➕ Add New Dish</button>
      </div>

      <div id="menuItemsGrid" class="menu-manage-grid">Loading menu...</div>
    </div>
  </div>

  <!-- Add Dish Modal -->
  <div id="addDishModal" class="modal-overlay hidden">
    <div class="modal-card">
      <button class="close-modal" id="closeAddDishModal">&times;</button>
      <h3>Add New Menu Item</h3>
      <form id="addDishForm" class="modal-form">
        <div class="input-group">
          <label>Restaurant</label>
          <select id="dishRestaurant" required>
            <option value="1">Spice Hub (North Indian)</option>
            <option value="2">Pizza Corner (Italian)</option>
            <option value="3">Burger House (American)</option>
            <option value="4">Wok Express (Chinese)</option>
          </select>
        </div>
        <div class="input-group">
          <label>Dish Name</label>
          <input type="text" id="dishName" placeholder="e.g. Special Paneer Tikka" required>
        </div>
        <div class="input-group">
          <label>Category</label>
          <input type="text" id="dishCategory" placeholder="e.g. Starters / Main Course / Pizza" required>
        </div>
        <div class="input-group">
          <label>Price (₹)</label>
          <input type="number" step="0.01" id="dishPrice" placeholder="199.00" required>
        </div>
        <div class="input-group">
          <label>Description</label>
          <textarea id="dishDescription" placeholder="Brief description of ingredients or taste"></textarea>
        </div>
        <div class="input-group">
          <label>Dish Photo <span style="color: #e84b22; font-weight: bold;">*(Compulsory for Staff)</span></label>
          <div style="display: flex; gap: 8px; align-items: center;">
            <input type="text" id="dishImage" placeholder="Upload file or enter URL" style="flex: 1;" required>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('staffDishImageFile').click()" style="white-space: nowrap; padding: 8px 12px; font-size: 13px;">📁 Upload File</button>
            <input type="file" id="staffDishImageFile" accept="image/*" class="hidden" onchange="handleStaffDishUpload(this)">
          </div>
          <img id="staffDishPreview" class="hidden" style="width: 100px; height: 75px; object-fit: cover; border-radius: 8px; margin-top: 8px; border: 2px solid #e84b22;">
        </div>
        <button type="submit" class="btn full">Save New Dish</button>
      </form>
    </div>
  </div>

  <!-- Staff Login Modal -->
  <div id="staffLoginModal" class="modal-overlay hidden">
    <div class="modal-card">
      <h3>Staff & Kitchen Sign In</h3>
      <p class="muted">Enter staff credentials to manage orders & menu availability.</p>
      <form id="staffLoginForm" class="modal-form">
        <div class="input-group">
          <label>Staff Email</label>
          <input type="email" id="staffEmail" value="staff@foodiego.com" required>
        </div>
        <div class="input-group">
          <label>Staff Password</label>
          <input type="password" id="staffPassword" value="staff123" required>
        </div>
        <p id="staffLoginErr" class="error-msg"></p>
        <button type="submit" class="btn full">Access Staff Portal</button>
      </form>
    </div>
  </div>

  <script>
    let staffUser = JSON.parse(localStorage.getItem('foodiego_staff')) || null;
    let allOrders = [];

    function initStaff() {
      if (!staffUser || staffUser.role !== 'staff') {
        document.getElementById('staffLoginModal').classList.remove('hidden');
      } else {
        document.getElementById('staffLoginModal').classList.add('hidden');
        renderStaffNav();
        loadStats();
        loadOrders();
        loadMenu();
      }
    }

    function renderStaffNav() {
      const avatarUrl = staffUser.avatar || 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?auto=format&fit=crop&w=100&q=80';
      document.getElementById('staffAuthNav').innerHTML = `
        <div class="user-badge" style="display: flex; align-items: center; gap: 8px;">
          <img src="${avatarUrl}" alt="Staff Avatar" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 2px solid #e84b22;">
          <span style="font-weight: 700; color: #333;">👨‍🍳 ${staffUser.name}</span>
          <button class="btn btn-outline btn-sm" onclick="staffLogout()">Sign Out</button>
        </div>
      `;
    }

    function staffLogout() {
      localStorage.removeItem('foodiego_staff');
      location.reload();
    }

    // Staff Login
    document.getElementById('staffLoginForm').addEventListener('submit', async e => {
      e.preventDefault();
      const email = document.getElementById('staffEmail').value;
      const password = document.getElementById('staffPassword').value;
      const err = document.getElementById('staffLoginErr');

      try {
        const res = await fetch('../api/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password })
        });
        const data = await res.json();
        if (res.ok && data.success && (data.user.role === 'staff' || data.user.role === 'admin')) {
          staffUser = data.user;
          localStorage.setItem('foodiego_staff', JSON.stringify(staffUser));
          initStaff();
        } else {
          err.textContent = 'Invalid staff credentials or insufficient role.';
        }
      } catch (e) {
        err.textContent = 'Server connection error.';
      }
    });

    // Stats
    async function loadStats() {
      try {
        const res = await fetch('../api/staff_stats.php');
        const d = await res.json();
        document.getElementById('statRevenue').textContent = '₹' + Number(d.revenue).toFixed(2);
        document.getElementById('statOrders').textContent = d.total_orders;
        document.getElementById('statActive').textContent = d.active_orders;
        document.getElementById('statCustomers').textContent = d.total_customers;
      } catch (e) {}
    }

    // Load Orders
    async function loadOrders() {
      try {
        const res = await fetch('../api/orders.php');
        allOrders = await res.json();
        renderOrders();
      } catch (e) {}
    }

    function renderOrders() {
      const filter = document.getElementById('statusFilter').value;
      let list = allOrders;

      if (filter === 'ACTIVE') {
        list = allOrders.filter(o => ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery'].includes(o.status));
      } else if (filter !== 'ALL') {
        list = allOrders.filter(o => o.status === filter);
      }

      const container = document.getElementById('ordersList');
      if (!list.length) {
        container.innerHTML = '<p class="empty">No orders matching filter.</p>';
        return;
      }

      container.innerHTML = list.map(o => {
        const pm = o.payment_method || 'COD';
        const ps = o.payment_status || 'Pending';
        const pmClass = pm === 'UPI' ? 'badge-upi' : (pm === 'Card' ? 'badge-card' : 'badge-cod');
        const psClass = ps === 'Paid' ? 'badge-paid' : 'badge-pending';

        const itemsHtml = o.items ? o.items.map(i => `<div>• <b>${i.name}</b> × ${i.quantity} (₹${i.price})</div>`).join('') : '';

        return `
          <div class="order-card-staff">
            <div class="order-header">
              <div>
                <b style="font-size: 18px;">Order #${o.id} — ${o.customer_name}</b>
                <span class="badge-pay ${pmClass}">${pm}</span>
                <span class="badge-pay ${psClass}">${ps}</span>
                <div class="muted" style="margin-top: 4px;">📞 ${o.phone} • 📍 ${o.address}</div>
                <div class="muted" style="font-size: 12px; margin-top: 2px;">🕒 Placed at: ${o.created_at}</div>
              </div>
              <div style="text-align: right;">
                <b style="font-size: 22px; color: #e84b22;">₹${Number(o.total).toFixed(2)}</b>
              </div>
            </div>

            <div class="order-items-list">
              <strong>Ordered Items:</strong>
              ${itemsHtml}
            </div>

            <div class="order-actions">
              <div>
                <button class="quick-btn quick-btn-prep" onclick="updateOrderStatus(${o.id}, 'Preparing')">👨‍🍳 Start Preparing</button>
                <button class="quick-btn quick-btn-deliv" onclick="updateOrderStatus(${o.id}, 'Out for Delivery')">🚴 Out for Delivery</button>
                <button class="quick-btn quick-btn-done" onclick="updateOrderStatus(${o.id}, 'Delivered')">✅ Mark Delivered</button>
              </div>

              <div>
                <label style="font-size: 12px; font-weight: 700;">Status:</label>
                <select onchange="updateOrderStatus(${o.id}, this.value)" style="width: auto; padding: 6px 10px;">
                  ${['Pending','Confirmed','Preparing','Out for Delivery','Delivered','Cancelled'].map(s => `<option ${s === o.status ? 'selected' : ''}>${s}</option>`).join('')}
                </select>
              </div>
            </div>
          </div>
        `;
      }).join('');
    }

    async function updateOrderStatus(id, status) {
      await fetch('../api/update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status })
      });
      loadOrders();
      loadStats();
    }

    document.getElementById('statusFilter').addEventListener('change', renderOrders);

    // Section Switcher
    document.getElementById('tabOrders').addEventListener('click', () => {
      document.getElementById('tabOrders').classList.add('active');
      document.getElementById('tabMenu').classList.remove('active');
      document.getElementById('secOrders').classList.remove('hidden');
      document.getElementById('secMenu').classList.add('hidden');
    });

    document.getElementById('tabMenu').addEventListener('click', () => {
      document.getElementById('tabMenu').classList.add('active');
      document.getElementById('tabOrders').classList.remove('active');
      document.getElementById('secMenu').classList.remove('hidden');
      document.getElementById('secOrders').classList.add('hidden');
    });

    // Load Menu for Staff Management
    async function loadMenu() {
      const res = await fetch('../api/menu.php?restaurant_id=0');
      const items = await res.json();
      const grid = document.getElementById('menuItemsGrid');

      grid.innerHTML = items.map(item => `
        <div class="menu-item-card">
          <img src="${item.image}" alt="${item.name}">
          <div style="flex: 1;">
            <b>${item.name}</b>
            <div class="muted">${item.restaurant_name || 'Restaurant'}</div>
            <div style="font-weight: 700; color: #e84b22; margin-top: 4px;">₹${Number(item.price).toFixed(2)}</div>
            <div class="switch-stock">
              <label>
                <input type="checkbox" ${item.is_available ? 'checked' : ''} onchange="toggleStock(${item.id}, this.checked)"> In Stock
              </label>
              <button style="border: 0; background: transparent; color: #d93025; cursor: pointer; margin-left: auto;" onclick="deleteDish(${item.id})">🗑️ Delete</button>
            </div>
          </div>
        </div>
      `).join('');
    }

    async function toggleStock(id, isAvailable) {
      await fetch('../api/menu_manage.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle_stock', id, is_available: isAvailable ? 1 : 0 })
      });
    }

    async function deleteDish(id) {
      if (!confirm('Are you sure you want to delete this menu item?')) return;
      await fetch('../api/menu_manage.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id })
      });
      loadMenu();
    }

    // Modal Add Dish
    document.getElementById('openAddDishModal').addEventListener('click', () => {
      document.getElementById('addDishModal').classList.remove('hidden');
    });
    document.getElementById('closeAddDishModal').addEventListener('click', () => {
      document.getElementById('addDishModal').classList.add('hidden');
    });

    async function handleStaffDishUpload(input) {
      if (!input.files || !input.files[0]) return;
      const file = input.files[0];
      const reader = new FileReader();
      reader.onload = async function(e) {
        const base64 = e.target.result;
        if (document.getElementById('staffDishPreview')) {
          document.getElementById('staffDishPreview').src = base64;
          document.getElementById('staffDishPreview').classList.remove('hidden');
        }
        try {
          const res = await fetch('../api/upload.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image: base64 })
          });
          const data = await res.json();
          if (data.success) {
            document.getElementById('dishImage').value = data.url;
            if (document.getElementById('staffDishPreview')) document.getElementById('staffDishPreview').src = '../' + data.url;
          }
        } catch (err) {
          console.error("Upload error:", err);
          document.getElementById('dishImage').value = base64;
        }
      };
      reader.readAsDataURL(file);
    }

    document.getElementById('addDishForm').addEventListener('submit', async e => {
      e.preventDefault();
      const imageVal = document.getElementById('dishImage').value.trim();
      if (!imageVal) {
        alert("⚠️ Uploading or providing a dish photo is compulsory for staff!");
        return;
      }
      const payload = {
        action: 'add',
        restaurant_id: document.getElementById('dishRestaurant').value,
        name: document.getElementById('dishName').value,
        category: document.getElementById('dishCategory').value,
        price: document.getElementById('dishPrice').value,
        description: document.getElementById('dishDescription').value,
        image: imageVal
      };

      const res = await fetch('../api/menu_manage.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        document.getElementById('addDishModal').classList.add('hidden');
        document.getElementById('addDishForm').reset();
        loadMenu();
      }
    });

    // Auto-refresh orders every 10s
    setInterval(loadOrders, 10000);

    initStaff();
  </script>
</body>
</html>
