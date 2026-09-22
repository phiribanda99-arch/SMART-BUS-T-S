// Initial State Database (Will load from LocalStorage if exists)
let state = {
  users: [],
  buses: [],
  routes: [],
  schedules: [],
  events: [],
  bookings: [],
  transactions: [],
  eventRegistrations: [],
  recentActivity: [],
  currentUser: null
};

// Selected items for booking sequence
let currentBookingFlow = {
  schedule: null,
  selectedSeats: [],
  passengers: [],
  paymentMethod: 'MTN MoMo',
  phone: ''
};

const EVENTS_PER_PAGE = 3;
let currentEventPage = 1;
let activeReportPeriod = 'Daily';

// Seed Data if localStorage is empty
const seedData = {
  users: [
    { uid: "USR-001", firstName: "System", lastName: "Administrator", email: "admin@sbts.zm", password: "Admin@123", role: "Admin", status: "Active", bookings: 0, spent: 0, staffId: "ADM-001", memberSince: "Jan 1, 2026" },
    { uid: "USR-002", firstName: "Mwenya", lastName: "Chileshe", email: "mwenya@gmail.com", password: "Password123!", role: "Passenger", status: "Active", bookings: 2, spent: 500, phone: "+260971234567", nationality: "Zambian", passport: "PN123456", memberSince: "Mar 15, 2026" },
    { uid: "USR-003", firstName: "John", lastName: "Banda", email: "john.banda@sbts.zm", password: "Driver123!", role: "Operator", status: "Active", bookings: 0, spent: 0, staffId: "DRV-101", memberSince: "Feb 10, 2026" },
    { uid: "USR-004", firstName: "Grace", lastName: "Chanda", email: "grace.chanda@sbts.zm", password: "Driver123!", role: "Operator", status: "Active", bookings: 0, spent: 0, staffId: "DRV-102", memberSince: "Feb 12, 2026" }
  ],
  buses: [
    { code: "SB-501", reg: "ABC-1234", model: "Scania K360", capacity: 50, status: "Active", operator: "John Banda" },
    { code: "SB-301", reg: "XYZ-5678", model: "Toyota Coaster", capacity: 30, status: "Active", operator: "John Banda" },
    { code: "SB-601", reg: "LMN-9012", model: "Volvo B9R", capacity: 55, status: "Maintenance", operator: "Unassigned" },
    { code: "SB-401", reg: "DEF-3456", model: "Mercedes Benz", capacity: 40, status: "Active", operator: "Grace Chanda" },
    { code: "SB-701", reg: "GHI-7890", model: "Hino Rainbow", capacity: 45, status: "Inactive", operator: "Unassigned" }
  ],
  routes: [
    { code: "LUS-KIT", name: "Lusaka to Kitwe", stations: "Lusaka - Intercity Bus Terminus → Kitwe - Main Bus Station", distance: 360, fare: 150, status: "Active" },
    { code: "LUS-LIV", name: "Lusaka to Livingstone", stations: "Lusaka - Intercity Bus Terminus → Livingstone - Bus Station", distance: 470, fare: 200, status: "Active" },
    { code: "LUS-CHI", name: "Lusaka to Chipata", stations: "Lusaka - Intercity Bus Terminus → Chipata - Bus Station", distance: 550, fare: 180, status: "Active" },
    { code: "KIT-NDL", name: "Kitwe to Ndola", stations: "Kitwe - Main Bus Station → Ndola - Broadway Bus Station", distance: 60, fare: 40, status: "Active" },
    { code: "LUS-MFU", name: "Lusaka to Mfuwe", stations: "Lusaka - Intercity Bus Terminus → Mfuwe - Bus Station", distance: 620, fare: 250, status: "Inactive" }
  ],
  schedules: [
    { id: "SCH-101", routeCode: "LUS-KIT", busCode: "SB-501", driver: "John Banda", time: "08:30", day: "Today", status: "Boarding", bookedSeats: [5, 6, 12, 18, 20] },
    { id: "SCH-102", routeCode: "LUS-LIV", busCode: "SB-401", driver: "Grace Chanda", time: "10:00", day: "Today", status: "On Time", bookedSeats: [1, 2, 3] },
    { id: "SCH-103", routeCode: "LUS-CHI", busCode: "SB-301", driver: "John Banda", time: "13:15", day: "Today", status: "On Time", bookedSeats: [7, 8] },
    { id: "SCH-104", routeCode: "KIT-NDL", busCode: "SB-501", driver: "John Banda", time: "15:30", day: "Today", status: "Delayed", bookedSeats: [] },
    { id: "SCH-105", routeCode: "LUS-MFU", busCode: "SB-701", driver: "Unassigned", time: "06:00", day: "Tomorrow", status: "Cancelled", bookedSeats: [] }
  ],
  events: [
    { id: 'EVT-101', title: 'Lusaka Startup Meetup', description: 'Networking and learning for entrepreneurs in Lusaka.', location: 'Lusaka Convention Hall', date: '2026-08-18', time: '10:00 AM', seats: 120, price: 75.00, status: 'Open', organizer: 'SBTS Events Team', createdAt: '2026-07-01' },
    { id: 'EVT-102', title: 'Zambia Tech Expo', description: 'Showcasing the latest transportation technology and smart systems.', location: 'Naparima Hall', date: '2026-09-05', time: '09:00 AM', seats: 200, price: 120.00, status: 'Open', organizer: 'SBTS Events Team', createdAt: '2026-07-02' },
    { id: 'EVT-103', title: 'Green Travel Conference', description: 'Travel sustainability and electrification panel discussions.', location: 'UNZA Conference Center', date: '2026-09-20', time: '11:00 AM', seats: 150, price: 95.00, status: 'Open', organizer: 'SBTS Events Team', createdAt: '2026-07-05' },
    { id: 'EVT-104', title: 'Transport Analytics Workshop', description: 'Hands-on workshop for building dashboards and monitoring registrations.', location: 'Intercity Training Center', date: '2026-10-12', time: '08:30 AM', seats: 80, price: 60.00, status: 'Open', organizer: 'SBTS Analytics', createdAt: '2026-07-10' }
  ],
  eventRegistrations: [
    { eventId: 'EVT-101', userId: 'USR-002', ticketCode: 'TCKT-101-7890', registeredAt: '2026-07-12 09:25', reminderSent: false },
    { eventId: 'EVT-102', userId: 'USR-002', ticketCode: 'TCKT-102-7512', registeredAt: '2026-07-14 14:18', reminderSent: false }
  ],
  bookings: [
    { ref: "SBTS-BK-9831", passengerName: "Mwenya Chileshe", routeName: "Lusaka to Kitwe", seats: "5, 6", amount: 300, status: "Confirmed", date: "2026-06-22", phone: "+260971234567" },
    { ref: "SBTS-BK-8422", passengerName: "Mwenya Chileshe", routeName: "Lusaka to Livingstone", seats: "2", amount: 200, status: "Completed", date: "2026-06-15", phone: "+260971234567" }
  ],
  transactions: [
    { txId: "TXN-9831-MOMO", bookingRef: "SBTS-BK-9831", passengerName: "Mwenya Chileshe", method: "MTN MoMo", amount: 300, status: "Success", timestamp: "2026-06-22 08:12" },
    { txId: "TXN-8422-AIRTEL", bookingRef: "SBTS-BK-8422", passengerName: "Mwenya Chileshe", method: "Airtel Money", amount: 200, status: "Success", timestamp: "2026-06-15 09:30" }
  ],
  recentActivity: [
    { desc: "New ticket booking SBTS-BK-9831 confirmed", user: "Mwenya Chileshe", time: "15 mins ago", status: "success" },
    { desc: "Bus SB-601 marked under maintenance", user: "admin@sbts.zm", time: "1 hour ago", status: "warning" },
    { desc: "New bus departure SCH-103 scheduled to Kitwe", user: "admin@sbts.zm", time: "3 hours ago", status: "info" }
  ]
};

// System Startup Initializer
window.onload = async function() {
  await initAppDatabase();
  startRealTimeClock();
  
  // Auto-search date picker initialization to today
  const dateInput = document.getElementById('book-search-date');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.value = today;
    dateInput.min = today;
  }
  
  // If there is an active session, auto-login (otherwise show login screen)
  const cachedUser = localStorage.getItem('sbts_session');
  if (cachedUser) {
    const u = JSON.parse(cachedUser);
    if (u.role === 'Admin') {
      window.location.href = 'admin.html';
      return;
    }
    state.currentUser = u;
    loadDashboardView();
  }
  
  // Auto-updates board random departures statuses periodically for realism
  setInterval(simulateSBTSBoardUpdates, 30000);
};

