function spotterApp() {
  return {
    // ── State ──────────────────────────────────────
    activated: false,
    currentView: 'home',
    viewHistory: [],
    profile: { id: '', name: '', county: '', ward: '', salesRep: '', role: '' },
    clockedIn: false,
    clockRecord: null,
    submissions: [],
    followups: [],
    online: navigator.onLine,
    syncing: false,
    syncMsg: '',
    pendingCount: 0,
    activationCode: '',
    activationError: '',
    activating: false,
    clockingIn: false,
    confirmingClockOut: false,
    gpsStatus: 'Tap to capture location',
    gpsCoords: null,
    gettingGPS: false,
    submissionFilter: 'all',
    taskFilter: 'open',
    completingTaskId: null,
    completionNote: '',
    duplicateWarning: '',
    duplicateBlock: false,
    confirmClearData: false,
    photoPreview: null,
    submitSuccess: false,
    submitting: false,
    wizardStep: 1,
    formErrors: {},
    showClockInPrompt: false,
    autosaveInterval: null,
    wardSubmissions: [],

    // ── Supervisor state ────────────────────────────
    loginTab: 'spotter',
    supervisorEmail: '',
    supervisorPassword: '',
    supervisorError: '',
    supervisorLoggingIn: false,
    supervisorStats: {},
    supervisorSubmissions: [],
    supervisorSubmissionFilter: 'all',
    supervisorDuplicates: [],
    supervisorFollowUps: [],
    supervisorFollowUpFilter: 'overdue',
    supervisorAttendance: [],
    supervisorSpotters: [],
    leaderboardData: [],
    leaderboardPeriod: 'programme',

    // ── Form ────────────────────────────────────────
    form: {
      pharmacy: '', town: '', ward: '', county: '', address: '',
      lat: '', lng: '', gpsAccuracy: null,
      openTime: '', closeTime: '', daysPerWeek: '',
      photoData: '', photoName: '',
      ownerName: '', ownerPhone: '', pharmacyPhone: '', ownerEmail: '',
      ownerPresent: '', followUp: '', callbackTime: '',
      footTraffic: '', stockLevel: '', potential: '', notes: '',
      nextStep: '', followUpDate: '', repNotes: '', brochure: '',
      landmark: '',
      status: 'submitted', date: new Date().toISOString().slice(0,10)
    },

    // ── Init ────────────────────────────────────────
    async init() {
      const device = await window.SpotterDB.getDevice();
      if (device?.activated) {
        this.activated = true;
        this.profile = device.profile || {};
      }

      // Supervisor: load dashboard data on startup
      if (this.activated && this.isSupervisor()) {
        this.currentView = 'supervisor_dashboard';
        setTimeout(() => this.loadSupervisorData(), 100);
      } else {
        const att = await window.SpotterDB.getTodayAttendance();
        if (att) { this.clockedIn = true; this.clockRecord = att; }

        this.submissions = await window.SpotterDB.getAllSubmissions();
        this.submissions.sort((a,b) => b.createdAt > a.createdAt ? 1 : -1);

        this.followups = await window.SpotterDB.getAllFollowups();
        this.pendingCount = await window.SpotterDB.countQueue();

        if (this.online && this.pendingCount > 0) this.handleSync();
        await this.checkOverdueTasks();
      }

      window.addEventListener('online', () => { this.online = true; if (!this.isSupervisor()) this.handleSync(); });
      window.addEventListener('offline', () => { this.online = false; });

      window.addEventListener('dm:session-expired', () => {
        localStorage.clear();
        window.SpotterDB.clearAll().then(() => {
          this.activated = false;
          this.currentView = 'home';
          this.profile = { id: '', name: '', county: '', ward: '', salesRep: '', role: '' };
          this.clockedIn = false;
          this.clockRecord = null;
          this.submissions = [];
          this.followups = [];
          this.pendingCount = 0;
          this.loginTab = 'spotter';
        });
      });

      setInterval(() => { /* triggers activeDuration recompute */ }, 60000);
    },

    // ── Supervisor helpers ───────────────────────────
    isSupervisor() {
      return ['sales_rep','county_coordinator','admin','super_admin','technical_admin'].includes(this.profile.role);
    },
    supervisorRoleLabel() {
      return { sales_rep:'Sales Rep Dashboard', county_coordinator:'County Coordinator Dashboard', admin:'Admin Dashboard', super_admin:'Admin Dashboard', technical_admin:'Technical Admin' }[this.profile.role] || 'Dashboard';
    },
    supervisorScopeLabel() {
      if (this.profile.role === 'sales_rep') return `${this.supervisorStats.spotterCount ?? 0} Spotter(s) assigned`;
      if (this.profile.role === 'county_coordinator') return `${this.profile.county} County`;
      return 'All Counties';
    },
    supervisorNavItems() {
      return [
        { view: 'supervisor_dashboard',   label: 'Dashboard',   icon: 'dashboard' },
        { view: 'supervisor_submissions', label: 'Submissions', icon: 'list' },
        { view: 'supervisor_duplicates',  label: 'Reviews',     icon: 'alert' },
        { view: 'supervisor_leaderboard', label: 'Leaders',     icon: 'trophy' },
        { view: 'settings',              label: 'Account',     icon: 'user' },
      ];
    },

    // ── Navigation ──────────────────────────────────
    navigate(view) {
      if (view === 'submit' && !this.clockedIn && !this.isSupervisor()) return;
      if (this.autosaveInterval) { clearInterval(this.autosaveInterval); this.autosaveInterval = null; }
      this.viewHistory.push(this.currentView);
      this.currentView = view;
      if (view === 'submit') this.resetWizard();
    },
    goBack() {
      if (this.autosaveInterval) { clearInterval(this.autosaveInterval); this.autosaveInterval = null; }
      this.currentView = this.viewHistory.pop() || (this.isSupervisor() ? 'supervisor_dashboard' : 'home');
    },
    viewTitle() {
      return {
        home: 'SPOTTER', clock: 'Attendance', submit: 'New Submission',
        submissions: 'My Submissions', tasks: 'Follow-ups', settings: 'Settings',
        ward: 'Co-Ward',
        supervisor_dashboard: this.supervisorRoleLabel(),
        supervisor_submissions: 'Submissions',
        supervisor_duplicates: 'Duplicate Reviews',
        supervisor_followups: 'Follow-ups',
        supervisor_attendance: 'Attendance',
        supervisor_leaderboard: 'Leaderboard',
      }[this.currentView] || 'SPOTTER';
    },

    // ── Activation ──────────────────────────────────
    formatActivationCode() {
      let v = this.activationCode.replace(/-/g,'').toUpperCase().slice(0,12);
      if (v.length > 8) v = v.slice(0,4)+'-'+v.slice(4,8)+'-'+v.slice(8);
      else if (v.length > 4) v = v.slice(0,4)+'-'+v.slice(4);
      this.activationCode = v;
    },
    async handleActivate() {
      const code = this.activationCode.replace(/-/g,'');
      if (code.length < 12) { this.activationError = 'Code must be 12 characters'; return; }
      this.activating = true;
      this.activationError = '';
      try {
        const API = document.querySelector('meta[name=api-base]').content;
        const res = await fetch(`${API}/spotter/activate`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ code: this.activationCode })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Activation failed');
        await window.SpotterDB.setDevice({ activated: true, token: data.token, refreshToken: data.refresh_token, profile: { ...data.profile, role: 'spotter' } });
        localStorage.setItem('dm_auth_token', data.token);
        localStorage.setItem('dm_refresh_token', data.refresh_token);
        localStorage.setItem('dm_activated', '1');
        this.profile = { ...data.profile, role: 'spotter' };
        this.activated = true;
        this.currentView = 'home';
      } catch (e) {
        this.activationError = e.message || 'Invalid or expired activation code';
      } finally {
        this.activating = false;
      }
    },

    // ── Supervisor Login ────────────────────────────
    async handleSupervisorLogin() {
      if (!this.supervisorEmail || !this.supervisorPassword) return;
      this.supervisorLoggingIn = true;
      this.supervisorError = '';
      try {
        const API = document.querySelector('meta[name=api-base]').content;
        const res = await fetch(`${API}/spotter/supervisor/login`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email: this.supervisorEmail, password: this.supervisorPassword })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Login failed');
        await window.SpotterDB.setDevice({ activated: true, token: data.token, refreshToken: data.refresh_token, profile: data.profile });
        localStorage.setItem('dm_auth_token', data.token);
        localStorage.setItem('dm_refresh_token', data.refresh_token);
        localStorage.setItem('dm_activated', '1');
        this.profile = data.profile;
        this.activated = true;
        this.currentView = 'supervisor_dashboard';
        await this.loadSupervisorData();
      } catch (e) {
        this.supervisorError = e.message || 'Login failed';
      } finally {
        this.supervisorLoggingIn = false;
      }
    },

    // ── Supervisor data loading ─────────────────────
    async loadSupervisorData() {
      await Promise.all([
        this.loadSupervisorStats(),
        this.loadSupervisorSubmissions(),
        this.loadSupervisorDuplicates(),
        this.loadSupervisorFollowUps(),
        this.loadSupervisorAttendance(),
        this.loadLeaderboard(),
        this.profile.role === 'sales_rep' ? this.loadSupervisorSpotters() : Promise.resolve(),
      ]);
    },
    async apiGet(path) {
      const API = document.querySelector('meta[name=api-base]').content;
      const token = localStorage.getItem('dm_auth_token');
      const res = await fetch(`${API}${path}`, { headers: { 'Authorization': `Bearer ${token}` } });
      if (!res.ok) throw new Error(`API error: ${res.status}`);
      return res.json();
    },
    async loadSupervisorStats() { try { this.supervisorStats = await this.apiGet('/spotter/supervisor/stats'); } catch {} },
    async loadSupervisorSubmissions() {
      try {
        const filter = this.supervisorSubmissionFilter !== 'all' ? `?status=${this.supervisorSubmissionFilter}` : '';
        const data = await this.apiGet(`/spotter/supervisor/submissions${filter}`);
        this.supervisorSubmissions = data.data || [];
      } catch {}
    },
    async loadSupervisorDuplicates() {
      try {
        const data = await this.apiGet('/spotter/supervisor/duplicates');
        this.supervisorDuplicates = (data.data || []).map(r => ({ ...r, _confirming: null, _notes: '', _decided: false }));
      } catch {}
    },
    async decideDuplicate(review, decision) {
      try {
        const API = document.querySelector('meta[name=api-base]').content;
        const token = localStorage.getItem('dm_auth_token');
        const res = await fetch(`${API}/spotter/supervisor/duplicates/${review.id}/decide`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
          body: JSON.stringify({ decision, notes: review._notes || '' })
        });
        if (res.ok) { review._decided = true; review._confirming = null; await this.loadSupervisorStats(); }
      } catch {}
    },
    async loadSupervisorFollowUps() {
      try {
        const data = await this.apiGet(`/spotter/supervisor/followups?status=${this.supervisorFollowUpFilter}`);
        this.supervisorFollowUps = data.data || [];
      } catch {}
    },
    async loadSupervisorAttendance() { try { const data = await this.apiGet('/spotter/supervisor/attendance'); this.supervisorAttendance = data.data || []; } catch {} },
    async loadLeaderboard() { try { const data = await this.apiGet(`/spotter/supervisor/leaderboard?period=${this.leaderboardPeriod}`); this.leaderboardData = data.leaderboard || []; } catch {} },
    async loadSupervisorSpotters() { try { const data = await this.apiGet('/spotter/supervisor/spotters'); this.supervisorSpotters = data.spotters || []; } catch {} },

    // ── Clock ────────────────────────────────────────
    async handleClockIn() {
      this.clockingIn = true;
      this.gpsStatus = 'Getting location...';
      const coords = await window.SpotterGPS.get();
      const record = { date: new Date().toISOString().slice(0,10), clockInAt: new Date().toISOString(), lat: coords?.lat || null, lng: coords?.lng || null, synced: false };
      const id = await window.SpotterDB.addAttendance(record);
      this.clockRecord = { ...record, id };
      this.clockedIn = true;
      this.clockingIn = false;
      await window.SpotterDB.addToQueue({ type: 'attendance', data: record, attempts: 0 });
      this.pendingCount++;
    },
    async handleClockOut() {
      this.confirmingClockOut = false;
      const coords = await window.SpotterGPS.get();
      const updates = { clockOutAt: new Date().toISOString(), outLat: coords?.lat || null, outLng: coords?.lng || null };
      await window.SpotterDB.updateAttendance(this.clockRecord.id, updates);
      this.clockRecord = { ...this.clockRecord, ...updates };
      await window.SpotterDB.addToQueue({ type: 'attendance', data: this.clockRecord, attempts: 0 });
      this.pendingCount++;
      this.currentView = 'home';
    },

    // ── GPS ──────────────────────────────────────────
    async getGPS() {
      this.gettingGPS = true; this.gpsStatus = 'Getting location...';
      const coords = await window.SpotterGPS.get();
      if (coords) { this.gpsCoords = coords; this.form.lat = coords.lat; this.form.lng = coords.lng; this.form.gpsAccuracy = coords.accuracy; this.gpsStatus = 'Location captured'; }
      else { this.gpsStatus = 'Location unavailable — tap to retry'; }
      this.gettingGPS = false;
    },

    // ── Photo ────────────────────────────────────────
    async handlePhotoCapture(event) {
      const file = event.target.files[0]; if (!file) return;
      try { const result = await window.SpotterCamera.compress(file); this.form.photoData = result.base64; this.form.photoName = result.filename; this.photoPreview = result.dataUrl; }
      catch (e) { console.error('Photo compression failed:', e); }
    },
    photoSizeKB() { if (!this.form.photoData) return 0; return Math.round((this.form.photoData.length * 3/4) / 1024); },

    // ── Duplicate check ──────────────────────────────
    async checkDuplicate() {
      if (!this.form.pharmacy || this.form.pharmacy.length < 3) return;
      const result = await window.SpotterDuplicate.check(this.form.pharmacy, this.profile.ward);
      if (!result) { this.duplicateWarning = ''; this.duplicateBlock = false; return; }
      if (result.type === 'exact') { this.duplicateBlock = true; this.duplicateWarning = 'This pharmacy has already been submitted. Duplicate submission is not permitted.'; this.formErrors.pharmacy = 'Exact duplicate — cannot proceed'; }
      else { this.duplicateBlock = false; this.duplicateWarning = `Similar pharmacy found: ${result.match}. Verify before continuing.`; }
    },

    // ── Wizard ───────────────────────────────────────
    resetWizard() {
      this.wizardStep = 1; this.submitSuccess = false; this.formErrors = {}; this.gpsCoords = null; this.photoPreview = null; this.duplicateWarning = ''; this.duplicateBlock = false;
      this.form = { pharmacy: '', town: '', ward: this.profile.ward || '', county: this.profile.county || '', address: '', lat: '', lng: '', gpsAccuracy: null, openTime: '', closeTime: '', daysPerWeek: '', photoData: '', photoName: '', ownerName: '', ownerPhone: '', pharmacyPhone: '', ownerEmail: '', ownerPresent: '', followUp: '', callbackTime: '', footTraffic: '', stockLevel: '', potential: '', notes: '', nextStep: '', followUpDate: '', repNotes: '', brochure: '', landmark: '', status: 'submitted', date: new Date().toISOString().slice(0,10) };
      this.autosaveInterval = setInterval(() => { if (this.currentView === 'submit' && this.wizardStep > 1) this.autosaveDraft(); }, 30000);
    },
    async autosaveDraft() { if (!this.form.pharmacy) return; const existing = this.submissions.find(s => s.status === 'draft' && s.pharmacy === this.form.pharmacy); if (existing) await window.SpotterDB.putSubmission({ ...existing, ...this.form, updatedAt: new Date().toISOString() }); },
    validateStep(step) {
      const e = {};
      if (step === 1) { if (!this.form.town) e.town = 'Required'; if (!this.form.pharmacy) e.pharmacy = 'Required'; if (this.duplicateBlock) e.pharmacy = 'Exact duplicate — submission blocked'; if (!this.form.address) e.address = 'Required'; if (!this.gpsCoords) e.gps = 'Required'; if (!this.form.openTime) e.openTime = 'Required'; if (!this.form.closeTime) e.closeTime = 'Required'; if (!this.form.daysPerWeek) e.daysPerWeek = 'Required'; if (!this.form.photoData) e.photo = 'Required'; }
      if (step === 2) { if (!this.form.ownerName) e.ownerName = 'Required'; if (!this.form.ownerPhone) e.ownerPhone = 'Required'; if (!this.form.ownerPresent) e.ownerPresent = 'Required'; }
      if (step === 3) { if (!this.form.footTraffic) e.footTraffic = 'Required'; if (!this.form.stockLevel) e.stockLevel = 'Required'; if (!this.form.potential) e.potential = 'Required'; if (!this.form.followUp) e.followUp = 'Required'; }
      if (step === 4) { if (!this.form.nextStep) e.nextStep = 'Required'; if (this.form.nextStep && this.form.nextStep !== 'no_action' && !this.form.followUpDate) e.followUpDate = 'Required'; if (!this.form.brochure) e.brochure = 'Required'; }
      this.formErrors = e; return Object.keys(e).length === 0;
    },
    validateAndNext() { if (!this.validateStep(this.wizardStep)) return; if (this.wizardStep === 2 && this.form.ownerPresent === 'No') { this.syncMsg = 'Owner absent — saving as draft...'; setTimeout(() => { this.saveDraft(); this.syncMsg = ''; }, 1500); return; } this.wizardStep++; },
    canSubmit() { return [1,2,3,4].every(s => { const saved = this.formErrors; this.formErrors = {}; const ok = this.validateStep(s); this.formErrors = saved; return ok; }); },
    async handleSubmitForm() {
      if (!this.canSubmit()) return; this.submitting = true;
      if (this.autosaveInterval) { clearInterval(this.autosaveInterval); this.autosaveInterval = null; }
      const localId = crypto.randomUUID();
      const record = { localId, syncStatus: 'pending', createdAt: new Date().toISOString(), ...this.form, id: localId };
      await window.SpotterDB.putSubmission(record);
      await window.SpotterDB.addToQueue({ localId, type: 'submission', attempts: 0, lastAttempt: null });
      if (this.form.nextStep && this.form.nextStep !== 'no_action') { await window.SpotterDB.addFollowup({ submissionLocalId: localId, pharmacyName: this.form.pharmacy, nextStep: this.form.nextStep, followUpDate: this.form.followUpDate, repNotes: this.form.repNotes, status: 'open', createdAt: new Date().toISOString() }); this.followups = await window.SpotterDB.getAllFollowups(); }
      this.submissions = [record, ...this.submissions]; this.pendingCount++; this.submitting = false; this.submitSuccess = true;
      if (this.online) setTimeout(() => this.handleSync(), 500);
      setTimeout(() => { this.submitSuccess = false; this.currentView = 'home'; }, 2500);
    },
    async saveDraft() {
      if (this.autosaveInterval) { clearInterval(this.autosaveInterval); this.autosaveInterval = null; }
      const localId = crypto.randomUUID();
      const record = { localId, syncStatus: 'local', status: 'draft', createdAt: new Date().toISOString(), ...this.form, id: localId };
      await window.SpotterDB.putSubmission(record); this.submissions = [record, ...this.submissions]; this.currentView = 'home';
    },

    // ── Sync ─────────────────────────────────────────
    async handleSync() {
      if (!this.online || this.syncing) return; this.syncing = true; this.syncMsg = 'Syncing...';
      try {
        const result = await window.SpotterSync.run();
        this.pendingCount = await window.SpotterDB.countQueue();
        this.submissions = await window.SpotterDB.getAllSubmissions();
        this.submissions.sort((a,b) => b.createdAt > a.createdAt ? 1 : -1);
        let msg = `Sync complete — ${result.synced} uploaded`;
        if (result.conflicts > 0) msg += `, ${result.conflicts} held for review`;
        if (result.failed > 0) msg += `, ${result.failed} failed (will retry)`;
        if (result.updatesReceived > 0) msg += `, ${result.updatesReceived} status updates received`;
        this.syncMsg = msg; setTimeout(() => this.syncMsg = '', 6000);
        await this.fetchWardSubmissions();
      } catch(e) { this.syncMsg = 'Sync failed — will retry when online'; setTimeout(() => this.syncMsg = '', 4000); }
      this.syncing = false;
    },
    async fetchWardSubmissions() { try { const API = document.querySelector('meta[name=api-base]').content; const token = localStorage.getItem('dm_auth_token'); if (!token) return; const res = await fetch(`${API}/spotter/ward-submissions`, { headers: { 'Authorization': `Bearer ${token}` } }); if (res.ok) { const data = await res.json(); this.wardSubmissions = data.submissions || []; } } catch {} },

    // ── Overdue task check ───────────────────────────
    async checkOverdueTasks() { const tasks = await window.SpotterDB.getAllFollowups(); const now = Date.now(); let updated = false; for (const task of tasks) { if (task.status === 'open' && task.followUpDate) { const due = new Date(task.followUpDate).getTime() + (48*60*60*1000); if (now > due) { await window.SpotterDB.updateFollowup(task.id, { status: 'overdue' }); updated = true; } } } if (updated) this.followups = await window.SpotterDB.getAllFollowups(); },

    // ── Clear all data / Logout ──────────────────────
    async clearAllData() {
      if (this.isSupervisor()) {
        try { const API = document.querySelector('meta[name=api-base]').content; await fetch(`${API}/spotter/supervisor/logout`, { method: 'POST', headers: { 'Authorization': `Bearer ${localStorage.getItem('dm_auth_token')}` } }); } catch {}
      }
      localStorage.clear(); await window.SpotterDB.clearAll();
      this.activated = false; this.profile = { id: '', name: '', county: '', ward: '', salesRep: '', role: '' };
      this.clockedIn = false; this.clockRecord = null; this.submissions = []; this.followups = [];
      this.pendingCount = 0; this.confirmClearData = false; this.currentView = 'home';
      this.wardSubmissions = []; this.loginTab = 'spotter';
      this.supervisorStats = {}; this.supervisorSubmissions = []; this.supervisorDuplicates = [];
      this.supervisorFollowUps = []; this.supervisorAttendance = []; this.supervisorSpotters = [];
      this.leaderboardData = [];
    },

    // ── Computed helpers ─────────────────────────────
    greeting() { const h = new Date().getHours(); const time = h < 12 ? 'morning' : h < 17 ? 'afternoon' : 'evening'; return `Good ${time}, ${this.profile.name?.split(' ')[0] || 'Spotter'}`; },
    todayDate() { return new Date().toLocaleDateString('en-KE', { weekday:'long', year:'numeric', month:'long', day:'numeric' }); },
    today() { return new Date().toISOString().slice(0,10); },
    formatTime(iso) { if (!iso) return '—'; return new Date(iso).toLocaleTimeString('en-KE', { hour:'2-digit', minute:'2-digit' }); },
    activeDuration() { if (!this.clockRecord?.clockInAt) return ''; const mins = Math.floor((Date.now() - new Date(this.clockRecord.clockInAt)) / 60000); const h = Math.floor(mins / 60), m = mins % 60; return h > 0 ? `Active for ${h}h ${m}m` : `Active for ${m}m`; },
    todaySubmissions() { const today = new Date().toISOString().slice(0,10); return this.submissions.filter(s => s.date === today && s.status !== 'draft').length; },
    overdueCount() { return this.followups.filter(f => f.status === 'overdue').length; },
    overdueFollowups() { return this.followups.filter(f => f.status === 'overdue'); },
    daysOverdue(dateStr) { if (!dateStr) return 0; return Math.max(0, Math.floor((Date.now() - new Date(dateStr)) / 86400000)); },
    filteredSubmissions() { if (this.submissionFilter === 'all') return this.submissions; return this.submissions.filter(s => s.status === this.submissionFilter); },
    filteredTasks() { return this.followups.filter(f => f.status === this.taskFilter); },
    nextStepLabel(v) { return { sales_rep:'Schedule Sales Rep Visit', spotter_followup:'Spotter Follow-up', owner_absent:'Owner Absent — Return Visit', no_action:'No Further Action' }[v] || v; },
    heldStatusLabel(status) { return { held:'Held — Awaiting Sales Rep Review', sr_reviewed:'SR Reviewed — Awaiting CC Verification', cc_verified:'CC Verified — Awaiting Admin Acceptance' }[status] || null; },
    reviewSections() { return [ { step:1, label:'Location & Identity', fields:[{label:'Pharmacy',value:this.form.pharmacy},{label:'Town',value:this.form.town},{label:'Address',value:this.form.address},{label:'GPS',value:this.gpsCoords?`${this.gpsCoords.lat}, ${this.gpsCoords.lng}`:''},{label:'Hours',value:this.form.openTime&&this.form.closeTime?`${this.form.openTime}–${this.form.closeTime}`:''},{label:'Days/week',value:this.form.daysPerWeek},{label:'Photo',value:this.form.photoData?'Captured':''}]}, { step:2, label:'Ownership', fields:[{label:'Owner',value:this.form.ownerName},{label:'Phone',value:this.form.ownerPhone},{label:'Present',value:this.form.ownerPresent}]}, { step:3, label:'Engagement', fields:[{label:'Foot Traffic',value:this.form.footTraffic},{label:'Stock Level',value:this.form.stockLevel},{label:'Potential',value:this.form.potential},{label:'Follow-up',value:this.form.followUp}]}, { step:4, label:'Follow-up', fields:[{label:'Next Step',value:this.nextStepLabel(this.form.nextStep)},{label:'Date',value:this.form.followUpDate||(this.form.nextStep==='no_action'?'N/A':'')},{label:'Brochure',value:this.form.brochure}]} ]; },
    async confirmComplete(task) { await window.SpotterDB.updateFollowup(task.id, { status:'completed', completedAt:new Date().toISOString(), outcomeNote:this.completionNote }); this.followups = await window.SpotterDB.getAllFollowups(); this.completingTaskId = null; this.completionNote = ''; },

  }; // end spotterApp()
}
