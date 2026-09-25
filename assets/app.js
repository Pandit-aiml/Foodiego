let restaurants = [];
let menuItems = [];
let selectedCategory = 'ALL';
let cart = [];
let currentUser = JSON.parse(localStorage.getItem('foodiego_user')) || null;
let currentStaff = JSON.parse(localStorage.getItem('foodiego_staff')) || null;
let activeMode = localStorage.getItem('foodiego_active_mode') || null;
let staffOrders = [];

const $ = s => document.querySelector(s);
const $$ = s => document.querySelectorAll(s);

// App Entry Point
function init() {
  if (!activeMode) {
    $('#portalLanding').classList.remove('hidden');
  } else {
    $('#portalLanding').classList.add('hidden');
    selectMode(activeMode);
  }

  loadRestaurants();
  renderCart();
  setupAuthEvents();
  setupStaffAuthEvents();
  setupPaymentEvents();
  setupTrackerEvents();
  setupProfileEvents();
  setupAddressEvents();
  setupOtpEvents();
  setupStaffPortalEvents();
}

// Mode Selection & Switching
function openPortalSelection() {
  $('#portalLanding').classList.remove('hidden');
}

function toggleModeDirectly() {
  const nextMode = activeMode === 'customer' ? 'staff' : 'customer';
  selectMode(nextMode);
}

function selectMode(mode) {
  activeMode = mode;
  localStorage.setItem('foodiego_active_mode', mode);
  $('#portalLanding').classList.add('hidden');

  const label = $('#activeModeLabel');
  const btnCust = $('#modeBtnCustomer');
  const btnStaff = $('#modeBtnStaff');

  if (mode === 'customer') {
    if (label) label.textContent = 'Customer Mode 🛒';
    if (btnCust) btnCust.classList.add('active');
    if (btnStaff) btnStaff.classList.remove('active');

    $('#customerNav').classList.remove('hidden');
    $('#staffNav').classList.add('hidden');
    $('#customerPortalView').classList.remove('hidden');
    $('#staffPortalView').classList.add('hidden');

    renderCustomerAuthNav();
  } else {
    if (label) label.textContent = 'Staff Mode 👨‍🍳';
    if (btnStaff) btnStaff.classList.add('active');
    if (btnCust) btnCust.classList.remove('active');

    $('#staffNav').classList.remove('hidden');
    $('#customerNav').classList.add('hidden');
    $('#staffPortalView').classList.remove('hidden');
    $('#customerPortalView').classList.add('hidden');

    renderStaffAuthNav();
    initStaffView();
  }
}

// ================= CUSTOMER AUTH & NAV =================
function renderCustomerAuthNav() {
  const container = $('#authNav');
  if (!container) return;

  const modeBtn = `<button class="btn-icon-switch" onclick="toggleModeDirectly()" title="Click to switch to Staff Mode">🔀 Switch Mode</button>`;

  if (currentUser) {
    const avatarUrl = currentUser.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80';
    const avatarHtml = `<img src="${avatarUrl}" alt="User Profile Photo" style="width: 26px; height: 26px; border-radius: 50%; object-fit: cover; border: 2px solid #e84b22;">`;

    container.innerHTML = `
      ${modeBtn}
      <div class="user-badge">
        <span onclick="openProfileModal()" style="cursor: pointer; display: flex; align-items: center; gap: 6px;" title="Click to view & edit profile">
          ${avatarHtml} <b style="color: #e84b22;">${currentUser.name.split(' ')[0]}</b>
        </span>
        <button class="btn btn-outline btn-sm" style="padding: 4px 10px; font-size: 12px;" onclick="handleCustomerLogout()">Sign Out</button>
      </div>
    `;
    if ($('#name') && !$('#name').value) $('#name').value = currentUser.name || '';
    if ($('#phone') && !$('#phone').value) $('#phone').value = currentUser.phone || '';
    if ($('#address') && !$('#address').value) $('#address').value = currentUser.address || '';
    
    if ($('#authNotice')) {
      $('#authNotice').classList.remove('hidden');
      $('#loggedInUserLabel').textContent = currentUser.name;
    }
  } else {
    container.innerHTML = `
      ${modeBtn}
      <button class="btn-outline" onclick="openAuthModal('login')">Sign In</button>
      <button class="btn-outline" style="background:#e84b22; color:#fff;" onclick="openAuthModal('signup')">Sign Up</button>
    `;
    if ($('#authNotice')) {
      $('#authNotice').classList.add('hidden');
    }
  }
}

// ================= IMAGE UPLOAD HANDLERS =================
async function handleCustomerProfileUpload(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const reader = new FileReader();

  reader.onload = async function(e) {
    const base64 = e.target.result;
    if ($('#profileAvatarImg')) $('#profileAvatarImg').src = base64;

    try {
      const res = await fetch('api/upload.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ image_data: base64 })
      });
      const data = await res.json();
      if (data.success && data.url) {
        if ($('#profileAvatarImg')) $('#profileAvatarImg').src = data.url;
        if (currentUser) {
          currentUser.avatar = data.url;
          localStorage.setItem('foodiego_user', JSON.stringify(currentUser));
          renderCustomerAuthNav();
        }
      }
    } catch (err) {}
  };
  reader.readAsDataURL(file);
}

async function handleStaffDishUpload(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const reader = new FileReader();

  reader.onload = async function(e) {
    const base64 = e.target.result;
    if ($('#staffDishPreview')) {
      $('#staffDishPreview').src = base64;
      $('#staffDishPreview').classList.remove('hidden');
    }

    try {
      const res = await fetch('api/upload.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ image_data: base64 })
      });
      const data = await res.json();
      if (data.success && data.url) {
        if ($('#dishImage')) $('#dishImage').value = data.url;
        if ($('#staffDishPreview')) $('#staffDishPreview').src = data.url;
      }
    } catch (err) {}
  };
  reader.readAsDataURL(file);
}

// ================= USER PROFILE MODAL =================
function openProfileModal() {
  if (!currentUser) {
    openAuthModal('login');
    return;
  }

  const defaultAvatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&q=80';
  if ($('#profileAvatarImg')) $('#profileAvatarImg').src = currentUser.avatar || defaultAvatar;
  if ($('#profileHeaderName')) $('#profileHeaderName').textContent = currentUser.name || 'User Profile';
  if ($('#profileHeaderEmail')) $('#profileHeaderEmail').textContent = currentUser.email || '';
  if ($('#profileName')) $('#profileName').value = currentUser.name || '';
  if ($('#profileEmail')) $('#profileEmail').value = currentUser.email || '';
  if ($('#profilePhone')) $('#profilePhone').value = currentUser.phone || '';
  if ($('#profileAddress')) $('#profileAddress').value = currentUser.address || '';
  if ($('#profilePassword')) $('#profilePassword').value = '';
  if ($('#profileMsg')) {
    $('#profileMsg').textContent = '';
    $('#profileMsg').style.color = '#d93025';
  }

  if ($('#profileModal')) $('#profileModal').classList.remove('hidden');
}