// MySQL (WampServer) & LocalStorage State Operations
async function initAppDatabase() {
  if (window.location.protocol.startsWith('http')) {
    try {
      const response = await fetch('api.php?action=get_state');
      const dbState = await response.json();
      if (dbState && dbState.users) {
        state.users = dbState.users;
        state.buses = dbState.buses;
        state.routes = dbState.routes;
        state.schedules = dbState.schedules;
        state.events = dbState.events || [];
        state.bookings = dbState.bookings;
        state.transactions = dbState.transactions;
        state.eventRegistrations = dbState.eventRegistrations || [];
        state.recentActivity = dbState.recentActivity;
        
        // Sync to local cache
        localStorage.setItem('sbts_users', JSON.stringify(state.users));
        localStorage.setItem('sbts_buses', JSON.stringify(state.buses));
        localStorage.setItem('sbts_routes', JSON.stringify(state.routes));
        localStorage.setItem('sbts_schedules', JSON.stringify(state.schedules));
        localStorage.setItem('sbts_bookings', JSON.stringify(state.bookings));
        localStorage.setItem('sbts_transactions', JSON.stringify(state.transactions));
        localStorage.setItem('sbts_activities', JSON.stringify(state.recentActivity));
        return;
      }
    } catch (e) {
      console.error('MySQL database sync failed. Falling back to local cache.', e);
    }
  }
  
  // LocalStorage Fallback
  if (!localStorage.getItem('sbts_users')) {
    localStorage.setItem('sbts_users', JSON.stringify(seedData.users));
    localStorage.setItem('sbts_buses', JSON.stringify(seedData.buses));
    localStorage.setItem('sbts_routes', JSON.stringify(seedData.routes));
    localStorage.setItem('sbts_schedules', JSON.stringify(seedData.schedules));
    localStorage.setItem('sbts_events', JSON.stringify(seedData.events));
    localStorage.setItem('sbts_eventRegistrations', JSON.stringify(seedData.eventRegistrations));
    localStorage.setItem('sbts_bookings', JSON.stringify(seedData.bookings));
    localStorage.setItem('sbts_transactions', JSON.stringify(seedData.transactions));
    localStorage.setItem('sbts_activities', JSON.stringify(seedData.recentActivity));
  }
  
  state.users = JSON.parse(localStorage.getItem('sbts_users'));
  state.buses = JSON.parse(localStorage.getItem('sbts_buses'));
  state.routes = JSON.parse(localStorage.getItem('sbts_routes'));
  state.schedules = JSON.parse(localStorage.getItem('sbts_schedules'));
  state.events = JSON.parse(localStorage.getItem('sbts_events')) || seedData.events;
  state.eventRegistrations = JSON.parse(localStorage.getItem('sbts_eventRegistrations')) || [];
  state.bookings = JSON.parse(localStorage.getItem('sbts_bookings'));
  state.transactions = JSON.parse(localStorage.getItem('sbts_transactions'));
  state.recentActivity = JSON.parse(localStorage.getItem('sbts_activities'));
}

function saveState(key) {
  localStorage.setItem('sbts_' + key, JSON.stringify(state[key]));
  
  // If served via Apache/PHP in WampServer, sync to MySQL database in background
  if (window.location.protocol.startsWith('http')) {
    fetch('api.php?action=save_state', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ key: key, data: state[key] })
    }).catch(err => console.error('Failed to sync changes to MySQL:', err));
  }
}

function shouldHideActivityUser(userName = '') {
  const normalized = String(userName).toLowerCase();
  return normalized.includes('selfuser@example.com') || normalized.includes('self user');
}

function appendActivity(desc, user, status = 'info') {
  if (shouldHideActivityUser(user)) {
    return;
  }

  state.recentActivity.unshift({
    desc: desc,
    user: user,
    time: 'Just now',
    status: status
  });
  saveState('recentActivity');
  renderActivityFeed();
}

// ====================================================
// AUTH LOGIC
// ====================================================
function switchAuthForm(formType) {
  document.getElementById('login-form').classList.toggle('active', formType === 'login');
  document.getElementById('register-form').classList.toggle('active', formType === 'register');
  document.getElementById('toggle-login-btn').classList.toggle('active', formType === 'login');
  document.getElementById('toggle-register-btn').classList.toggle('active', formType === 'register');
  
  const tip = document.getElementById('auth-footer-tip');
  if (formType === 'login') {
    tip.innerHTML = `Don't have an account? <a href="#" onclick="switchAuthForm('register')">Register Now</a>`;
  } else {
    tip.innerHTML = `Already have an account? <a href="#" onclick="switchAuthForm('login')">Sign In</a>`;
  }
}

function toggleRegFields(type) {
  const isPassenger = (type === 'Passenger');
  document.getElementById('passenger-fields').style.display = isPassenger ? 'block' : 'none';
  document.getElementById('admin-fields').style.display = isPassenger ? 'none' : 'block';
  document.getElementById('lbl-passenger').classList.toggle('selected', isPassenger);
  document.getElementById('lbl-admin').classList.toggle('selected', !isPassenger);
  
  // Update inputs requirement
  document.getElementById('reg-phone').required = isPassenger;
  document.getElementById('reg-passport').required = isPassenger;
  document.getElementById('reg-staffid').required = !isPassenger;
}

function isStrongPassword(password) {
  return typeof password === 'string' &&
    password.length >= 6 &&
    /[a-z]/.test(password) &&
    /[A-Z]/.test(password) &&
    /\d/.test(password) &&
    /[^A-Za-z0-9]/.test(password);
}

function checkPasswordStrength(password) {
  const fill = document.getElementById('strength-bar-fill');
  const text = document.getElementById('strength-text');
  fill.className = 'password-strength-fill';
  
  if (!password) {
    text.innerText = 'Strength: Empty';
    return;
  }

  const hasLength = password.length >= 6;
  const hasLower = /[a-z]/.test(password);
  const hasUpper = /[A-Z]/.test(password);
  const hasNumber = /\d/.test(password);
  const hasSymbol = /[^A-Za-z0-9]/.test(password);
  const mixCount = [hasLower, hasUpper, hasNumber, hasSymbol].filter(Boolean).length;

  if (!hasLength || mixCount < 3) {
    fill.classList.add('strength-weak');
    text.innerText = 'Strength: Weak';
    text.style.color = '#ef4444';
    return;
  }

  if (mixCount === 3 || (mixCount === 4 && password.length < 10)) {
    fill.classList.add('strength-medium');
    text.innerText = 'Strength: Medium';
    text.style.color = '#f59e0b';
    return;
  }

  fill.classList.add('strength-strong');
  text.innerText = 'Strength: Strong';
  text.style.color = '#22c55e';
}

function handleLogin(e) {
  e.preventDefault();
  const email = document.getElementById('login-email').value.trim();
  const pass = document.getElementById('login-password').value;

  const user = state.users.find(u => u.email.toLowerCase() === email.toLowerCase());

  if (!user || user.password !== pass) {
    showNotification('Invalid email or password combination.', 'danger');
    return;
  }
  if (user.status !== 'Active') {
    showNotification('This account is currently marked ' + user.status + '. Contact Support.', 'warning');
    return;
  }

  state.currentUser = user;
  localStorage.setItem('sbts_session', JSON.stringify(user));
  appendActivity(`User ${user.email} signed in successfully`, user.firstName + " " + user.lastName, 'success');
  
  if (user.role === 'Admin') {
    showNotification(`Welcome back, Admin! Redirecting to Administration...`, 'success');
    setTimeout(() => {
      window.location.href = 'admin.html';
    }, 1000);
  } else {
    showNotification(`Welcome back, ${user.firstName}!`, 'success');
    loadDashboardView();
  }
}

function handleRegister(e) {
  e.preventDefault();
  const role = document.querySelector('input[name="account-type"]:checked').value;
  const first = document.getElementById('reg-firstname').value.trim();
  const last = document.getElementById('reg-lastname').value.trim();
  const email = document.getElementById('reg-email').value.trim();
  const pass = document.getElementById('reg-password').value;
  const confirmPass = document.getElementById('reg-confirm-password').value;

  if (!email) {
    showNotification('Please enter your email address.', 'warning');
    return;
  }

  if (pass !== confirmPass) {
    showNotification('Passwords do not match.', 'danger');
    return;
  }

  if (!isStrongPassword(pass)) {
    showNotification('Password must be at least 6 characters and include uppercase, lowercase, number, and special character.', 'warning');
    return;
  }

  if (state.users.some(u => u.email.toLowerCase() === email.toLowerCase())) {
    showNotification('An account with this email already exists.', 'warning');
    return;
  }

  let newUser = {
    uid: "USR-" + Math.floor(100 + Math.random() * 900),
    firstName: first,
    lastName: last,
    email: email,
    password: pass,
    role: role,
    status: 'Active', // Set to Active by default for immediate login and booking access
    bookings: 0,
    spent: 0,
    memberSince: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
  };

  if (role === 'Passenger') {
    newUser.phone = document.getElementById('reg-phone').value.trim();
    newUser.nationality = document.getElementById('reg-nationality').value;
    newUser.passport = document.getElementById('reg-passport').value.trim();
  } else {
    newUser.staffId = document.getElementById('reg-staffid').value.trim();
  }

  state.users.push(newUser);
  saveState('users');
  
  showNotification('Registration successful! Please login to your account.', 'success');
  switchAuthForm('login');
  
  appendActivity(`New user ${email} registered as ${role}`, first + " " + last, 'info');
  
  // Reset form
  document.getElementById('register-form').reset();
}

function showForgotPassword(e) {
  e.preventDefault();
  showNotification("Password reset request sent to network. Contact helpdesk support@sbts.zm", "info");
}

function performLogout() {
  localStorage.removeItem('sbts_session');
  appendActivity(`User logged out`, state.currentUser.firstName + " " + state.currentUser.lastName, 'info');
  state.currentUser = null;
  
  document.getElementById('app-view').classList.remove('active');
  document.getElementById('auth-view').classList.add('active');
  showNotification('Logged out successfully.', 'info');
}

// ====================================================
// VIEW SWITCHER & CLOCK
// ====================================================
function startRealTimeClock() {
  setInterval(() => {
    const timeString = new Date().toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' });
    const clock = document.getElementById('header-clock');
    if (clock) clock.innerText = timeString;
  }, 1000);
}

function toggleSidebar() {
  document.getElementById('app-sidebar').classList.toggle('open');
}

