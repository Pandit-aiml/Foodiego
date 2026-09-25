<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FoodieGo - Select Portal (Customer & Staff)</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    .mode-bar {
      background: #111111;
      color: #ffffff;
      padding: 8px 5%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 13px;
    }
    .mode-switcher-btns {
      display: flex;
      gap: 10px;
    }
    .mode-btn {
      background: rgba(255,255,255,0.15);
      color: #fff;
      border: 0;
      padding: 5px 12px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
    }
    .mode-btn.active {
      background: #e84b22;
      color: #fff;
    }
    /* Mode Selection Screen */
    .portal-landing-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: linear-gradient(135deg, #111 0%, #2a150c 100%);
      z-index: 2000;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      padding: 20px;
      text-align: center;
    }
    .portal-choices {
      display: flex;
      gap: 24px;
      margin-top: 30px;
      flex-wrap: wrap;
      justify-content: center;
      max-width: 800px;
    }
    .portal-card-choice {
      background: rgba(255, 255, 255, 0.07);
      border: 2px solid rgba(255, 255, 255, 0.15);
      border-radius: 20px;
      padding: 35px 28px;
      width: 300px;
      cursor: pointer;
      transition: all 0.25s;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .portal-card-choice:hover {
      transform: translateY(-8px);
      border-color: #e84b22;
      background: rgba(232, 75, 34, 0.12);
      box-shadow: 0 15px 35px rgba(232, 75, 34, 0.3);
    }
    .portal-icon {
      font-size: 60px;
      margin-bottom: 16px;
    }
    .portal-card-choice h3 {
      font-size: 22px;
      margin: 0 0 8px;
      font-weight: 800;
    }
    .portal-card-choice p {
      font-size: 13px;
      color: #ccc;
      margin-bottom: 20px;
      line-height: 1.5;
    }
  </style>
</head>
<body>
  <!-- Initial Portal Selection Overlay Screen -->
  <div id="portalLanding" class="portal-landing-overlay">
    <div style="font-size: 32px; font-weight: 800; color: #e84b22; margin-bottom: 6px;">🍔 FoodieGo</div>
    <h1 style="font-size: 36px; font-weight: 800; margin: 0 0 8px;">Welcome! Select Your Mode</h1>
    <p style="color: #aaa; max-width: 500px; margin: 0;">Please choose whether you want to order food as a Customer or manage kitchen operations as Staff.</p>

    <div class="portal-choices">
      <!-- Customer Mode Choice -->
      <div class="portal-card-choice" onclick="selectMode('customer')">
        <div class="portal-icon">🛒</div>
        <h3>Customer Mode</h3>
        <p>Browse local restaurant menus, order delicious meals, pay online/COD & track delivery live.</p>
        <button class="btn full">Enter Customer Portal ➔</button>
      </div>

      <!-- Staff Mode Choice -->
      <div class="portal-card-choice" onclick="selectMode('staff')">
        <div class="portal-icon">👨‍🍳</div>
        <h3>Staff & Kitchen Mode</h3>
        <p>Manage incoming kitchen order queue, update order statuses, view live revenue stats & edit menu stock.</p>
        <button class="btn full" style="background: #ffffff; color: #222;">Enter Staff Portal ➔</button>
      </div>
    </div>
  </div>

  <!-- App Header -->
  <header>
    <div style="display: flex; align-items: center; gap: 16px;">
      <div class="logo" id="appLogo" style="cursor: pointer;">🍔 FoodieGo</div>
      <div class="header-location" onclick="openAddressModal()" style="display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #555; background: #f5f5f5; padding: 6px 14px; border-radius: 20px; cursor: pointer; border: 1px solid #e0e0e0;" title="Click to select or add new delivery address">
        <span id="headerLocIcon">📍</span> <span id="headerLocText" style="color: #222;">Chandigarh University</span> <small id="headerLocSub" style="color: #e84b22;">• TT Sector</small> <span style="font-size: 10px; color: #888;">▼</span>
      </div>
    </div>
    <nav>
      <!-- Customer Nav Links -->
      <div id="customerNav" class="nav-group">
        <a href="#home">Home</a>
        <a href="#restaurants">Restaurants</a>
        <a href="#menu">Menu</a>
        <a href="#cart">Cart <span id="cartCount">0</span></a>
        <a href="#" id="myOrdersLink" class="nav-highlight">📦 My Orders Tracker</a>
      </div>

      <!-- Staff Nav Links -->
      <div id="staffNav" class="nav-group hidden">
        <a href="#" onclick="showStaffSec('secStaffOrders')">📦 Orders Queue</a>
        <a href="#" onclick="showStaffSec('secStaffMenu')">🍲 Menu & Stock Manager</a>
      </div>

      <!-- Dynamic Mode Auth Container (Sign In / Sign Out) -->
      <div id="authNav" class="auth-nav-container"></div>
    </nav>
  </header>

  <!-- ================= CUSTOMER PORTAL VIEW ================= -->
  <div id="customerPortalView">
    <!-- Teej Festival Special Offer Banner -->
    <div class="festival-banner">
      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
          <span class="fest-tag">🪔 TEEJ FESTIVAL SPECIAL • 14 SEP OFFER</span>
          <h3 style="margin: 6px 0 4px; font-size: 20px; font-weight: 800;">Celebrate Teej with South Indian & Festive Feasts!</h3>
          <p style="margin: 0; font-size: 14px; opacity: 0.9;">Get <strong>FLAT 20% OFF</strong> on all orders! Use Promo Code: <span class="code-badge" onclick="applyTeejCode()">TEEJ20</span></p>
        </div>
        <button type="button" class="btn" style="background: #ffffff; color: #b81400; font-weight: 800;" onclick="applyTeejCode()">Apply TEEJ20 (20% OFF) ➔</button>
      </div>
    </div>

    <section class="hero-new-container" id="home">
      <div class="hero-content-col">
        <div class="hero-badge-pill">✨ FAST DELIVERY • FRESH INGREDIENTS • PUNJABI, BIHARI & SOUTH INDIAN SPECIALS</div>
        <h1 class="hero-main-title">
          Taste the Authentic Flavour of <br>
          <span class="gradient-text">Punjabi, Bihari & South India</span>
        </h1>
        <p class="hero-subtext">
          Order authentic Punjabi Amritsari Kulchas & Butter Chicken, 50+ traditional Bihari delicacies (Litti Chokha, Sattu Paratha), South Indian dosas, Italian pizzas & more delivered fresh to your door.
        </p>

        <!-- Search Bar with Quick Tags -->
        <div class="hero-search-wrapper">
          <div class="hero-search-input-box">
            <span class="search-icon-emoji">🔍</span>
            <input id="heroSearchInput" placeholder="Search for 'Amritsari Kulcha', 'Litti Chokha', 'Butter Chicken', 'Dosa'..." oninput="handleHeroSearch(this.value)">
            <button type="button" class="hero-search-action-btn" onclick="handleHeroSearchBtn()">Search ➔</button>
          </div>
          <div class="hero-quick-tags">
            <span class="quick-tag-label">Trending:</span>
            <button type="button" class="quick-tag-chip" onclick="filterByMindCategory('Punjabi')">🥘 Amritsari Kulcha</button>
            <button type="button" class="quick-tag-chip" onclick="filterByMindCategory('Litti Chokha')">🔥 Litti Chokha</button>
            <button type="button" class="quick-tag-chip" onclick="filterByMindCategory('Sattu Paratha')">🫓 Sattu Paratha</button>
            <button type="button" class="quick-tag-chip" onclick="filterByMindCategory('South Indian')">🥞 Dosa</button>
            <button type="button" class="quick-tag-chip" onclick="filterByMindCategory('Pizza')">🍕 Pizza</button>
          </div>
        </div>

        <!-- Live Stats Highlights -->
        <div class="hero-stats-bar">
          <div class="hero-stat-item">
            <span class="stat-number">50+</span>
            <span class="stat-label">Bihari Delicacies</span>
          </div>
          <div class="stat-divider"></div>
          <div class="hero-stat-item">
            <span class="stat-number">25-35</span>
            <span class="stat-label">Mins Avg Delivery</span>
          </div>
          <div class="stat-divider"></div>
          <div class="hero-stat-item">
            <span class="stat-number">4.8 ★</span>
            <span class="stat-label">Customer Rating</span>
          </div>
        </div>
      </div>

      <!-- Right Column Showcase Card -->
      <div class="hero-visual-col">
        <div class="hero-showcase-card">
          <div class="showcase-img-badge">🔥 SPECIAL FEATURE</div>
          <img src="https://i0.wp.com/flavoursonplate.com/wp-content/uploads/2018/11/litti-chokha.png?w=1536&ssl=1" alt="Champaran Rasoi Bihari Thali" class="showcase-img">
          <div class="showcase-body">
            <div class="showcase-title-row">
              <h3>Champaran Rasoi (Bihari Special)</h3>
              <span class="rating-badge-pill">★ 4.8</span>
            </div>
            <p>50 Authentic Bihari items including Litti Chokha, Sattu Paratha, Dal Pitha, Thekua & Khaja.</p>
            <div class="showcase-offer-chip" onclick="applyTeejCode()">
              🎉 <strong>TEEJ20</strong> — Get FLAT 20% OFF
            </div>
            <button type="button" class="btn full" style="margin-top: 12px;" onclick="filterByMindCategory('Litti Chokha')">Explore Champaran Menu ➔</button>
          </div>
        </div>
      </div>
    </section>

    <main>
      <!-- What's on your mind Carousel Section -->
      <section class="mind-categories-section" style="margin: 25px 0 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
          <h2 style="font-size: 22px; font-weight: 800; color: #222; margin: 0;">What's on your mind?</h2>
          <span style="font-size: 13px; color: #e84b22; font-weight: 700; cursor: pointer;" onclick="resetMindFilter()">View All Categories ➔</span>
        </div>
        <div class="mind-carousel" style="display: flex; gap: 20px; overflow-x: auto; padding-bottom: 12px; scroll-behavior: smooth;">
          <div class="mind-card" onclick="filterByMindCategory('Punjabi')">
            <div class="mind-img-box"><img src="https://images.unsplash.com/photo-1626777552726-4a6b54c97e46?auto=format&fit=crop&w=300&q=80" alt="Punjabi Special"></div>
            <span>Punjabi Special</span>
          </div>
          <div class="mind-card" onclick="filterByMindCategory('Litti Chokha')">
            <div class="mind-img-box"><img src="https://i0.wp.com/flavoursonplate.com/wp-content/uploads/2018/11/litti-chokha.png?w=1536&ssl=1" alt="Litti Chokha"></div>
            <span>Litti Chokha</span>
          </div>
          <div class="mind-card" onclick="filterByMindCategory('Sattu Paratha')">
            <div class="mind-img-box"><img src="https://i2.wp.com/www.vegrecipesofindia.com/wp-content/uploads/2026/06/sattu-paratha.jpg" alt="Sattu Paratha"></div>
            <span>Sattu Paratha</span>
          </div>
          <div class="mind-card" onclick="filterByMindCategory('South Indian')">
            <div class="mind-img-box"><img src="https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=300&q=80" alt="South Indian"></div>
            <span>South Indian</span>
          </div>
          <div class="mind-card" onclick="filterByMindCategory('Sweet')">
            <div class="mind-img-box"><img src="https://www.cadburydessertscorner.com/hubfs/dc-website-2022/web-stories/history-of-thekua-explore-the-story-of-this-bihars-sweet-treat/feature-image.png" alt="Sweets"></div>
            <span>Sweets & Desserts</span>
          </div>
          <div class="mind-card" onclick="filterByMindCategory('Snack')">
            <div class="mind-img-box"><img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4y0d78HS3vac12meUEslkZLmdsOGFhFIlK7LzJ5E3L-pH3y75WtcNQFnC&s=10" alt="Snacks"></div>
            <span>Dal Pitha & Snacks</span>
          </div>
          <div class="mind-card" onclick="filterByMindCategory('Pizza')">
            <div class="mind-img-box"><img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=300&q=80" alt="Pizza"></div>
            <span>Pizzas</span>
          </div>
          <div class="mind-card" onclick="filterByMindCategory('Burger')">
            <div class="mind-img-box"><img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=300&q=80" alt="Burger"></div>
            <span>Burgers</span>
          </div>
        </div>
      </section>
      <section id="restaurants">
        <h2>Popular Restaurants</h2>
        <div class="filters">
          <input id="search" placeholder="Search restaurants or cuisine...">
          <select id="restaurantSelect">
            <option value="">All restaurants</option>
          </select>
        </div>
        <div id="restaurantsGrid" class="grid"></div>
      </section>

      <section id="menu">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
          <h2>Menu Items</h2>
          <div id="categoryPills" class="category-pills"></div>
        </div>
        <div id="menuGrid" class="grid menu-grid">
          <p class="empty">Choose a restaurant to view its menu.</p>
        </div>
      </section>

      <section id="cart">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
          <div>
            <h2 style="margin: 0; font-size: 24px; font-weight: 800; color: #282c3f;">🛒 Secure Checkout & Cart Summary</h2>
            <p style="margin: 4px 0 0; color: #686b78; font-size: 13px;">Review your delicious items and complete your order</p>
          </div>
          <span style="background: #e8f5e9; color: #2e7d32; font-weight: 700; font-size: 12px; padding: 6px 14px; border-radius: 20px; display: flex; align-items: center; gap: 6px; border: 1px solid #c8e6c9;">
            ⚡ 30 Mins Express Delivery
          </span>
        </div>

        <div class="cart-layout">
          <!-- Left Column: Order Items & Delivery Details -->
          <div style="display: flex; flex-direction: column; gap: 20px;">
            <div id="cartItems" class="cart-box">
              <p class="empty">Your cart is empty.</p>
            </div>
            
            <!-- Delivery Address Card -->
            <div class="cart-box" style="background: #ffffff; border-radius: 16px; border: 1px solid #eee; padding: 22px;">
              <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 18px; font-weight: 800; color: #282c3f; display: flex; align-items: center; gap: 8px;">
                📍 Delivery Address & Contact
              </h3>
              
              <div id="authNotice" class="auth-notice hidden" style="margin-bottom: 15px;">
                <span>💡 Logged in as <strong id="loggedInUserLabel" onclick="openProfileModal()" style="cursor: pointer; color: #e84b22; text-decoration: underline;" title="Click to view & edit profile">User</strong></span>
              </div>

              <div class="input-group">
                <label for="name" style="font-weight: 700;">Full Name</label>
                <input id="name" placeholder="Enter your full name">
              </div>
              
              <div class="input-group">
                <label for="phone" style="font-weight: 700;">Phone Number</label>
                <input id="phone" placeholder="Enter 10-digit mobile number">
              </div>

              <div style="display: grid; grid-template-columns: 1fr 140px; gap: 10px; margin-top: 10px;">
                <div class="input-group">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label for="address" style="font-weight: 700; margin: 0;">Delivery Address</label>
                    <button type="button" onclick="openAddressModal()" style="border: none; background: #fff5ee; color: #e84b22; font-size: 11px; font-weight: 800; cursor: pointer; padding: 2px 6px; border-radius: 6px; border: 1px solid #ffd0bf;">➕ Add / Manage</button>
                  </div>
                  <textarea id="address" placeholder="Flat / House No., Building, Street, City, Landmark"></textarea>
                </div>
                <div class="input-group">
                  <label for="pincode" style="font-weight: 700;">PIN Code</label>
                  <input id="pincode" placeholder="140413" maxlength="6" style="font-weight: 700; text-align: center;">
                </div>
              </div>

              <!-- Quick Address Pills -->
              <div id="checkoutAddressPills" style="display: flex; gap: 6px; margin-bottom: 8px; flex-wrap: wrap;">
                <span onclick="selectCheckoutAddress('Home')" style="background: #ffffff; border: 1px solid #ddd; padding: 4px 10px; border-radius: 20px; font-size: 11px; cursor: pointer; font-weight: 700; color: #444;" title="Select Home Address">🏠 Home</span>
                <span onclick="selectCheckoutAddress('Work')" style="background: #ffffff; border: 1px solid #ddd; padding: 4px 10px; border-radius: 20px; font-size: 11px; cursor: pointer; font-weight: 700; color: #444;" title="Select Work Address">🏢 Work</span>
                <span onclick="selectCheckoutAddress('Campus')" style="background: #ffffff; border: 1.5px solid #e84b22; padding: 4px 10px; border-radius: 20px; font-size: 11px; cursor: pointer; font-weight: 800; color: #e84b22;" title="Select Campus Address">🎓 Campus (CU)</span>
              </div>
            </div>
          </div>

          <!-- Right Column: Payment, Coupons & Bill Breakdown -->
          <div class="checkout" style="background: #ffffff; padding: 22px; border-radius: 16px; border: 1px solid #eee; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);">
            <h3 style="margin-top: 0; font-size: 18px; font-weight: 800; color: #282c3f; border-bottom: 1px solid #f0f0f0; padding-bottom: 12px;">
              💳 Payment & Order Summary
            </h3>

            <!-- Payment Options -->
            <div class="payment-section">
              <h4 style="margin: 0 0 10px; font-size: 14px; font-weight: 700; color: #282c3f;">Select Payment Method</h4>
              <div class="payment-options">
                <label class="payment-option active" data-method="COD">
                  <input type="radio" name="paymentMethod" value="COD" checked>
                  <div class="option-icon">💵</div>
                  <div class="option-info">
                    <strong>Cash on Delivery</strong>
                    <small>Pay cash upon delivery</small>
                  </div>
                </label>

                <label class="payment-option" data-method="UPI">
                  <input type="radio" name="paymentMethod" value="UPI">
                  <div class="option-icon">⚡</div>
                  <div class="option-info">
                    <strong>UPI / Instant Pay</strong>
                    <small>GPay, PhonePe, Paytm, QR</small>
                  </div>
                </label>

                <label class="payment-option" data-method="Card">
                  <input type="radio" name="paymentMethod" value="Card">
                  <div class="option-icon">💳</div>
                  <div class="option-info">
                    <strong>Credit / Debit Card</strong>
                    <small>Visa, Mastercard, RuPay</small>
                  </div>
                </label>
              </div>

              <div id="paymentDetails" class="payment-details">
                <div id="codFields" class="pay-box">
                  <p>💡 Please keep exact cash ready for payment upon delivery.</p>
                </div>

                <div id="upiFields" class="pay-box hidden">
                  <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                    <button type="button" id="upiSubTabId" class="pill-btn active" style="flex: 1; text-align: center; font-size: 12px; padding: 8px 10px;">✍️ Enter UPI ID</button>
                    <button type="button" id="upiSubTabQr" class="pill-btn" style="flex: 1; text-align: center; font-size: 12px; padding: 8px 10px;">📱 Generate QR Code</button>
                  </div>

                  <div id="upiSubSecId">
                    <label for="upiId">Your VPA / UPI ID</label>
                    <input id="upiId" value="8809023427@jupiteraxis" placeholder="e.g. 8809023427@jupiteraxis or name@upi">
                    <small style="color: #666; display: block; margin-top: 6px;">💡 We will send a payment request to your UPI App (GPay, PhonePe, Paytm, BHIM).</small>
                  </div>

                  <div id="upiSubSecQr" class="hidden">
                    <button type="button" class="btn btn-outline full" id="generateQrBtn">📱 Generate Payment QR Code</button>

                    <div id="qrContainer" class="qr-preview hidden" style="margin-top: 14px;">
                      <div style="background: #ffffff; padding: 16px; border-radius: 14px; border: 2px solid #e84b22; text-align: center;">
                        <img id="qrImage" src="" alt="UPI QR Code" style="width: 180px; height: 180px; margin: 0 auto; display: block; border-radius: 8px;">
                        <div style="font-size: 15px; font-weight: 800; margin-top: 10px; color: #222;">Scan with GPay / PhonePe / Paytm</div>
                        <div style="font-size: 13px; color: #e84b22; font-weight: 700; margin-top: 2px;">Pay ₹<span id="qrAmount">0.00</span> to <span id="qrMerchant">8809023427@jupiteraxis</span></div>
                        <div style="font-size: 11px; background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 6px; display: inline-block; margin-top: 8px; font-weight: 700;">✅ Active Dynamic UPI Payment QR Code</div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="cardFields" class="pay-box hidden">
                  <label for="cardNumber">Card Number</label>
                  <input id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19">
                  <div class="card-row">
                    <div>
                      <label for="cardExpiry">Expiry Date</label>
                      <input id="cardExpiry" placeholder="MM/YY" maxlength="5">
                    </div>
                    <div>
                      <label for="cardCvv">CVV</label>
                      <input id="cardCvv" type="password" placeholder="123" maxlength="4">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Festival Coupon Code Section -->
            <div class="coupon-section" style="margin-top: 10px; padding: 12px 14px; background: #fff7f2; border: 1px dashed #ffa485; border-radius: 12px;">
              <label for="couponCode" style="font-size: 13px; font-weight: 800; color: #d64019; display: flex; align-items: center; gap: 6px;">
                🏷️ Apply Promo / Festival Coupon
              </label>
              <div style="display: flex; gap: 8px; margin-top: 6px;">
                <input id="couponCode" placeholder="Enter TEEJ20" style="text-transform: uppercase; border-color: #ffd0bf;">
                <button type="button" class="btn" id="applyCouponBtn" style="white-space: nowrap; padding: 8px 14px; background: #e84b22; color: #fff;">Apply</button>
              </div>
              <div id="couponNotice" style="font-size: 12px; margin-top: 4px; font-weight: 700;"></div>
              
              <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px;">
                <span class="fest-coupon-chip" onclick="applyTeejCode('FIRSTORDER')" style="background: #ffffff; border: 1.5px solid #e84b22; padding: 4px 10px; border-radius: 6px; font-size: 11px; cursor: pointer; color: #e84b22; font-weight: 800; box-shadow: 0 2px 6px rgba(232,75,34,0.15);">
                  🎁 FIRSTORDER (60% OFF)
                </span>
                <span class="fest-coupon-chip" onclick="applyTeejCode('FOODIEGO50')" style="background: #ffffff; border: 1px solid #ffd0bf; padding: 4px 8px; border-radius: 6px; font-size: 11px; cursor: pointer; color: #e84b22; font-weight: 700;">
                  🔥 FOODIEGO50 (50% OFF)
                </span>
                <span class="fest-coupon-chip" onclick="applyTeejCode('TEEJ20')" style="background: #ffffff; border: 1px solid #ffd0bf; padding: 4px 8px; border-radius: 6px; font-size: 11px; cursor: pointer; color: #e84b22; font-weight: 700;">
                  🎉 TEEJ20 (20% OFF)
                </span>
                <span class="fest-coupon-chip" onclick="applyTeejCode('BIHAR25')" style="background: #ffffff; border: 1px solid #ffd0bf; padding: 4px 8px; border-radius: 6px; font-size: 11px; cursor: pointer; color: #e84b22; font-weight: 700;">
                  🌾 BIHAR25 (25% OFF)
                </span>
              </div>
            </div>

            <!-- Bill Details Summary -->
            <div class="price-summary" style="margin-top: 10px; padding: 14px; background: #fafafa; border-radius: 12px; border: 1px solid #eee;">
              <h4 style="margin: 0 0 10px; font-size: 13px; font-weight: 800; color: #282c3f; text-transform: uppercase; letter-spacing: 0.5px;">Bill Details</h4>
              <div style="display: flex; justify-content: space-between; font-size: 13px; color: #686b78; margin-bottom: 6px;">
                <span>Item Subtotal</span>
                <span>₹<span id="subtotalVal">0.00</span></span>
              </div>
              <div style="display: flex; justify-content: space-between; font-size: 13px; color: #686b78; margin-bottom: 6px;">
                <span>Delivery Fee | 30 Mins</span>
                <span style="color: #2e7d32; font-weight: 700;"><s style="color: #999; font-weight: 400; margin-right: 4px;">₹35</s> FREE</span>
              </div>
              <div style="display: flex; justify-content: space-between; font-size: 13px; color: #686b78; margin-bottom: 6px;">
                <span>Platform & Packaging Fee</span>
                <span style="color: #2e7d32; font-weight: 700;">FREE</span>
              </div>
              <div id="discountRow" class="hidden" style="display: flex; justify-content: space-between; font-size: 13px; color: #2e7d32; font-weight: 700; margin-bottom: 6px; padding-top: 4px;">
                <span>Festival Coupon Savings</span>
                <span>- ₹<span id="discountVal">0.00</span></span>
              </div>
              <div class="total" style="padding-top: 8px; border-top: 2px dashed #ddd; margin-top: 6px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 15px; font-weight: 800; color: #282c3f;">To Pay</span>
                <b style="font-size: 20px; color: #e84b22;">₹<span id="total">0.00</span></b>
              </div>
            </div>

            <button class="btn full" id="placeOrder" style="padding: 14px; font-size: 16px; font-weight: 800; border-radius: 12px; background: linear-gradient(135deg, #e84b22 0%, #d64019 100%); box-shadow: 0 4px 15px rgba(232,75,34,0.3); border: none; cursor: pointer; color: #ffffff;">
              PLACE ORDER & PAY ➔
            </button>
            <p id="orderMsg"></p>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- ================= STAFF PORTAL VIEW ================= -->
  <div id="staffPortalView" class="hidden">
    <main style="max-width: 1200px; padding: 25px 20px;">
      <!-- Staff Live Stats -->
      <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 16px; border: 1px solid #eee; display: flex; align-items: center; gap: 16px;">
          <div style="font-size: 32px; width: 50px; height: 50px; border-radius: 12px; background: #fff5ee; display: flex; align-items: center; justify-content: center;">💰</div>
          <div>
            <div id="staffStatRevenue" style="font-size: 24px; font-weight: 800;">₹0.00</div>
            <div style="font-size: 13px; color: #777; font-weight: 600;">Total Revenue</div>
          </div>
        </div>

        <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 16px; border: 1px solid #eee; display: flex; align-items: center; gap: 16px;">
          <div style="font-size: 32px; width: 50px; height: 50px; border-radius: 12px; background: #fff5ee; display: flex; align-items: center; justify-content: center;">🔥</div>
          <div>
            <div id="staffStatActive" style="font-size: 24px; font-weight: 800;">0</div>
            <div style="font-size: 13px; color: #777; font-weight: 600;">Active Kitchen Orders</div>
          </div>
        </div>

        <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 16px; border: 1px solid #eee; display: flex; align-items: center; gap: 16px;">
          <div style="font-size: 32px; width: 50px; height: 50px; border-radius: 12px; background: #fff5ee; display: flex; align-items: center; justify-content: center;">📦</div>
          <div>
            <div id="staffStatOrders" style="font-size: 24px; font-weight: 800;">0</div>
            <div style="font-size: 13px; color: #777; font-weight: 600;">Total Orders Placed</div>
          </div>
        </div>

        <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 16px; border: 1px solid #eee; display: flex; align-items: center; gap: 16px;">
          <div style="font-size: 32px; width: 50px; height: 50px; border-radius: 12px; background: #fff5ee; display: flex; align-items: center; justify-content: center;">👥</div>
          <div>
            <div id="staffStatCustomers" style="font-size: 24px; font-weight: 800;">0</div>
            <div style="font-size: 13px; color: #777; font-weight: 600;">Registered Customers</div>
          </div>
        </div>
      </div>

      <!-- Staff Orders Section -->
      <div id="secStaffOrders">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
          <h3 style="margin: 0;">👨‍🍳 Kitchen Live Orders Queue</h3>
          <select id="staffStatusFilter" style="width: auto; padding: 8px 14px; font-weight: 700;">
            <option value="ALL">All Order Statuses</option>
            <option value="ACTIVE" selected>🔥 Active Kitchen Orders Only</option>
            <option value="Pending">Pending</option>
            <option value="Preparing">Preparing</option>
            <option value="Out for Delivery">Out for Delivery</option>
            <option value="Delivered">Delivered</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
        <div id="staffOrdersList">Loading kitchen orders...</div>
      </div>

      <!-- Staff Menu Manager Section -->
      <div id="secStaffMenu" class="hidden">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <h3 style="margin: 0;">🍲 Restaurant Menu & Stock Items</h3>
          <button class="btn" id="openAddDishModal">➕ Add New Dish</button>
        </div>
        <div id="staffMenuItemsGrid" class="grid">Loading menu items...</div>
      </div>
    </main>
  </div>

  <!-- Delivery Address Manager Modal -->
  <div id="addressModal" class="modal-overlay hidden">
    <div class="modal-card">
      <button class="close-modal" id="closeAddressModal">&times;</button>
      <h3 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">📍 Select & Add Delivery Address</h3>
      <p class="muted">Manage your saved addresses for fast 30-minute food delivery.</p>

      <!-- Saved Addresses List -->
      <div style="margin-bottom: 20px;">
        <h4 style="margin: 0 0 10px; font-size: 14px; font-weight: 800; color: #282c3f;">Saved Delivery Locations</h4>
        <div id="savedAddressesList" style="display: flex; flex-direction: column; gap: 10px;"></div>
      </div>

      <!-- Add New Address Form -->
      <form id="addAddressForm" class="modal-form" style="background: #fafafa; padding: 16px; border-radius: 12px; border: 1px solid #eee;">
        <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 800; color: #e84b22; display: flex; align-items: center; gap: 6px;">
          ➕ Add New Delivery Address
        </h4>

        <div class="input-group">
          <label>Address Type</label>
          <div style="display: flex; gap: 8px; margin-top: 4px;">
            <label style="flex: 1; text-align: center; border: 1px solid #ddd; padding: 6px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700;">
              <input type="radio" name="addrTag" value="Home" checked> 🏠 Home
            </label>
            <label style="flex: 1; text-align: center; border: 1px solid #ddd; padding: 6px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700;">
              <input type="radio" name="addrTag" value="Work"> 🏢 Work
            </label>
            <label style="flex: 1; text-align: center; border: 1px solid #ddd; padding: 6px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700;">
              <input type="radio" name="addrTag" value="Campus"> 🎓 Campus
            </label>
            <label style="flex: 1; text-align: center; border: 1px solid #ddd; padding: 6px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700;">
              <input type="radio" name="addrTag" value="Other"> 📍 Other
            </label>
          </div>
        </div>

        <div class="input-group">
          <label>Flat / House / Room No. & Building Name</label>
          <input type="text" id="newAddrBuilding" placeholder="e.g. Flat 302, Shanti Heights" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 130px; gap: 10px;">
          <div class="input-group">
            <label>Street, Area, City</label>
            <input type="text" id="newAddrArea" placeholder="e.g. Boring Road, Patna / Sector 71, Mohali" required>
          </div>
          <div class="input-group">
            <label>PIN Code</label>
            <input type="text" id="newAddrPincode" placeholder="140413" maxlength="6" required style="font-weight: 700; text-align: center;">
          </div>
        </div>

        <div class="input-group">
          <label>Landmark (Optional)</label>
          <input type="text" id="newAddrLandmark" placeholder="e.g. Near Patna Junction / Opposite Gate 2">
        </div>

        <button type="submit" class="btn full" style="margin-top: 10px; background: #e84b22; color: #ffffff;">Save Address & Deliver Here</button>
      </form>
    </div>
  </div>

  <!-- OTP Verification Modal -->
  <div id="otpModal" class="modal-overlay hidden">
    <div class="modal-card" style="text-align: center; max-width: 420px;">
      <button class="close-modal" id="closeOtpModal" onclick="closeOtpModal()">&times;</button>
      <div style="font-size: 48px; margin-bottom: 8px;">🔐</div>
      <h3 style="margin: 0 0 6px; font-size: 20px; font-weight: 800; color: #282c3f;">Verify OTP Code</h3>
      <p style="font-size: 13px; color: #666; margin: 0 0 12px;">We sent a 6-digit OTP code to <b id="otpTargetLabel" style="color: #e84b22;">user@example.com</b></p>
      
      <div id="otpSimulatedNotice" style="background: #eef2ff; border: 1px solid #c7d2fe; color: #3730a3; padding: 10px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; margin-bottom: 15px;">
        📩 <b>Direct Outbox Dispatch:</b> The 6-digit verification code has been dispatched directly to your registered destination. Please enter any 6-digit code to confirm.
      </div>

      <div class="input-group" style="margin-bottom: 15px;">
        <input type="text" id="otpInput" placeholder="• • • • • •" maxlength="6" style="text-align: center; font-size: 24px; font-weight: 800; letter-spacing: 8px; padding: 10px; border: 2px solid #e84b22; border-radius: 12px;">
      </div>

      <p id="otpErrorMsg" class="error-msg" style="margin-bottom: 15px;"></p>

      <button type="button" class="btn full" id="verifyOtpSubmitBtn" onclick="verifyOtpSubmit()" style="padding: 12px; font-size: 15px; font-weight: 800; background: #e84b22; color: #fff;">
        Verify & Confirm OTP ➔
      </button>

      <div style="margin-top: 15px; font-size: 12px; color: #777;">
        Didn't receive code? <button type="button" id="resendOtpBtn" onclick="if (currentOtpState) triggerOtpVerification(currentOtpState.inputId, currentOtpState.type);" style="border: none; background: transparent; color: #e84b22; font-weight: 800; cursor: pointer; text-decoration: underline;">Resend OTP</button>
      </div>
    </div>
  </div>

  <!-- User Profile Modal -->
  <div id="profileModal" class="modal-overlay hidden">
    <div class="modal-card">
      <button class="close-modal" id="closeProfileModal">&times;</button>
      
      <div class="profile-header-card" style="text-align: center; margin-bottom: 20px; background: linear-gradient(135deg, #fff0eb 0%, #fff7f2 100%); padding: 20px; border-radius: 16px; border: 1px solid #ffd8c7;">
        <div style="position: relative; display: inline-block; margin-bottom: 8px;">
          <img id="profileAvatarImg" src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&q=80" alt="Profile Photo" style="width: 85px; height: 85px; border-radius: 50%; object-fit: cover; border: 3px solid #e84b22; box-shadow: 0 4px 15px rgba(232,75,34,0.2);">
          <button type="button" class="btn btn-sm" onclick="document.getElementById('profileImageFile').click()" style="position: absolute; bottom: 0; right: -5px; padding: 4px 8px; border-radius: 50%; font-size: 12px; background: #e84b22; color: #fff; border: 2px solid #fff;" title="Upload Profile Picture">📷</button>
        </div>
        <input type="file" id="profileImageFile" accept="image/*" class="hidden" onchange="handleCustomerProfileUpload(this)">
        <h3 id="profileHeaderName" style="margin: 0 0 4px; font-size: 22px; font-weight: 800;">User Profile</h3>
        <p id="profileHeaderEmail" style="margin: 0 0 10px; color: #666; font-size: 13px;">user@example.com</p>
        <span class="badge" style="background: #e84b22; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 11px;">ACTIVE CUSTOMER</span>
      </div>

      <form id="profileForm" class="modal-form">
        <h3 style="margin-top: 0;">Edit Personal Information</h3>
        
        <div class="input-group">
          <label>Full Name</label>
          <input type="text" id="profileName" placeholder="Your full name" required>
        </div>

        <div class="input-group">
          <label>Email Address</label>
          <div style="display: flex; gap: 8px; align-items: center;">
            <input type="email" id="profileEmail" placeholder="name@example.com" required style="flex: 1;">
            <button type="button" class="btn btn-outline" onclick="triggerOtpVerification('profileEmail', 'email')" style="white-space: nowrap; padding: 8px 12px; font-size: 12px; font-weight: 700;">📩 Verify OTP</button>
          </div>
          <span id="profileEmailBadge" class="hidden" style="color: #2e7d32; font-size: 11px; font-weight: 800; margin-top: 2px;">✅ Email Verified</span>
        </div>

        <div class="input-group">
          <label>Phone Number</label>
          <div style="display: flex; gap: 8px; align-items: center;">
            <input type="tel" id="profilePhone" placeholder="10-digit mobile number" style="flex: 1;">
            <button type="button" class="btn btn-outline" onclick="triggerOtpVerification('profilePhone', 'phone')" style="white-space: nowrap; padding: 8px 12px; font-size: 12px; font-weight: 700;">📱 Verify OTP</button>
          </div>
          <span id="profilePhoneBadge" class="hidden" style="color: #2e7d32; font-size: 11px; font-weight: 800; margin-top: 2px;">✅ Mobile Contact Verified</span>
        </div>

        <div class="input-group">
          <label>Default Delivery Address</label>
          <textarea id="profileAddress" placeholder="Flat No., House Name, Street, Landmark"></textarea>
        </div>

        <div class="input-group">
          <label>New Password <small style="color: #888;">(Leave blank to keep current)</small></label>
          <input type="password" id="profilePassword" placeholder="Enter new password">
        </div>

        <p id="profileMsg" class="error-msg" style="margin-top: 10px;"></p>
        
        <div style="display: flex; gap: 10px; margin-top: 15px;">
          <button type="submit" class="btn full" style="flex: 2;">Save Profile Changes</button>
          <button type="button" class="btn btn-outline" style="flex: 1;" onclick="closeProfileModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Customer Live Orders Tracker Modal -->
  <div id="ordersModal" class="modal-overlay hidden">
    <div class="modal-card modal-large">
      <button class="close-modal" id="closeOrdersModal">&times;</button>
      <h3>📦 Your Live Orders Tracker</h3>
      <p class="muted">Track your food preparation and delivery status in real-time.</p>
      <div id="customerOrdersList" class="orders-tracker-list">Loading your orders...</div>
    </div>
  </div>

  <!-- Customer Auth Modal (Sign In / Sign Up) -->
  <div id="authModal" class="modal-overlay hidden">
    <div class="modal-card">
      <button class="close-modal" id="closeAuthModal">&times;</button>
      <div class="modal-tabs">
        <button class="tab-btn active" id="tabLoginBtn">Customer Sign In</button>
        <button class="tab-btn" id="tabSignupBtn">Customer Sign Up</button>
      </div>

      <!-- Customer Sign In Form -->
      <form id="loginForm" class="modal-form">
        <h3>Customer Sign In</h3>
        <p class="muted">Sign in to auto-fill delivery details & view live order tracking.</p>
        <div class="input-group">
          <label>Email Address</label>
          <input type="email" id="loginEmail" placeholder="name@example.com" required>
        </div>
        <div class="input-group">
          <label>Password</label>
          <input type="password" id="loginPassword" placeholder="Enter password" required>
        </div>
        <p id="loginError" class="error-msg"></p>
        <button type="submit" class="btn full">Sign In</button>
      </form>

      <!-- Customer Sign Up Form -->
      <form id="signupForm" class="modal-form hidden">
        <h3>Create Customer Account</h3>
        <p class="muted">Join FoodieGo for quick checkouts and order tracking.</p>
        <div class="input-group">
          <label>Full Name</label>
          <input type="text" id="signupName" placeholder="John Doe" required>
        </div>
        <div class="input-group">
          <label>Email Address</label>
          <div style="display: flex; gap: 8px; align-items: center;">
            <input type="email" id="signupEmail" placeholder="name@example.com" required style="flex: 1;">
            <button type="button" class="btn btn-outline" onclick="triggerOtpVerification('signupEmail', 'email')" style="white-space: nowrap; padding: 8px 12px; font-size: 12px; font-weight: 700;">📩 Verify Email</button>
          </div>
          <span id="signupEmailBadge" class="hidden" style="color: #2e7d32; font-size: 11px; font-weight: 800; margin-top: 2px;">✅ Email Verified via OTP</span>
        </div>
        <div class="input-group">
          <label>Password</label>
          <input type="password" id="signupPassword" placeholder="Minimum 6 characters" required minlength="6">
        </div>
        <div class="input-group">
          <label>Phone Number</label>
          <div style="display: flex; gap: 8px; align-items: center;">
            <input type="tel" id="signupPhone" placeholder="10-digit mobile number" style="flex: 1;">
            <button type="button" class="btn btn-outline" onclick="triggerOtpVerification('signupPhone', 'phone')" style="white-space: nowrap; padding: 8px 12px; font-size: 12px; font-weight: 700;">📱 Verify Mobile</button>
          </div>
          <span id="signupPhoneBadge" class="hidden" style="color: #2e7d32; font-size: 11px; font-weight: 800; margin-top: 2px;">✅ Mobile Contact Verified via OTP</span>
        </div>
        <div class="input-group">
          <label>Default Address</label>
          <textarea id="signupAddress" placeholder="Flat No., Street, City"></textarea>
        </div>
        <p id="signupError" class="error-msg"></p>
        <button type="submit" class="btn full">Create Account</button>
      </form>
    </div>
  </div>

  <!-- Staff Auth Modal (Sign In) -->
  <div id="staffAuthModal" class="modal-overlay hidden">
    <div class="modal-card">
      <button class="close-modal" id="closeStaffAuthModal">&times;</button>
      <h3>👨‍🍳 Staff & Kitchen Sign In</h3>
      <p class="muted">Enter staff credentials to manage kitchen order queue & menu stock.</p>
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
        <button type="submit" class="btn full">Sign In to Staff Portal</button>
      </form>
    </div>
  </div>

  <!-- Staff Add Dish Modal -->
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
            <button type="button" class="btn btn-outline" onclick="document.getElementById('staffDishImageFile').click()" style="white-space: nowrap; padding: 10px 14px; font-size: 13px;">📁 Upload File</button>
            <input type="file" id="staffDishImageFile" accept="image/*" class="hidden" onchange="handleStaffDishUpload(this)">
          </div>
          <img id="staffDishPreview" class="hidden" style="width: 100px; height: 75px; object-fit: cover; border-radius: 8px; margin-top: 8px; border: 2px solid #e84b22;">
        </div>
        <button type="submit" class="btn full">Save New Dish</button>
      </form>
    </div>
  </div>

  <!-- Floating Bottom Cart Bar -->
  <div id="floatingCartBar" class="floating-cart-bar hidden">
    <div class="floating-cart-content">
      <div>
        <span class="floating-item-count"><span id="floatingCartQty">0</span> ITEMS ADDED</span>
        <div class="floating-price">₹<span id="floatingCartPrice">0.00</span> <small>(Extra 20% OFF with TEEJ20)</small></div>
      </div>
      <a href="#cart" class="floating-view-cart-btn">VIEW CART & CHECKOUT ➔</a>
    </div>
  </div>

  <footer>© 2026 FoodieGo • Built with HTML, CSS, JavaScript, Python / PHP & MySQL</footer>
  <script src="assets/app.js"></script>
</body>
</html>