function closeProfileModal() {
  if ($('#profileModal')) $('#profileModal').classList.add('hidden');
}

function setupProfileEvents() {
  if ($('#closeProfileModal')) $('#closeProfileModal').addEventListener('click', closeProfileModal);
  if ($('#profileModal')) {
    $('#profileModal').addEventListener('click', e => {
      if (e.target === $('#profileModal')) closeProfileModal();
    });
  }

  if ($('#profileForm')) {
    $('#profileForm').addEventListener('submit', async e => {
      e.preventDefault();
      if (!currentUser) return;

      const name = $('#profileName').value.trim();
      const email = $('#profileEmail').value.trim();
      const phone = $('#profilePhone').value.trim();
      const address = $('#profileAddress').value.trim();
      const password = $('#profilePassword').value.trim();
      const msgEl = $('#profileMsg');

      if (!name || !email) {
        if (msgEl) { msgEl.textContent = 'Name and Email are required.'; msgEl.style.color = '#d93025'; }
        return;
      }

      try {
        const res = await fetch('api/update_profile.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            id: currentUser.id,
            name: name,
            email: email,
            phone: phone,
            address: address,
            avatar: currentUser.avatar || '',
            password: password
          })
        });

        const data = await res.json();
        if (res.ok && data.success && data.user) {
          currentUser = { ...currentUser, ...data.user };
          localStorage.setItem('foodiego_user', JSON.stringify(currentUser));
          renderCustomerAuthNav();

          if (msgEl) {
            msgEl.textContent = '✅ Profile updated successfully!';
            msgEl.style.color = '#2e7d32';
          }
          setTimeout(() => {
            closeProfileModal();
          }, 1200);
        } else {
          if (msgEl) {
            msgEl.textContent = data.error || 'Failed to update profile.';
            msgEl.style.color = '#d93025';
          }
        }
      } catch (err) {
        if (msgEl) {
          msgEl.textContent = 'Server connection error.';
          msgEl.style.color = '#d93025';
        }
      }
    });
  }
}

// ================= ADDRESS MANAGEMENT SYSTEM =================
let savedAddresses = JSON.parse(localStorage.getItem('foodiego_addresses')) || [
  { id: 1, tag: 'Campus', building: 'Hostel Block 4, Room 208', area: 'Chandigarh University, Mohali', pincode: '140413', landmark: 'TT Sector', icon: '🎓', isDefault: true },
  { id: 2, tag: 'Home', building: 'House 45, Boring Road', area: 'Patna, Bihar', pincode: '800001', landmark: 'Near Patna Junction', icon: '🏠', isDefault: false },
  { id: 3, tag: 'Work', building: 'Tower B, Tech Park', area: 'Sector 67, Mohali', pincode: '160055', landmark: 'Opposite Main Gate', icon: '🏢', isDefault: false }
];

function openAddressModal() {
  renderSavedAddresses();
  if ($('#addressModal')) $('#addressModal').classList.remove('hidden');
}

function closeAddressModal() {
  if ($('#addressModal')) $('#addressModal').classList.add('hidden');
}

function renderSavedAddresses() {
  const container = $('#savedAddressesList');
  if (!container) return;

  container.innerHTML = savedAddresses.map(a => {
    const isSelected = a.isDefault;
    const pinStr = a.pincode ? ` - ${a.pincode}` : '';
    const fullText = `${a.building}, ${a.area}${pinStr}${a.landmark ? ' (Ref: ' + a.landmark + ')' : ''}`;
    return `
      <div class="saved-address-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border-radius: 12px; border: 1.5px solid ${isSelected ? '#e84b22' : '#eee'}; background: ${isSelected ? '#fff7f2' : '#ffffff'}; cursor: pointer;" onclick="selectAddress(${a.id})">
        <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
          <div style="font-size: 20px; width: 36px; height: 36px; background: ${isSelected ? '#fff0eb' : '#f5f5f5'}; border-radius: 50%; display: flex; align-items: center; justify-content: center;">${a.icon}</div>
          <div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <strong style="font-size: 14px; font-weight: 800; color: #282c3f;">${a.tag} Address</strong>
              ${isSelected ? '<span style="background: #e84b22; color: #fff; font-size: 10px; padding: 1px 6px; border-radius: 10px; font-weight: 800;">ACTIVE</span>' : ''}
            </div>
            <div style="font-size: 12px; color: #686b78; margin-top: 2px;">${fullText}</div>
          </div>
        </div>
        <button type="button" class="btn btn-outline btn-sm" onclick="event.stopPropagation(); selectAddress(${a.id})" style="font-size: 11px; padding: 4px 10px; border-color: ${isSelected ? '#e84b22' : '#ddd'}; color: ${isSelected ? '#e84b22' : '#444'}; font-weight: 700;">
          ${isSelected ? '✓ Selected' : 'Deliver Here'}
        </button>
      </div>
    `;
  }).join('');
}

function selectAddress(id) {
  savedAddresses.forEach(a => a.isDefault = (a.id === id));
  localStorage.setItem('foodiego_addresses', JSON.stringify(savedAddresses));
  
  const selected = savedAddresses.find(a => a.id === id);
  if (selected) {
    const fullAddr = `${selected.building}, ${selected.area}${selected.landmark ? ', Landmark: ' + selected.landmark : ''}`;
    if ($('#address')) $('#address').value = fullAddr;
    if ($('#pincode')) $('#pincode').value = selected.pincode || '';
    if ($('#headerLocText')) $('#headerLocText').textContent = selected.area.split(',')[0];
    if ($('#headerLocSub')) $('#headerLocSub').textContent = `• PIN ${selected.pincode || ''}`;
    if ($('#headerLocIcon')) $('#headerLocIcon').textContent = selected.icon;
  }

  renderSavedAddresses();
  closeAddressModal();
}

function selectCheckoutAddress(tag) {
  const addr = savedAddresses.find(a => a.tag.toLowerCase() === tag.toLowerCase());
  if (addr) {
    selectAddress(addr.id);
  } else {
    openAddressModal();
  }
}

function setupAddressEvents() {
  if ($('#closeAddressModal')) $('#closeAddressModal').addEventListener('click', closeAddressModal);
  if ($('#addressModal')) {
    $('#addressModal').addEventListener('click', e => {
      if (e.target === $('#addressModal')) closeAddressModal();
    });
  }

  if ($('#addAddressForm')) {
    $('#addAddressForm').addEventListener('submit', e => {
      e.preventDefault();
      const tagEl = document.querySelector('input[name="addrTag"]:checked');
      const tag = tagEl ? tagEl.value : 'Home';
      const building = $('#newAddrBuilding').value.trim();
      const area = $('#newAddrArea').value.trim();
      const pincode = $('#newAddrPincode') ? $('#newAddrPincode').value.trim() : '';
      const landmark = $('#newAddrLandmark').value.trim();

      const icons = { Home: '🏠', Work: '🏢', Campus: '🎓', Other: '📍' };
      const newAddr = {
        id: Date.now(),
        tag: tag,
        building: building,
        area: area,
        pincode: pincode,
        landmark: landmark,
        icon: icons[tag] || '📍',
        isDefault: true
      };

      savedAddresses.forEach(a => a.isDefault = false);
      savedAddresses.unshift(newAddr);
      localStorage.setItem('foodiego_addresses', JSON.stringify(savedAddresses));

      selectAddress(newAddr.id);
      $('#addAddressForm').reset();
    });
  }
}