function switchTab(tabId) {
  // Toggle sidebar button styles
  document.querySelectorAll('.nav-item-btn').forEach(btn => {
    btn.classList.toggle('active', btn.id === 'tab-' + tabId);
  });

  // Toggle content panel visibility
  document.querySelectorAll('.content-container > .view-section').forEach(section => {
    section.classList.toggle('active', section.id === 'panel-' + tabId);
  });

  // Collapse sidebar drawer on mobile after clicking
  document.getElementById('app-sidebar').classList.remove('open');

  // Refresh corresponding module tables/data
  if (tabId === 'overview') {
    renderDashboardStats();
    renderSBTSBoard();
    renderActivityFeed();
  } else if (tabId === 'buses') {
    renderBusesTable();
  } else if (tabId === 'routes') {
    renderRoutesTable();
    renderSchedulesTable();
  } else if (tabId === 'booking') {
    renderBookingsTable();
  } else if (tabId === 'events') {
    renderEventsTable();
  } else if (tabId === 'payments') {
    renderPaymentsTable();
  } else if (tabId === 'reports') {
    renderReportsDashboard();
  }
}

function loadDashboardView() {
  // Hide Auth, Show Main App
  document.getElementById('auth-view').classList.remove('active');
  document.getElementById('app-view').classList.add('active');
  
  // Update User details in Profile Summaries & Headers
  const u = state.currentUser;
  
  // Header details
  document.getElementById('header-greeting').innerHTML = `Welcome, <span>${u.firstName} ${u.lastName}</span>`;
  
  // Mini Profile (Sidebar Footer)
  document.getElementById('mini-avatar').innerText = u.firstName.charAt(0) + u.lastName.charAt(0);
  document.getElementById('mini-name').innerText = `${u.firstName} ${u.lastName}`;
  document.getElementById('mini-role').innerText = u.role;
  
  // Profile Widget Card (Overview)
  document.getElementById('profile-avatar').innerText = u.firstName.charAt(0) + u.lastName.charAt(0);
  document.getElementById('profile-full-name').innerText = `${u.firstName} ${u.lastName}`;
  document.getElementById('profile-uid').innerText = u.uid;
  
  const badge = document.getElementById('profile-role-badge');
  badge.className = 'badge';
  badge.classList.add(u.role === 'Admin' ? 'badge-admin' : (u.role === 'Operator' ? 'badge-operator' : 'badge-passenger'));
  badge.innerText = u.role;
  
  document.getElementById('profile-status').innerText = u.status;
  document.getElementById('profile-joined').innerText = u.memberSince;
  document.getElementById('profile-total-bookings').innerText = u.bookings || 0;
  document.getElementById('profile-total-spent').innerText = `K${(u.spent || 0).toFixed(2)}`;

  setRoleBasedVisibility();
  renderEventsTable();
  renderMyRegistrations();
  renderActivityFeed();

  // Switch to Overview Tab initially
  switchTab('overview');
}

function setRoleBasedVisibility() {
  const isAdmin = state.currentUser && state.currentUser.role === 'Admin';
  const isPassenger = state.currentUser && state.currentUser.role === 'Passenger';

  document.querySelectorAll('.admin-only').forEach(el => {
    el.classList.toggle('hidden', !isAdmin);
  });

  document.querySelectorAll('.event-module').forEach(el => {
    el.classList.toggle('hidden', !isAdmin);
  });

  document.querySelectorAll('.booking-module').forEach(el => {
    el.classList.toggle('hidden', isAdmin);
  });

  const bookTicketAction = document.getElementById('action-book-ticket');
  if (bookTicketAction) {
    bookTicketAction.style.display = isAdmin ? 'none' : 'flex';
  }

  if (!isAdmin && document.querySelector('.content-container > .view-section.active.admin-only, .content-container > .view-section.active.event-module')) {
    switchTab('overview');
  }

  if (isAdmin && document.querySelector('.content-container > .view-section.active.booking-module')) {
    switchTab('overview');
  }

  if (isPassenger && document.querySelector('.content-container > .view-section.active.admin-only')) {
    switchTab('overview');
  }
}

function renderEventsTable(page = 1) {
  currentEventPage = page;
  const query = document.getElementById('events-search-input')?.value.trim().toLowerCase() || '';
  const filteredEvents = state.events.filter(ev => {
    const text = `${ev.title} ${ev.description} ${ev.location}`.toLowerCase();
    return text.includes(query);
  });

  const totalPages = Math.max(1, Math.ceil(filteredEvents.length / EVENTS_PER_PAGE));
  if (page > totalPages) page = totalPages;
  const start = (page - 1) * EVENTS_PER_PAGE;
  const pageItems = filteredEvents.slice(start, start + EVENTS_PER_PAGE);

  const tbody = document.getElementById('events-table-body');
  if (!tbody) return;
  tbody.innerHTML = '';

  pageItems.forEach(ev => {
    const registrations = state.eventRegistrations.filter(reg => reg.eventId === ev.id);
    const available = Math.max(0, ev.seats - registrations.length);
    let actionCell = `<span class="badge badge-${ev.status === 'Open' ? 'confirmed' : 'inactive'}">${ev.status}</span>`;

    if (ev.status === 'Open' && available > 0) {
      if (state.currentUser?.role === 'Passenger') {
        const already = registrations.some(reg => reg.userId === state.currentUser.uid);
        actionCell = already
          ? '<span class="badge badge-confirmed">Registered</span>'
          : `<button class="btn btn-primary" onclick="registerEvent('${ev.id}')">Register</button>`;
      } else if (state.currentUser?.role === 'Admin') {
        actionCell = `<button class="btn btn-secondary" onclick="openEventPassengerList('${ev.id}')">View Attendees</button>`;
      }
    }

    tbody.innerHTML += `
      <tr>
        <td style="font-weight:600;">${ev.title}</td>
        <td>${ev.location}</td>
        <td>${ev.date}</td>
        <td>${ev.time}</td>
        <td>${available} / ${ev.seats}</td>
        <td>K${ev.price.toFixed(2)}</td>
        <td>${ev.status}</td>
        <td>${actionCell}</td>
      </tr>
    `;
  });

  const pagination = document.getElementById('events-pagination');
  if (pagination) {
    pagination.innerHTML = '';
    for (let idx = 1; idx <= totalPages; idx++) {
      pagination.innerHTML += `<button class="btn btn-secondary" style="padding:0.45rem 0.75rem;" onclick="renderEventsTable(${idx})">${idx}</button>`;
    }
  }

  renderMyRegistrations();
  renderAdminEventsTable();
}

function renderMyRegistrations() {
  const tbody = document.getElementById('my-registrations-table-body');
  if (!tbody) return;
  tbody.innerHTML = '';

  if (!state.currentUser) {
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--text-muted);">Please sign in to view registrations.</td></tr>`;
    return;
  }

  const registrations = state.eventRegistrations.filter(reg => reg.userId === state.currentUser.uid);
  if (!registrations.length) {
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--text-muted);">You have no event registrations yet.</td></tr>`;
    return;
  }

  registrations.forEach(reg => {
    const ev = state.events.find(event => event.id === reg.eventId) || {};
    tbody.innerHTML += `
      <tr>
        <td style="font-family:'Share Tech Mono', monospace; font-weight:700;">${reg.ticketCode}</td>
        <td>${ev.title || 'Unknown Event'}</td>
        <td>${ev.date || 'N/A'}</td>
        <td>${ev.status === 'Open' ? 'Confirmed' : ev.status}</td>
        <td><span class="badge badge-confirmed">Ticket Sent</span></td>
      </tr>
    `;
  });
}

function renderAdminEventsTable() {
  const tbody = document.getElementById('admin-events-table-body');
  if (!tbody) return;
  tbody.innerHTML = '';

  state.events.forEach(ev => {
    const registrations = state.eventRegistrations.filter(reg => reg.eventId === ev.id);
    tbody.innerHTML += `
      <tr>
        <td style="font-weight:600;">${ev.title}</td>
        <td>${ev.date}</td>
        <td>${ev.seats}</td>
        <td>${registrations.length}</td>
        <td>${ev.status}</td>
        <td>
          <button class="btn btn-dark" onclick="openEventPassengerList('${ev.id}')">View Passengers</button>
          <button class="btn btn-secondary" onclick="exportEventPassengers('${ev.id}')">Export</button>
        </td>
      </tr>
    `;
  });
}

function openAddEventModal() {
  document.getElementById('add-event-modal').classList.add('active');
}

function saveNewEvent(e) {
  e.preventDefault();
  const title = document.getElementById('ev-title').value.trim();
  const location = document.getElementById('ev-location').value.trim();
  const date = document.getElementById('ev-date').value;
  const time = document.getElementById('ev-time').value;
  const seats = parseInt(document.getElementById('ev-seats').value, 10);
  const price = parseFloat(document.getElementById('ev-price').value);
  const description = document.getElementById('ev-description').value.trim();
  const status = document.getElementById('ev-status').value;

  if (state.events.some(ev => ev.title.toLowerCase() === title.toLowerCase() && ev.date === date && ev.time === time)) {
    showNotification('A matching event already exists for that time and title.', 'warning');
    return;
  }

  const eventId = 'EVT-' + Math.floor(100 + Math.random() * 900);
  state.events.unshift({
    id: eventId,
    title,
    location,
    date,
    time,
    seats,
    price,
    description,
    status,
    organizer: 'SBTS Events Team',
    createdAt: new Date().toISOString().split('T')[0]
  });
  saveState('events');
  closeModal('add-event-modal');
  renderEventsTable(currentEventPage);
  showNotification('Event published successfully.', 'success');
  appendActivity(`Admin published event ${title}`, state.currentUser.email, 'success');
  e.target.reset();
}

