function spotterApp() {
  return {
    activated: false,
    currentView: 'home',
    viewHistory: [],
    profile: { id: '', name: '', county: '', ward: '', salesRep: '' },
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
    confirmClearData: false,
    photoPreview: null,
    submitSuccess: false,
    submitting: false,
    wizardStep: 1,
    formErrors: {},

    form: {
      pharmacy: '', town: '', ward: '', county: '', address: '',
      lat: '', lng: '', gpsAccuracy: null,
      openTime: '', closeTime: '', daysPerWeek: '',
      photoData: '', photoName: '',
      ownerName: '', ownerPhone: '', pharmacyPhone: '', ownerEmail: '',
      ownerPresent: '', followUp: '', callbackTime: '',
      footTraffic: '', stockLevel: '', potential: '', notes: '',
      nextStep: '', followUpDate: '', repNotes: '', brochure: '',
      status: 'submitted', date: new Date().toISOString().slice(0,10)
    },

    async init() {
      try {
        var device = await window.SpotterDB.getDevice();
        if (device && device.activated) {
          this.activated = true;
          this.profile = device.profile || {};
        }

        var att = await window.SpotterDB.getTodayAttendance();
        if (att) { this.clockedIn = true; this.clockRecord = att; }

        this.submissions = await window.SpotterDB.getAllSubmissions();
        this.submissions.sort(function(a,b) { return b.createdAt > a.createdAt ? 1 : -1; });

        this.followups = await window.SpotterDB.getAllFollowups();
        this.pendingCount = await window.SpotterDB.countQueue();
      } catch(e) { console.warn('DB init error:', e); }

      var self = this;
      window.addEventListener('online', function() { self.online = true; self.handleSync(); });
      window.addEventListener('offline', function() { self.online = false; });

      if (this.online && this.pendingCount > 0) this.handleSync();
    },

    navigate: function(view) {
      if (view === 'submit' && !this.clockedIn) return;
      this.viewHistory.push(this.currentView);
      this.currentView = view;
      if (view === 'submit') this.resetWizard();
    },
    goBack: function() {
      this.currentView = this.viewHistory.pop() || 'home';
    },
    viewTitle: function() {
      var titles = { home:'SPOTTER', clock:'Attendance', submit:'New Submission', submissions:'My Submissions', tasks:'Follow-ups', settings:'Settings' };
      return titles[this.currentView] || 'SPOTTER';
    },

    formatActivationCode: function() {
      var v = this.activationCode.replace(/-/g,'').toUpperCase().slice(0,12);
      if (v.length > 8) v = v.slice(0,4)+'-'+v.slice(4,8)+'-'+v.slice(8);
      else if (v.length > 4) v = v.slice(0,4)+'-'+v.slice(4);
      this.activationCode = v;
    },
    handleActivate: async function() {
      var code = this.activationCode.replace(/-/g,'');
      if (code.length < 12) { this.activationError = 'Code must be 12 characters'; return; }
      this.activating = true;
      this.activationError = '';
      try {
        var API = document.querySelector('meta[name=api-base]').content;
        var res = await fetch(API + '/spotter/activate', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ code: this.activationCode, device_fingerprint: this.deviceFingerprint() })
        });
        var data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Activation failed');
        await window.SpotterDB.setDevice({
          activated: true,
          token: data.token,
          refreshToken: data.refresh_token,
          profile: data.profile
        });
        this.profile = data.profile;
        this.activated = true;
        this.currentView = 'home';
      } catch(e) {
        this.activationError = e.message || 'Invalid or expired activation code';
      } finally {
        this.activating = false;
      }
    },
    deviceFingerprint: function() {
      return btoa([navigator.userAgent, screen.width, screen.height].join('|')).slice(0,64);
    },

    handleClockIn: async function() {
      this.clockingIn = true;
      this.gpsStatus = 'Getting location...';
      var coords = await window.SpotterGPS.get();
      var record = {
        date: new Date().toISOString().slice(0,10),
        clockInAt: new Date().toISOString(),
        lat: coords ? coords.lat : null,
        lng: coords ? coords.lng : null,
        synced: false
      };
      var id = await window.SpotterDB.addAttendance(record);
      this.clockRecord = Object.assign({ id: id }, record);
      this.clockedIn = true;
      this.clockingIn = false;
      await window.SpotterDB.addToQueue({ type: 'attendance', data: record, attempts: 0 });
      this.pendingCount++;
    },
    handleClockOut: async function() {
      this.confirmingClockOut = false;
      var coords = await window.SpotterGPS.get();
      var updates = {
        clockOutAt: new Date().toISOString(),
        outLat: coords ? coords.lat : null,
        outLng: coords ? coords.lng : null
      };
      await window.SpotterDB.updateAttendance(this.clockRecord.id, updates);
      this.clockRecord = Object.assign({}, this.clockRecord, updates);
      await window.SpotterDB.addToQueue({ type: 'attendance', data: this.clockRecord, attempts: 0 });
      this.pendingCount++;
      this.currentView = 'home';
    },

    getGPS: async function() {
      this.gettingGPS = true;
      this.gpsStatus = 'Getting location...';
      var coords = await window.SpotterGPS.get();
      if (coords) {
        this.gpsCoords = coords;
        this.form.lat = coords.lat;
        this.form.lng = coords.lng;
        this.form.gpsAccuracy = coords.accuracy;
        this.gpsStatus = 'Location captured';
      } else {
        this.gpsStatus = 'Location unavailable — tap to retry';
      }
      this.gettingGPS = false;
    },

    handlePhotoCapture: async function(event) {
      var file = event.target.files[0];
      if (!file) return;
      try {
        var result = await window.SpotterCamera.compress(file);
        this.form.photoData = result.base64;
        this.form.photoName = result.filename;
        this.photoPreview = result.dataUrl;
      } catch(e) { console.error('Photo compression failed:', e); }
    },

    checkDuplicate: async function() {
      if (!this.form.pharmacy || this.form.pharmacy.length < 3) return;
      var result = await window.SpotterDuplicate.check(this.form.pharmacy, this.profile.ward);
      this.duplicateWarning = result ? result.match : '';
    },

    resetWizard: function() {
      this.wizardStep = 1;
      this.submitSuccess = false;
      this.formErrors = {};
      this.gpsCoords = null;
      this.photoPreview = null;
      this.duplicateWarning = '';
      this.form = {
        pharmacy: '', town: '', ward: this.profile.ward || '', county: this.profile.county || '',
        address: '', lat: '', lng: '', gpsAccuracy: null,
        openTime: '', closeTime: '', daysPerWeek: '',
        photoData: '', photoName: '',
        ownerName: '', ownerPhone: '', pharmacyPhone: '', ownerEmail: '',
        ownerPresent: '', followUp: '', callbackTime: '',
        footTraffic: '', stockLevel: '', potential: '', notes: '',
        nextStep: '', followUpDate: '', repNotes: '', brochure: '',
        status: 'submitted', date: new Date().toISOString().slice(0,10)
      };
    },
    validateStep: function(step) {
      var e = {};
      if (step === 1) {
        if (!this.form.town) e.town = 'Required';
        if (!this.form.pharmacy) e.pharmacy = 'Required';
        if (!this.form.address) e.address = 'Required';
        if (!this.gpsCoords) e.gps = 'Required';
        if (!this.form.openTime) e.openTime = 'Required';
        if (!this.form.closeTime) e.closeTime = 'Required';
        if (!this.form.daysPerWeek) e.daysPerWeek = 'Required';
        if (!this.form.photoData) e.photo = 'Required';
      }
      if (step === 2) {
        if (!this.form.ownerName) e.ownerName = 'Required';
        if (!this.form.ownerPhone) e.ownerPhone = 'Required';
        if (!this.form.ownerPresent) e.ownerPresent = 'Required';
      }
      if (step === 3) {
        if (!this.form.footTraffic) e.footTraffic = 'Required';
        if (!this.form.stockLevel) e.stockLevel = 'Required';
        if (!this.form.potential) e.potential = 'Required';
        if (!this.form.followUp) e.followUp = 'Required';
      }
      if (step === 4) {
        if (!this.form.nextStep) e.nextStep = 'Required';
        if (this.form.nextStep && this.form.nextStep !== 'no_action' && !this.form.followUpDate) e.followUpDate = 'Required';
        if (!this.form.brochure) e.brochure = 'Required';
      }
      this.formErrors = e;
      return Object.keys(e).length === 0;
    },
    validateAndNext: function() {
      if (this.validateStep(this.wizardStep)) this.wizardStep++;
    },
    canSubmit: function() {
      var self = this;
      var saved = self.formErrors;
      var ok = true;
      [1,2,3,4].forEach(function(s) {
        self.formErrors = {};
        if (!self.validateStep(s)) ok = false;
      });
      self.formErrors = saved;
      return ok;
    },
    handleSubmitForm: async function() {
      if (!this.canSubmit()) return;
      this.submitting = true;
      var localId = crypto.randomUUID();
      var record = Object.assign({ localId: localId, syncStatus: 'pending', createdAt: new Date().toISOString(), id: localId }, this.form);
      await window.SpotterDB.putSubmission(record);
      await window.SpotterDB.addToQueue({ localId: localId, type: 'submission', attempts: 0, lastAttempt: null });

      if (this.form.nextStep && this.form.nextStep !== 'no_action') {
        await window.SpotterDB.addFollowup({
          submissionLocalId: localId,
          pharmacyName: this.form.pharmacy,
          nextStep: this.form.nextStep,
          followUpDate: this.form.followUpDate,
          repNotes: this.form.repNotes,
          status: 'open',
          createdAt: new Date().toISOString()
        });
        this.followups = await window.SpotterDB.getAllFollowups();
      }

      this.submissions = [record].concat(this.submissions);
      this.pendingCount++;
      this.submitting = false;
      this.submitSuccess = true;

      var self = this;
      if (this.online) setTimeout(function() { self.handleSync(); }, 500);
      setTimeout(function() { self.submitSuccess = false; self.currentView = 'home'; }, 2500);
    },
    saveDraft: async function() {
      var localId = crypto.randomUUID();
      var record = Object.assign({ localId: localId, syncStatus: 'local', status: 'draft', createdAt: new Date().toISOString(), id: localId }, this.form);
      await window.SpotterDB.putSubmission(record);
      this.submissions = [record].concat(this.submissions);
      this.currentView = 'home';
    },

    handleSync: async function() {
      if (!this.online || this.syncing) return;
      this.syncing = true;
      this.syncMsg = 'Syncing...';
      var self = this;
      try {
        var result = await window.SpotterSync.run();
        self.pendingCount = await window.SpotterDB.countQueue();
        self.submissions = await window.SpotterDB.getAllSubmissions();
        self.submissions.sort(function(a,b) { return b.createdAt > a.createdAt ? 1 : -1; });
        var msg = 'Sync complete — ' + result.synced + ' uploaded';
        if (result.conflicts > 0) msg += ', ' + result.conflicts + ' held for review';
        if (result.failed > 0) msg += ', ' + result.failed + ' failed';
        self.syncMsg = msg;
        setTimeout(function() { self.syncMsg = ''; }, 6000);
      } catch(e) {
        self.syncMsg = 'Sync failed — will retry when online';
        setTimeout(function() { self.syncMsg = ''; }, 4000);
      }
      self.syncing = false;
    },

    clearAllData: async function() {
      await window.SpotterDB.clearAll();
      this.activated = false;
      this.profile = { id:'', name:'', county:'', ward:'', salesRep:'' };
      this.submissions = [];
      this.followups = [];
      this.clockedIn = false;
      this.clockRecord = null;
      this.pendingCount = 0;
      this.confirmClearData = false;
      this.currentView = 'home';
    },

    greeting: function() {
      var h = new Date().getHours();
      var time = h < 12 ? 'morning' : h < 17 ? 'afternoon' : 'evening';
      return 'Good ' + time + ', ' + (this.profile.name ? this.profile.name.split(' ')[0] : 'Spotter');
    },
    todayDate: function() {
      return new Date().toLocaleDateString('en-KE', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
    },
    today: function() { return new Date().toISOString().slice(0,10); },
    formatTime: function(iso) {
      if (!iso) return '—';
      return new Date(iso).toLocaleTimeString('en-KE', { hour:'2-digit', minute:'2-digit' });
    },
    activeDuration: function() {
      if (!this.clockRecord || !this.clockRecord.clockInAt) return '';
      var mins = Math.floor((Date.now() - new Date(this.clockRecord.clockInAt)) / 60000);
      var h = Math.floor(mins / 60), m = mins % 60;
      return h > 0 ? 'Active for ' + h + 'h ' + m + 'm' : 'Active for ' + m + 'm';
    },
    todaySubmissions: function() {
      var today = new Date().toISOString().slice(0,10);
      return this.submissions.filter(function(s) { return s.date === today && s.status !== 'draft'; }).length;
    },
    overdueCount: function() { return this.followups.filter(function(f) { return f.status === 'overdue'; }).length; },
    overdueFollowups: function() { return this.followups.filter(function(f) { return f.status === 'overdue'; }); },
    daysOverdue: function(dateStr) {
      if (!dateStr) return 0;
      return Math.max(0, Math.floor((Date.now() - new Date(dateStr)) / 86400000));
    },
    filteredSubmissions: function() {
      var f = this.submissionFilter;
      if (f === 'all') return this.submissions;
      return this.submissions.filter(function(s) { return s.status === f; });
    },
    filteredTasks: function() {
      var f = this.taskFilter;
      return this.followups.filter(function(t) { return t.status === f; });
    },
    nextStepLabel: function(v) {
      var labels = { sales_rep:'Schedule Sales Rep Visit', spotter_followup:'Spotter Follow-up', owner_absent:'Owner Absent — Return Visit', no_action:'No Further Action' };
      return labels[v] || v;
    },
    reviewSections: function() {
      return [
        { step:1, label:'Location & Identity', fields:[
          {label:'Pharmacy', value: this.form.pharmacy},
          {label:'Town', value: this.form.town},
          {label:'Address', value: this.form.address},
          {label:'GPS', value: this.gpsCoords ? this.gpsCoords.lat+', '+this.gpsCoords.lng : ''},
          {label:'Hours', value: this.form.openTime && this.form.closeTime ? this.form.openTime+'–'+this.form.closeTime : ''},
          {label:'Days/week', value: this.form.daysPerWeek},
          {label:'Photo', value: this.form.photoData ? 'Captured' : ''}
        ]},
        { step:2, label:'Ownership', fields:[
          {label:'Owner', value: this.form.ownerName},
          {label:'Phone', value: this.form.ownerPhone},
          {label:'Present', value: this.form.ownerPresent}
        ]},
        { step:3, label:'Engagement', fields:[
          {label:'Foot Traffic', value: this.form.footTraffic},
          {label:'Stock Level', value: this.form.stockLevel},
          {label:'Potential', value: this.form.potential},
          {label:'Follow-up', value: this.form.followUp}
        ]},
        { step:4, label:'Follow-up', fields:[
          {label:'Next Step', value: this.nextStepLabel(this.form.nextStep)},
          {label:'Date', value: this.form.followUpDate || (this.form.nextStep === 'no_action' ? 'N/A' : '')},
          {label:'Brochure', value: this.form.brochure}
        ]}
      ];
    },
    confirmComplete: async function(task) {
      await window.SpotterDB.updateFollowup(task.id, {
        status: 'completed',
        completedAt: new Date().toISOString(),
        outcomeNote: this.completionNote
      });
      this.followups = await window.SpotterDB.getAllFollowups();
      this.completingTaskId = null;
      this.completionNote = '';
    }
  };
}