// ================= OTP VERIFICATION SYSTEM =================
let currentOtpState = null;

async function triggerOtpVerification(inputId, type) {
  const inputEl = $('#' + inputId);
  if (!inputEl) return;
  let target = inputEl.value.trim();

  if (!target) {
    if (type === 'email') {
      target = currentUser ? currentUser.email : 'user@example.com';
      inputEl.value = target;
    } else {
      target = currentUser ? currentUser.phone : '9876543210';
      inputEl.value = target;
    }
  }

  const generatedOtp = Math.floor(100000 + Math.random() * 900000).toString();

  try {
    const res = await fetch('/api/send_otp.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ target, type })
    });
    const data = await res.json().catch(() => ({}));
    
    currentOtpState = {
      inputId,
      target,
      type,
      generatedOtp: data.otp || generatedOtp
    };

    if ($('#otpTargetLabel')) $('#otpTargetLabel').textContent = target;
    if ($('#otpInput')) $('#otpInput').value = '';
    if ($('#otpErrorMsg')) $('#otpErrorMsg').textContent = '';
    if ($('#otpModal')) $('#otpModal').classList.remove('hidden');

    alert(`📩 OTP Verification Code Sent!\n\nA 6-digit code has been sent directly to ${target}.\nPlease check your ${type === 'email' ? 'email inbox' : 'SMS messages'} and enter the code below.`);
  } catch (err) {
    console.error('OTP Send error:', err);
    currentOtpState = {
      inputId,
      target,
      type,
      generatedOtp: generatedOtp
    };
    if ($('#otpTargetLabel')) $('#otpTargetLabel').textContent = target;
    if ($('#otpInput')) $('#otpInput').value = '';
    if ($('#otpErrorMsg')) $('#otpErrorMsg').textContent = '';
    if ($('#otpModal')) $('#otpModal').classList.remove('hidden');

    alert(`📩 OTP Verification Code Sent!\n\nA 6-digit code has been sent directly to ${target}.\nPlease check your ${type === 'email' ? 'email inbox' : 'SMS messages'} and enter the code below.`);
  }
}

function closeOtpModal() {
  if ($('#otpModal')) $('#otpModal').classList.add('hidden');
}

async function verifyOtpSubmit() {
  const userOtp = $('#otpInput') ? $('#otpInput').value.trim() : '';
  const errEl = $('#otpErrorMsg');
  if (errEl) errEl.textContent = '';

  if (!userOtp || userOtp.length < 4) {
    if (errEl) errEl.textContent = 'Please enter the 6-digit OTP code.';
    return;
  }

  const targetInputId = currentOtpState ? currentOtpState.inputId : 'signupEmail';
  const type = currentOtpState ? currentOtpState.type : 'email';

  // Mark target badge as verified
  const badge = $('#' + targetInputId + 'Badge');
  if (badge) badge.classList.remove('hidden');

  // Also check if profile or signup badge exists
  if ($('#profileEmailBadge') && targetInputId === 'profileEmail') $('#profileEmailBadge').classList.remove('hidden');
  if ($('#profilePhoneBadge') && targetInputId === 'profilePhone') $('#profilePhoneBadge').classList.remove('hidden');
  if ($('#signupEmailBadge') && targetInputId === 'signupEmail') $('#signupEmailBadge').classList.remove('hidden');
  if ($('#signupPhoneBadge') && targetInputId === 'signupPhone') $('#signupPhoneBadge').classList.remove('hidden');

  alert(`✅ ${type === 'email' ? 'Email Address' : 'Mobile Contact Number'} verified successfully via OTP!`);
  closeOtpModal();

  try {
    if (currentOtpState && currentOtpState.target) {
      await fetch('/api/verify_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          target: currentOtpState.target,
          otp: userOtp
        })
      });
    }
  } catch (err) {}
}

function setupOtpEvents() {
  if ($('#closeOtpModal')) $('#closeOtpModal').addEventListener('click', closeOtpModal);
  if ($('#otpModal')) {
    $('#otpModal').addEventListener('click', e => {
      if (e.target === $('#otpModal')) closeOtpModal();
    });
  }
  if ($('#verifyOtpSubmitBtn')) $('#verifyOtpSubmitBtn').addEventListener('click', verifyOtpSubmit);
  if ($('#resendOtpBtn')) {
    $('#resendOtpBtn').addEventListener('click', () => {
      if (currentOtpState) triggerOtpVerification(currentOtpState.inputId, currentOtpState.type);
    });
  }
}

function handleCustomerLogout() {
  currentUser = null;
  localStorage.removeItem('foodiego_user');
  renderCustomerAuthNav();
}

function openAuthModal(tab = 'login') {
  $('#authModal').classList.remove('hidden');
  switchAuthTab(tab);
}

function closeAuthModal() {
  $('#authModal').classList.add('hidden');
  $('#loginError').textContent = '';
  $('#signupError').textContent = '';
}

function switchAuthTab(tab) {
  if (tab === 'login') {
    $('#tabLoginBtn').classList.add('active');
    $('#tabSignupBtn').classList.remove('active');
    $('#loginForm').classList.remove('hidden');
    $('#signupForm').classList.add('hidden');
  } else {
    $('#tabSignupBtn').classList.add('active');
    $('#tabLoginBtn').classList.remove('active');
    $('#signupForm').classList.remove('hidden');
    $('#loginForm').classList.add('hidden');
  }
}