function registerEvent(eventId) {
  if (!state.currentUser || state.currentUser.role !== 'Passenger') {
    showNotification('Only passengers can register for events.', 'warning');
    return;
  }

  const ev = state.events.find(item => item.id === eventId);
  if (!ev) {
    showNotification('Event not found.', 'danger');
    return;
  }
  if (ev.status !== 'Open') {
    showNotification('This event is currently not open for registrations.', 'warning');
    return;
  }

  const alreadyRegistered = state.eventRegistrations.some(reg => reg.eventId === eventId && reg.userId === state.currentUser.uid);
  if (alreadyRegistered) {
    showNotification('You already registered for this event.', 'warning');
    return;
  }

  const registrationCount = state.eventRegistrations.filter(reg => reg.eventId === eventId).length;
  if (registrationCount >= ev.seats) {
    showNotification('Event is fully booked.', 'danger');
    return;
  }

  const ticketCode = `TKT-${eventId}-${Math.floor(1000 + Math.random() * 9000)}`;
  state.eventRegistrations.push({
    eventId,
    userId: state.currentUser.uid,
    ticketCode,
    registeredAt: new Date().toISOString().replace('T', ' ').slice(0, 16),
    reminderSent: false
  });
  saveState('eventRegistrations');
  renderEventsTable(currentEventPage);
  renderMyRegistrations();
  showNotification(`Registered successfully. Ticket code ${ticketCode} was emailed to you.`, 'success');
  appendActivity(`User ${state.currentUser.email} registered for ${ev.title}`, state.currentUser.email, 'info');
  sendUpcomingEventReminders();
}

function openEventPassengerList(eventId) {
  const ev = state.events.find(item => item.id === eventId);
  if (!ev) return;
  const registrations = state.eventRegistrations.filter(reg => reg.eventId === eventId);
  const tbody = document.getElementById('event-passengers-list-body');
  tbody.innerHTML = '';

  if (!registrations.length) {
    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:var(--text-muted);">No attendees registered yet.</td></tr>`;
  } else {
    registrations.forEach(reg => {
      const user = state.users.find(u => u.uid === reg.userId) || { firstName: 'Unknown', lastName: '', email: '' };
      tbody.innerHTML += `
        <tr>
          <td>${reg.ticketCode}</td>
          <td>${user.firstName} ${user.lastName}</td>
          <td>${user.email}</td>
          <td>${reg.registeredAt}</td>
        </tr>
      `;
    });
  }

  document.getElementById('event-passengers-modal-title').innerText = `Attendees for ${ev.title}`;
  document.getElementById('event-passengers-modal').classList.add('active');
}

function exportEventPassengers(eventId) {
  const ev = state.events.find(item => item.id === eventId);
  if (!ev) return;
  const registrations = state.eventRegistrations.filter(reg => reg.eventId === eventId);
  if (!registrations.length) {
    showNotification('No registrations available for export.', 'warning');
    return;
  }

  const csvRows = [['Ticket Code', 'Passenger Name', 'Email', 'Registered At']];
  registrations.forEach(reg => {
    const user = state.users.find(u => u.uid === reg.userId) || { firstName: 'Unknown', lastName: '', email: '' };
    csvRows.push([reg.ticketCode, `${user.firstName} ${user.lastName}`, user.email, reg.registeredAt]);
  });
  const csv = csvRows.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = `${ev.title.replace(/\s+/g, '_')}_passengers.csv`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  showNotification('Passenger list exported successfully.', 'success');
}

function sendUpcomingEventReminders() {
  const now = new Date();
  state.eventRegistrations.forEach(reg => {
    const ev = state.events.find(item => item.id === reg.eventId);
    if (!ev || reg.reminderSent || ev.status !== 'Open') return;
    const eventDate = new Date(`${ev.date}T${ev.time}`);
    const diffDays = Math.ceil((eventDate - now) / (1000 * 60 * 60 * 24));
    if (diffDays <= 2 && diffDays >= 0) {
      reg.reminderSent = true;
      appendActivity(`Reminder email sent for ${ev.title}`, state.currentUser.email, 'info');
    }
  });
  saveState('eventRegistrations');
}

// ====================================================
// DASHBOARD OVERVIEW DATA RENDERING
// ====================================================
function renderDashboardStats() {
  const margaretBookings = state.bookings.filter(b =>
    (b.passengerName || '').toLowerCase().includes('mwenya') ||
    (b.passengerName || '').toLowerCase().includes('margaret')
  ).length;

  const statCard = document.getElementById('stat-margaret-bookings');
  if (statCard) {
    statCard.innerText = margaretBookings;
  }
}

function renderSBTSBoard() {
  const tbody = document.getElementById('sbts-board-rows');
  if (!tbody) return;
  tbody.innerHTML = '';

  state.schedules.slice(0, 5).forEach(sched => {
    const route = state.routes.find(r => r.code === sched.routeCode);
    const routeName = route ? route.name : sched.routeCode;
    
    let badgeColor = 'badge-active';
    if (sched.status === 'Cancelled') badgeColor = 'badge-inactive';
    if (sched.status === 'Boarding') badgeColor = 'badge-confirmed';
    if (sched.status === 'Departed') badgeColor = 'badge-completed';
    
    const capacity = state.buses.find(b => b.code === sched.busCode)?.capacity || 40;
    const availableSeats = capacity - (sched.bookedSeats?.length || 0);

    tbody.innerHTML += `
      <tr>
        <td style="font-weight:700; color:var(--primary-orange);">${sched.busCode}</td>
        <td style="font-weight:600;">${routeName}</td>
        <td style="font-family:'Share Tech Mono', monospace; font-weight:700; color:#fff;">${sched.time}</td>
        <td><span class="badge ${badgeColor}">${sched.status}</span></td>
        <td><span style="color:#4ade80; font-weight:600;">${availableSeats} left</span></td>
      </tr>
    `;
  });
}

function simulateSBTSBoardUpdates() {
  if (state.schedules.length === 0) return;
  const randomIndex = Math.floor(Math.random() * state.schedules.length);
  const statuses = ['On Time', 'Boarding', 'Delayed', 'Departed'];
  const nextStatus = statuses[Math.floor(Math.random() * statuses.length)];
  
  state.schedules[randomIndex].status = nextStatus;
  saveState('schedules');
  
  if (document.getElementById('panel-overview').classList.contains('active')) {
    renderSBTSBoard();
  }
}

function renderActivityFeed() {
  const feed = document.getElementById('dashboard-activity-feed');
  if (!feed) return;

  const visibleActivities = (state.recentActivity || []).filter(act => !shouldHideActivityUser(act.user));
  feed.innerHTML = '';
  
  visibleActivities.slice(0, 6).forEach(act => {
    feed.innerHTML += `
      <div class="activity-item">
        <div class="activity-indicator ${act.status}"></div>
        <div class="activity-details">
          <div class="activity-desc">${act.desc}</div>
          <div class="activity-meta">
            <span>By: ${act.user}</span>
            <span>${act.time}</span>
          </div>
        </div>
      </div>
    `;
  });
}

// ====================================================
// MODULE 2: BUS MANAGEMENT
// ====================================================
function renderBusesTable() {
  const tbody = document.getElementById('buses-table-body');
  tbody.innerHTML = '';

  state.buses.forEach(b => {
    let statusBadge = `<span class="badge badge-inactive">Inactive</span>`;
    if (b.status === 'Active') statusBadge = `<span class="badge badge-active">Active</span>`;
    if (b.status === 'Maintenance') statusBadge = `<span class="badge badge-maintenance">Maintenance</span>`;

    const hasActions = state.currentUser.role === 'Admin';

    tbody.innerHTML += `
      <tr>
        <td style="font-weight: 700; color: var(--primary-orange);">${b.code}</td>
        <td>${b.reg}</td>
        <td>${b.model}</td>
        <td>${b.capacity} Seats</td>
        <td>K${Number(b.amount || 0).toFixed(2)}</td>
        <td>${statusBadge}</td>
        <td>${b.operator}</td>
        <td>
          ${hasActions ? `
            <button class="btn btn-dark" style="padding:0.25rem 0.5rem; font-size:0.75rem; text-transform:none;" onclick="changeBusStatus('${b.code}')">Status</button>
            <button class="btn btn-danger" style="padding:0.25rem 0.5rem; font-size:0.75rem; text-transform:none;" onclick="deleteBus('${b.code}')">Delete</button>
          ` : `<span>--</span>`}
        </td>
      </tr>
    `;
  });
}

function filterBusesTable() {
  const query = document.getElementById('buses-search-input').value.toLowerCase();
  const statusFilter = document.getElementById('buses-filter-status').value;
  const rows = document.querySelectorAll('#buses-table-body tr');

  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    const matchesQuery = text.includes(query);
    const matchesStatus = statusFilter === 'All' || text.includes(statusFilter.toLowerCase());
    
    row.style.display = (matchesQuery && matchesStatus) ? '' : 'none';
  });
}

function changeBusStatus(code) {
  const bus = state.buses.find(b => b.code === code);
  if (bus) {
    const next = bus.status === 'Active' ? 'Maintenance' : (bus.status === 'Maintenance' ? 'Inactive' : 'Active');
    bus.status = next;
    saveState('buses');
    renderBusesTable();
    showNotification(`Fleet code ${code} set to ${next}.`, 'success');
    appendActivity(`Fleet bus ${code} set to ${next}`, state.currentUser.email, 'warning');
  }
}

function deleteBus(code) {
  if (confirm('Delete bus ' + code + ' from fleet database?')) {
    const idx = state.buses.findIndex(b => b.code === code);
    if (idx !== -1) {
      state.buses.splice(idx, 1);
      saveState('buses');
      renderBusesTable();
      showNotification('Bus deleted successfully.', 'success');
      appendActivity(`Deleted bus ${code} from fleet database`, state.currentUser.email, 'danger');
    }
  }
}

