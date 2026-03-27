window.SpotterCamera = {
  compress: function(file, maxPx, targetKB) {
    maxPx = maxPx || 1200;
    targetKB = targetKB || 300;
    return new Promise(function(resolve, reject) {
      var reader = new FileReader();
      reader.onload = function(e) {
        var img = new Image();
        img.onload = function() {
          var canvas = document.createElement('canvas');
          var w = img.width, h = img.height;
          if (w > maxPx || h > maxPx) {
            if (w > h) { h = Math.round(h * maxPx / w); w = maxPx; }
            else { w = Math.round(w * maxPx / h); h = maxPx; }
          }
          canvas.width = w;
          canvas.height = h;
          canvas.getContext('2d').drawImage(img, 0, 0, w, h);
          var quality = 0.85;
          var dataUrl = canvas.toDataURL('image/jpeg', quality);
          while (dataUrl.length > targetKB * 1024 * 1.37 && quality > 0.3) {
            quality -= 0.05;
            dataUrl = canvas.toDataURL('image/jpeg', quality);
          }
          resolve({
            base64: dataUrl.split(',')[1],
            dataUrl: dataUrl,
            filename: file.name
          });
        };
        img.onerror = reject;
        img.src = e.target.result;
      };
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  }
};