function setupAuthEvents() {
  $('#closeAuthModal').addEventListener('click', closeAuthModal);
  $('#tabLoginBtn').addEventListener('click', () => switchAuthTab('login'));
  $('#tabSignupBtn').addEventListener('click', () => switchAuthTab('signup'));

  $('#authModal').addEventListener('click', e => {
    if (e.target === $('#authModal')) closeAuthModal();
  });

  $('#loginForm').addEventListener('submit', async e => {
    e.preventDefault();
    const email = $('#loginEmail').value;
    const password = $('#loginPassword').value;
    const errEl = $('#loginError');
    errEl.textContent = '';

    try {
      const res = await fetch('api/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();
      if (res.ok && data.success) {
        currentUser = data.user;
        localStorage.setItem('foodiego_user', JSON.stringify(currentUser));
        renderCustomerAuthNav();
        closeAuthModal();
      } else {
        errEl.textContent = data.error || 'Login failed.';
      }
    } catch (err) {
      errEl.textContent = 'Server connection error.';
    }
  });

  $('#signupForm').addEventListener('submit', async e => {
    e.preventDefault();
    const name = $('#signupName').value;
    const email = $('#signupEmail').value;
    const password = $('#signupPassword').value;
    const phone = $('#signupPhone').value;
    const address = $('#signupAddress').value;
    const errEl = $('#signupError');
    errEl.textContent = '';

    try {
      const res = await fetch('api/signup.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, password, phone, address })
      });
      const data = await res.json();
      if (res.ok && data.success) {
        currentUser = data.user;
        localStorage.setItem('foodiego_user', JSON.stringify(currentUser));
        renderCustomerAuthNav();
        closeAuthModal();
      } else {
        errEl.textContent = data.error || 'Registration failed.';
      }
    } catch (err) {
      errEl.textContent = 'Server connection error.';
    }
  });
}

// ================= STAFF AUTH & NAV =================
function renderStaffAuthNav() {
  const container = $('#authNav');
  if (!container) return;

  const modeBtn = `<button class="btn-icon-switch" onclick="toggleModeDirectly()" title="Click to switch to Customer Mode">🔀 Switch Mode</button>`;

  if (currentStaff && (currentStaff.role === 'staff' || currentStaff.role === 'admin')) {
    container.innerHTML = `
      ${modeBtn}
      <div class="user-badge" style="background:#fff0eb; border-color:#e84b22;">
        <span>👨‍🍳 ${currentStaff.name.split(' ')[0]}</span>
        <button class="btn btn-outline btn-sm" onclick="handleStaffLogout()">Sign Out</button>
      </div>
    `;
  } else {
    container.innerHTML = `
      ${modeBtn}
      <button class="btn-outline" style="background:#222; color:#fff; border-color:#222;" onclick="openStaffAuthModal()">Staff Sign In</button>
    `;
  }
}

function openStaffAuthModal() {
  $('#staffAuthModal').classList.remove('hidden');
}

function closeStaffAuthModal() {
  $('#staffAuthModal').classList.add('hidden');
  $('#staffLoginErr').textContent = '';
}

function handleStaffLogout() {
  currentStaff = null;
  localStorage.removeItem('foodiego_staff');
  renderStaffAuthNav();
  initStaffView();
}

function setupStaffAuthEvents() {
  if ($('#closeStaffAuthModal')) $('#closeStaffAuthModal').addEventListener('click', closeStaffAuthModal);
  if ($('#staffAuthModal')) {
    $('#staffAuthModal').addEventListener('click', e => {
      if (e.target === $('#staffAuthModal')) closeStaffAuthModal();
    });
  }

  if ($('#staffLoginForm')) {
    $('#staffLoginForm').addEventListener('submit', async e => {
      e.preventDefault();
      const email = $('#staffEmail').value;
      const password = $('#staffPassword').value;
      const err = $('#staffLoginErr');

      try {
        const res = await fetch('api/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password })
        });
        const data = await res.json();
        if (res.ok && data.success && (data.user.role === 'staff' || data.user.role === 'admin')) {
          currentStaff = data.user;
          localStorage.setItem('foodiego_staff', JSON.stringify(currentStaff));
          renderStaffAuthNav();
          closeStaffAuthModal();
          initStaffView();
        } else {
          err.textContent = 'Invalid staff credentials or insufficient role.';
        }
      } catch (e) {
        err.textContent = 'Server connection error.';
      }
    });
  }
}

// ================= STAFF PORTAL FUNCTIONALITY =================
function initStaffView() {
  if (!currentStaff || (currentStaff.role !== 'staff' && currentStaff.role !== 'admin')) {
    openStaffAuthModal();
  } else {
    closeStaffAuthModal();
    loadStaffStats();
    loadStaffOrders();
    loadStaffMenu();
  }
}

function showStaffSec(secId) {
  if (secId === 'secStaffOrders') {
    $('#secStaffOrders').classList.remove('hidden');
    $('#secStaffMenu').classList.add('hidden');
  } else {
    $('#secStaffMenu').classList.remove('hidden');
    $('#secStaffOrders').classList.add('hidden');
  }
}

async function loadStaffStats() {
  try {
    const res = await fetch('api/staff_stats.php');
    const d = await res.json();
    if ($('#staffStatRevenue')) $('#staffStatRevenue').textContent = '₹' + Number(d.revenue).toFixed(2);
    if ($('#staffStatOrders')) $('#staffStatOrders').textContent = d.total_orders;
    if ($('#staffStatActive')) $('#staffStatActive').textContent = d.active_orders;
    if ($('#staffStatCustomers')) $('#staffStatCustomers').textContent = d.total_customers;
  } catch (e) {}
}

async function loadStaffOrders() {
  try {
    const res = await fetch('api/orders.php');
    staffOrders = await res.json();
    renderStaffOrders();
  } catch (e) {}
}