function openAddBusModal() {
  const opSelect = document.getElementById('mb-operator');
  opSelect.innerHTML = '<option value="Unassigned">Unassigned</option>';
  
  // Find all drivers/operators in DB
  state.users.filter(u => u.role === 'Operator').forEach(driver => {
    const name = `${driver.firstName} ${driver.lastName}`;
    opSelect.innerHTML += `<option value="${name}">${name} (${driver.staffId})</option>`;
  });

  document.getElementById('add-bus-modal').classList.add('active');
}

function saveNewBus(e) {
  e.preventDefault();
  const code = document.getElementById('mb-code').value.trim();
  const reg = document.getElementById('mb-reg').value.trim();
  const model = document.getElementById('mb-model').value.trim();
  const capacity = parseInt(document.getElementById('mb-capacity').value);
  const amount = parseFloat(document.getElementById('mb-amount').value || 0);
  const status = document.getElementById('mb-status').value;
  const operator = document.getElementById('mb-operator').value;

  if (state.buses.some(b => b.code.toUpperCase() === code.toUpperCase())) {
    showNotification('Bus Fleet code already exists.', 'warning');
    return;
  }

  state.buses.push({
    code: code.toUpperCase(),
    reg: reg.toUpperCase(),
    model: model,
    capacity: capacity,
    amount: isNaN(amount) ? 0 : amount,
    status: status,
    operator: operator
  });
  saveState('buses');
  closeModal('add-bus-modal');
  renderBusesTable();
  showNotification(`Bus ${code} added successfully to fleet.`, 'success');
  appendActivity(`Added new fleet vehicle ${code}`, state.currentUser.email, 'info');

  e.target.reset();
}

// ====================================================
// MODULE 3: ROUTE & SCHEDULES
// ====================================================
function switchRouteSubtab(sub) {
  document.getElementById('btn-routes-subtab').classList.toggle('active', sub === 'routes');
  document.getElementById('btn-schedules-subtab').classList.toggle('active', sub === 'schedules');
  document.getElementById('subtab-routes').style.display = sub === 'routes' ? 'block' : 'none';
  document.getElementById('subtab-schedules').style.display = sub === 'schedules' ? 'block' : 'none';
}

function renderRoutesTable() {
  const tbody = document.getElementById('routes-table-body');
  tbody.innerHTML = '';

  state.routes.forEach(r => {
    let statusBadge = r.status === 'Active' 
      ? `<span class="badge badge-active">Active</span>`
      : `<span class="badge badge-inactive">Inactive</span>`;

    const hasActions = state.currentUser.role === 'Admin';

    tbody.innerHTML += `
      <tr>
        <td style="font-weight: 700; color: var(--text-accent);">${r.code}</td>
        <td style="font-weight: 600;">${r.name}</td>
        <td>${r.stations}</td>
        <td>${r.distance} KM</td>
        <td>K${r.fare.toFixed(2)}</td>
        <td>${statusBadge}</td>
        <td>
          ${hasActions ? `
            <button class="btn btn-dark" style="padding:0.25rem 0.5rem; font-size:0.75rem; text-transform:none;" onclick="toggleRouteStatus('${r.code}')">Status</button>
            <button class="btn btn-danger" style="padding:0.25rem 0.5rem; font-size:0.75rem; text-transform:none;" onclick="deleteRoute('${r.code}')">Delete</button>
          ` : `<span>--</span>`}
        </td>
      </tr>
    `;
  });
}

function filterRoutesTable() {
  const query = document.getElementById('routes-search-input').value.toLowerCase();
  const rows = document.querySelectorAll('#routes-table-body tr');
  rows.forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
  });
}

function toggleRouteStatus(code) {
  const route = state.routes.find(r => r.code === code);
  if (route) {
    route.status = route.status === 'Active' ? 'Inactive' : 'Active';
    saveState('routes');
    renderRoutesTable();
    showNotification(`Route status of ${code} updated to ${route.status}.`, 'success');
  }
}

function deleteRoute(code) {
  if (confirm(`Remove route ${code} from system?`)) {
    const idx = state.routes.findIndex(r => r.code === code);
    if (idx !== -1) {
      state.routes.splice(idx, 1);
      saveState('routes');
      renderRoutesTable();
      showNotification('Route deleted.', 'success');
    }
  }
}

function openAddRouteModal() {
  document.getElementById('add-route-modal').classList.add('active');
}

function saveNewRoute(e) {
  e.preventDefault();
  const code = document.getElementById('mr-code').value.trim().toUpperCase();
  const name = document.getElementById('mr-name').value.trim();
  const origin = document.getElementById('mr-origin').value.trim();
  const dest = document.getElementById('mr-destination').value.trim();
  const dist = parseFloat(document.getElementById('mr-distance').value);
  const fare = parseFloat(document.getElementById('mr-fare').value);
  const status = document.getElementById('mr-status').value;

  if (state.routes.some(r => r.code === code)) {
    showNotification('Route code already registered.', 'warning');
    return;
  }

  state.routes.push({
    code: code,
    name: name,
    stations: `${origin} → ${dest}`,
    distance: dist,
    fare: fare,
    status: status
  });
  
  saveState('routes');
  closeModal('add-route-modal');
  renderRoutesTable();
  showNotification('Route successfully registered.', 'success');
  appendActivity(`Created new transit route ${code}`, state.currentUser.email, 'info');
  
  e.target.reset();
}

function renderSchedulesTable() {
  const tbody = document.getElementById('schedules-table-body');
  tbody.innerHTML = '';

  state.schedules.forEach(s => {
    const route = state.routes.find(r => r.code === s.routeCode);
    const routeName = route ? route.name : s.routeCode;
    const hasActions = state.currentUser.role === 'Admin';
    
    let badgeColor = 'badge-active';
    if (s.status === 'Delayed') badgeColor = 'badge-maintenance';
    if (s.status === 'Cancelled') badgeColor = 'badge-inactive';
    if (s.status === 'Boarding') badgeColor = 'badge-confirmed';

    tbody.innerHTML += `
      <tr>
        <td>${s.id}</td>
        <td style="font-weight:600;">${routeName}</td>
        <td>${s.busCode}</td>
        <td>${s.driver}</td>
        <td style="font-family:'Share Tech Mono', monospace; font-weight:700; color:var(--primary-orange);">${s.time}</td>
        <td>${s.day}</td>
        <td><span class="badge ${badgeColor}">${s.status}</span></td>
        <td>
          ${hasActions ? `
            <button class="btn btn-dark" style="padding:0.25rem 0.5rem; font-size:0.75rem; text-transform:none;" onclick="toggleScheduleStatus('${s.id}')">Status</button>
            <button class="btn btn-danger" style="padding:0.25rem 0.5rem; font-size:0.75rem; text-transform:none;" onclick="deleteSchedule('${s.id}')">Delete</button>
          ` : `<span>--</span>`}
        </td>
      </tr>
    `;
  });
}

function filterSchedulesTable() {
  const query = document.getElementById('schedules-search-input').value.toLowerCase();
  const rows = document.querySelectorAll('#schedules-table-body tr');
  rows.forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
  });
}

function toggleScheduleStatus(id) {
  const sched = state.schedules.find(s => s.id === id);
  if (sched) {
    const list = ['On Time', 'Boarding', 'Delayed', 'Departed', 'Cancelled'];
    let currentIdx = list.indexOf(sched.status);
    let nextIdx = (currentIdx + 1) % list.length;
    sched.status = list[nextIdx];
    saveState('schedules');
    renderSchedulesTable();
    showNotification(`Schedule ${id} status changed to ${sched.status}.`, 'success');
  }
}

function deleteSchedule(id) {
  if (confirm(`Remove schedule ${id}?`)) {
    const idx = state.schedules.findIndex(s => s.id === id);
    if (idx !== -1) {
      state.schedules.splice(idx, 1);
      saveState('schedules');
      renderSchedulesTable();
      showNotification('Departure schedule removed.', 'success');
    }
  }
}

function openAddScheduleModal() {
  const routeSelect = document.getElementById('ms-route');
  routeSelect.innerHTML = '';
  state.routes.filter(r => r.status === 'Active').forEach(r => {
    routeSelect.innerHTML += `<option value="${r.code}">${r.name} (K${r.fare})</option>`;
  });

  const busSelect = document.getElementById('ms-bus');
  busSelect.innerHTML = '';
  state.buses.filter(b => b.status === 'Active').forEach(b => {
    busSelect.innerHTML += `<option value="${b.code}">${b.code} - ${b.model} (${b.capacity} seats)</option>`;
  });

  const driverSelect = document.getElementById('ms-driver');
  driverSelect.innerHTML = '<option value="Unassigned">Unassigned Driver</option>';
  state.users.filter(u => u.role === 'Operator').forEach(d => {
    driverSelect.innerHTML += `<option value="${d.firstName} ${d.lastName}">${d.firstName} ${d.lastName}</option>`;
  });

  document.getElementById('add-schedule-modal').classList.add('active');
}

function saveNewSchedule(e) {
  e.preventDefault();
  
  const newSched = {
    id: "SCH-" + Math.floor(100 + Math.random() * 900),
    routeCode: document.getElementById('ms-route').value,
    busCode: document.getElementById('ms-bus').value,
    driver: document.getElementById('ms-driver').value,
    time: document.getElementById('ms-time').value,
    day: document.getElementById('ms-day').value,
    status: document.getElementById('ms-status').value,
    bookedSeats: []
  };

  state.schedules.push(newSched);
  saveState('schedules');
  closeModal('add-schedule-modal');
  renderSchedulesTable();
  showNotification('New schedule published.', 'success');
  appendActivity(`Scheduled departure ${newSched.id}`, state.currentUser.email, 'info');

  e.target.reset();
}

