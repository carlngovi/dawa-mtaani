window.SpotterGPS = {
  get: function(timeoutMs) {
    timeoutMs = timeoutMs || 8000;
    return new Promise(function(resolve) {
      if (!navigator.geolocation) { resolve(null); return; }
      var timer = setTimeout(function() { resolve(null); }, timeoutMs);
      navigator.geolocation.getCurrentPosition(
        function(pos) {
          clearTimeout(timer);
          resolve({
            lat: pos.coords.latitude.toFixed(6),
            lng: pos.coords.longitude.toFixed(6),
            accuracy: Math.round(pos.coords.accuracy)
          });
        },
        function() { clearTimeout(timer); resolve(null); },
        { enableHighAccuracy: true, timeout: timeoutMs, maximumAge: 0 }
      );
    });
  }
};