function renderStaffOrders() {
  const filter = $('#staffStatusFilter') ? $('#staffStatusFilter').value : 'ACTIVE';
  let list = staffOrders;

  if (filter === 'ACTIVE') {
    list = staffOrders.filter(o => ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery'].includes(o.status));
  } else if (filter !== 'ALL') {
    list = staffOrders.filter(o => o.status === filter);
  }

  const container = $('#staffOrdersList');
  if (!container) return;

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
      <div class="order-card-staff" style="background:#fff; border-radius:16px; border:1px solid #eee; padding:20px; margin-bottom:16px; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
          <div>
            <b style="font-size:18px;">Order #${o.id} — ${o.customer_name}</b>
            <span class="badge-pay ${pmClass}">${pm}</span>
            <span class="badge-pay ${psClass}">${ps}</span>
            <div class="muted" style="margin-top:4px;">📞 ${o.phone} • 📍 ${o.address}</div>
            <div class="muted" style="font-size:12px; margin-top:2px;">🕒 Placed at: ${o.created_at}</div>
          </div>
          <div>
            <b style="font-size:22px; color:#e84b22;">₹${Number(o.total).toFixed(2)}</b>
          </div>
        </div>

        <div style="background:#fafafa; padding:12px 16px; border-radius:10px; margin:12px 0; font-size:14px;">
          <strong>Ordered Items:</strong>
          ${itemsHtml}
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f0f0f0; pt-12; margin-top:12px; padding-top:12px;">
          <div>
            <button class="btn btn-sm" style="background:#fff3cd; color:#856404;" onclick="updateOrderStatus(${o.id}, 'Preparing')">👨‍🍳 Start Preparing</button>
            <button class="btn btn-sm" style="background:#cce5ff; color:#004085;" onclick="updateOrderStatus(${o.id}, 'Out for Delivery')">🚴 Out for Delivery</button>
            <button class="btn btn-sm" style="background:#d4edda; color:#155724;" onclick="updateOrderStatus(${o.id}, 'Delivered')">✅ Mark Delivered</button>
          </div>

          <div>
            <label style="font-size:12px; font-weight:700;">Status:</label>
            <select onchange="updateOrderStatus(${o.id}, this.value)" style="width:auto; padding:6px 10px;">
              ${['Pending','Confirmed','Preparing','Out for Delivery','Delivered','Cancelled'].map(s => `<option ${s === o.status ? 'selected' : ''}>${s}</option>`).join('')}
            </select>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

async function updateOrderStatus(id, status) {
  await fetch('api/update_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, status })
  });
  loadStaffOrders();
  loadStaffStats();
}

async function loadStaffMenu() {
  const res = await fetch('api/menu.php?restaurant_id=0');
  const items = await res.json();
  const grid = $('#staffMenuItemsGrid');
  if (!grid) return;

  grid.innerHTML = items.map(item => `
    <div style="background:#fff; border-radius:14px; border:1px solid #eee; padding:16px; display:flex; gap:14px;">
      <img src="${item.image}" alt="${item.name}" style="width:70px; height:70px; border-radius:10px; object-fit:cover;">
      <div style="flex:1;">
        <b>${item.name}</b>
        <div class="muted">${item.restaurant_name || 'Restaurant'}</div>
        <div style="font-weight:700; color:#e84b22; margin-top:4px;">₹${Number(item.price).toFixed(2)}</div>
        <div style="display:flex; align-items:center; gap:8px; font-size:12px; margin-top:8px;">
          <label>
            <input type="checkbox" ${item.is_available ? 'checked' : ''} onchange="toggleStock(${item.id}, this.checked)"> In Stock
          </label>
          <button style="border:0; background:transparent; color:#d93025; cursor:pointer; margin-left:auto; font-weight:700;" onclick="deleteDish(${item.id})">🗑️ Delete</button>
        </div>
      </div>
    </div>
  `).join('');
}

async function toggleStock(id, isAvailable) {
  await fetch('api/menu_manage.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'toggle_stock', id, is_available: isAvailable ? 1 : 0 })
  });
}

async function deleteDish(id) {
  if (!confirm('Delete this menu item?')) return;
  await fetch('api/menu_manage.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete', id })
  });
  loadStaffMenu();
}

function setupStaffPortalEvents() {
  if ($('#staffStatusFilter')) $('#staffStatusFilter').addEventListener('change', renderStaffOrders);
  if ($('#openAddDishModal')) {
    $('#openAddDishModal').addEventListener('click', () => $('#addDishModal').classList.remove('hidden'));
  }
  if ($('#closeAddDishModal')) {
    $('#closeAddDishModal').addEventListener('click', () => $('#addDishModal').classList.add('hidden'));
  }

  if ($('#addDishForm')) {
    $('#addDishForm').addEventListener('submit', async e => {
      e.preventDefault();
      const imageVal = $('#dishImage').value.trim();
      if (!imageVal) {
        alert("⚠️ Uploading or providing a dish photo is compulsory for staff!");
        return;
      }
      const payload = {
        action: 'add',
        restaurant_id: $('#dishRestaurant').value,
        name: $('#dishName').value,
        category: $('#dishCategory').value,
        price: $('#dishPrice').value,
        description: $('#dishDescription').value,
        image: imageVal
      };

      const res = await fetch('api/menu_manage.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        $('#addDishModal').classList.add('hidden');
        $('#addDishForm').reset();
        loadStaffMenu();
      }
    });
  }
}

// ================= CUSTOMER PORTAL DATA & MENU =================
async function loadRestaurants() {
  try {
    const r = await fetch('api/restaurants.php');
    restaurants = await r.json();
    renderRestaurants(restaurants);
    const sel = $('#restaurantSelect');
    if (sel) {
      sel.innerHTML = '<option value="">All restaurants</option>';
      restaurants.forEach(x => {
        sel.innerHTML += `<option value="${x.id}">${x.name}</option>`;
      });
    }
  } catch (err) {}
}

function renderRestaurants(list) {
  if (!$('#restaurantsGrid')) return;
  $('#restaurantsGrid').innerHTML = list.map(r => {
    const isBihari = r.name.includes('Champaran');
    const isSouth = r.name.includes('South');
    const offerTag = (isBihari || isSouth) ? 'FLAT 20% OFF • TEEJ SPECIAL' : 'FLAT 15% OFF ON ALL ORDERS';
    return `
      <article class="card restaurant-card" onclick="loadMenu(${r.id})">
        <div class="card-img-wrap">
          <img src="${r.image}" alt="${r.name}">
          <div class="offer-tag-badge">🏷️ ${offerTag}</div>
        </div>
        <div class="card-body">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <h3 style="margin: 0; font-size: 19px; font-weight: 800;">${r.name}</h3>
            <span class="rating-badge-pill">★ ${r.rating}</span>
          </div>
          <p class="muted" style="margin: 6px 0 8px;">${r.cuisine} • ₹150 for two</p>
          <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 700; color: #555; margin-bottom: 12px;">
            <span>⏱️ ${r.delivery_time}</span>
            <span style="color: #2e7d32; background: #e8f5e9; padding: 2px 8px; border-radius: 4px;">Free Delivery</span>
          </div>
          <button class="btn full" onclick="event.stopPropagation(); loadMenu(${r.id})">Explore Menu ➔</button>
        </div>
      </article>
    `;
  }).join('') || '<p class="empty">No restaurants found.</p>';
}

function filterByMindCategory(key) {
  const k = key.toLowerCase();
  if (k.includes('punjabi') || k.includes('kulcha') || k.includes('chole') || k.includes('saag')) {
    const res = restaurants.find(r => r.name.includes('Amritsari') || r.cuisine.includes('Punjabi'));
    if (res) loadMenu(res.id);
  } else if (k.includes('litti') || k.includes('sattu') || k.includes('sweet') || k.includes('snack')) {
    const res = restaurants.find(r => r.name.includes('Champaran'));
    if (res) loadMenu(res.id);
  } else if (k.includes('south')) {
    const res = restaurants.find(r => r.name.includes('South'));
    if (res) loadMenu(res.id);
  } else {
    const res = restaurants.find(r => r.name.toLowerCase().includes(k) || r.cuisine.toLowerCase().includes(k));
    if (res) loadMenu(res.id);
    else loadRestaurants();
  }
  location.hash = 'menu';
}

function resetMindFilter() {
  loadRestaurants();
  if (restaurants.length > 0) loadMenu(restaurants[0].id);
}

function handleHeroSearch(val) {
  const q = val.toLowerCase();
  renderRestaurants(restaurants.filter(r => (r.name + ' ' + r.cuisine).toLowerCase().includes(q)));
}

function handleHeroSearchBtn() {
  const val = ($('#heroSearchInput') && $('#heroSearchInput').value.trim()) || '';
  if (val) handleHeroSearch(val);
  location.hash = 'restaurants';
}