// ====================================================
// MODULE 4: TICKET BOOKING FLOW & SEAT SELECTOR
// ====================================================
function renderBookingsTable() {
  const tbody = document.getElementById('bookings-table-body');
  tbody.innerHTML = '';

  const ticketsToShow = state.currentUser.role === 'Admin'
    ? state.bookings.filter(b =>
        (b.passengerName || '').toLowerCase().includes('mwenya') ||
        (b.passengerName || '').toLowerCase().includes('margaret')
      )
    : state.bookings.filter(b => b.passengerName.toLowerCase().includes((state.currentUser.firstName + " " + state.currentUser.lastName).toLowerCase()));

  ticketsToShow.forEach(b => {
    let badge = 'badge-pending';
    if (b.status === 'Confirmed') badge = 'badge-confirmed';
    if (b.status === 'Completed') badge = 'badge-completed';
    if (b.status === 'Cancelled') badge = 'badge-cancelled';

    tbody.innerHTML += `
      <tr>
        <td style="font-family:'Share Tech Mono', monospace; font-weight:700; color:var(--primary-orange);">${b.ref}</td>
        <td style="font-weight:600;">${b.passengerName}</td>
        <td>${b.routeName}</td>
        <td>${b.seats}</td>
        <td>K${b.amount.toFixed(2)}</td>
        <td><span class="badge ${badge}">${b.status}</span></td>
        <td>
          <button class="btn-icon" onclick="viewTicketReceipt('${b.ref}')" title="Print/View Ticket"></button>
          ${state.currentUser.role === 'Admin' && b.status === 'Confirmed' ? `
            <button class="btn-icon" onclick="cancelTicketBooking('${b.ref}')" style="color:var(--primary-red);" title="Cancel booking"></button>
          ` : ''}
        </td>
      </tr>
    `;
  });
}

function filterBookingsTable() {
  const query = document.getElementById('bookings-search-input').value.toLowerCase();
  const statusFilter = document.getElementById('bookings-filter-status').value;
  const rows = document.querySelectorAll('#bookings-table-body tr');

  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    const matchesQuery = text.includes(query);
    const matchesStatus = statusFilter === 'All' || text.includes(statusFilter.toLowerCase());
    row.style.display = (matchesQuery && matchesStatus) ? '' : 'none';
  });
}

function startNewBookingFlow() {
  document.getElementById('booking-history-subpanel').style.display = 'none';
  goBackToStep(1);
}

function cancelBookingFlow() {
  document.getElementById('booking-history-subpanel').style.display = 'block';
  document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
}

function goBackToStep(step) {
  document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('booking-step-' + step).classList.add('active');
  
  document.querySelectorAll('.step-item').forEach((item, idx) => {
    item.classList.toggle('active', idx + 1 === step);
    item.classList.toggle('completed', idx + 1 < step);
  });
}

function performBusSearch() {
  const from = document.getElementById('book-search-from').value;
  const to = document.getElementById('book-search-to').value;
  const date = document.getElementById('book-search-date').value;

  if (!date) {
    showNotification('Please choose a travel date.', 'warning');
    return;
  }

  const matches = state.schedules.filter(s => {
    const r = state.routes.find(route => route.code === s.routeCode);
    if (!r) return false;
    
    const stationsMatch = r.stations.includes(from) && r.stations.includes(to);
    return stationsMatch && s.status !== 'Cancelled';
  });

  const resultsRows = document.getElementById('searched-buses-rows');
  resultsRows.innerHTML = '';

  if (matches.length === 0) {
    resultsRows.innerHTML = `<tr><td colspan="8" style="text-align:center; color:var(--text-muted);">No departures matching this route on ${date}.</td></tr>`;
  } else {
    matches.forEach(s => {
      const r = state.routes.find(route => route.code === s.routeCode);
      const bus = state.buses.find(b => b.code === s.busCode);
      const capacity = bus ? bus.capacity : 40;
      const availableSeats = capacity - (s.bookedSeats?.length || 0);

      let glowColor = 'glow-text-green';
      if (s.status === 'Delayed') glowColor = 'glow-text-red';
      if (s.status === 'Boarding') glowColor = 'glow-text-amber';

      resultsRows.innerHTML += `
        <tr>
          <td style="font-weight:700; color:var(--primary-orange);">${s.busCode}</td>
          <td>${r.stations}</td>
          <td style="font-family:'Share Tech Mono', monospace; font-weight:700;">${s.time}</td>
          <td>${capacity} Seats</td>
          <td>K${r.fare.toFixed(2)}</td>
          <td class="${glowColor}">${s.status.toUpperCase()}</td>
          <td class="glow-text-green">${availableSeats} seats left</td>
          <td>
            <button class="btn btn-primary" style="padding:0.4rem 0.8rem; font-size:0.75rem;" onclick="chooseScheduleToBook('${s.id}')">Select</button>
          </td>
        </tr>
      `;
    });
  }

  document.getElementById('booking-bus-results').style.display = 'block';
}

function chooseScheduleToBook(schedId) {
  const sched = state.schedules.find(s => s.id === schedId);
  currentBookingFlow.schedule = sched;
  currentBookingFlow.selectedSeats = [];
  
  const route = state.routes.find(r => r.code === sched.routeCode);
  const bus = state.buses.find(b => b.code === sched.busCode);
  
  document.getElementById('summary-route-code').innerText = route.name;
  document.getElementById('summary-bus-code').innerText = sched.busCode;
  document.getElementById('summary-base-fare').innerText = `K${route.fare.toFixed(2)}`;
  document.getElementById('summary-selected-seats').innerText = 'None';
  document.getElementById('summary-total-price').innerText = 'K0.00';

  const container = document.getElementById('seat-map-grid-render');
  container.innerHTML = '';

  const capacity = bus ? bus.capacity : 40;
  const cols = 4;
  const rows = Math.ceil(capacity / cols);
  const bookedList = sched.bookedSeats || [];

  for (let r = 1; r <= rows; r++) {
    const s1 = (r - 1) * 4 + 1;
    const s2 = (r - 1) * 4 + 2;
    
    container.innerHTML += s1 <= capacity ? renderSingleSeatHTML(s1, bookedList) : '<div style="width: 40px; height: 40px;"></div>';
    container.innerHTML += s2 <= capacity ? renderSingleSeatHTML(s2, bookedList) : '<div style="width: 40px; height: 40px;"></div>';
    
    container.innerHTML += `<div class="seat-aisle-divider">Row ${r}</div>`;
    
    const s3 = (r - 1) * 4 + 3;
    const s4 = (r - 1) * 4 + 4;
    
    container.innerHTML += s3 <= capacity ? renderSingleSeatHTML(s3, bookedList) : '<div style="width: 40px; height: 40px;"></div>';
    container.innerHTML += s4 <= capacity ? renderSingleSeatHTML(s4, bookedList) : '<div style="width: 40px; height: 40px;"></div>';
  }

  goBackToStep(2);
}

function renderSingleSeatHTML(seatNo, bookedList) {
  const isBooked = bookedList.includes(seatNo);
  const seatClass = isBooked ? 'booked' : 'available';
  const action = isBooked ? '' : `onclick="toggleSeatSelection(${seatNo})"`;
  
  return `
    <div id="seat-btn-${seatNo}" class="seat-btn ${seatClass}" ${action}>
      ${seatNo}
    </div>
  `;
}

function toggleSeatSelection(seatNo) {
  const btn = document.getElementById('seat-btn-' + seatNo);
  const idx = currentBookingFlow.selectedSeats.indexOf(seatNo);
  const allowedPax = parseInt(document.getElementById('book-search-pax').value);

  if (idx === -1) {
    if (currentBookingFlow.selectedSeats.length >= allowedPax) {
      showNotification(`You selected passenger count of ${allowedPax}. Change search to select more seats.`, 'warning');
      return;
    }
    currentBookingFlow.selectedSeats.push(seatNo);
    btn.classList.add('selected');
  } else {
    currentBookingFlow.selectedSeats.splice(idx, 1);
    btn.classList.remove('selected');
  }

  updateSeatSummary();
}

function updateSeatSummary() {
  const seats = currentBookingFlow.selectedSeats;
  const count = seats.length;
  
  const route = state.routes.find(r => r.code === currentBookingFlow.schedule.routeCode);
  const price = count * route.fare;

  document.getElementById('summary-selected-seats').innerText = count > 0 ? seats.join(', ') : 'None';
  document.getElementById('summary-total-price').innerText = `K${price.toFixed(2)}`;
}

function proceedToPassengerDetails() {
  if (currentBookingFlow.selectedSeats.length === 0) {
    showNotification('Please select at least one seat to continue.', 'warning');
    return;
  }

  const container = document.getElementById('passenger-inputs-container');
  container.innerHTML = '';

  currentBookingFlow.selectedSeats.forEach((seat, idx) => {
    const u = state.currentUser;
    
    const defaultTitle = 'Mr';
    const defaultFirst = (idx === 0 && u.role === 'Passenger') ? u.firstName : '';
    const defaultLast = (idx === 0 && u.role === 'Passenger') ? u.lastName : '';
    const defaultPhone = (idx === 0 && u.role === 'Passenger') ? (u.phone || '') : '';

    container.innerHTML += `
      <div style="margin-bottom: 1.5rem; border-bottom: 1px dashed var(--glass-border); padding-bottom: 1rem;">
        <h4 style="color:var(--primary-orange); margin-bottom:1rem;">Passenger for Seat ${seat}</h4>
        <div class="form-row">
          <div class="form-group" style="max-width: 120px;">
            <label>Title</label>
            <select class="form-control pax-title" data-seat="${seat}">
              <option value="Mr" ${defaultTitle==='Mr'?'selected':''}>Mr.</option>
              <option value="Mrs">Mrs.</option>
              <option value="Ms">Ms.</option>
              <option value="Dr">Dr.</option>
            </select>
          </div>
          <div class="form-group">
            <label>First Name</label>
            <input type="text" class="form-control pax-first" data-seat="${seat}" value="${defaultFirst}" required>
          </div>
          <div class="form-group">
            <label>Other/Last Name</label>
            <input type="text" class="form-control pax-last" data-seat="${seat}" value="${defaultLast}" required>
          </div>
        </div>
        <div class="form-group">
          <label>Contact Phone Number</label>
          <input type="text" class="form-control pax-phone" data-seat="${seat}" value="${defaultPhone}" placeholder="Contact Phone" required>
        </div>
      </div>
    `;
  });

  goBackToStep(3);
}