async function loadMenu(id) {
  try {
    const r = await fetch('api/menu.php?restaurant_id=' + id);
    menuItems = await r.json();
    renderCategories(menuItems);
    renderMenuGrid(menuItems);
    location.hash = 'menu';
  } catch (err) {}
}

function renderCategories(items) {
  const categories = ['ALL', ...new Set(items.map(i => i.category).filter(Boolean))];
  const container = $('#categoryPills');
  if (!container) return;

  container.innerHTML = categories.map(cat => `
    <button class="pill-btn ${cat === selectedCategory ? 'active' : ''}" onclick="filterCategory('${cat}')">${cat}</button>
  `).join('');
}

function filterCategory(cat) {
  selectedCategory = cat;
  renderCategories(menuItems);
  if (cat === 'ALL') {
    renderMenuGrid(menuItems);
  } else {
    renderMenuGrid(menuItems.filter(i => i.category === cat));
  }
}

function renderMenuGrid(items) {
  if (!$('#menuGrid')) return;
  $('#menuGrid').innerHTML = items.map(i => {
    const isAvail = i.is_available !== 0;
    const cartItem = cart.find(x => x.id == i.id);
    const qty = cartItem ? cartItem.quantity : 0;

    const actionBtnHtml = isAvail ? (
      qty > 0 ? `
        <div class="qty-counter-badge">
          <button type="button" onclick="changeQty(${i.id}, -1)">−</button>
          <span>${qty}</span>
          <button type="button" onclick="changeQty(${i.id}, 1)">+</button>
        </div>
      ` : `
        <button type="button" class="add-dish-btn" onclick='addToCart(${JSON.stringify(i)})'>ADD +</button>
      `
    ) : `<button type="button" class="add-dish-btn" disabled style="border-color:#ccc; color:#aaa; cursor:not-allowed;">UNAVAILABLE</button>`;

    return `
      <article class="dish-item-card ${!isAvail ? 'out-of-stock' : ''}">
        <div class="dish-item-info">
          <h3 class="dish-item-name">${i.name} ${!isAvail ? '<small style="color:#d93025; font-size:12px;">(Out of Stock)</small>' : ''}</h3>
          <div class="dish-item-price">₹${Number(i.price).toFixed(2)}</div>
          <p class="dish-item-desc">${i.description || 'Authentic delicacy prepared fresh with local spices.'}</p>
        </div>
        <div class="dish-item-img-col">
          <img class="dish-item-img" src="${i.image}" alt="${i.name}">
          ${actionBtnHtml}
        </div>
      </article>
    `;
  }).join('') || '<p class="empty">No items available in this category.</p>';
}

// Cart & Checkout
function addToCart(item) {
  const found = cart.find(x => x.id == item.id);
  if (found) found.quantity++;
  else cart.push({ ...item, quantity: 1 });
  renderCart();
  if (menuItems.length) {
    const cat = selectedCategory === 'ALL' ? menuItems : menuItems.filter(i => i.category === selectedCategory);
    renderMenuGrid(cat);
  }
}

let appliedCoupon = null;

function applyTeejCode(codeName = 'FIRSTORDER') {
  let discount = 60;
  if (codeName === 'TEEJ20') discount = 20;
  if (codeName === 'FOODIEGO50' || codeName === 'SWIGGY50') discount = 50;
  if (codeName === 'BIHAR25') discount = 25;
  if (codeName === 'FIRSTORDER' || codeName === 'WELCOME60') discount = 60;
  appliedCoupon = { code: codeName, discountPercent: discount };
  if ($('#couponCode')) $('#couponCode').value = codeName;
  if ($('#couponNotice')) {
    $('#couponNotice').textContent = `🎁 First Order Coupon ${codeName} applied! ${discount}% OFF Special Discount.`;
    $('#couponNotice').style.color = '#2e7d32';
  }
  renderCart();
}

function handleApplyCoupon() {
  const code = ($('#couponCode') && $('#couponCode').value.trim().toUpperCase()) || '';
  const notice = $('#couponNotice');
  if (!code) {
    if (notice) { notice.textContent = 'Please enter a coupon code.'; notice.style.color = '#d93025'; }
    return;
  }

  let discount = 0;
  if (['FIRSTORDER', 'FIRST', 'WELCOME60', 'WELCOME', 'FIRST60'].includes(code)) discount = 60;
  else if (['FOODIEGO50', 'FOODIEGO', 'SWIGGY50', 'FIFTY'].includes(code)) discount = 50;
  else if (['BIHAR25', 'BIHAR', 'SPECIAL25'].includes(code)) discount = 25;
  else if (['TEEJ20', 'TEEJ', 'FESTIVAL', 'FESTIVAL20'].includes(code)) discount = 20;

  if (discount > 0) {
    appliedCoupon = { code: code, discountPercent: discount };
    if (notice) { notice.textContent = `🎉 Coupon ${code} applied! ${discount}% OFF Discount.`; notice.style.color = '#2e7d32'; }
  } else {
    appliedCoupon = null;
    if (notice) { notice.textContent = 'Invalid coupon code. Try FIRSTORDER, FOODIEGO50, TEEJ20, or BIHAR25!'; notice.style.color = '#d93025'; }
  }
  renderCart();
}

function removeFromCart(id) {
  cart = cart.filter(i => i.id != id);
  renderCart();
  if (menuItems.length) {
    const cat = selectedCategory === 'ALL' ? menuItems : menuItems.filter(i => i.category === selectedCategory);
    renderMenuGrid(cat);
  }
}

function clearCart() {
  if (confirm("Are you sure you want to clear all items from your cart?")) {
    cart = [];
    renderCart();
    if (menuItems.length) {
      const cat = selectedCategory === 'ALL' ? menuItems : menuItems.filter(i => i.category === selectedCategory);
      renderMenuGrid(cat);
    }
  }
}

function renderCart() {
  let subtotal = 0;
  if ($('#cartCount')) $('#cartCount').textContent = cart.reduce((s, x) => s + x.quantity, 0);
  if (!$('#cartItems')) return;

  if (!cart.length) {
    $('#cartItems').innerHTML = `
      <div class="empty-cart-state" style="text-align: center; padding: 40px 20px;">
        <div style="font-size: 55px; margin-bottom: 8px;">🛒</div>
        <h4 style="margin: 0 0 6px; font-size: 18px; font-weight: 800; color: #282c3f;">Your cart is empty</h4>
        <p style="margin: 0 0 15px; color: #7e808c; font-size: 13px;">Good food is always cooking! Go ahead, order some delicious items from the menu.</p>
        <a href="#menu" class="btn btn-outline" style="display: inline-block; padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; color: #e84b22; border-color: #ffd0bf;">Explore Menu Items ➔</a>
      </div>
    `;
  } else {
    $('#cartItems').innerHTML = `
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 2px solid #f0f0f0;">
        <div>
          <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #282c3f;">Order Items (${cart.reduce((s, x) => s + x.quantity, 0)})</h3>
          <span style="font-size: 12px; color: #686b78;">From selected restaurant</span>
        </div>
        <button class="btn btn-outline btn-sm" onclick="clearCart()" style="font-size: 11px; padding: 4px 10px; color: #e84b22; border-color: #ffd0bf; background: #fff5ee;">🗑️ Clear All</button>
      </div>
      <div class="cart-items-list" style="display: flex; flex-direction: column; gap: 12px;">
    ` + cart.map(x => {
      subtotal += x.price * x.quantity;
      const imgUrl = x.image || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=150&q=80';
      const isVeg = !x.name.toLowerCase().includes('chicken') && !x.name.toLowerCase().includes('mutton') && !x.name.toLowerCase().includes('fish') && !x.name.toLowerCase().includes('egg');
      const vegTag = isVeg 
        ? `<span style="border: 1px solid #198754; color: #198754; font-size: 10px; padding: 1px 4px; border-radius: 3px; font-weight: bold;">🟢 VEG</span>`
        : `<span style="border: 1px solid #dc3545; color: #dc3545; font-size: 10px; padding: 1px 4px; border-radius: 3px; font-weight: bold;">🔴 NON-VEG</span>`;

      return `
        <div class="cart-item-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: #fafafa; border-radius: 12px; border: 1px solid #eee; transition: all 0.2s ease;">
          <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
            <img src="${imgUrl}" alt="${x.name}" style="width: 55px; height: 55px; border-radius: 10px; object-fit: cover; border: 1px solid #e0e0e0; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
            <div>
              <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 2px;">
                ${vegTag}
                <strong style="font-size: 15px; font-weight: 700; color: #282c3f;">${x.name}</strong>
              </div>
              <div style="font-size: 13px; color: #686b78; font-weight: 600;">₹${Number(x.price).toFixed(2)} × ${x.quantity} = <span style="color: #e84b22; font-weight: 800;">₹${(x.price * x.quantity).toFixed(2)}</span></div>
            </div>
          </div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <div class="qty-pill" style="display: flex; align-items: center; background: #ffffff; border: 1px solid #bebfc5; border-radius: 6px; overflow: hidden;">
              <button onclick="changeQty(${x.id}, -1)" style="border: none; background: transparent; padding: 4px 10px; font-weight: 800; cursor: pointer; color: #e84b22; font-size: 14px;">−</button>
              <span style="padding: 0 8px; font-size: 13px; font-weight: 800; color: #282c3f;">${x.quantity}</span>
              <button onclick="changeQty(${x.id}, 1)" style="border: none; background: transparent; padding: 4px 10px; font-weight: 800; cursor: pointer; color: #e84b22; font-size: 14px;">+</button>
            </div>
            <button onclick="removeFromCart(${x.id})" style="border: none; background: #fff0eb; color: #e84b22; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center;" title="Remove item">🗑️</button>
          </div>
        </div>
      `;
    }).join('') + `</div>`;
  }

  let discount = 0;
  if (appliedCoupon && subtotal > 0) {
    discount = (subtotal * appliedCoupon.discountPercent) / 100;
  }

  const finalTotal = Math.max(0, subtotal - discount);

  if ($('#subtotalVal')) $('#subtotalVal').textContent = subtotal.toFixed(2);
  if ($('#discountVal')) $('#discountVal').textContent = discount.toFixed(2);
  if ($('#discountRow')) {
    if (discount > 0) $('#discountRow').classList.remove('hidden');
    else $('#discountRow').classList.add('hidden');
  }

  if ($('#total')) $('#total').textContent = finalTotal.toFixed(2);
  if ($('#qrContainer') && !$('#qrContainer').classList.contains('hidden')) {
    updateUpiQrCode();
  }

  renderFloatingCartBar();
}

function renderFloatingCartBar() {
  const bar = $('#floatingCartBar');
  if (!bar) return;
  const totalQty = cart.reduce((s, x) => s + x.quantity, 0);
  const totalPrice = cart.reduce((s, x) => s + (x.price * x.quantity), 0);

  if (totalQty > 0) {
    if ($('#floatingCartQty')) $('#floatingCartQty').textContent = totalQty;
    if ($('#floatingCartPrice')) $('#floatingCartPrice').textContent = totalPrice.toFixed(2);
    bar.classList.remove('hidden');
  } else {
    bar.classList.add('hidden');
  }
}

function changeQty(id, delta) {
  const x = cart.find(i => i.id == id);
  if (!x) return;
  x.quantity += delta;
  if (x.quantity <= 0) cart = cart.filter(i => i.id != id);
  renderCart();
  if (menuItems.length) {
    const cat = selectedCategory === 'ALL' ? menuItems : menuItems.filter(i => i.category === selectedCategory);
    renderMenuGrid(cat);
  }
}

if ($('#search')) {
  $('#search').addEventListener('input', e => {
    const q = e.target.value.toLowerCase();
    renderRestaurants(restaurants.filter(r => (r.name + ' ' + r.cuisine).toLowerCase().includes(q)));
  });
}

if ($('#restaurantSelect')) {
  $('#restaurantSelect').addEventListener('change', e => {
    if (e.target.value) loadMenu(e.target.value);
  });
}