function proceedToPaymentStage() {
  const firstInputs = document.querySelectorAll('.pax-first');
  const lastInputs = document.querySelectorAll('.pax-last');
  const phoneInputs = document.querySelectorAll('.pax-phone');

  let isValid = true;
  currentBookingFlow.passengers = [];

  firstInputs.forEach((el, index) => {
    const seat = el.getAttribute('data-seat');
    const first = el.value.trim();
    const last = lastInputs[index].value.trim();
    const phone = phoneInputs[index].value.trim();
    const title = document.querySelectorAll('.pax-title')[index].value;

    if (!first || !last || !phone) isValid = false;

    currentBookingFlow.passengers.push({
      seat: parseInt(seat),
      title: title,
      name: `${first} ${last}`,
      phone: phone
    });
  });

  if (!isValid) {
    showNotification('Please fill in all passenger details before proceeding.', 'warning');
    return;
  }

  const listDiv = document.getElementById('verification-passenger-list');
  listDiv.innerHTML = '<h4 style="margin-bottom:0.5rem; font-size:0.95rem; color:#fff;">Reserved Ticket Details:</h4>';
  
  currentBookingFlow.passengers.forEach(p => {
    listDiv.innerHTML += `
      <div class="summary-row" style="font-size:0.85rem; margin-bottom:0.25rem;">
        <span class="lbl">${p.title}. ${p.name}</span>
        <span class="val">Seat No: ${p.seat} (Phone: ${p.phone})</span>
      </div>
    `;
  });

  const route = state.routes.find(r => r.code === currentBookingFlow.schedule.routeCode);
  const totalCost = currentBookingFlow.passengers.length * route.fare;

  document.getElementById('verify-route').innerText = route.stations;
  document.getElementById('verify-seat-count').innerText = `${currentBookingFlow.passengers.length} seat(s)`;
  document.getElementById('verify-fare').innerText = `K${route.fare.toFixed(2)}`;
  document.getElementById('verify-total-amount').innerText = `K${totalCost.toFixed(2)}`;

  if (state.currentUser && state.currentUser.role === 'Passenger') {
    document.getElementById('wallet-phone-number').value = state.currentUser.phone || '';
  }

  goBackToStep(4);
}

function selectPayMethod(method) {
  currentBookingFlow.paymentMethod = method;
  document.getElementById('pay-mtn').classList.toggle('active', method === 'MTN MoMo');
  document.getElementById('pay-airtel').classList.toggle('active', method === 'Airtel Money');
  document.getElementById('pay-zamtel').classList.toggle('active', method === 'Zamtel Kwacha');
}

function processSimulationPayment() {
  const phone = document.getElementById('wallet-phone-number').value.trim();
  const termsChecked = document.getElementById('verify-terms-checkbox').checked;

  if (!phone) {
    showNotification('Please enter your Mobile Money wallet phone number.', 'warning');
    return;
  }
  if (!termsChecked) {
    showNotification('Please accept the SBTS Terms & Conditions to proceed.', 'warning');
    return;
  }

  currentBookingFlow.phone = phone;

  const spinner = document.getElementById('payment-spinner-loader');
  spinner.classList.add('flex-center');
  spinner.style.display = 'flex';

  setTimeout(() => {
    spinner.style.display = 'none';
    spinner.classList.remove('flex-center');
    completeSimulatedBookingReceipt();
  }, 3500);
}

function completeSimulatedBookingReceipt() {
  const sched = currentBookingFlow.schedule;
  const route = state.routes.find(r => r.code === sched.routeCode);
  const amount = currentBookingFlow.passengers.length * route.fare;
  const bkRef = "SBTS-BK-" + Math.floor(1000 + Math.random() * 9000);
  const txId = "TXN-" + Math.floor(1000 + Math.random() * 9000) + "-MOMO";

  const passengerNamesString = currentBookingFlow.passengers.map(p => p.name).join(', ');
  const seatsString = currentBookingFlow.selectedSeats.join(', ');

  const newBooking = {
    ref: bkRef,
    passengerName: passengerNamesString,
    routeName: route.name,
    seats: seatsString,
    amount: amount,
    status: 'Confirmed',
    date: new Date().toISOString().split('T')[0],
    phone: currentBookingFlow.phone
  };

  state.bookings.unshift(newBooking);
  saveState('bookings');

  const newTx = {
    txId: txId,
    bookingRef: bkRef,
    passengerName: passengerNamesString,
    method: currentBookingFlow.paymentMethod,
    amount: amount,
    status: 'Success',
    timestamp: new Date().toISOString().slice(0, 16).replace('T', ' ')
  };

  state.transactions.unshift(newTx);
  saveState('transactions');

  sched.bookedSeats = (sched.bookedSeats || []).concat(currentBookingFlow.selectedSeats);
  saveState('schedules');

  if (state.currentUser) {
    state.currentUser.bookings = (state.currentUser.bookings || 0) + 1;
    state.currentUser.spent = (state.currentUser.spent || 0) + amount;
    
    const u = state.users.find(usr => usr.uid === state.currentUser.uid);
    if (u) {
      u.bookings = state.currentUser.bookings;
      u.spent = state.currentUser.spent;
      saveState('users');
    }
    localStorage.setItem('sbts_session', JSON.stringify(state.currentUser));
  }

  appendActivity(`Issued Ticket Booking Ref ${bkRef} to ${passengerNamesString}`, state.currentUser ? state.currentUser.email : 'Public User', 'success');

  printReceipt(newBooking, newTx);
  goBackToStep(5);
}

function printReceipt(booking, tx) {
  const container = document.getElementById('receipt-holder');
  container.innerHTML = `
    <div class="receipt-container">
      <div class="receipt-logo">SBTS<span>.</span></div>
      <div class="receipt-title">Official Travel Voucher</div>
      
      <div class="receipt-grid">
        <div class="receipt-group">
          <div class="lbl">Booking Ref</div>
          <div class="val">${booking.ref}</div>
        </div>
        <div class="receipt-group">
          <div class="lbl">Tx ID</div>
          <div class="val">${tx.txId}</div>
        </div>
        <div class="receipt-group" style="grid-column: span 2;">
          <div class="lbl">Passenger(s)</div>
          <div class="val">${booking.passengerName}</div>
        </div>
        <div class="receipt-group" style="grid-column: span 2;">
          <div class="lbl">Transit Route</div>
          <div class="val">${booking.routeName}</div>
        </div>
        <div class="receipt-group">
          <div class="lbl">Seats Booked</div>
          <div class="val" style="color:var(--primary-orange); font-weight:700;">${booking.seats}</div>
        </div>
        <div class="receipt-group">
          <div class="lbl">Fare Total</div>
          <div class="val">K${booking.amount.toFixed(2)}</div>
        </div>
        <div class="receipt-group">
          <div class="lbl">Payment Provider</div>
          <div class="val">${tx.method}</div>
        </div>
        <div class="receipt-group">
          <div class="lbl">Issue Date</div>
          <div class="val">${booking.date}</div>
        </div>
      </div>

      <div style="font-size:0.75rem; text-align:center; color:#64748b; line-height:1.4;">
        Please present this voucher to the SBTS boarding crew at least 30 minutes before departure. Happy Travels!
      </div>

      <div class="receipt-barcode">
        <svg class="barcode-svg" viewBox="0 0 100 20" preserveAspectRatio="none">
          <rect x="5" y="2" width="2" height="16" fill="#000"></rect>
          <rect x="9" y="2" width="1" height="16" fill="#000"></rect>
          <rect x="12" y="2" width="4" height="16" fill="#000"></rect>
          <rect x="18" y="2" width="1" height="16" fill="#000"></rect>
          <rect x="21" y="2" width="3" height="16" fill="#000"></rect>
          <rect x="26" y="2" width="2" height="16" fill="#000"></rect>
          <rect x="30" y="2" width="4" height="16" fill="#000"></rect>
          <rect x="36" y="2" width="1" height="16" fill="#000"></rect>
          <rect x="39" y="2" width="3" height="16" fill="#000"></rect>
          <rect x="44" y="2" width="2" height="16" fill="#000"></rect>
          <rect x="48" y="2" width="4" height="16" fill="#000"></rect>
          <rect x="54" y="2" width="1" height="16" fill="#000"></rect>
          <rect x="57" y="2" width="3" height="16" fill="#000"></rect>
          <rect x="62" y="2" width="2" height="16" fill="#000"></rect>
          <rect x="66" y="2" width="4" height="16" fill="#000"></rect>
          <rect x="72" y="2" width="1" height="16" fill="#000"></rect>
          <rect x="75" y="2" width="3" height="16" fill="#000"></rect>
          <rect x="80" y="2" width="2" height="16" fill="#000"></rect>
          <rect x="84" y="2" width="4" height="16" fill="#000"></rect>
          <rect x="90" y="2" width="1" height="16" fill="#000"></rect>
          <rect x="93" y="2" width="2" height="16" fill="#000"></rect>
        </svg>
        <span style="font-family:'Share Tech Mono', monospace; font-size:0.75rem; color:#475569;">*${booking.ref}*</span>
      </div>
    </div>
  `;
}

function viewTicketReceipt(ref) {
  const b = state.bookings.find(booking => booking.ref === ref);
  const tx = state.transactions.find(t => t.bookingRef === ref) || { txId: "MOCK-TXN", method: "Cash/Card" };
  if (b) {
    printReceipt(b, tx);
    goBackToStep(5);
    document.getElementById('booking-history-subpanel').style.display = 'none';
    switchTab('booking');
  }
}

function closeReceiptFlow() {
  document.getElementById('booking-history-subpanel').style.display = 'block';
  document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
  renderBookingsTable();
}

function cancelTicketBooking(ref) {
  if (confirm(`Are you sure you want to cancel the ticket booking: ${ref}?`)) {
    const booking = state.bookings.find(b => b.ref === ref);
    if (booking) {
      booking.status = 'Cancelled';
      saveState('bookings');
      
      const tx = state.transactions.find(t => t.bookingRef === ref);
      if (tx) {
        tx.status = 'Failed/Refunded';
        saveState('transactions');
      }

      showNotification(`Booking ${ref} was cancelled.`, 'success');
      renderBookingsTable();
      appendActivity(`Cancelled booking ticket ${ref}`, state.currentUser.email, 'danger');
    }
  }
}

// ====================================================
// MODULE 5: PAYMENT TRACKING
// ====================================================
function renderPaymentsTable() {
  const tbody = document.getElementById('payments-table-body');
  tbody.innerHTML = '';

  state.transactions.forEach(tx => {
    let badge = 'badge-pending';
    if (tx.status === 'Success') badge = 'badge-confirmed';
    if (tx.status === 'Failed/Refunded') badge = 'badge-cancelled';

    tbody.innerHTML += `
      <tr>
        <td style="font-family:'Share Tech Mono', monospace; font-weight:700; color:var(--text-accent);">${tx.txId}</td>
        <td>${tx.bookingRef}</td>
        <td style="font-weight:600;">${tx.passengerName}</td>
        <td>${tx.method}</td>
        <td>K${tx.amount.toFixed(2)}</td>
        <td><span class="badge ${badge}">${tx.status}</span></td>
        <td>${tx.timestamp}</td>
        <td>
          ${state.currentUser.role === 'Admin' && tx.status === 'Success' ? `
            <button class="btn btn-danger" style="padding:0.25rem 0.5rem; font-size:0.75rem; text-transform:none;" onclick="refundTransaction('${tx.txId}')">Refund</button>
          ` : `<span>--</span>`}
        </td>
      </tr>
    `;
  });
}

function filterPaymentsTable() {
  const query = document.getElementById('payments-search-input').value.toLowerCase();
  const rows = document.querySelectorAll('#payments-table-body tr');
  rows.forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
  });
}

function refundTransaction(txId) {
  if (confirm(`Process mobile money refund for transaction: ${txId}?`)) {
    const tx = state.transactions.find(t => t.txId === txId);
    if (tx) {
      tx.status = 'Failed/Refunded';
      saveState('transactions');
      
      const bk = state.bookings.find(b => b.ref === tx.bookingRef);
      if (bk) {
        bk.status = 'Cancelled';
        saveState('bookings');
      }

      showNotification(`Transaction ${txId} refunded back to Mobile Wallet.`, 'success');
      renderPaymentsTable();
      appendActivity(`Refunded transaction ${txId}`, state.currentUser.email, 'warning');
    }
  }
}

// ====================================================
// MODULE 6: REPORTS & BUSINESS INTELLIGENCE
// ====================================================
function filterReportPeriod(period) {
  if (state.currentUser.role !== 'Admin') {
    showNotification('Business reports are restricted to administrators only.', 'warning');
    return;
  }
  
  activeReportPeriod = period;
  document.querySelectorAll('[id^="btn-rep-"]').forEach(btn => {
    btn.classList.toggle('active', btn.id === 'btn-rep-' + period.toLowerCase());
  });
  renderReportsDashboard();
}

function renderReportsDashboard() {
  if (state.currentUser.role !== 'Admin') {
    showNotification('Business reports are restricted to administrators only.', 'warning');
    return;
  }
  
  let multiplier = 1;
  if (activeReportPeriod === 'Weekly') multiplier = 5.2;
  if (activeReportPeriod === 'Monthly') multiplier = 21.5;
  if (activeReportPeriod === 'Yearly') multiplier = 250;

  const baseRevenue = state.transactions.filter(t => t.status === 'Success').reduce((sum, t) => sum + t.amount, 0);
  const totalRev = baseRevenue * multiplier;
  const countTx = Math.round(state.transactions.filter(t => t.status === 'Success').length * multiplier);
  const avgTix = countTx > 0 ? (totalRev / countTx) : 0;

  document.getElementById('report-total-revenue').innerText = `K${totalRev.toFixed(2)}`;
  document.getElementById('report-transactions').innerText = countTx;
  document.getElementById('report-avg-ticket').innerText = `K${avgTix.toFixed(2)}`;
  document.getElementById('report-monthly-revenue').innerText = `K${totalRev.toFixed(2)}`;

  const paymentBars = document.getElementById('payment-method-bars');
  if (paymentBars) {
    paymentBars.innerHTML = '';
    const methods = ['MTN MoMo', 'Airtel Money', 'Zamtel Kwacha', 'Credit Card', 'Cash'];
    methods.forEach(m => {
      let pct = 0;
      const matches = state.transactions.filter(t => t.method === m && t.status === 'Success');
      const totalMatches = state.transactions.filter(t => t.status === 'Success').length;
      
      if (totalMatches > 0) {
        pct = (matches.length / totalMatches) * 100;
      } else {
        if (m === 'MTN MoMo') pct = 45;
        if (m === 'Airtel Money') pct = 30;
        if (m === 'Zamtel Kwacha') pct = 15;
        if (m === 'Credit Card') pct = 7;
        if (m === 'Cash') pct = 3;
      }

      paymentBars.innerHTML += `
        <div class="chart-bar-row">
          <span class="chart-bar-label">${m}</span>
          <div class="chart-bar-track">
            <div class="chart-bar-fill" style="width: ${pct}%; background-color: var(--primary-orange);"></div>
          </div>
          <span class="chart-bar-value">${pct.toFixed(0)}%</span>
        </div>
      `;
    });
  }

  const routeBars = document.getElementById('route-performance-bars');
  if (routeBars) {
    routeBars.innerHTML = '';
    state.routes.slice(0, 4).forEach(r => {
      const bookingCount = state.bookings.filter(b => b.routeName.includes(r.name) || b.routeName.includes(r.code)).length;
      const maxSimBookings = 10;
      const pct = Math.min((bookingCount / maxSimBookings) * 100 + 10, 100);

      routeBars.innerHTML += `
        <div class="chart-bar-row">
          <span class="chart-bar-label">${r.code}</span>
          <div class="chart-bar-track">
            <div class="chart-bar-fill" style="width: ${pct}%; background-color: var(--primary-green);"></div>
          </div>
          <span class="chart-bar-value">${pct.toFixed(0)}%</span>
        </div>
      `;
    });
  }
}

function exportReportsCSV() {
  if (state.currentUser.role !== 'Admin') {
    showNotification('Report exports are restricted to administrators only.', 'warning');
    return;
  }
  
  const csvRows = [
    ["Smart Bus Transport System (SBTS) Zambia - Analytics Report"],
    ["Period", activeReportPeriod],
    ["Exported At", new Date().toLocaleString()],
    [],
    ["Metric", "Value"],
    ["Total Revenue", document.getElementById('report-total-revenue').innerText],
    ["Total Transactions", document.getElementById('report-transactions').innerText],
    ["Average Ticket Value", document.getElementById('report-avg-ticket').innerText],
    ["Monthly Revenue", document.getElementById('report-monthly-revenue').innerText]
  ];

  let csvContent = "data:text/csv;charset=utf-8," 
      + csvRows.map(e => e.map(val => `"${val}"`).join(",")).join("\n");
  
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `sbts_analytics_${activeReportPeriod.toLowerCase()}_report.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  showNotification("Business Intelligence Report Exported successfully.", "success");
}

// ====================================================
// GENERAL UTILITY LOGICS
// ====================================================
function closeModal(id) {
  document.getElementById(id).classList.remove('active');
}

function showNotification(msg, type = 'warning') {
  const container = document.getElementById('notif-box');
  const id = 'notif-' + Math.floor(Math.random() * 1000);
  
  const notifHTML = `
    <div class="notification ${type}" id="${id}">
      <span class="notification-icon"></span>
      <div class="notification-content">${msg}</div>
      <button class="notification-close" onclick="this.parentElement.remove()"></button>
    </div>
  `;

  container.innerHTML += notifHTML;

  setTimeout(() => {
    const el = document.getElementById(id);
    if (el) el.classList.add('show');
  }, 50);

  setTimeout(() => {
    const el = document.getElementById(id);
    if (el) {
      el.classList.remove('show');
      setTimeout(() => el.remove(), 300);
    }
  }, 4000);
}

function exportTableToCSV(tableId, filename) {
  const csv = [];
  const rows = document.querySelectorAll('#' + tableId + ' tr');
  
  for (let i = 0; i < rows.length; i++) {
    const row = [], cols = rows[i].querySelectorAll('td, th');
    
    for (let j = 0; j < cols.length - 1; j++) { 
      let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").trim();
      data = data.replace(/"/g, '""');
      row.push('"' + data + '"');
    }
    csv.push(row.join(","));
  }

  const csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
  const downloadLink = document.createElement("a");
  downloadLink.download = filename;
  downloadLink.href = window.URL.createObjectURL(csvFile);
  downloadLink.style.display = "none";
  document.body.appendChild(downloadLink);
  downloadLink.click();
  document.body.removeChild(downloadLink);
  showNotification(`Table exported to ${filename} successfully.`, 'success');
}