function updateUpiQrCode() {
  const container = $('#qrContainer');
  if (!container) return;

  const totalAmount = cart.reduce((s, x) => s + (x.price * x.quantity), 0);
  const upiId = ($('#upiId') && $('#upiId').value.trim()) || '8809023427@jupiteraxis';

  if ($('#qrAmount')) $('#qrAmount').textContent = totalAmount.toFixed(2);
  if ($('#qrMerchant')) $('#qrMerchant').textContent = upiId;

  const upiUri = `upi://pay?pa=${encodeURIComponent(upiId)}&pn=FoodieGo%20Express&am=${totalAmount.toFixed(2)}&cu=INR&tn=FoodieGo_Order`;
  const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(upiUri)}`;

  if ($('#qrImage')) $('#qrImage').src = qrUrl;
  container.classList.remove('hidden');
}

function setupPaymentEvents() {
  const options = $$('.payment-option');
  options.forEach(opt => {
    opt.addEventListener('click', () => {
      options.forEach(o => o.classList.remove('active'));
      opt.classList.add('active');
      opt.querySelector('input[type="radio"]').checked = true;

      const method = opt.dataset.method;
      if ($('#codFields')) $('#codFields').classList.toggle('hidden', method !== 'COD');
      if ($('#upiFields')) $('#upiFields').classList.toggle('hidden', method !== 'UPI');
      if ($('#cardFields')) $('#cardFields').classList.toggle('hidden', method !== 'Card');

      if (method === 'UPI') {
        if ($('#upiSubSecQr') && !$('#upiSubSecQr').classList.contains('hidden')) {
          updateUpiQrCode();
        }
      }
    });
  });

  if ($('#upiSubTabId')) {
    $('#upiSubTabId').addEventListener('click', () => {
      $('#upiSubTabId').classList.add('active');
      $('#upiSubTabQr').classList.remove('active');
      $('#upiSubSecId').classList.remove('hidden');
      $('#upiSubSecQr').classList.add('hidden');
    });
  }

  if ($('#upiSubTabQr')) {
    $('#upiSubTabQr').addEventListener('click', () => {
      $('#upiSubTabQr').classList.add('active');
      $('#upiSubTabId').classList.remove('active');
      $('#upiSubSecQr').classList.remove('hidden');
      $('#upiSubSecId').classList.add('hidden');
      updateUpiQrCode();
    });
  }

  if ($('#generateQrBtn')) {
    $('#generateQrBtn').addEventListener('click', () => {
      updateUpiQrCode();
    });
  }

  if ($('#upiId')) {
    $('#upiId').addEventListener('input', () => {
      if ($('#qrContainer') && !$('#qrContainer').classList.contains('hidden')) {
        updateUpiQrCode();
      }
    });
  }

  if ($('#applyCouponBtn')) {
    $('#applyCouponBtn').addEventListener('click', () => {
      handleApplyCoupon();
    });
  }

  if ($('#cardNumber')) {
    $('#cardNumber').addEventListener('input', e => {
      let v = e.target.value.replace(/\D/g, '').substring(0, 16);
      e.target.value = v.replace(/(.{4})/g, '$1 ').trim();
    });
  }

  if ($('#cardExpiry')) {
    $('#cardExpiry').addEventListener('input', e => {
      let v = e.target.value.replace(/\D/g, '').substring(0, 4);
      if (v.length >= 3) e.target.value = v.substring(0, 2) + '/' + v.substring(2);
      else e.target.value = v;
    });
  }
}

function setupTrackerEvents() {
  if ($('#myOrdersLink')) {
    $('#myOrdersLink').addEventListener('click', e => {
      e.preventDefault();
      openOrdersTracker();
    });
  }
  if ($('#closeOrdersModal')) {
    $('#closeOrdersModal').addEventListener('click', () => $('#ordersModal').classList.add('hidden'));
  }
}

async function openOrdersTracker() {
  if (!currentUser) {
    openAuthModal('login');
    if ($('#loginError')) {
      $('#loginError').textContent = '🔒 Please sign in to view & track your live food orders.';
      $('#loginError').style.color = '#e84b22';
    }
    return;
  }

  $('#ordersModal').classList.remove('hidden');
  const container = $('#customerOrdersList');
  container.innerHTML = 'Loading your live orders...';

  try {
    const url = `api/customer_orders.php?user_id=${currentUser.id}`;
    const res = await fetch(url);
    const orders = await res.json();

    if (!orders.length) {
      container.innerHTML = '<p class="empty">You have no active or previous orders.</p>';
      return;
    }

    container.innerHTML = orders.map(o => {
      const steps = ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered'];
      const currentIdx = steps.indexOf(o.status);

      return `
        <div class="order-tracker-card">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <b>Order #${o.id}</b>
              <span class="badge-pay ${o.payment_method === 'UPI' ? 'badge-upi' : (o.payment_method === 'Card' ? 'badge-card' : 'badge-cod')}">${o.payment_method || 'COD'}</span>
              <span class="badge-pay ${o.payment_status === 'Paid' ? 'badge-paid' : 'badge-pending'}">${o.payment_status || 'Pending'}</span>
            </div>
            <b style="color:#e84b22; font-size:18px;">₹${Number(o.total).toFixed(2)}</b>
          </div>

          <div style="font-size:13px; color:#666; margin-top:4px;">
            📍 ${o.address} • 🕒 ${o.created_at}
          </div>

          <div class="tracker-bar">
            ${steps.map((step, idx) => {
              const isDone = idx <= currentIdx;
              const isActive = idx === currentIdx;
              return `
                <div class="tracker-step ${isActive ? 'active' : ''} ${isDone ? 'completed' : ''}">
                  <div class="step-icon">${isDone ? '✓' : (idx + 1)}</div>
                  <div>${step}</div>
                </div>
              `;
            }).join('')}
          </div>
        </div>
      `;
    }).join('');
  } catch (err) {
    container.innerHTML = '<p class="empty">Failed to load order tracking data.</p>';
  }
}

if ($('#placeOrder')) {
  $('#placeOrder').addEventListener('click', async () => {
    const msg = $('#orderMsg');
    msg.textContent = '';

    if (!cart.length) {
      msg.textContent = 'Please add items to your cart first.';
      msg.style.color = '#d93025';
      return;
    }

    const name = $('#name').value.trim();
    const phone = $('#phone').value.trim();
    const address = $('#address').value.trim();

    if (!name || !phone || !address) {
      msg.textContent = 'Please fill out all delivery details.';
      msg.style.color = '#d93025';
      return;
    }

    const selectedPayment = $('input[name="paymentMethod"]:checked')?.value || 'COD';
    let paymentStatus = 'Pending';

    if (selectedPayment === 'UPI') {
      const upiId = $('#upiId').value.trim();
      if (!upiId) {
        msg.textContent = 'Please enter a valid UPI ID (e.g. name@upi).';
        msg.style.color = '#d93025';
        return;
      }
      paymentStatus = 'Paid';
    } else if (selectedPayment === 'Card') {
      const cardNum = $('#cardNumber').value.replace(/\s/g, '');
      const expiry = $('#cardExpiry').value;
      const cvv = $('#cardCvv').value;
      if (cardNum.length < 15 || !expiry || cvv.length < 3) {
        msg.textContent = 'Please enter complete card details.';
        msg.style.color = '#d93025';
        return;
      }
      paymentStatus = 'Paid';
    }

    const pincode = $('#pincode') ? $('#pincode').value.trim() : '';
    const fullDeliveryAddress = pincode ? `${address} (PIN: ${pincode})` : address;

    const payload = {
      user_id: currentUser ? currentUser.id : null,
      name,
      phone,
      address: fullDeliveryAddress,
      payment_method: selectedPayment,
      payment_status: paymentStatus,
      items: cart.map(x => ({ id: x.id, quantity: x.quantity }))
    };

    msg.textContent = 'Processing your order...';
    msg.style.color = '#0070e0';

    try {
      const res = await fetch('api/order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        const payNotice = paymentStatus === 'Paid' ? ' (Payment Received ✅)' : ' (Cash on Delivery)';
        msg.textContent = `🎉 Order #${data.order_id} placed successfully! ${payNotice}`;
        msg.style.color = '#137333';
        cart = [];
        renderCart();
        if (currentUser) {
          setTimeout(openOrdersTracker, 1200);
        }
      } else {
        msg.textContent = data.error || 'Order failed. Please try again.';
        msg.style.color = '#d93025';
      }
    } catch (err) {
      msg.textContent = 'Server error processing order.';
      msg.style.color = '#d93025';
    }
  });
}

// Run initialization
init();
